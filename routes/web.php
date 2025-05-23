<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\McategoryController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomebannerController;

// Auth
Auth::routes();

// Public welcome page
Route::get('/', function () {
    return view('welcome');
});

// Admin Auth Pages (Login)
Route::prefix('admin')->middleware('admin.guest')->group(function () {
    Route::view('/login', 'admin.login')->name('admin.login');
    Route::post('/login', [AdminController::class, 'adminlogin'])->name('adminlogin.submit');
});

// Admin Protected API Routes
Route::prefix('admin')->middleware('admin.auth')->group(function () {

    // admin Logout
    Route::get('/logout', [AdminController::class, 'adminlogout'])->name('admin.logout');

    // Customers User routes
    Route::get('/users/vlist', [AdminController::class, 'userVlist'])->name('users.vlist');
    Route::post('/users/update-approval', [AdminController::class, 'updateUserApproval']);

    // Options routes
    Route::get('/moptions/vlist', [AdminController::class, 'moptionsVlist'])->name('moptions.vlist');
    Route::post('/moption/add', [AdminController::class, 'addMoption'])->name('moption.add');
    Route::post('/moption/update', [AdminController::class, 'editMoption'])->name('moption.edit');
    Route::post('/moption-delete', [AdminController::class, 'deleteMoption']);

    // Brands routes
    Route::get('/mbrands/vlist', [AdminController::class, 'mbrandVlist'])->name('mbrands.vlist');
    Route::post('/mbrands/add', [AdminController::class, 'addBrand'])->name('mbrand.add');
    Route::post('/mbrands/update', [AdminController::class, 'editBrand'])->name('mbrand.edit');
    Route::post('/mbrand-delete', [AdminController::class, 'deleteBrand']);

    // Tags routes
    Route::get('/mtags/vlist', [AdminController::class, 'mtagVlist'])->name('mtags.vlist');

    // Products routes
    Route::get('/products/vlist', [AdminController::class, 'adminProductlist'])->name('products.vlist');
    Route::get('/product/pdata', [AdminController::class, 'productAddData'])->name('adminproduct.pdata');
    Route::post('/product-duplicate', [AdminController::class, 'productDuplicate'])->name('mproduct.duplicate');
    Route::post('/product-delete', [AdminController::class, 'deleteProduct']);
    Route::post('/products/bulk-delete', [AdminController::class, 'bulkDeleteProduct']);
    Route::post('/save-product', [AdminController::class, 'productStoreData'])->name('adminproduct.storedata');
    Route::post('/update-product', [AdminController::class, 'updateProductData'])->name('adminproduct.update-product');
    Route::get('/vproduct/editdata', [AdminController::class, 'productEditData'])->name('adminproduct.editdata');

    // Products more bulk option routes
    Route::post('/products-bulk/mark-status', [AdminController::class, 'productsBulkmarkStatus']);
    Route::post('/products-bulk/delete', [AdminController::class, 'productsBulkDelete']);
    Route::post('/products-bulk/add-tags', [AdminController::class, 'productsBulkAddTags']);
    Route::post('/products-bulk/remove-tags', [AdminController::class, 'productsBulkRemoveTags']);
    // Product add view add type, brand, tag
    Route::post('/mproduct-types', [AdminController::class, 'storeProductType']);
    Route::post('/mbrands', [AdminController::class, 'storeBrand']);
    Route::post('/mtags', [AdminController::class, 'storeTag']);

    // Offers
    Route::get('/product-offers/vlist', [AdminController::class, 'productofferVlist'])->name('productoffers.vlist');
    Route::post('/product-offers/add', [AdminController::class, 'addProductoffer'])->name('productoffer.add');
    Route::post('/product-offers/update', [AdminController::class, 'editProductoffer'])->name('productoffer.edit');
    Route::post('/product-offers/delete', [AdminController::class, 'deleteProductoffer'])->name('productoffer.delete');

    // Main Categories routes
    Route::get('/main-mcategories/vlist', [McategoryController::class, 'mainMcatVlist'])->name('mainmcats.vlist');
    Route::post('/main-mcategory/add', [McategoryController::class, 'addMainMcat'])->name('mainmcat.add');
    Route::post('/main-mcategory/update', [McategoryController::class, 'editMainMcat'])->name('mainmcat.edit');
    Route::post('/main-mcategory-delete', [McategoryController::class, 'deleteMainMcat']);
    Route::post('/main-mcategories/reorder', [McategoryController::class, 'mainCatReorder']);

    // Categories routes
    Route::get('/mcategories/vlist', [McategoryController::class, 'mcatVlist'])->name('mcats.vlist');
    Route::post('/mcategory/add', [McategoryController::class, 'addMcat'])->name('mcat.add');
    Route::post('/mcategory/update', [McategoryController::class, 'editMcat'])->name('mcat.edit');
    Route::post('/mcategory-delete', [McategoryController::class, 'deleteMcat']);
    Route::post('/mcategories/bulk-delete', [McategoryController::class, 'bulkDeleteMcat']);

    // Sub-Categories routes
    Route::get('/msub-categories/vlist', [McategoryController::class, 'mcatsubVlist'])->name('msubcats.vlist');
    Route::get('/vsub-category/editdata/{msubcatid}', [McategoryController::class, 'msubcatEditData'])->name('msubcat.editdata');
    Route::post('/msub-category/add', [McategoryController::class, 'addMsubcat'])->name('msubcat.add');
    Route::post('/msub-category/{msubcatid}/update', [McategoryController::class, 'updateMsubcatData'])->name('msubcat.update');

    Route::post('/msub-category-delete', [McategoryController::class, 'deleteMsubcat']);
    Route::post('/msub-categories/bulk-delete', [McategoryController::class, 'bulkDeleteMsubcat']);

    // Sub-Categories Collection API routes
    Route::get('/mcollproducts/vlist', [McategoryController::class,'productsVlist'])->name('mcollproducts.vlist');
    Route::get('/querys/vlist', [McategoryController::class,'querysVlist'])->name('querys.vlist');
    
    // Browse sliders routes
    Route::get('/browsebanners/vlist', [BannerController::class, 'browseBannerVlist'])->name('browsebanners.vlist');
    Route::post('/browsebanners/add', [BannerController::class, 'addBrowseBanner'])->name('browsebanner.add');
    Route::post('/browsebanners/update', [BannerController::class, 'editBrowseBanner'])->name('browsebanner.edit');
    Route::post('/browsebanners/reorder', [BannerController::class, 'reorder']);
    Route::post('/browsebanner-delete', [BannerController::class, 'deleteBrowseBanner'])->name('browsebanner.delete');

    // Main Category api categories->sub-categories->products
    Route::get('/main/categories', [BannerController::class, 'index']);
});


        


// Home Routes
Route::get('/home', [HomeController::class, 'index'])->name('home');

// User Routes
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');


// Vue SPA Catch-All Route for admin
Route::get('/admin/{any}', function () {
    return view('spa'); // This blade contains your Vue <div id="app">
})->where('any', '.*')->middleware('admin.auth');

// Vue SPA Catch-All for front pages if needed
Route::get('/{any}', function () {
    return view('spa');
})->where('any', '^(?!admin).*$');

