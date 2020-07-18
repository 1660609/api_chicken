<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\User;
use App\Notifications\ResetPasswordRequest;
use App\Models\PasswordReset;

class ResetPasswordController extends Controller
{
    //
    public function sendMail(Request $request)
    {

        $user = User::where('email',$request->email)->firstOrFail();
       // var_dump($user);
        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
        ],[
            'token' =>Str::random(60),
        ]);
        if ($passwordReset){
            $user->notify(new ResetPasswordRequest($passwordReset->token));
        }
        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);
    }
    public function reset(Request $request , $token)
    {
        $passwordReset = PasswordReset::where('token',$token)->firstOrFail();
        if( Carbon::parse($passwordReset->updated_at)->addMinute(720)->isPast){
            $passwordReset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid . ',
            ],422);
        }
        $user = User::where('email',$passwordReset->email)->firstOrFail();
        $updatePasswordUser = $user->update($request->only('password'));
        $passwordReset->delete();
        return response()->json([
            'success' => $updatePasswordUser,
        ]); 

    }
}