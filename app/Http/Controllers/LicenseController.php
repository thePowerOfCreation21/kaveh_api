<?php

namespace App\Http\Controllers;

use App\Actions\LicenseAction;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function store (Request $request)
    {
        (new LicenseAction())->store_by_request($request);

        return response()->json([
            'message' => 'license stored successfully'
        ]);
    }
}
