<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

abstract class Controller
{
    protected Request $request;
    protected $model;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function setModel($model)
    {
        $this->model = new $model;
    }

    protected function merge($key, $value)
    {
        $this->request->merge([
            $key => $value,
        ]);
    }

    protected function validator(array $rules, array $include = null)
    {
        $include = $include ?? array_keys($this->request->except('_method'));
        $filteredRules = empty($include) ? [] : array_intersect_key($rules, array_flip($include));
        $validator = validator()->make($this->request->all(), $filteredRules);
        if ($validator->fails()) {
            return $this->invalid($validator);
        }
    }

    protected function empty(){
        return $this->model::count() > 0 ? true : false;
    }

    protected function current_user($id) {
        return auth()->user()->id == $id ? true : false;
    }

    protected function read($page = 1, $population = 15)
    {
        return $this->empty() ? $this->model::paginate($population, ['*'], 'page', $page) : $this->notFound('no data yet.');
    }

    protected function get($id)
    {
        return $this->model::find($id) ?? $this->notFound("data with id {$id} not found.");
    }

    protected function create($except = [], $model = false)
    {
        $created = $this->model::create($this->request->except($except));
        $boolean = $created ? true : false;
        return $model ? $created : $boolean;
    }

    protected function update($id)
    {
        $model = $this->model::find($id);
        if(!$model) {
            return $this->notFound("data with id {$id} not found.");
        }
        return $model->update($this->request->all()) ? true : false;
    }

    protected function delete($id)
    {
        $data = $this->model::find($id);
        if (!$data) {
            return $this->notFound("data with id {$id} not found.");
        }
        $data->delete();
        return $data;
    }

    protected function ok($message)
    {
        return response([
            'message' => $message
        ], 200);
    }

    protected function created($message)
    {
        return response([
            'message' => $message
        ], 201);
    }

    protected function unauthenticated($message)
    {
        abort(
            response()->json(
                [
                    'message' => $message,
                ],
                401,
            ),
        );
    }

    protected function unauthorized($message)
    {
        abort(
            response()->json(
                [
                    'message' => $message,
                ],
                403,
            ),
        );
    }

    protected function notFound($message)
    {
        abort(
            response()->json(
                [
                    'message' => $message,
                ],
                404,
            ),
        );
    }

    protected function invalid($validator)
    {
        abort(
            response()->json(
                [
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                422,
            ),
        );
    }
}
