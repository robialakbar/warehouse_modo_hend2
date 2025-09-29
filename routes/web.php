<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderSyncController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/search', [App\Http\Controllers\ProductController::class, 'search'])->name('search');

Route::prefix('master')->group(function () {
    Route::get('products', [App\Http\Controllers\ProductController::class, 'products'])->name('products');
    Route::post('products', [App\Http\Controllers\ProductController::class, 'product_save'])->name('products.save');
    Route::delete('products', [App\Http\Controllers\ProductController::class, 'product_delete'])
        ->name('products.delete')
        ->middleware('adminRole');
    Route::get('services', [App\Http\Controllers\ProductController::class, 'services'])->name('services');
    Route::post('services', [App\Http\Controllers\ProductController::class, 'services_save'])->name('services.save');
    Route::delete('services', [App\Http\Controllers\ProductController::class, 'services_delete'])
        ->name('services.delete')
        ->middleware('adminRole');
    Route::get('city', [App\Http\Controllers\ProductController::class, 'city'])->name('city');
    Route::post('city', [App\Http\Controllers\ProductController::class, 'city_save'])
        ->name('city.save')
        ->middleware('adminRole');
    Route::delete('city', [App\Http\Controllers\ProductController::class, 'city_delete'])
        ->name('city.delete')
        ->middleware('adminRole');
});

Route::prefix('products')->group(function () {
    Route::get('wip', [App\Http\Controllers\ProductController::class, 'products_wip'])->name('products.wip');
    Route::post('wip', [App\Http\Controllers\ProductController::class, 'product_wip_save'])->name('products.wip.save');
    Route::delete('wip', [App\Http\Controllers\ProductController::class, 'product_wip_delete'])->name('products.wip.delete');
    Route::post('wip/complete', [App\Http\Controllers\ProductController::class, 'product_wip_complete'])->name('products.wip.complete');
    Route::get('wipHistory', [App\Http\Controllers\ProductController::class, 'products_wip_history'])->name('products.wip.history');
    Route::get('/check/{pcode}', [App\Http\Controllers\ProductController::class, 'product_check'])->name('products.check');
    Route::post('/stockUpdate', [App\Http\Controllers\ProductController::class, 'product_stock'])->name('products.stock');
    Route::get('/stockHistory', [App\Http\Controllers\ProductController::class, 'product_stock_history'])->name('products.stock.history');
    Route::get('categories', [App\Http\Controllers\ProductController::class, 'categories'])->name('products.categories');
    Route::post('categories', [App\Http\Controllers\ProductController::class, 'categories_save'])
        ->name('products.categories.save')
        ->middleware('adminRole');
    Route::delete('categories', [App\Http\Controllers\ProductController::class, 'categories_delete'])
        ->name('products.categories.delete')
        ->middleware('adminRole');
    Route::get('barcode/{code}', [App\Http\Controllers\ProductController::class, 'generateBarcode'])->name('products.barcode');
    Route::post('import', [App\Http\Controllers\ProductController::class, 'product_import'])->name('products.import');
    Route::post('wipImport', [App\Http\Controllers\ProductController::class, 'product_wip_import'])->name('products.wip.import');
});

Route::prefix('users')->group(function () {
    Route::get('', [App\Http\Controllers\UserController::class, 'users'])
        ->name('users')
        ->middleware('adminRole');
    Route::delete('', [App\Http\Controllers\UserController::class, 'user_delete'])
        ->name('users.delete')
        ->middleware('adminRole');
    Route::post('', [App\Http\Controllers\UserController::class, 'user_save'])
        ->name('users.save')
        ->middleware('adminRole');
});

Route::prefix('warehouse')->group(function () {
    Route::get('', [App\Http\Controllers\ProductController::class, 'warehouse'])
        ->name('warehouse')
        ->middleware('adminRole');
    Route::delete('', [App\Http\Controllers\ProductController::class, 'warehouse_delete'])
        ->name('warehouse.delete')
        ->middleware('adminRole');
    Route::post('', [App\Http\Controllers\ProductController::class, 'warehouse_save'])
        ->name('warehouse.save')
        ->middleware('adminRole');
    Route::get('change/{warehouse_id}', [App\Http\Controllers\ProductController::class, 'warehouse_select'])->name('warehouse.select');
});

Route::prefix('account')->group(function () {
    Route::get('', [App\Http\Controllers\UserController::class, 'myaccount'])->name('myaccount');
    Route::post('profile', [App\Http\Controllers\UserController::class, 'myaccount_update'])->name('myaccount.update');
    Route::post('password', [App\Http\Controllers\UserController::class, 'myaccount_update_password'])->name('myaccount.updatePassword');
});

Route::prefix('settings')->group(function () {
    Route::get('', [App\Http\Controllers\UserController::class, 'settings'])
        ->name('settings')
        ->middleware('adminRole');
    Route::post('', [App\Http\Controllers\UserController::class, 'settings_update'])
        ->name('settings.update')
        ->middleware('adminRole');
});

Route::prefix('order')->group(function () {
    Route::get('', [App\Http\Controllers\ProductController::class, 'order'])->name('order');
    Route::delete('', [App\Http\Controllers\ProductController::class, 'order_delete'])->name('order.delete');
    Route::post('', [App\Http\Controllers\ProductController::class, 'order_save'])->name('order.save');
    Route::post('process', [App\Http\Controllers\ProductController::class, 'order_proses'])->name('order.process');
    Route::post('done', [App\Http\Controllers\ProductController::class, 'order_done'])->name('order.done');
    Route::get('list', [App\Http\Controllers\ProductController::class, 'order_list'])->name('order.list');
    Route::post('payment', [App\Http\Controllers\ProductController::class, 'order_payment'])->name('order.payment');
    Route::get('cetakInvoice/{id?}', [App\Http\Controllers\ProductController::class, 'cetak_invoice'])->name('cetakInvoice');
    Route::post('repeat', [App\Http\Controllers\ProductController::class, 'order_repeat'])->name('order.repeat');
    Route::get('cekNopol', [App\Http\Controllers\ProductController::class, 'nopol_check'])->name('check.nopol');
    Route::get('cancel', [App\Http\Controllers\ProductController::class, 'order_cancel'])->name('order.cancel');
});

Route::prefix('laporan')->group(function () {
    Route::get('', [App\Http\Controllers\ProductController::class, 'laporan'])->name('laporan');
});

Route::prefix('orders')->group(function () {
    Route::get('/sync', [OrderSyncController::class, 'index'])->name('orders.sync');
    Route::get('/sync/{order_id}', [OrderSyncController::class, 'sync'])->name('orders.sync.single');
    Route::get('/sync-all', [OrderSyncController::class, 'syncAll'])->name('orders.sync.all');
    Route::get('/sync-purcase-order', [OrderSyncController::class, 'syncPurcasePrice'])->name('orders.sync.purcase_price');
    Route::get('/detail/{order_id}', [OrderSyncController::class, 'show'])->name('orders.show');
});
