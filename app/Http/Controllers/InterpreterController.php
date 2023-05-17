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
        return Interpreter::all();
    }

    public function indexStatus()
    {
        return Interpreter::where('status', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $interpreter = Interpreter::create($request->all());
        return response()->json($interpreter, 201);
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
            ->where([
                ['lenguage_id', $lenguage],
                ['full_name', 'like', '%' . $search . '%'],
                ['status', 1]
            ])
            ->get();
        return response()->json($interpreters);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Interpreter $interpreter)
    {
        Interpreter::where('id', $interpreter->id)->update($request->all());
        return response()->json($interpreter, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interpreter $interpreter)
    {
        //
    }
}
