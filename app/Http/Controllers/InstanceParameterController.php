<?php

namespace App\Http\Controllers;

use App\Models\InstanceParameter;
use Illuminate\Http\Request;

class InstanceParameterController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->setModel(InstanceParameter::class);
    }

    public function show($page)
    {
        return $this->unauthorized('access parameters list from an instance instead.');
    }

    public function pick($id)
    {
        return $this->get($id);
    }

    public function make()
    {
        $this->validator([
            'value' => 'nullable',
            'parameter_id' => 'required|exists:parameters,id',
            'instance_id' => 'required|exists:category_instances,id',
        ], ['value', 'parameter_id', 'instance_id']);
        return $this->create() ? $this->created('parameter created.') : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'value' => 'nullable',
        ]);
        return $this->update($id, ['parameter_id', 'instance_id']) ? $this->ok('parameter updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        return $this->delete($id);
    }
}
