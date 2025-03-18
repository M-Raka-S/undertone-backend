<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $content = !Str::contains($response->getContent(), '<!DOCTYPE html>') ? $response->getContent() : 'the requested page does not exist.';

        if ($response->status() == 404) {
            throw new HttpResponseException(response()->json([
                'message' => 'data not found.',
                'error' => $content,
            ], 404));
        }

        if ($response->status() == 405) {
            throw new HttpResponseException(response()->json([
                'message' => 'method not allowed.',
                'error' => 'the requested HTTP method is not allowed for this route.',
            ], 405));
        }

        if ($response->status() == 500) {
            throw new HttpResponseException(response()->json([
                'message' => 'internal server error. ',
                'error' => 'a fatal error has occured.',
            ], 500));
        }

        return $response;
    }
}
