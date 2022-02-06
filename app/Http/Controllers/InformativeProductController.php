<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\InformativeProductAction;

class InformativeProductController extends Controller
{
    public function store (Request $request)
    {
        (new InformativeProductAction())->store_by_request($request);

        return response()->json([
            'message' => 'stored successfully'
        ]);
    }

    public function get (Request $request)
    {
        return response()->json(
            (new InformativeProductAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new InformativeProductAction())->get_by_id($id)
        );
    }

    public function update_by_id (Request $request, string $id)
    {
        (new InformativeProductAction())->update_entity_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'updated successfully'
        ]);
    }

    public function delete_by_id (string $id)
    {
        (new InformativeProductAction())->delete_by_id($id);

        return response()->json([
            'message' => 'deleted successfully'
        ]);
    }
}
