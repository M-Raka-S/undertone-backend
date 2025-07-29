<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModel(Comment::class);
    }

    public function show($page)
    {
        $this->unauthorized('access comments list from a chapter instead.');
    }

    public function all()
    {
        $this->unauthorized('access comments list from a chapter instead.');
    }

    public function pick($id)
    {
        $this->unauthorized('access comments list from a chapter instead.');
    }

    public function make()
    {
        $this->validator([
            'uuid' => 'required',
            'content' => 'required',
            'user_id' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:comments,id',
            'chapter_id' => 'required|exists:chapters,id',
        ], ['uuid', 'content', 'user_id', 'parent_id', 'chapter_id']);
        $chapter = $this->checkExists($this->request->chapter_id, Chapter::class);
        if (!$this->checkPrivilege($chapter->project, ['lead author', 'author', 'editor'])) {
            return $this->unauthorized('you do not have permission to make comments.');
        }

        $created = $this->create([], true);
        return $created ? $created : $this->invalid('creation failed.');
    }

    public function edit($id)
    {
        $comment = $this->checkExists($id);
        $this->validator([
            'content' => 'required',
        ]);
        $chapter = $this->checkExists($comment->chapter_id, Chapter::class);
        if (!$this->checkPrivilege($chapter->project, ['lead author', 'author', 'editor'])) {
            return $this->unauthorized('you do not have permission to edit comments.');
        }
        return $this->update($id, ['uuid', 'user_id', 'parent_id', 'chapter_id']) ? $this->ok('comment updated.') : $this->invalid('update failed.');
    }

    public function remove($id)
    {
        $this->checkExists($id);
        return $this->delete($id);
    }
}
