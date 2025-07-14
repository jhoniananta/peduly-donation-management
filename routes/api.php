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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DonorController;
use Illuminate\Support\Facades\Mail;

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

    Route::get('/role', [RoleController::class, 'index'])->middleware('ability:role.index');
    Route::post('/role', [RoleController::class, 'store'])->middleware('ability:role.create');
    Route::put('/role/{id}', [RoleController::class, 'store'])->middleware('ability:role.edit');
    Route::delete('/role/{id}', [RoleController::class, 'destroy'])->middleware('ability:role.delete');
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

    Route::post('/update-plan', [SubscriptionController::class, 'updatePlan'])->middleware('ability:plan.update');

    // Subscription upgrade routes
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgradePlan']);
    Route::post('/subscription/check-payment', [SubscriptionController::class, 'checkSubscriptionPayment']);
    Route::get('/subscription/current', [SubscriptionController::class, 'getUserSubscription']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription']);
    Route::post('/subscription/extend', [SubscriptionController::class, 'extendSubscription']);
    Route::get('/subscription/history', [SubscriptionController::class, 'getSubscriptionHistory']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/all', [NotificationController::class, 'getAll']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications', [NotificationController::class, 'clearAllNotifications']);

    // Donor routes
    Route::get('/donors', [DonorController::class, 'index']);
    Route::get('/donors/{id}', [DonorController::class, 'show']);
    Route::put('/donors/{id}', [DonorController::class, 'update']);
    Route::post('/donors/resend-receipt', [DonorController::class, 'resendDonationReceipt']);
    Route::get('/donors-export', [DonorController::class, 'exportDonors']);
    Route::get('/donors-statistics', [DonorController::class, 'statistics']);
});
