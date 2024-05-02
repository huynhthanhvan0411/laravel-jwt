<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|current_password:api',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();
            $user->password = Hash::make($request->password);
            $user->save();
            DB::commit();
            return response()->json(['message' => 'Password updated successfully',],200);
        }catch(\Exception $e){
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
