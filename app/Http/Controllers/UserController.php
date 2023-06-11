<?php

namespace App\Http\Controllers;

use App\Mail\UserActivationNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $user = User::create(
            [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]
        );
        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return User::findOrFail($user->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        User::where('id', $user->id)->update($request->all());
    }

    public function updatePassword(Request $request, User $user)
    {
        //Validate current
        if($request->has('currentPassword')){
        	$currentPassword = User::findOrFail($user->id)->password;
        	if (!\Hash::check($request->currentPassword, $currentPassword)) {
        	    return response()->json(['errors' => ['current_password' => ['Current password is incorrect']]], 422);
        	}
        }

        $request->validate([
            'password' => 'required|string|min:6',
        ]);
        $user->update(
            [
                'password' => bcrypt($request->password),
            ]
        );
        return response()->json($user, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
