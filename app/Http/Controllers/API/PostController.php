<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    private Model $model;
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
    public function index(Request $request)
    {
        try{
            $posts = $this->model->all();
            return response()->json([
                'success' => true,
                'data' => $posts
            ], 200);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function store (Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'user_id'=> 'required|integer|exists:users,id',
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }
        DB::beginTransaction();
        try{
            $post = $this->model->create($request->all());
            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $post
            ], 200);
        }catch(\Exception $e){
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

       
    }

    public function show(string $id){
        try{
            $post = $this->model->find($id);
            if(!$post){
                return response()->json(['success' => false,'message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true,'data' => $post], 200);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['success' => false,'message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, string $id){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'user_id'=> 'required|integer|exists:users,id',
        ]);
        if($validator->fails()){
            return response()->json(['success' => false,'message' => $validator->errors()], 400);
        }
        DB::beginTransaction(); 
        try{
            $post = $this->model->find($id);
            if(!$post){
                return response()->json(['success' => false,'message' => 'Post not found'], 404);
            }
            $post->update($request->all());
            DB::commit();
            return response()->json(['success' => true,'data' => $post], 200);
        }catch(\Exception $e){
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(['success' => false,'message' => $e->getMessage()], 500);
        }
    }
    public function destroy(string $id){
        DB::beginTransaction();
        try{
            $post = $this->model->find($id);
            if(!$post){
                return response()->json(['success' => false,'message' => 'Post not found'], 404);
            }
            $post->delete();
            DB::commit();
            return response()->json(['success' => true,'message' => 'Post deleted successfully'], 200);
        }catch(\Exception $e){
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(['success' => false,'message' => $e->getMessage()], 500);
        }
    }
}