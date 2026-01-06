<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Show the landing page.
     */
    public function home()
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'LandingController.php:15','message'=>'LandingController home() entry','data'=>['method'=>'home'],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        try {
            // #region agent log
            try {
                $routeExists = route('register', [], false);
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'LandingController.php:20','message'=>'Checking route register exists','data'=>['route_exists'=>true,'route_url'=>$routeExists],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            } catch (\Exception $routeEx) {
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'LandingController.php:22','message'=>'Route register check failed','data'=>['error'=>$routeEx->getMessage()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            }
            // #endregion
            $view = view('landing.home');
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'LandingController.php:26','message'=>'View created successfully','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            return $view;
        } catch (\Exception $e) {
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'LandingController.php:30','message'=>'Exception in home()','data'=>['error'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
    }

    /**
     * Show the plans page (redirects to home with anchor).
     */
    public function plans()
    {
        return redirect()->route('landing.home') . '#planes';
    }

    /**
     * Show the contact page (redirects to home with anchor).
     */
    public function contact()
    {
        return redirect()->route('landing.home') . '#contacto';
    }
}

