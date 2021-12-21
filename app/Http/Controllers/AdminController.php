<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Services\AdminService;

class AdminController extends Controller
{
    public function register (Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required|min:6',
            'is_primary' => 'boolean',
            'privileges' => 'array',
            'privileges.*' => 'distinct|in:'.implode(",", (isset($request->user()->privileges) && ! $request->user()->is_primary) ? $request->user()->privileges : Admin::$privileges_list)
        ]);

        return AdminService::register($request);
    }

    public function login (Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required'
        ]);

        return AdminService::login($request);
    }

    public function get_all (Request $request)
    {
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        return AdminService::get_all($request);
    }
}
