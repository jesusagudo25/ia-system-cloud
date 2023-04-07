<?php

namespace App\Http\Controllers;

use App\Models\Interpreter;
use Illuminate\Http\Request;

class InterpreterController extends Controller
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
    public function show(Interpreter $interpreter)
    {
        //
    }

    public function search($state, $lenguage, $search)
    {
        $interpreters = Interpreter::where('state', $state)
            ->where('lenguage_id', $lenguage)
            ->where('full_name', 'like', '%' . $search . '%')
            ->get();
        return response()->json($interpreters);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Interpreter $interpreter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interpreter $interpreter)
    {
        //
    }
}
