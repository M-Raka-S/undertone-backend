<?php

namespace App\Http\Controllers;

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

    protected function empty()
    {
        return $this->model::count() > 0 ? true : false;
    }

    protected function current_user($id)
    {
        return auth()->user()->id == $id ? true : false;
    }

    protected function read($page = 1, $population = 15)
    {
        return $this->empty() ? $this->model::paginate($population, ['*'], 'page', $page) : $this->notFound('no data yet.');
    }

    protected function get($id, $with = [])
    {
        $query = $this->model::query();
        if (!empty($with)) {
            $query->with($with);
        }
        $model = $query->find($id);
        $modelName = class_basename($this->model);
        return $model ?? $this->notFound("{$modelName} with id {$id} not found.");
    }

    protected function create($except = [], $model = false)
    {
        $created = $this->model::create($this->request->except($except));
        $boolean = $created ? true : false;
        return $model ? $created : $boolean;
    }

    protected function checkExists($id, $check_model = null)
    {
        if (!$check_model) {
            $check_model = $this->model;
        }
        $model = $check_model::find($id);
        if (!$model) {
            $modelName = class_basename($check_model);
            return $this->notFound("{$modelName} with id {$id} not found.");
        }
        return $model;
    }

    protected function update($id, $exclude = [])
    {
        $model = $this->checkExists($id);
        return $model->update($this->request->except($exclude)) ? true : false;
    }

    protected function delete($id)
    {
        $data = $this->checkExists($id);
        $data->delete();
        return $data;
    }

    protected function ok($message)
    {
        return response([
            'message' => $message
        ], 200);
    }

    protected function created($message, $id = null)
    {
        $response = ['message' => $message];
        if (!is_null($id) && app()->environment('testing')) {
            $response['id'] = $id;
        }
        return response($response, 201);
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

    protected function conflict($message)
    {
        abort(
            response()->json(
                [
                    'message' => $message,
                ],
                status: 409,
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
