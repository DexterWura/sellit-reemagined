<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanSessionFiles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * This runs before session is saved, so we can clean files here.
     */
    public function terminate($request, $response)
    {
        // Clean UploadedFile instances from session before it's saved
        if ($request->hasSession()) {
            $session = $request->session();
            $attributes = $session->all();
            $cleaned = $this->removeFilesFromArray($attributes);
            
            // Replace all session data with cleaned version
            $session->replace($cleaned);
        }
    }

    /**
     * Recursively remove UploadedFile instances from array
     */
    private function removeFilesFromArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $cleaned = [];
        foreach ($data as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                // Skip UploadedFile instances
                continue;
            } elseif (is_array($value)) {
                // Recursively clean arrays
                $cleaned[$key] = $this->removeFilesFromArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}

