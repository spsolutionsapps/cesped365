<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;

class InfoUserController extends Controller
{

    public function create()
    {
        return view('laravel-examples/user-profile');
    }

    public function store(Request $request)
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:19','message'=>'InfoUserController store() entry','data'=>['request_email'=>$request->get('email')],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        try {
            // #region agent log
            $authUser = Auth::user();
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'InfoUserController.php:25','message'=>'Auth user check','data'=>['user_id'=>$authUser?->id,'user_email'=>$authUser?->email],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            $attributes = request()->validate([
                'name' => ['required', 'max:50'],
                'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($authUser?->id)],
                'phone'     => ['max:50'],
                'location' => ['max:70'],
                'about_me'    => ['max:150'],
            ]);
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:33','message'=>'Validation passed','data'=>['attributes_email'=>$attributes['email']??null],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            $attribute = null;
            if($request->get('email') != $authUser->email)
            {
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:37','message'=>'Email changed branch','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                if(env('IS_DEMO') && $authUser->id == 1)
                {
                    return redirect()->back()->withErrors(['msg2' => 'You are in a demo version, you can\'t change the email address.']);
                }
                $attribute = request()->validate([
                    'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($authUser->id)],
                ]);
            }
            else{
                // #region agent log
                file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:46','message'=>'Email unchanged branch','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                $attribute = ['email' => $attributes['email']];
            }
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:51','message'=>'Before database update','data'=>['attribute_email'=>$attribute['email']??null,'attribute_defined'=>isset($attribute)],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            
            User::where('id', $authUser->id)
            ->update([
                'name'    => $attributes['name'],
                'email' => $attribute['email'],
                'phone'     => $attributes['phone'] ?? null,
                'location' => $attributes['location'] ?? null,
                'about_me'    => $attributes["about_me"] ?? null,
            ]);
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:61','message'=>'Database update completed','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion

            return redirect('/user-profile')->with('success','Profile updated successfully');
        } catch (\Exception $e) {
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'InfoUserController.php:66','message'=>'Exception in store()','data'=>['error'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
    }
}
