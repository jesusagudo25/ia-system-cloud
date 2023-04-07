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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Description $description)
    {
        //
    }
}
