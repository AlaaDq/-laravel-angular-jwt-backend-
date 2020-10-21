<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function signup(Request $request)
    {
         $this->validate($request, [
             'name' => 'required',
             'email' => 'required|email|unique:users',
             'password' => 'required|min:6',
			 'password_confirmation' => 'required|min:6',
         ]);
         $user = new User([
             'name' => $request->input('name'),
             'email' => $request->input('email'),
             'password' => bcrypt($request->input('password'))
         ]);
         $user->save();
         return response()->json([
             'message' => 'Successfully created user!'
         ], 201);


        // $request->validate(
            // [   'name' => 'required',
                // 'email' => 'required|string|email|max:255|unique:users',
                // 'password' => 'required|string|min:6',
                 // 'password_confirmation' => 'required|string|min:6',
            // ]
        // );
        // $user = new User();
        // $user->name = $request->name;
        // $user->email = $request->email;
        // $user->password = bcrypt($request->password);
        // $user->save();
        // $token = JWTAuth::attempt($request->only('email', 'password'));
        // return response()->json(['token' => "Bearer $token"]);
    }

    public function signin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid Credentials!'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token!'
            ], 500);
        }
        return response()->json([
            'token' => $token
        ], 200);
    }
}


