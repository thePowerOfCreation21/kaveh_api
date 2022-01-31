<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;

class LicenseController extends Controller
{
    public function store (Request $request)
    {
        (new License())->store_by_request($request);

        return response()->json([
            'message' => 'license stored successfully'
        ]);
    }
}
