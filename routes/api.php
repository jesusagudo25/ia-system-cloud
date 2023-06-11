<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankCheckController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\DescriptionController;
use App\Http\Controllers\InterpreterController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LenguageController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class,'logout']);

    Route::controller(LenguageController::class)->group(function () {
        Route::get('lenguages', 'index');
        Route::get('lenguages/status', 'indexStatus');
        Route::get('lenguages/{lenguage}', 'show');
        Route::post('lenguages', 'store');
        Route::put('lenguages/{lenguage}', 'update');
        Route::delete('lenguages/{lenguage}', 'destroy');
    });

    Route::controller(AgencyController::class)->group(function () {
        Route::get('agencies', 'index');
        Route::get('agencies/status', 'indexStatus');
        Route::get('agencies/{agency}', 'show');
        Route::get('/agencies/search/{search}/', 'search');
        Route::post('agencies', 'store');
        Route::put('agencies/{agency}', 'update');
        Route::delete('agencies/{agency}', 'destroy');
    });
    
    Route::controller(InterpreterController::class)->group(function () {
        Route::get('interpreters', 'index');
        Route::get('interpreters/status', 'indexStatus');
        Route::get('interpreters/{interpreter}', 'show');
        Route::get('/interpreters/{state}/{lenguage}/{search}/', 'search');
        Route::post('interpreters', 'store');
        Route::put('interpreters/{interpreter}', 'update');
        Route::delete('interpreters/{interpreter}', 'destroy');
    });
    
    Route::controller(DescriptionController::class)->group(function () {
        Route::get('descriptions', 'index');
        Route::get('descriptions/status', 'indexStatus');
        Route::get('descriptions/{description}', 'show');
        Route::get('/descriptions/search/{search}/', 'search');
        Route::post('descriptions', 'store');
        Route::put('descriptions/{description}', 'update');
        Route::delete('descriptions/{description}', 'destroy');
    });
    
    Route::controller(InvoiceController::class)->group(function () {
        Route::get('invoices', 'index');
        Route::get('invoices/paid', 'indexPaid');
        Route::get('invoices/{invoice}', 'show');
        Route::get('/invoices/assignmentNumber/{assignmentNumber}/', 'validateAssignmentNumber');
        Route::post('invoices', 'store');
        Route::put('invoices/{invoice}', 'update');
        Route::put('invoices/new-status/{invoice}', 'newStatus');
        Route::delete('invoices/{invoice}', 'destroy');
    });
    
    Route::controller(PayrollController::class)->group(function () {
        Route::get('payrolls', 'index');
        Route::get('payrolls/{payroll}', 'show');

        Route::post('/payrolls/review', 'review');
        Route::post('payrolls', 'store');
        Route::put('payrolls/{payroll}', 'update');
        Route::delete('payrolls/{payroll}', 'destroy');
    });
    
    Route::controller(BankCheckController::class)->group(function () {
        Route::get('bank-checks', 'index');
        Route::get('bank-checks/{bankCheck}', 'show');

        Route::post('bank-checks', 'store');
        Route::put('bank-checks/{bankCheck}', 'update');
        Route::delete('bank-checks/{bankCheck}', 'destroy');
    });
    
    Route::controller(AddressController::class)->group(function () {
        Route::get('addresses', 'index');
        Route::get('addresses/{address}', 'show');
        Route::get('/addresses/search/{search}/', 'search');
        Route::post('addresses', 'store');
        Route::put('addresses/{address}', 'update');
        Route::delete('addresses/{address}', 'destroy');
    });
    
    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index');
        Route::get('users/{user}', 'show');
        Route::put('/users/{user}/password','updatePassword');
        Route::get('/users/search/{search}/', 'search');
        Route::post('users', 'store');
        Route::put('users/{user}', 'update');
        Route::delete('users/{user}', 'destroy');
    });
    
    Route::controller(CoordinatorController::class)->group(function () {
        Route::get('coordinators', 'index');
        Route::get('coordinators/{coordinator}', 'show');
        Route::get('/coordinators/search/{search}/', 'search');
        Route::post('coordinators', 'store');
        Route::put('coordinators/{coordinator}', 'update');
        Route::delete('coordinators/{coordinator}', 'destroy');
    });
    
    Route::controller(ReportController::class)->group(function () {
        Route::get('reports', 'index');
        Route::get('reports/dashboards', 'indexDashboards');
        Route::get('reports/{report}', 'show');

        Route::post('reports', 'store');
        Route::put('reports/{report}', 'update');
        Route::delete('reports/{report}', 'destroy');
    });
});

Route::controller(AuthController::class)->group(function(){
    Route::post('/login', 'login');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/reset-password', 'resetPassword');
    Route::get('/validate-token-reset', 'validateTokenReset');
    Route::get('/validate-token-access', 'validateTokenAccess');
});

Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'pdf']);
Route::get('/payrolls/{payroll}/download', [PayrollController::class, 'pdf']);
Route::get('/bank-checks/{payroll}/download', [BankCheckController::class, 'pdf']);
Route::get('/reports/{report}/download', [ReportController::class, 'pdf']);
