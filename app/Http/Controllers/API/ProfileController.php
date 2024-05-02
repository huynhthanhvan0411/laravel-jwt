<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        try{
            $user = $request->user();
            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request){
        DB::beginTransaction();
        try{
            $user = $request->user();
            $user->fill($request->validated());
            if($user->isDirty('email')){
                $user->email_verified_at = null;
            }
            $user->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required|current_password'
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try{
            $user = $request->user();
            auth('api')->logout();
            $user->delete();
            DB::commit();
            return response()->json([
                'success' => true
            ]);
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
