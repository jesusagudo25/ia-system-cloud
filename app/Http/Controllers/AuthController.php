<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMailable;
use App\Models\PasswordReset;
use App\Models\PersonalAccess;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
/**
     * Create a new AuthController instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $attr = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($attr)) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'Invalid login details'
            ], 401);
        }

        //Validar si el usuario tiene un token de acceso
        $token = PersonalAccessToken::where('tokenable_id', Auth::id())->first();

        if ($token) {
            $token->delete();

            return response()->json([
                'status' => 'wrong',
                'message' => 'User already logged in, for security reasons you have been logged out'
            ], 401);
        }

        //Crear un nuevo token de acceso

        $token = Auth::user()->createToken(Auth::id())->plainTextToken;
        $user = auth()->user();

        $response = [
            'status' => 'success',
            'message' => 'Login successfully',
            'token' => $token,
            'id' => $user->id,
            ];

        return response()->json($response,200);
    }

        /**
     * xxxxx
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        $response = [
            'status' => 'success',
            'message' => 'Logged out'
        ];

        return response()->json($response,200);
    }

    /**
     * xxxx
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:150'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'We can\'t find a user with that e-mail address.'
            ], 404);
        }

        /* table - generate token */

        $token = Str::random(60);

        /* Delete */
        PasswordReset::where('email', $user->email)->delete();


        PasswordReset::create([
            'email' => $user->email,
            'token' => $token
        ]);
        

        /* send email */

        Mail::to($user->email)->send(new ForgotPasswordMailable($token));

        $response = [
            'status' => 'success',
            'message' => 'Reset password link sent to your email',
            'token' => $token,
            'id' => $user->id,
        ];

        return response()->json($response,200);
    }

    /**
     * xxxx
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        //Validate datetie created_at
        $created_at = strtotime($passwordReset->created_at);
        $now = strtotime(date('Y-m-d H:i:s'));

        if($now - $created_at > 3600){ // 1 hour
            return response()->json([
                'status' => 'wrong',
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'We can\'t find a user with that e-mail address.'
            ], 404);
        }

        $user->password = Hash::make($request->password);

        $user->save();

        $passwordReset->delete();

        /* Return */

        $token = $user->createToken($user->id)->plainTextToken;

        $response = [
            'status' => 'success',
            'message' => 'Register successfully',
            'token' => $token,
            'id' => $user->id,
            'role_id' => $user->role_id
        ];

        return response()->json($response,200);

    }

    /**
     * xxxx
     *
     * @return \Illuminate\Http\Response
     */
    public function validateTokenReset(Request $request){
        $request->validate([
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        //Validate datetie created_at
        $created_at = strtotime($passwordReset->created_at);
        $now = strtotime(date('Y-m-d H:i:s'));

        if($now - $created_at > 3600){ // 1 hour
            return response()->json([
                'status' => 'wrong',
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'We can\'t find a user with that e-mail address.'
            ], 404);
        }

        $response = [
            'status' => 'success',
            'message' => 'Token is valid',
            'token' => $request->token,
            'id' => $user->id,
        ];

        return response()->json($response,200);
    }

    /**
     * xxxx
     *
     * @return \Illuminate\Http\Response
     */
    public function validateTokenAccess(Request $request){
        $request->validate([
            'token' => 'required|string',
            'id' => 'required|integer'
        ]);

        $user = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);

        if($user->tokenable_id != $request->id){
            return response()->json([
                'status' => 'wrong',
                'message' => 'This token is invalid.'
            ], 404);
        }

        if (!$user) {
            return response()->json([
                'status' => 'wrong',
                'message' => 'This token is invalid.'
            ], 404);
        }
        else{
            $response = [
                'status' => 'success',
                'message' => 'Token is valid',
                'token' => $request->token,
            ];

            return response()->json($response,200);
        }
        
    }
}
