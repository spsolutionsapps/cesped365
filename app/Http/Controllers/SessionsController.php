<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SessionsController extends Controller
{
    public function create()
    {
        return view('session.login-session');
    }

    public function store()
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:16','message'=>'SessionsController store() entry','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        try {
            $attributes = request()->validate([
                'email'=>'required|email',
                'password'=>'required' 
            ]);
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:22','message'=>'Validation passed','data'=>['email'=>$attributes['email']],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion

            if(Auth::attempt($attributes))
            {
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:25','message'=>'Auth attempt successful','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                // Regenerar sesión para prevenir fijación de sesión
                session()->regenerate();
                
                // Limpiar caché del usuario autenticado
                $authUser = Auth::user();
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'SessionsController.php:31','message'=>'Auth user retrieved','data'=>['user_id'=>$authUser?->id,'user_role'=>$authUser?->role],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                if ($authUser) {
                    $authUser->refresh();
                }
                
                // Redirect admins to admin dashboard, clients to client dashboard
                if($authUser && $authUser->isAdmin()) {
                    // #region agent log
                    file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:37','message'=>'Redirecting admin','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                    // #endregion
                    return redirect()->route('admin.index')
                        ->with(['success'=>'You are logged in.'])
                        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
                }
                
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:46','message'=>'Redirecting client','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                return redirect('dashboard')
                    ->with(['success'=>'You are logged in.'])
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }
            else{
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:54','message'=>'Auth attempt failed','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                return back()->withErrors(['email'=>'Email or password invalid.']);
            }
        } catch (\Exception $e) {
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'SessionsController.php:59','message'=>'Exception in store()','data'=>['error'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
    }
    
    public function destroy()
    {

        Auth::logout();

        return redirect('/login')->with(['success'=>'You\'ve been logged out.']);
    }
}
