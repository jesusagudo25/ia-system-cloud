<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Agency::all();
    }

    public function indexStatus()
    {
        return Agency::where('status', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required'
        ]);
        $agency = Agency::create($request->all());

        return response()->json([
            'message' => 'Agency created successfully',
            'agency' => $agency
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agency $agency)
    {
        //
    }

    /**
     * Search the specified resource.
     */
    public function search($search)
    {
        $agencies = Agency::where(
            [
                ['name', 'like', '%' . $search . '%'],
                ['status', '=', '1']
            ]
        )->get();

        return response()->json($agencies);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agency $agency)
    {
        Agency::where('id', $agency->id)->update($request->all());

        return response()->json([
            'message' => 'Agency updated successfully',
            'agency' => $agency
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agency $agency)
    {
        //
    }
}
