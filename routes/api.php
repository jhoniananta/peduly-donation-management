<?php

use App\Http\Controllers\DonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\FundraisingController;
use App\Http\Controllers\SubscriptionController;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::get('/user/reset', action: [AuthController::class, 'sendEmailResetPassword']);
Route::post('/user/reset', [AuthController::class, 'resetPassword']);

Route::get('/plan', [SubscriptionController::class, 'listPlan']);
Route::get('/fundraising', [FundraisingController::class, 'index']);
Route::get('/fundraising/{id}', [FundraisingController::class, 'show']);
Route::post('/donation', [DonationController::class, 'donation']);
Route::post('/donation-callback', [DonationController::class, 'callback']);
Route::post('/donation-check', [DonationController::class, 'checkStatus']);

Route::get('/company-find/{id}', [CompanyController::class, 'findCompany']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/user/activate', action: [AuthController::class, 'sendActivationEmail']);
    // Route::post('/user/activate', [AuthController::class, 'activateUser']);

    Route::get('/feature', [FeatureController::class, 'index']);
    Route::post('/feature', [FeatureController::class, 'assign'])->middleware('ability:feature.assign');
    Route::post('/feature-unassign', [FeatureController::class, 'unassign'])->middleware('ability:feature.unassign');

    Route::get('/role',[RoleController::class,'index'])->middleware('ability:role.index');
    Route::post('/role',[RoleController::class,'store'])->middleware('ability:role.create');
    Route::put('/role/{id}',[RoleController::class,'store'])->middleware('ability:role.edit');
    Route::delete('/role/{id}',[RoleController::class,'destroy'])->middleware('ability:role.delete');
    Route::post('/role-assign', [RoleController::class, 'assign'])->middleware('ability:role.assign');
    Route::post('/role-unassign', [RoleController::class, 'unassign'])->middleware('ability:role.unassign');

    Route::get('/company', [CompanyController::class, 'index'])->middleware('ability:company.index');
    Route::put('/company/{id}', [CompanyController::class, 'update'])->middleware('ability:company.update');
    Route::post('/company', [CompanyController::class, 'verification'])->middleware('ability:company.verify');
    
    Route::get('/users', [UserController::class, 'index'])->middleware('ability:users.index');
    Route::post('/users', [UserController::class, 'create'])->middleware('ability:users.create');
    Route::put('/users', [UserController::class, 'update'])->middleware('ability:users.edit');

    Route::post('/fundraising', [FundraisingController::class, 'store'])->middleware('ability:fundraising.create');
    Route::put('/fundraising/{id}', [FundraisingController::class, 'update'])->middleware('ability:fundraising.edit');
    Route::post('/fundraising-news/', [FundraisingController::class, 'storeNews'])->middleware('ability:fundraising_news.create');

    Route::post('/plan', [SubscriptionController::class, 'createPlan'])->middleware('ability:plan.create');
    
});
