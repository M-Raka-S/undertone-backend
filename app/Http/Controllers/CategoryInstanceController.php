<?php

namespace App\Http\Controllers;

use App\Jobs\createParametersForInstance;
use App\Models\CategoryInstance;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class CategoryInstanceController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(CategoryInstance::class);
    }

    protected function filterByOwnership($query)
    {
        $currentUserId = auth()->user()->id;
        return $query->whereHas('project.users', function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        });
    }

    public function show($page)
    {
        return $this->unauthorized('access instances list from a project instead.');
    }

    public function pick($id)
    {
        return $this->get($id, ['parameters.parameter', 'category', 'media']);
    }

    public function make()
    {
        $this->validator([
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'required|exists:projects,id',
        ], ['category_id', 'project_id']);
        $project = $this->checkExists($this->request->project_id, Project::class);
        if (!$this->checkPrivilege($project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to create components.');
        }
        $instance = $this->create([], true);
        if ($instance) {
            createParametersForInstance::dispatchSync($instance);
        }
        return $instance ? $this->created('instance created.', $instance->id) : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'summary' => 'nullable',
        ]);
        $instance = $this->checkExists($id);
        if (!$this->checkPrivilege($instance->project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to edit components.');
        }
        return $this->update($id, ['category_id', 'project_id']) ? $this->ok('instance updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        $instance = $this->checkExists($id);
        if ($instance) {
            if (!$this->checkPrivilege($instance->project, ['lead author', 'author'])) {
                return $this->unauthorized('you do not have permission to delete components.');
            }
        }
        File::deleteDirectory(public_path("/storage/uploads/instance/$id"));
        return $this->delete($id);
    }

    public function search()
    {
        $this->validator([
            'term' => 'nullable',
        ], ['term']);

        $term = $this->request->term;

        $matchingInstances = CategoryInstance::whereHas('parameters', function ($query) use ($term) {
            $query->whereHas('parameter', function ($q) {
                $q->where('identifier', true);
            });
            $query->where('value', 'like', "%{$term}%");
        })->get();

        $result = $matchingInstances->map(function ($instance) {
            return [
                'id' => $instance->id,
                'label' => $instance->identifier_value["value"],
            ];
        });

        return $result;
    }

    public function generateSummary($id)
    {
        $instance = $this->checkExists($id);
        $type = $instance->getIdentifierValueAttribute()["parameter_name"];
        $string = "Generate a one paragraph summary for the $type. Do not use any styling other than standard punctuation.\n";

        foreach ($instance->parameters as $parameter) {
            $value = $parameter->value ?? 'N/A';
            $summaries = Arr::join($this->extractMentionSummaries($value), '\n');
            $cleanValue = strip_tags($value);
            $string .= $parameter->parameter->name . ": " . $cleanValue . "\n";
            $name = $parameter->parameter->name;
            if (!empty($mentionSummaries)) {
                $string .= "Context mentioned in $name:\n" . $summaries . "\n";
            }
        }

        return $instance->update([
            'summary' => AIController::generate($string),
        ]) ? $this->ok('summary generated') : $this->invalid('failed to generate summary');
    }
}
