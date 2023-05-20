<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use Illuminate\Http\Request;

class CoordinatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Coordinator::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $coordinator = Coordinator::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Coordinator $coordinator)
    {
        return Coordinator::findOrFail($coordinator->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coordinator $coordinator)
    {
        Coordinator::where('id', $coordinator->id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coordinator $coordinator)
    {
        //
    }
}
