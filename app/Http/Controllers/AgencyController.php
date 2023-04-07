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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agency $agency)
    {
        //
    }
}
