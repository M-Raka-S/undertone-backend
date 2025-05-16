<?php

namespace App\Http\Controllers;

use App\Jobs\createParametersForInstance;
use App\Models\CategoryInstance;
use App\Models\InstanceParameter;
use Illuminate\Http\Request;

class CategoryInstanceController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(CategoryInstance::class);
    }

    public function show($page)
    {
        return $this->unauthorized('access instances list from a project instead.');
    }

    public function pick($id)
    {
        return $this->get($id, ['parameters']);
    }

    public function make()
    {
        $this->validator([
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'required|exists:projects,id',
        ], ['category_id', 'project_id']);
        $instance = $this->create([], true);
        if ($instance) {
            createParametersForInstance::dispatch($instance);
        }
        return $instance ? $this->created('instance created.', $instance->id) : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'summarisation' => 'nullable',
        ]);
        return $this->update($id, ['category_id', 'project_id']) ? $this->ok('instance updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        return InstanceParameter::where('instance_id', $id)->doesntExist() ? $this->delete($id) : $this->conflict('there are parameters attached to this instance.');
    }
}
