<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Http\Controllers\AIController;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateChapterSummary implements ShouldQueue
{
    use Queueable;

    protected $id, $content;

    public function __construct($id, $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    public function handle(): void
    {
        $chapter = Chapter::find($this->id);
        if ($chapter) {
            $chapter->summary = AIController::generate($this->content);
            $chapter->save();
        }
    }
}
