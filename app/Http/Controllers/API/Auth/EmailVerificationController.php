<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


class EmailVerificationController extends Controller
{
    public function verifyEmail(EmailVerificationRequest $request){
        DB::beginTransaction();
        try{
            if($request->user()->hasVerifiedEmail()){
                return response()->json(['messega'=> 'Email already verified'], 200);
            }
            $request->user()->markEmailAsVerified();
            event(new Verified($request->user()));
            DB::commit();
            return response()->json(['messega'=> 'Email successfully verified'], 200);
        }catch(\Exception $e){
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function sendVerificationEmail(Request $request){
        try{
            if($request->user()->hasVerifiedEmail()){
                return response()->json(['messega'=> 'Email already verified'], 200);
            }
            $request->user()->sendEmailVerificationNotification();
            return response()->json(['messega'=> 'Email verification link sent on your email'], 200);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
