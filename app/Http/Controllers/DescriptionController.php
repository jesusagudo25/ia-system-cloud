<?php

namespace App\Http\Controllers;

use App\Models\Description;
use Illuminate\Http\Request;

class DescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Description::all();
    }

    public function indexStatus()
    {
        return Description::where('status', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);
        $description = Description::create($request->all());

        return response()->json([
            'message' => 'Description created successfully',
            'description' => $description
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Description $description)
    {
        //
    }

    public function search($search)
    {
        return Description::where('title', 'like', '%' . $search . '%')->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Description $description)
    {
        Description::where('id', $description->id)->update($request->all());

        return response()->json([
            'message' => 'Description updated successfully',
            'description' => $description
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Description $description)
    {
        //
    }
}
