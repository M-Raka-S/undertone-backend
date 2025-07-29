<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateChapterSummary;
use App\Models\Chapter;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(Chapter::class);
    }

    public function show($page)
    {
        return $this->unauthorized('access chapters list from a project instead.');
    }

    public function pick($id)
    {
        $this->checkExists($id);
        $chapter = $this->get($id, [
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user:id,username', 'replies']);
            }
        ]);

        $content = Storage::disk('public')->exists($chapter->path)
            ? Storage::disk('public')->get($chapter->path)
            : null;

        return response()->json([
            'chapter' => $chapter,
            'content' => $content,
        ]);
    }

    public function make()
    {
        $this->validator([
            'name' => 'required',
            'project_id' => 'required|exists:projects,id',
        ], ['name', 'project_id']);
        $project = $this->checkExists($this->request->project_id, Project::class);
        $order_level = $project->chapters()->max('order_level') ?? 0;
        $filename = uniqid('chapter_') . '.txt';
        $path = "chapters/$project->id/$filename";
        Storage::disk('public')->put($path, '');

        $username = auth()->user()->username;
        $storageDir = storage_path("app/public/chapters/$project->id");
        $filenameEscaped = escapeshellarg($filename);
        $commitMessage = escapeshellarg("Create empty chapter: {$this->request->name}");
        exec("cd {$storageDir} && git config user.name undertone");
        exec("cd {$storageDir} && git config user.email app@undertone.com");
        if (!is_dir($storageDir . '/.git')) {
            exec("cd {$storageDir} && git init -b main");
        }
        exec("cd {$storageDir} && git add {$filenameEscaped} && git commit -m {$commitMessage} --author=\"{$username} <>\"", result_code: $returnVar);
        if ($returnVar !== 0) {
            unlink("$storageDir/$filename");
            return $this->invalid('failed to handle git.');
        }

        $this->request->merge([
            'order_level' => $order_level + 1,
            'path' => $path,
        ]);

        return $this->create() ? $this->created('chapter created.') : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $chapter = $this->checkExists($id);
        if (!$this->checkPrivilege($chapter->project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to edit the chaoter');
        }
        $this->validator([
            'name' => 'required',
            'order_level' => 'required',
        ]);
        return $this->update($id, ['path', 'project_id']) ? $this->ok('chapter updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        $chapter = $this->checkExists($id);
        $storageDir = storage_path("app/public/$chapter->path");
        unlink($storageDir);
        if (!$this->checkPrivilege($chapter->project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to delete the chapter');
        }
        return $this->delete($id);
    }

    public function updateContent($id)
    {
        $chapter = $this->checkExists($id);
        $project = $chapter->project;
        if (!$this->checkPrivilege($project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to update the chapter');
        }
        $this->validator([
            'content' => 'required',
        ], ['content']);

        $content = $this->request->content;
        $storageDir = storage_path("app/public/chapters/$project->id");
        $filePath = $storageDir . '/' . basename($chapter->path);

        $summaries = Arr::join($this->extractMentionSummaries($content), '\n');
        $string = strip_tags($content) . "\n\nGenerate a chapter summary for this. The summary should cover only the events of the chapter beat-by-beat and an overall tone. Present the summary as paragraphs withou titling or division. Do not use any styling other than standard punctuation. Context mentioned:\n$summaries";

        if(!$chapter->summary) {
            GenerateChapterSummary::dispatch($id, $string);
        }

        file_put_contents($filePath, $content);

        $commitMessage = escapeshellarg("Update chapter: {$chapter->name}");
        $username = auth()->user()->username;

        exec("cd {$storageDir} && git config user.name " . escapeshellarg($username));
        exec("cd {$storageDir} && git config user.email " . escapeshellarg($username) . "@undertone.com");
        exec("cd {$storageDir} && git add " . escapeshellarg(basename($chapter->path)));
        exec("cd {$storageDir} && git diff --cached --quiet || git commit -m " . escapeshellarg($commitMessage) . " --author=\"" . escapeshellarg($username) . " <" . escapeshellarg($username) . "@undertone.com>\"", $output, $returnVar);
        if ($returnVar !== 0) {
            $outputString = implode("\n", $output);
            if (strpos($outputString, 'nothing to commit') === false) {
                return $this->invalid('failed to handle git commit.');
            }
        }

        return $this->ok('chapter content updated.');
    }

    public function generateContent($id)
    {
        $this->checkExists($id);
        $chapter = $this->get($id, [
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user:id,username', 'replies']);
            }
        ]);

        $content = Storage::disk('public')->exists($chapter->path)
            ? Storage::disk('public')->get($chapter->path)
            : null;

        $summaries = Arr::join($this->extractMentionSummaries($content), '\n');

        $before = $this->request->before ?? "";
        $string = "before: $before\ncontext mentioned:\n$summaries\nGenerate paragraphs of a story, including dialogue, between before and after. The content must strictly follow up the events in \"before,\" focusing only on the immediate preceding moments and interactions. The narrative should maintain the established character dynamics and tone, ensuring a seamless transition. Only generate new content, do not include any parts of the before and after. Do not include any other text other than the new content. Do not use any styling other than standard punctuation.";
        $string = trim(AIController::generate($string));

        $string = str_replace(["\r\n", "\r"], "\n", $string);
        $paragraphs = preg_split("/\n{2,}/", $string);
        $processedParagraphs = [];
        foreach ($paragraphs as $paragraph) {
            $processedParagraphs[] = trim($paragraph);
        }

        return $processedParagraphs;
    }
}
