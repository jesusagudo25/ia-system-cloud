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
            'price_per_hour_interpreter' => 'required',
        ]);
        $lenguage = Lenguage::create($request->all());

        return response()->json([
            'message' => 'Lenguage created successfully',
            'lenguage' => $lenguage
        ], 201);
    }

    /**
     * Store special price for a lenguage and agency
     */
    public function storeSpecialPrice(Request $request)
    {
        $request->validate([
            'lenguage_id' => 'required',
            'agency_id' => 'required',
            'price_per_hour' => 'required',
        ]);

        $lenguage = Lenguage::find($request->lenguage_id);
        $lenguage->agencies()->attach($request->agency_id, [
            'price_per_hour' => $request->price_per_hour,
            'price_per_hour_interpreter' => $request->price_per_hour_interpreter
        ]);
        
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
     * Display special price for a lenguage
     * 
     */
    public function showSpecialPrice(Lenguage $lenguage)
    {
        $lenguage = Lenguage::with('agencies')->find($lenguage->id);
        return response()->json([
            'message' => 'Lenguage retrieved successfully',
            'lenguage' => $lenguage
        ], 200);
    }
    /**
     * Display special price for a lenguage and agency
     * 
     */
    public function showSpecialPriceByAgency(Lenguage $lenguage, $agency)
    {
        $lenguage = Lenguage::with(['agencies' => function ($query) use ($agency) {
            $query->where('agency_id', $agency);
        }])->find($lenguage->id);

        return response()->json([
            'message' => 'Lenguage retrieved successfully',
            'lenguage' => $lenguage
        ], 200);
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
     * Update special price for a lenguage and agency
     */
    public function updateSpecialPrice(Request $request)
    {
        $request->validate([
            'lenguage_id' => 'required',
            'agency_id' => 'required',
            'price_per_hour' => 'required',
        ]);

        $lenguage = Lenguage::find($request->lenguage_id);
        $lenguage->agencies()->updateExistingPivot($request->agency_id, [
            'price_per_hour' => $request->price_per_hour,
            'price_per_hour_interpreter' => $request->price_per_hour_interpreter
        ]);
        
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

    /**
     * Remove special price for a lenguage and agency
     */
    public function destroySpecialPrice(Request $request)
    {
        $request->validate([
            'lenguage_id' => 'required',
            'agency_id' => 'required',
        ]);

        $lenguage = Lenguage::find($request->lenguage_id);
        $lenguage->agencies()->detach($request->agency_id);
        
        return response()->json([
            'message' => 'Lenguage deleted successfully',
            'lenguage' => $lenguage
        ], 200);
    }
}
