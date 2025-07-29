<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules\Enum;

class ProjectController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(Project::class);
    }

    protected function filterByOwnership($query)
    {
        $current = auth()->user()->id;
        return $query->whereHas('users', function ($q) use ($current) {
            $q->where('user_id', $current);
        });
    }

    public function show($page)
    {
        return $this->read($page, 9, with: ["users", "categoryInstances"]);
    }

    public function all()
    {
        return $this->getAll(["users", "categoryInstances"]);
    }

    public function pick($id)
    {
        return $this->get($id, ["chapters", "media", "categoryInstances.category"]);
    }

    public function make()
    {
        $this->validator([
            'name' => 'required',
            'summary' => 'nullable',
        ], ['name', 'summary']);
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
        if (!$this->checkPrivilege($project, ['lead author', 'project manager'])) {
            return $this->unauthorized('you do not have permission to add members');
        }
        $project->attachUser($user);
        return $project ? $this->created("$user->username added to $project->name.") : $this->invalid('addition failed.');
    }

    public function edit($id)
    {
        $project = $this->checkExists($id);
        if (!$this->checkPrivilege($project, ['lead author', 'project manager'])) {
            return $this->unauthorized('you do not have permission to edit the project');
        }
        $this->validator([
            'name' => 'required',
            'summary' => 'nullable',
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
        if ($roleEnum->value == "leadauthor" && auth()->user()->getRoleInfo($project)['name'] != "lead author") {
            return $this->unauthorized('only lead authors can assign other lead authors');
        }
        if ($project->users()->where('user_id', $user->id)->doesntExist()) {
            return $this->notFound("{$user->username} is not a member of {$project->name}.");
        }
        if ($user->getRoleInfo($project)['name'] == "lead author") {
            if ($project->users()->wherePivot('role', Roles::LEADAUTHOR->value)->count() - 1 == 0) {
                return $this->unauthorized('the project must have at least one lead author.');
            }
        }
        if (auth()->user()->id == $user_id) {
            return $this->unauthorized('you can not change your own role in a project.');
        }
        $project->updateUserRole($user, $roleEnum->value);
        $roleName = $roleEnum->info()['name'];
        return $project ? $this->ok("$user->username changed to $roleName.") : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        $project = $this->checkExists($id);
        if (!$this->checkPrivilege($project, ['lead author'])) {
            return $this->unauthorized('only lead authors can delete a project');
        }
        File::deleteDirectory(public_path("/storage/uploads/project/$id"));
        File::deleteDirectory(public_path("/storage/uploads/instance/$id"));
        File::deleteDirectory(public_path("/storage/chapters/$id"));
        return $this->delete($id);
    }

    public function removeUser($id, $user_id)
    {
        $project = $this->checkExists($id);
        $user = $this->checkExists($user_id, User::class);
        if ($project->users()->where('user_id', $user->id)->doesntExist()) {
            return $this->notFound("{$user->username} is not a member of {$project->name}.");
        }
        if (auth()->user()->id == $user_id) {
            return $this->unauthorized('you can not remove yourself from a project.');
        }
        if ($user->getRoleInfo($project)['name'] == "lead author") {
            if ($project->users()->wherePivot('role', Roles::LEADAUTHOR->value)->count() - 1 == 0) {
                return $this->unauthorized('the project must have at least one lead author.');
            }
        }
        if (!$this->checkPrivilege($project, ['lead author', 'project manager'])) {
            return $this->unauthorized('you do not have permission to remove members');
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

    public function getUsers($id)
    {
        $project = $this->checkExists($id);
        return $project->users;
    }

    public function getCandidates($id)
    {
        $this->checkExists($id);
        return User::whereDoesntHave('projects', function ($q) use ($id) {
            $q->where('projects.id', $id);
        })->get();
    }

    public function getRole($id) {
        $project = $this->checkExists($id);
        $user = $project->users()->find(auth()->user()->id);
        if(!$user) {
            return $this->unauthorized('you are not part of this project.');
        }
        return $user->getRoleInfo($project);
    }
}
