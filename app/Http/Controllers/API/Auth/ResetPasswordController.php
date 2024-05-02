<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;


class ResetPasswordController extends Controller
{
       public function createResetPasswordToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try{
            $status = Password::sendResetLink(
                $request->only('email')
            );
            return $status === Password::RESET_LINK_SENT
                        ? response()->json(['message' => 'We have e-mailed your password reset link.',], 200)
                        : response()->json(['message' => 'We can not find a user with that e-mail address.'], 400);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        DB::beginTransaction(); 
        try{
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();
                    event (new PasswordReset($user));
                }
            );
            DB::commit();
            return $status === Password::PASSWORD_RESET
                        ? response()->json(['message' => 'Password has been reset successfully.'], 200)
                        : response()->json(['message' => 'We can not reset password with provided details.'], 500);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
