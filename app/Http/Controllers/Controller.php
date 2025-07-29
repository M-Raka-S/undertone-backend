<?php

namespace App\Http\Controllers;

use App\Models\CategoryInstance;
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

    protected function replace($key, $value)
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

    protected function is_current_user($id)
    {
        return auth()->user()->id == $id ? true : false;
    }

    protected function filterByOwnership($query)
    {
        return $query;
    }

    protected function read($page = 1, $population = 15, $with = [])
    {
        if ($this->empty()) {
            $query = $this->filterByOwnership($this->model::query());
            if (!empty($with)) {
                $query = $query->with($with);
            }
            return $query->paginate($population, ['*'], 'page', $page);
        } else {
            return $this->notFound('no data yet.');
        }
    }

    protected function getAll($with = [])
    {
        if ($this->empty()) {
            $query = $this->filterByOwnership($this->model::query());
            if (!empty($with)) {
                $query = $query->with($with);
            }
            return response()->json(["data" => $query->get()]);
        } else {
            return $this->notFound('no data yet.');
        }
    }

    protected function get($id, $with = [])
    {
        $query = $this->filterByOwnership($this->model::query());
        if (!empty($with)) {
            $query->with($with);
        }
        $model = $query->find($id);
        $modelName = class_basename($this->model);
        return $model ?? $this->notFound("{$modelName} not found or is not yours.");
    }

    protected function create($except = [], $model = false)
    {
        $created = $this->model::create($this->request->except($except));
        $boolean = $created ? true : false;
        return $model ? $created : $boolean;
    }

    protected function checkPrivilege($project, $allowed)
    {
        $roleInfo = auth()->user()->getRoleInfo($project);
        if (!$roleInfo || !in_array($roleInfo['name'], $allowed)) {
            return false;
        }
        return true;
    }

    protected function checkExists($id, $check_model = null)
    {
        if (!$check_model) {
            $check_model = $this->model;
        }
        $model = $check_model::find($id);
        if (!$model) {
            $modelName = class_basename($check_model);
            return $this->notFound("{$modelName} not found.");
        }
        return $model;
    }

    protected function checkOwned($id)
    {
        $model = $this->filterByOwnership($this->model::query())
            ->where('id', $id)
            ->first();
        if (!$model) {
            $modelName = class_basename($this->model);
            $this->notFound("{$modelName} not found or is not yours.");
        }
        return $model;
    }

    protected function update($id, $exclude = [])
    {
        $model = $this->checkOwned($id);
        return $model->update($this->request->except($exclude)) ? true : false;
    }

    protected function delete($id)
    {
        $data = $this->checkOwned($id);
        $data->delete();
        return $data;
    }

    protected function extractMentionSummaries(string $html): array
    {
        $summaries = [];

        $dom = new \DOMDocument();

        // Suppress warnings due to malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $spans = $dom->getElementsByTagName('span');

        foreach ($spans as $span) {
            if ($span->hasAttribute('data-type') && $span->getAttribute('data-type') === 'mention') {
                $mentionedId = $span->getAttribute('data-id');
                if ($mentionedId) {
                    $instance = $this->checkExists($mentionedId, CategoryInstance::class);
                    $mentionSummary = $instance->getIdentifierValueAttribute()["value"] . ": " . (strip_tags($instance->summary) ?? 'N/A');
                    $summaries[] = $mentionSummary;
                }
            }
        }

        return $summaries;
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

    protected function invalid($message)
    {
        if (is_string($message)) {
            $errors = $message;
        } else {
            $errors = $message->errors();
        }

        abort(
            response()->json(
                [
                    'message' => 'validation failed.',
                    'errors' => $errors,
                ],
                422,
            ),
        );
    }
}
