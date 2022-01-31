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

    public function get (Request $request)
    {
        return response()->json(
            (new LicenseAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new LicenseAction())->get_by_id($id)
        );
    }

    public function delete_by_id (string $id)
    {
        (new LicenseAction())->delete_by_id($id);

        return response()->json([
            'message' => 'deleted successfully'
        ]);
    }
}
