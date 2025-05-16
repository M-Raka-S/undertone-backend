<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Parameter;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->setModel(Category::class);
    }

    public function show($page)
    {
        return $this->read($page);
    }

    public function pick($id)
    {
        return $this->get($id, ['parameters']);
    }

    public function make()
    {
        $this->validator([
            'name' => 'required|unique:categories',
        ], ['name']);
        return $this->create() ? $this->created('category created.') : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $this->validator([
            'name' => "required|unique:categories,name,{$id}",
        ]);
        return $this->update($id) ? $this->ok('category updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        return Parameter::where('category_id', $id)->doesntExist() ? $this->delete($id) : $this->conflict('there are parameters attached to this category.');
    }
}
