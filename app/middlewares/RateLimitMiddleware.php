<?php

namespace App\Middlewares;

use Core\Classes\Request;

class RateLimitMiddleware
{
    private $window = 60;      // seconds
    private $maxRequests = 5;  // per window
    private $blockMinutesInc = 15; // block for no of minutes, incremental
    private $maxBlockMinutes = 90; // minutes
    private $violationDecay = 86400; // 24 hours to reset violations

    public function handle(Request $req, \Closure $next)
    {
        // Check for forwarded IPs if behind a proxy (Be careful: users can spoof this if NOT behind a proxy)
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = sys_get_temp_dir() . "/rl_" . md5($ip);

        $now = time();

        // 1. Open the file for reading and writing ('c+' creates it if it doesn't exist)
        $fp = fopen($key, 'c+');
        if (!$fp) {
            // Failsafe if directory permissions are broken
            return;
        }

        // 2. EXCLUSIVELY lock the file. Other concurrent requests will pause and wait right here.
        flock($fp, LOCK_EX);

        // 3. Now that we have the lock, read the contents
        $fileSize = filesize($key);
        $fileContents = $fileSize > 0 ? fread($fp, $fileSize) : '';
        $data = json_decode($fileContents, true);

        // Normalize + version control
        if (!is_array($data) || !isset($data['version']) || $data['version'] !== 2) {
            $data = [
                'version' => 2,
                'requests' => [],
                'violations' => 0,
                'blocked_until' => 0,
                'last_violation' => 0
            ];
        }

        // Decay old violations (Reset if it's been a long time since the last offense)
        if ($data['violations'] > 0 && ($now - $data['last_violation']) > $this->violationDecay) {
            $data['violations'] = 0;
        }

        // Block check
        if ($data['blocked_until'] > $now) {
            flock($fp, LOCK_UN); // Always unlock before exiting!
            fclose($fp);
            http_response_code(429);
            echo "Blocked. Try again later.";
            exit;
        }

        // Clean old requests
        $data['requests'] = array_filter($data['requests'], function ($timestamp) use ($now) {
            return $timestamp > ($now - $this->window);
        });

        // Limit exceeded
        if (count($data['requests']) >= $this->maxRequests) {
            $data['violations']++;
            $data['last_violation'] = $now;

            $blockMinutes = min($this->blockMinutesInc * $data['violations'], $this->maxBlockMinutes);
            $data['blocked_until'] = $now + ($blockMinutes * 60);

            // Write back
            ftruncate($fp, 0);      // Clear the file
            rewind($fp);            // Go back to the start
            fwrite($fp, json_encode($data));

            flock($fp, LOCK_UN);    // Unlock
            fclose($fp);

            http_response_code(429);
            echo "Blocked for {$blockMinutes} minutes.";
            exit;
        }

        // Delay (moved safely inside the lock if necessary, but consider removing it 
        // entirely as holding locks during sleep degrades server performance)
        usleep(rand(50000, 150000));

        $data['requests'][] = $now;

        // Write back successful request
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));

        // 4. Release lock and close
        flock($fp, LOCK_UN);
        fclose($fp);

        return $next($req);
    }
}
