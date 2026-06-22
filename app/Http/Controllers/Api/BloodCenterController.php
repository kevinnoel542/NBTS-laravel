<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BloodCenterResource;
use App\Services\BloodCenterService;
use Illuminate\Http\Request;

class BloodCenterController extends Controller
{
    protected $bloodCenterService;

    public function __construct(BloodCenterService $bloodCenterService)
    {
        $this->bloodCenterService = $bloodCenterService;
    }

    public function index()
    {
        $centers = $this->bloodCenterService->getActiveCenters();
        return BloodCenterResource::collection($centers);
    }

    public function show($id)
    {
        $center = $this->bloodCenterService->getCenterById($id);
        if (!$center) {
            return response()->json(['message' => 'Blood center not found'], 404);
        }
        return new BloodCenterResource($center);
    }
}
