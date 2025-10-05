<?php
namespace App\Http\Controllers;
use App\Models\LicenseDomain;
use Illuminate\Http\Request;
class LicenseDomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
    public function show(LicenseDomain $licenseDomain)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LicenseDomain $licenseDomain)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LicenseDomain $licenseDomain)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LicenseDomain $licenseDomain)
    {
        $licenseDomain->delete();
        return redirect()->back()->with('success', 'Domain removed successfully.');
    }
}
