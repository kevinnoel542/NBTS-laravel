<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BloodCenter;
use Illuminate\Contracts\View\View;

final class PublicPageController extends Controller
{
    public function about(): View
    {
        return view('web.about');
    }

    public function contact(): View
    {
        $centers = BloodCenter::query()
            ->select(['id', 'name', 'address', 'city', 'phone', 'opening_hours'])
            ->active()
            ->orderBy('city')
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('web.contact', compact('centers'));
    }

    public function donate(): View
    {
        return view('web.donate');
    }

    public function download(): View
    {
        return view('web.download');
    }

    public function eligibility(): View
    {
        return view('web.eligibility');
    }

    public function faq(): View
    {
        return view('web.faq');
    }

    public function services(): View
    {
        return view('web.services');
    }

    public function privacy(): View
    {
        return view('web.legal', ['page' => 'privacy']);
    }

    public function dataProtection(): View
    {
        return view('web.legal', ['page' => 'data_protection']);
    }

    public function terms(): View
    {
        return view('web.legal', ['page' => 'terms']);
    }

    public function cookies(): View
    {
        return view('web.legal', ['page' => 'cookies']);
    }

    public function complaints(): View
    {
        return view('web.legal', ['page' => 'complaints']);
    }
}
