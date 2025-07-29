<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;

class AIController extends Controller
{
    protected $model;

    public function __construct() {
        $this->model = env("OPENROUTER_API_MODEL", 'deepseek/deepseek-chat-v3-0324:free');
    }

    public static function generate($content, $max_tokens = 500, $temperature = 0.9)
    {
        $self = new static;
        $message = new MessageData(
            content: $content,
            role: RoleType::USER,
        );
        $chat = new ChatData(
            messages: [
                $message,
            ],
            model: $self->model,
            max_tokens: $max_tokens,
            temperature: $temperature,
        );
        $response = LaravelOpenRouter::chatRequest($chat);
        $choice = (array) $response->choices[0];
        return Arr::get($choice, 'message.content');
    }
}
