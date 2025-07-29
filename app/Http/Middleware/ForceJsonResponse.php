<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $responseData = json_decode($response->getContent(), true);
        $customMessage = $responseData['message'] ?? null;

        if ($response->status() == 404) {
            throw new HttpResponseException(response()->json([
                'message' => 'data not found.',
                'error' => $customMessage ?? 'the requested page does not exist.',
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
