<?php

use Illuminate\Support\Facades\Route;
use Cloudinary\Cloudinary;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('produk.index');
})->name('produk.index');

Route::get('/produk/{id}', function ($id) {
    return view('produk.detail', compact('id'));
})->name('produk.detail');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/produk/edit/{id}', function ($id) {
    return view('admin.edit-produk', compact('id'));
});

Route::get('/admin/produk', function () {
    return view('admin.produk');
})->name('admin.produk');

// Route Pesanan (URL /admin/pesanan)
Route::get('/admin/pesanan', function () {
    return view('admin.pesanan');
})->name('admin.pesanan');

Route::get('/admin/pesanan/{id}', function ($id) {
    return view('admin.pesanan-detail', compact('id'));
});

// Route Pelanggan (analisa customer & performa produk)
Route::get('/admin/pelanggan', function () {
    return view('admin.pelanggan');
})->name('admin.pelanggan');

// Route Orders - Alias dari Pesanan (URL /admin/orders)
Route::get('/admin/orders', function () {
    return view('admin.pesanan');
});

Route::get('/admin/orders/{id}', function ($id) {
    return view('admin.pesanan-detail', compact('id'));
});

/*
|--------------------------------------------------------------------------
| UPLOAD IMAGE (CLOUDINARY)
|--------------------------------------------------------------------------
*/
Route::post('/upload-image', function (\Illuminate\Http\Request $request) {

    $request->validate([
        'image' => 'required|image|max:2048'
    ]);

    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key'    => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
        ]
    ]);

    $uploaded = $cloudinary->uploadApi()->upload(
        $request->file('image')->getRealPath(),
        ['folder' => 'produk']
    );

    return response()->json([
        'url' => $uploaded['secure_url']
    ]);
});

/*
|--------------------------------------------------------------------------
| ORDER (CUSTOMER)
|--------------------------------------------------------------------------
*/
Route::get('/order/{id}', function ($id) {
    return view('order.form', compact('id'));
})->name('order.form');

Route::get('/pesanan-saya', function () {
    return view('order.pesanan-saya');
})->name('order.pesanan-saya');