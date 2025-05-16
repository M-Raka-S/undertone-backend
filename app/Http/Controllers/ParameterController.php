<?php

namespace App\Http\Controllers;

use App\Models\InstanceParameter;
use App\Models\Parameter;
use Illuminate\Http\Request;

class ParameterController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->setModel(Parameter::class);
    }

    public function show($page)
    {
        return $this->unauthorized('access parameters list from a category instead.');
    }

    public function pick($id)
    {
        return $this->get($id);
    }

    public function make()
    {
        $this->validator([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id',
        ], ['name', 'category_id']);
        return $this->create() ? $this->created('parameter created.') : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'name' => 'required',
        ]);
        return $this->update($id, ['category_id']) ? $this->ok('parameter updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        return InstanceParameter::where('parameter_id', $id)->doesntExist() ? $this->delete($id) : $this->conflict('there are instances attached to this parameter.');
    }
}
