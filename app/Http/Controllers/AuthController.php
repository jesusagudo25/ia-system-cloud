<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMailable;
use App\Models\PasswordReset;
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
    public function validateToken(Request $request){
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
}
