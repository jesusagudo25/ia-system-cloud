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
    public function show(Lenguage $lenguage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lenguage $lenguage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lenguage $lenguage)
    {
        //
    }
}
