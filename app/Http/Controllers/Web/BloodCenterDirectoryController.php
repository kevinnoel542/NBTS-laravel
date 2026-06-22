<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BloodCenter;
use Illuminate\Http\Request;

class BloodCenterDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = BloodCenter::where('is_active', true);

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        $centers = $query->latest()->paginate(9);

        return view('web.centers.index', compact('centers'));
    }

    public function show(BloodCenter $center)
    {
        return view('web.centers.show', compact('center'));
    }
}
