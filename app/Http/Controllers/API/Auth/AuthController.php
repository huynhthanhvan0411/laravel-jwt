<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(private User $user){
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:3'
        ]);
        if($validator->fails()){
            return response()->json(['success' => false,'message' => 'Validation error', 'errors' => $validator->errors()], 4);
        }
        try{
            $credentails = request(['email','password']); 
            if(!$token = auth()->attempt($credentails)){
                return response()->json(['success' => false,'message' => 'Unauthorized'], 4);
            }
            return $this->createNewToken($token);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['success' => false,'messege'=> $e->getMessage()], 401);
        }
    }
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name'=>'requred|string|max:255|min:3',
            'email' => 'required|email',
            'password' => 'required|string|min:3'
        ]);
        if($validator->fails()){
            return response()->json(['success' => false,'message' => 'Validation error', 'errors' => $validator->errors()], 4);
        }
        DB::beginTransaction();
        try {
            //create
            $user = $this->user->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            //create even 
            event(new Registered($user));
            //create token
            $token = auth('api')->login($user);
            DB::commit();
            return response()->json(['success' => true,'data' => $user], 200);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['success' => false,'messege'=> $e->getMessage()], 401);}
    }
}
