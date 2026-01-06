<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'AdminMiddleware.php:16','message'=>'AdminMiddleware handle() entry','data'=>['path'=>$request->path()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        try {
            $authCheck = auth()->check();
            $authUser = auth()->user();
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'AdminMiddleware.php:20','message'=>'Auth check in middleware','data'=>['auth_check'=>$authCheck,'user_id'=>$authUser?->id,'user_role'=>$authUser?->role],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            if (!$authCheck || !$authUser || !$authUser->isAdmin()) {
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'AdminMiddleware.php:23','message'=>'Access denied','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                abort(403, 'Acceso no autorizado');
            }
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'AdminMiddleware.php:27','message'=>'Access granted, continuing','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            return $next($request);
        } catch (\Exception $e) {
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'AdminMiddleware.php:31','message'=>'Exception in handle()','data'=>['error'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
    }
}

