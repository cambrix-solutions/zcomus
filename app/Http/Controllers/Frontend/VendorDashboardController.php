<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class VendorDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Welcome to the Vendor Dashboard',
        ]);
    }
}
