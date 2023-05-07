<?php

namespace App\Http\Controllers;

use App\Models\Lenguage;
use Illuminate\Http\Request;

class LenguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Lenguage::all();
    }

    public function indexStatus()
    {
        return Lenguage::where('status', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price_per_hour' => 'required',
        ]);
        $lenguage = Lenguage::create($request->all());

        return response()->json([
            'message' => 'Lenguage created successfully',
            'lenguage' => $lenguage
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lenguage $lenguage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lenguage $lenguage)
    {
        Lenguage::where('id', $lenguage->id)->update($request->all());

        return response()->json([
            'message' => 'Lenguage updated successfully',
            'lenguage' => $lenguage
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lenguage $lenguage)
    {
        //
    }
}
