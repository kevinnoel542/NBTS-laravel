<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\DonationResource;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $donations = $request->user()->donations()->with('bloodCenter')->get();
        return DonationResource::collection($donations);
    }
}
