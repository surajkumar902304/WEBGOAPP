<?php

namespace App\Http\Controllers;

use App\Models\HomeLargeBanner;
use App\Models\HomeRoundBanner;
use App\Models\HomeSmallBanner;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class HomebannerController extends Controller
{
    // Home Round Banners
    public function roundBannerVlist()
    {
        $banners = HomeRoundBanner::with([
            'category:mcat_id,mcat_name',
            'subcategory:msubcat_id,msubcat_name',
            'product:mproduct_id,mproduct_title'
        ])
        ->orderBy('home_round_banner_position')
        ->get()
        ->map(function ($b) {
            return [
                'home_round_banner_id'      => $b->home_round_banner_id,
                'home_round_banner_name'    => $b->home_round_banner_name,
                'home_round_banner_image'   => $b->home_round_banner_image,
                'home_round_banner_position'=> $b->home_round_banner_position,

                'main_mcat_id'    => $b->main_mcat_id,
                'mcat_id'    => $b->mcat_id,
                'msubcat_id' => $b->msubcat_id,
                'mproduct_id'=> $b->mproduct_id,

                'mcat_name'     => optional($b->category)->mcat_name,
                'msubcat_name'  => optional($b->subcategory)->msubcat_name,
                'mproduct_title'=> optional($b->product)->mproduct_title,
            ];
        });

        return response()->json([
        'status'       => true,
        'home_round_banner' => $banners
        ], 200);
    }
    public function roundreorder(Request $request)
    {
        foreach ($request->all() as $item) {
            HomeRoundBanner::where('home_round_banner_id', $item['id'])
                ->update(['home_round_banner_position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function addRoundBanner(Request $request)
    {
        $request->validate([
            'main_mcat_id' => 'nullable|exists:main_categories,main_mcat_id',
            'mcat_id' => 'nullable|exists:mcategories,mcat_id',
            'msubcat_id' => 'nullable|exists:msubcategories,msubcat_id',
            'mproduct_id' => 'nullable|exists:mproducts,mproduct_id',
            'home_round_banner_name'  => 'required|string|max:50',
            'home_round_banner_image' => 'nullable|image|max:2048',
        ]);

        $banner_imgpath = null;
        if ($request->hasFile('home_round_banner_image')) {
            $image  = $request->file('home_round_banner_image');
            $filename = 'home_round_banner_' . uniqid() . '.png';
            $img = Image::make($image->getRealPath());
            $banner_imgpath      = 'goapp/images/home_round_banner/' . $filename;
            Storage::disk('s3')->put($banner_imgpath, (string) $img->encode());
        }

        $home_round_banner  = new HomeRoundBanner();
        $home_round_banner->main_mcat_id    = $request->main_mcat_id;
        $home_round_banner->mcat_id    = $request->mcat_id;
        $home_round_banner->msubcat_id    = $request->msubcat_id;
        $home_round_banner->mproduct_id    = $request->mproduct_id;
        $home_round_banner->home_round_banner_name    = $request->home_round_banner_name;
        $home_round_banner->home_round_banner_image   = $banner_imgpath;
        $home_round_banner->home_round_banner_position = HomeRoundBanner::max('home_round_banner_position') + 1;
        $home_round_banner->save();

        return response()->json(['status' => true]);
    }

    public function editRoundBanner(Request $request)
    {
        $request->validate([
            'home_round_banner_id'    => 'required|exists:home_round_banners,home_round_banner_id',
            'main_mcat_id' => 'nullable|exists:main_categories,main_mcat_id',
            'mcat_id' => 'nullable|exists:mcategories,mcat_id',
            'msubcat_id' => 'nullable|exists:msubcategories,msubcat_id',
            'mproduct_id' => 'nullable|exists:mproducts,mproduct_id',
            'home_round_banner_name'  => 'required|string|max:255',
            'home_round_banner_image' => 'nullable|image|max:2048',
        ]);

        $home_round_banner = HomeRoundBanner::find($request->home_round_banner_id);
        $home_round_banner->main_mcat_id  = $request->main_mcat_id;
        $home_round_banner->mcat_id  = $request->mcat_id;
        $home_round_banner->msubcat_id  = $request->msubcat_id;
        $home_round_banner->mproduct_id  = $request->mproduct_id;
        $home_round_banner->home_round_banner_name  = $request->home_round_banner_name;
        $banner_imgpath = $home_round_banner->home_round_banner_image;

        if ($request->hasFile('home_round_banner_image')) {
            if (!empty($banner_imgpath) && Storage::disk('s3')->exists($banner_imgpath)) {
                Storage::disk('s3')->delete($banner_imgpath);
            }
            $image = $request->file('home_round_banner_image');
            $filename = 'home_round_banner_' . uniqid() . '.png';
            $img = Image::make($image->getRealPath());
            
            $banner_imgpath      = "goapp/images/home_round_banner/$filename";
            Storage::disk('s3')->put($banner_imgpath, (string) $img->encode());

            $home_round_banner->home_round_banner_image = $banner_imgpath;
        }

        $home_round_banner->save();

        return response()->json(['status' => true]);
    }
    public function deleteRoundBanner(Request $request)
    {
        $request->validate([
            'home_round_banner_id'    => 'required|exists:home_round_banners,home_round_banner_id',
        ]);

        try {
            $home_round_banner = HomeRoundBanner::findOrFail($request->home_round_banner_id);

            if ($home_round_banner->home_round_banner_image && Storage::disk('s3')->exists($home_round_banner->home_round_banner_image)) {
                Storage::disk('s3')->delete($home_round_banner->home_round_banner_image);
            }

            $home_round_banner->delete();

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Home Large Banner
    public function largeBannerVlist()
    {
        $banners = HomeLargeBanner::with([
            'category:mcat_id,mcat_name',
            'subcategory:msubcat_id,msubcat_name',
            'product:mproduct_id,mproduct_title'
        ])
        ->orderBy('home_large_banner_position')
        ->get()
        ->map(function ($b) {
            return [
                'home_large_banner_id'      => $b->home_large_banner_id,
                'home_large_banner_name'    => $b->home_large_banner_name,
                'home_large_banner_image'   => $b->home_large_banner_image,
                'home_large_banner_position'=> $b->home_large_banner_position,

                'main_mcat_id'    => $b->main_mcat_id,
                'mcat_id'    => $b->mcat_id,
                'msubcat_id' => $b->msubcat_id,
                'mproduct_id'=> $b->mproduct_id,

                'mcat_name'     => optional($b->category)->mcat_name,
                'msubcat_name'  => optional($b->subcategory)->msubcat_name,
                'mproduct_title'=> optional($b->product)->mproduct_title,
            ];
        });

        return response()->json([
        'status'       => true,
        'home_large_banner' => $banners
        ], 200);
    }
    public function largereorder(Request $request)
    {
        foreach ($request->all() as $item) {
            HomeLargeBanner::where('home_large_banner_id', $item['id'])
                ->update(['home_large_banner_position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function addLargeBanner(Request $request)
    {
        $request->validate([
            'main_mcat_id' => 'nullable|exists:main_categories,main_mcat_id',
            'mcat_id' => 'nullable|exists:mcategories,mcat_id',
            'msubcat_id' => 'nullable|exists:msubcategories,msubcat_id',
            'mproduct_id' => 'nullable|exists:mproducts,mproduct_id',
            'home_large_banner_name'  => 'required|string|max:50',
            'home_large_banner_image' => 'nullable|image|max:2048',
        ]);

        $banner_imgpath = null;
        if ($request->hasFile('home_large_banner_image')) {
            $image  = $request->file('home_large_banner_image');
            $filename = 'home_large_banner_' . uniqid() . '.png';
            $img = Image::make($image->getRealPath());
            $banner_imgpath      = 'goapp/images/home_large_banner/' . $filename;
            Storage::disk('s3')->put($banner_imgpath, (string) $img->encode());
        }

        $home_large_banner  = new HomeLargeBanner();
        $home_large_banner->main_mcat_id    = $request->main_mcat_id;
        $home_large_banner->mcat_id    = $request->mcat_id;
        $home_large_banner->msubcat_id    = $request->msubcat_id;
        $home_large_banner->mproduct_id    = $request->mproduct_id;
        $home_large_banner->home_large_banner_name    = $request->home_large_banner_name;
        $home_large_banner->home_large_banner_image   = $banner_imgpath;
        $home_large_banner->home_large_banner_position = HomeLargeBanner::max('home_large_banner_position') + 1;
        $home_large_banner->save();

        return response()->json(['status' => true]);
    }

    public function editLargeBanner(Request $request)
    {
        $request->validate([
            'home_large_banner_id'    => 'required|exists:home_large_banners,home_large_banner_id',
            'main_mcat_id' => 'nullable|exists:main_categories,main_mcat_id',
            'mcat_id' => 'nullable|exists:mcategories,mcat_id',
            'msubcat_id' => 'nullable|exists:msubcategories,msubcat_id',
            'mproduct_id' => 'nullable|exists:mproducts,mproduct_id',
            'home_large_banner_name'  => 'required|string|max:255',
            'home_large_banner_image' => 'nullable|image|max:2048',
        ]);

        $home_large_banner = HomeLargeBanner::find($request->home_large_banner_id);
        $home_large_banner->main_mcat_id  = $request->main_mcat_id;
        $home_large_banner->mcat_id  = $request->mcat_id;
        $home_large_banner->msubcat_id  = $request->msubcat_id;
        $home_large_banner->mproduct_id  = $request->mproduct_id;
        $home_large_banner->home_large_banner_name  = $request->home_large_banner_name;
        $banner_imgpath = $home_large_banner->home_large_banner_image;

        if ($request->hasFile('home_large_banner_image')) {
            if (!empty($banner_imgpath) && Storage::disk('s3')->exists($banner_imgpath)) {
                Storage::disk('s3')->delete($banner_imgpath);
            }
            $image = $request->file('home_large_banner_image');
            $filename = 'home_large_banner_' . uniqid() . '.png';
            $img = Image::make($image->getRealPath());
            
            $banner_imgpath      = "goapp/images/home_large_banner/$filename";
            Storage::disk('s3')->put($banner_imgpath, (string) $img->encode());

            $home_large_banner->home_large_banner_image = $banner_imgpath;
        }

        $home_large_banner->save();

        return response()->json(['status' => true]);
    }
    public function deleteLargeBanner(Request $request)
    {
        $request->validate([
            'home_large_banner_id'    => 'required|exists:home_large_banners,home_large_banner_id',
        ]);

        try {
            $home_large_banner = HomeLargeBanner::findOrFail($request->home_large_banner_id);

            if ($home_large_banner->home_large_banner_image && Storage::disk('s3')->exists($home_large_banner->home_large_banner_image)) {
                Storage::disk('s3')->delete($home_large_banner->home_large_banner_image);
            }

            $home_large_banner->delete();

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Home Small banner 
    public function smallBannerVlist()
    {
        $banners = HomeSmallBanner::with([
            'category:mcat_id,mcat_name',
            'subcategory:msubcat_id,msubcat_name',
            'product:mproduct_id,mproduct_title'
        ])
        ->orderBy('home_small_banner_position')
        ->get()
        ->map(function ($b) {
            return [
                'home_small_banner_id'      => $b->home_small_banner_id,
                'home_small_banner_name'    => $b->home_small_banner_name,
                'home_small_banner_image'   => $b->home_small_banner_image,
                'home_small_banner_position'=> $b->home_small_banner_position,

                'main_mcat_id'    => $b->main_mcat_id,
                'mcat_id'    => $b->mcat_id,
                'msubcat_id' => $b->msubcat_id,
                'mproduct_id'=> $b->mproduct_id,

                'mcat_name'     => optional($b->category)->mcat_name,
                'msubcat_name'  => optional($b->subcategory)->msubcat_name,
                'mproduct_title'=> optional($b->product)->mproduct_title,
            ];
        });

        return response()->json([
        'status'       => true,
        'home_small_banner' => $banners
        ], 200);
    }

    public function smallreorder(Request $request)
    {
        foreach ($request->all() as $item) {
            HomeSmallBanner::where('home_small_banner_id', $item['id'])
                ->update(['home_small_banner_position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function addSmallBanner(Request $request)
    {
        $request->validate([
            'main_mcat_id' => 'nullable|exists:main_categories,main_mcat_id',
            'mcat_id' => 'nullable|exists:mcategories,mcat_id',
            'msubcat_id' => 'nullable|exists:msubcategories,msubcat_id',
            'mproduct_id' => 'nullable|exists:mproducts,mproduct_id',
            'home_small_banner_name'  => 'required|string|max:50',
            'home_small_banner_image' => 'nullable|image|max:2048',
        ]);

        $banner_imgpath = null;
        if ($request->hasFile('home_small_banner_image')) {
            $image  = $request->file('home_small_banner_image');
            $filename = 'home_small_banner_' . uniqid() . '.png';
            $img = Image::make($image->getRealPath());
            $banner_imgpath      = 'goapp/images/home_small_banner/' . $filename;
            Storage::disk('s3')->put($banner_imgpath, (string) $img->encode());
        }

        $home_small_banner  = new HomeSmallBanner();
        $home_small_banner->main_mcat_id    = $request->main_mcat_id;
        $home_small_banner->mcat_id    = $request->mcat_id;
        $home_small_banner->msubcat_id    = $request->msubcat_id;
        $home_small_banner->mproduct_id    = $request->mproduct_id;
        $home_small_banner->home_small_banner_name    = $request->home_small_banner_name;
        $home_small_banner->home_small_banner_image   = $banner_imgpath;
        $home_small_banner->home_small_banner_position = HomeSmallBanner::max('home_small_banner_position') + 1;
        $home_small_banner->save();

        return response()->json(['status' => true]);
    }

    public function editSmallBanner(Request $request)
    {
        $request->validate([
            'home_small_banner_id'    => 'required|exists:home_small_banners,home_small_banner_id',
            'main_mcat_id' => 'nullable|exists:main_categories,main_mcat_id',
            'mcat_id' => 'nullable|exists:mcategories,mcat_id',
            'msubcat_id' => 'nullable|exists:msubcategories,msubcat_id',
            'mproduct_id' => 'nullable|exists:mproducts,mproduct_id',
            'home_small_banner_name'  => 'required|string|max:255',
            'home_small_banner_image' => 'nullable|image|max:2048',
        ]);

        $home_small_banner = HomeSmallBanner::find($request->home_small_banner_id);
        $home_small_banner->main_mcat_id  = $request->main_mcat_id;
        $home_small_banner->mcat_id  = $request->mcat_id;
        $home_small_banner->msubcat_id  = $request->msubcat_id;
        $home_small_banner->mproduct_id  = $request->mproduct_id;
        $home_small_banner->home_small_banner_name  = $request->home_small_banner_name;
        $banner_imgpath = $home_small_banner->home_small_banner_image;

        if ($request->hasFile('home_small_banner_image')) {
            if (!empty($banner_imgpath) && Storage::disk('s3')->exists($banner_imgpath)) {
                Storage::disk('s3')->delete($banner_imgpath);
            }
            $image = $request->file('home_small_banner_image');
            $filename = 'home_small_banner_' . uniqid() . '.png';
            $img = Image::make($image->getRealPath());
            
            $banner_imgpath      = "goapp/images/home_small_banner/$filename";
            Storage::disk('s3')->put($banner_imgpath, (string) $img->encode());

            $home_small_banner->home_small_banner_image = $banner_imgpath;
        }

        $home_small_banner->save();

        return response()->json(['status' => true]);
    }

    public function deleteSmallBanner(Request $request)
    {
        $request->validate([
            'home_small_banner_id'    => 'required|exists:home_small_banners,home_small_banner_id',
        ]);

        try {
            $home_small_banner = HomeSmallBanner::findOrFail($request->home_small_banner_id);

            if ($home_small_banner->home_small_banner_image && Storage::disk('s3')->exists($home_small_banner->home_small_banner_image)) {
                Storage::disk('s3')->delete($home_small_banner->home_small_banner_image);
            }

            $home_small_banner->delete();

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
