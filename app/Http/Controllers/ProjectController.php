<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

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
        $created->attachUser(auth()->user(), 'leadauthor');
        return $created ? $this->created('project created.') : $this->invalid('creation failed.');
    }

    public function addUser($id, $user_id)
    {
        $project = $this->checkExists($id);
        $user = $this->checkExists($user_id, User::class);
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return $this->conflict("{$user->username} is already a member of {$project->name}.");
        }
        $project->attachUser($user);
        return $project ? $this->created("$user->username added to $project->name.") : $this->invalid('addition failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'name' => 'required',
            'hidden_categories' => 'nullable|array',
        ]);
        return $this->update($id) ? $this->ok('project updated.') : $this->invalid('update failed.');
    }

    public function editUserRole($id, $user_id)
    {
        $this->validator([
            'role' => ['required', new Enum(Roles::class)],
        ], ['role']);
        $project = $this->checkExists($id);
        $user = $this->checkExists($user_id, User::class);
        $roleEnum = Roles::from($this->request->role);
        if ($project->users()->where('user_id', $user->id)->doesntExist()) {
            return $this->notFound("{$user->username} is not a member of {$project->name}.");
        }
        $project->updateUserRole($user, $roleEnum->value);
        $roleName = $roleEnum->info()['name'];
        return $project ? $this->ok("$user->username changed to $roleName.") : $this->invalid('update failed.');
    }


    public function remove($id)
    {
        return $this->delete($id);
    }

    public function removeUser($id, $user_id)
    {
        $project = $this->checkExists($id);
        $user = $this->checkExists($user_id, User::class);
        if ($project->users()->where('user_id', $user->id)->doesntExist()) {
            return $this->notFound("{$user->username} is not a member of {$project->name}.");
        }
        $project->detachUser($user);
        return $project ? $this->ok("$user->username removed from $project->name.") : $this->invalid('removal failed.');
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
        $action = request('action');
        $categories = request('hidden_categories');

        if ($action === 'add') {
            $newCategories = array_merge($existingCategories, $categories);
            $newCategories = array_unique($newCategories);
        } else {
            $newCategories = array_values(array_diff($existingCategories, $categories));
        }

        $project->update(['hidden_categories' => $newCategories]);

        return $this->ok('Hidden categories updated.');
    }
}
