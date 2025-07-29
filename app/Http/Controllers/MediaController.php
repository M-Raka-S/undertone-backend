<?php

namespace App\Http\Controllers;

use App\Models\CategoryInstance;
use App\Models\Media;
use App\Models\Project;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(Media::class);
    }

    protected function filterByOwnership($query)
    {
        $currentUserId = auth()->id();

        return $query->where(function ($q) use ($currentUserId) {
            $q->whereHas('project.users', function ($q2) use ($currentUserId) {
                $q2->where('users.id', $currentUserId);
            })
                ->orWhereHas('instance.project.users', function ($q2) use ($currentUserId) {
                    $q2->where('users.id', $currentUserId);
                });
        });
    }

    public function show($page)
    {
        return $this->unauthorized("this behaviour is disabled. access media through project or instance instead.");
    }

    public function all()
    {
        return $this->unauthorized("this behaviour is disabled. access media through project or instance instead.");
    }

    public function pick($id)
    {
        return $this->unauthorized("this behaviour is disabled. access media through project or instance instead.");
    }

    public function make()
    {
        $this->validator([
            'path' => 'required|file|mimes:jpg,jpeg,png',
            'instance_id' => 'nullable|exists:category_instances,id|required_without:project_id',
            'project_id' => 'nullable|exists:projects,id|required_without:instance_id',
        ], ['path', 'instance_id', 'project_id']);
        if ($this->request->filled('project_id')) {
            $project = $this->checkExists($this->request->project_id, Project::class);
        } elseif ($this->request->filled('instance_id')) {
            $instance = CategoryInstance::with('project')->findOrFail($this->request->input('instance_id'));
            $project = $instance->project;
        } else {
            return $this->unauthorized("invalid connection type.");
        }
        if (!$this->checkPrivilege($project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to add images');
        }
        $data = $this->request->except(['path']);
        if ($this->request->hasFile('path')) {
            $file = $this->request->file('path');
            if ($this->request->filled('project_id')) {
                $folder = 'uploads/project/' . $this->request->input('project_id');
            } elseif ($this->request->filled('instance_id')) {
                $folder = 'uploads/instance/' . $this->request->input('instance_id');
            } else {
                return $this->unauthorized("invalid connection type.");
            }
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $storedPath = $file->storeAs($folder, $filename, 'public');
            $data['path'] = $storedPath;
        } else {
            return $this->invalid('No file uploaded.');
        }
        $created = $this->model::create($data);
        return $created ? $this->created('media created.') : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        return $this->unauthorized("this behaviour is disabled.");
    }

    public function remove($id)
    {
        $media = $this->checkExists($id);
        $storageDir = storage_path("app/public/$media->path");
        if (!filter_var($media->path, FILTER_VALIDATE_URL)) {
            unlink($storageDir);
        }
        if (!$this->checkPrivilege($media->project, ['lead author', 'author'])) {
            return $this->unauthorized('you do not have permission to delete the image');
        }
        return $this->delete($id);
    }
}
