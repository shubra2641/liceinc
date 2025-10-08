<?php

namespace App\Http\Controllers;

use App\Models\LicenseDomain;
use Illuminate\Http\Request;

class LicenseDomainController extends Controller
{
    /**   * Display a listing of the resource. */
    public function index(): \Illuminate\Http\Response
    {
        return response('', 200);
    }
    /**   * Show the form for creating a new resource. */
    public function create(): \Illuminate\Http\Response
    {
        return response('', 200);
    }
    /**   * Store a newly created resource in storage. */
    public function store(Request $request): \Illuminate\Http\Response
    {
        return response('', 200);
    }
    /**   * Display the specified resource. */
    public function show(LicenseDomain $licenseDomain): \Illuminate\Http\Response
    {
        return response('', 200);
    }
    /**   * Show the form for editing the specified resource. */
    public function edit(LicenseDomain $licenseDomain): \Illuminate\Http\Response
    {
        return response('', 200);
    }
    /**   * Update the specified resource in storage. */
    public function update(Request $request, LicenseDomain $licenseDomain): \Illuminate\Http\Response
    {
        return response('', 200);
    }
    /**   * Remove the specified resource from storage. */
    public function destroy(LicenseDomain $licenseDomain): \Illuminate\Http\RedirectResponse
    {
        $licenseDomain->delete();
        return redirect()->back()->with('success', 'Domain removed successfully.');
    }
}
