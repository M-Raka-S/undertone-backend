<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(Project::class);
    }

    public function show($page)
    {
        return $this->read($page);
    }

    public function pick($id)
    {
        return $this->get($id);
    }

    public function make()
    {
        $this->validator([
            'name' => 'required',
            'hidden_categories' => 'nullable|array',
        ], ['name', 'hidden_categories']);
        $created = $this->create([], true);
        $created->attachUser(auth()->user());
        return $created ? $this->created('project created.') : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'name' => 'required',
            'hidden_categories' => 'nullable|array',
        ]);
        return $this->update($id) ? $this->ok('project updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        return $this->delete($id);
    }

    public function updateHiddenCategories($id)
    {
        $this->validator(
            [
                'action' => 'required|in:add,remove',
                'hidden_categories' => 'required|array',
            ],
            ['action', 'hidden_categories'],
        );
        $project = $this->get($id);
        $existingCategories = $project->hidden_categories ?? [];
        $action = $this->request->action;
        $categories = $this->request->hidden_categories;
        if ($action === 'add') {
            $newCategories = array_merge($existingCategories, $categories);
            $newCategories = array_unique($newCategories);
            $project->hidden_categories = $newCategories;
            $message = 'Hidden categories added.';
        } elseif ($action === 'remove') {
            $newCategories = array_diff($existingCategories, $categories);
            $project->hidden_categories = $newCategories;
            $message = 'Hidden categories removed.';
        }
        $project->save();
        return $this->ok($message);
    }
}
