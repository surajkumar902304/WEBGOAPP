<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Browsebanner;

class BannerController extends Controller
{
    
    public function browseBanner()
    {
        $browseSliders = Browsebanner::orderBy('browsebanner_position')->get();

        return response()->json([
            'status' => true,
            'message'     => 'Fetch all Browse Sliders Successfully',
            'cdnURL'     => config('cdn.url'),
            'browseBanners' => $browseSliders
        ]);
    }
}
