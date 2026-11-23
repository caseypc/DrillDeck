<?php

use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ShopBoardController;
use App\Http\Controllers\WorkflowColumnController;
use App\Http\Controllers\WorkflowRuleController;
use App\Http\Controllers\DrillingController;
use App\Http\Controllers\DrillingSpecController;
use App\Http\Controllers\CustomerBallController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\FittingSpecController;
use App\Http\Controllers\OrderCustomerController;
use App\Http\Controllers\ShopOrdersController;
use App\Http\Controllers\ShopCatalogController;
use App\Models\Shop;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');

Route::get('/dashboard', function () {
    return auth()->user()->shops()->exists()
        ? redirect()->route('shop.board')
        : redirect()->route('portal.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/shop', [ShopBoardController::class,'index'])->name('shop.board');
    Route::get('/shop/orders',[ShopOrdersController::class,'index'])->name('shop.orders.index');
    Route::post('/shop/orders', [ShopBoardController::class,'store'])->name('shop.orders.store');
    Route::get('/shop/orders/{order}', [ShopBoardController::class,'show'])->name('shop.orders.show');
    Route::put('/shop/orders/{order}', [ShopBoardController::class,'update'])->name('shop.orders.update');
    Route::patch('/shop/orders/{order}/move', [ShopBoardController::class,'move'])->name('shop.orders.move');

    Route::get('/shop/customers', [CustomerProfileController::class,'index'])->name('shop.customers.index');
    Route::post('/shop/customers', [CustomerProfileController::class,'store'])->name('shop.customers.store');
    Route::get('/shop/customers/{customer}', [CustomerProfileController::class,'show'])->name('shop.customers.show');
    Route::put('/shop/customers/{customer}', [CustomerProfileController::class,'update'])->name('shop.customers.update');
    Route::get('/shop/customers/{customer}/fittings/new',[FittingSpecController::class,'create'])->name('shop.customers.fittings.create');
    Route::post('/shop/customers/{customer}/fittings', [FittingSpecController::class,'store'])->name('shop.customers.fittings.store');
    Route::get('/shop/customers/{customer}/fittings/{fitting}', [FittingSpecController::class,'show'])->name('shop.customers.fittings.show');
    Route::put('/shop/orders/{order}/customer', [OrderCustomerController::class,'update'])->name('shop.orders.customer.update');

    Route::get('/shop/balls', [CustomerBallController::class,'index'])->name('shop.balls.index');
    Route::post('/shop/balls', [CustomerBallController::class,'store'])->name('shop.balls.store');
    Route::post('/shop/balls/{ball}/photo',[CustomerBallController::class,'uploadPhoto'])->name('shop.balls.photo.upload');
    Route::get('/shop/balls/{ball}/photo',[CustomerBallController::class,'photo'])->name('shop.balls.photo');
    Route::get('/shop/balls/{ball}', [CustomerBallController::class,'show'])->name('shop.balls.show');
    Route::put('/shop/balls/{ball}/customer', [CustomerBallController::class,'assignCustomer'])->name('shop.balls.customer.update');
    Route::post('/shop/orders/{order}/ball', [CustomerBallController::class,'link'])->name('shop.orders.ball.link');
    Route::delete('/shop/orders/{order}/ball', [CustomerBallController::class,'unlink'])->name('shop.orders.ball.unlink');
    Route::post('/shop/balls/{ball}/specs', [DrillingSpecController::class,'store'])->name('shop.balls.specs.store');
    Route::get('/shop/balls/{ball}/specs/{spec}', [DrillingSpecController::class,'show'])->name('shop.balls.specs.show');

    Route::get('/shop/catalog',[ShopCatalogController::class,'index'])->name('shop.catalog.index');
    Route::post('/shop/catalog',[ShopCatalogController::class,'store'])->name('shop.catalog.store');
    Route::patch('/shop/catalog/{option}',[ShopCatalogController::class,'update'])->name('shop.catalog.update');
    Route::delete('/shop/catalog/{option}',[ShopCatalogController::class,'destroy'])->name('shop.catalog.destroy');
    Route::post('/shop/catalog/reorder',[ShopCatalogController::class,'reorder'])->name('shop.catalog.reorder');

    Route::get('/shop/workflow', [WorkflowColumnController::class,'index'])->name('shop.workflow');
    Route::post('/shop/workflow', [WorkflowColumnController::class,'store'])->name('shop.workflow.store');
    Route::patch('/shop/workflow/{column}', [WorkflowColumnController::class,'update'])->name('shop.workflow.update');
    Route::delete('/shop/workflow/{column}',[WorkflowColumnController::class,'destroy'])->name('shop.workflow.destroy');
    Route::post('/shop/workflow/reorder', [WorkflowColumnController::class,'reorder'])->name('shop.workflow.reorder');
    Route::post('/shop/workflow/rules', [WorkflowRuleController::class,'store'])->name('shop.rules.store');
    Route::patch('/shop/workflow/rules/{rule}/toggle', [WorkflowRuleController::class,'toggle'])->name('shop.rules.toggle');
    Route::delete('/shop/workflow/rules/{rule}', [WorkflowRuleController::class,'destroy'])->name('shop.rules.destroy');

    Route::get('/shop/staff', [StaffController::class,'index'])->name('shop.staff.index');
    Route::post('/shop/staff/invite', [StaffController::class,'invite'])->name('shop.staff.invite');
    Route::get('/shop/invitations/{invitation}/accept/{token}', [StaffController::class,'accept'])->name('shop.invitations.accept')->middleware('throttle:10,1');

    Route::get('/shop/drilling', [DrillingController::class,'index'])->name('shop.drilling');
    Route::post('/shop/drilling', [DrillingController::class,'store'])->name('shop.drilling.store');

    Route::get('/portal', [CustomerOrderController::class,'index'])->name('portal.index');
    Route::post('/portal/{shop:slug}/orders', [CustomerOrderController::class,'store'])->name('portal.orders.store');
});

Route::get('/shops/{shop:slug}', function (Shop $shop) {
    return view('public.shop', compact('shop'));
})->name('public.shop');
