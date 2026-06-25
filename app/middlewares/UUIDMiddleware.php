<?php

namespace App\Middlewares;

use Core\Classes\Request;

class UUIDMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        // Get UID from query (adjust if using route params)
        $uid = $request->route('id');

        // UUID v4 regex
        if (!$this->isValidUUID($uid)) {
            http_response_code(404);
            view('certificate/certificateNotFound');
            exit; // silent fail (no info leak)
        }

        return $next($request);
    }

    private function isValidUUID($uuid)
    {
        return preg_match(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i',
            $uuid
        );
    }
}
