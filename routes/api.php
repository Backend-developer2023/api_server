<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\WinnerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(
    [
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
        Route::post('google', [AuthController::class, 'googleLogin']);
        Route::post('one_click', [AuthController::class, 'oneClick']);
    }
);

Route::apiResource('clients', ClientController::class)->middleware('auth:api');
Route::get('mancala/test', \App\Http\Controllers\Mancala\TestController::class);
Route::get('rates', \App\Http\Controllers\RatesController::class);
//Route::apiResource('providers', ProviderController::class);
//Route::apiResource('slides', SlideController::class);
//Route::apiResource('winners', WinnerController::class);

Route::group(['prefix' => 'admin', 'middleware' => ['is_admin']],
    function () {
        Route::post(
            '/clients/{id}/balance',
            \App\Http\Controllers\Admin\ClientUpdateBalanceController::class
        );
        Route::get(
            '/metrics',
            \App\Http\Controllers\Admin\MetricsController::class
        );
        Route::post(
            '/confirm_withdraw',
            \App\Http\Controllers\Admin\ConfirmWithdrawController::class
        );
        Route::post(
            '/wallet_withdraw',
            \App\Http\Controllers\Admin\WalletWithdrawController::class
        );
        Route::post(
            '/wallet_invoice',
            \App\Http\Controllers\Admin\WalletInvoiceController::class
        );
    }
);

Route::prefix('payment')->group(
    function () {
        Route::get(
            '/balance',
            \App\Http\Controllers\Payment\BalanceController::class
        )->middleware('is_admin');
        Route::post(
            '/invoice',
            \App\Http\Controllers\Payment\UserInvoiceController::class
        )->middleware('auth:api');
        Route::get(
            '/payment_status',
            \App\Http\Controllers\Payment\Status\PaymentStatusController::class
        );
        Route::get(
            '/withdraw_status',
            \App\Http\Controllers\Payment\Status\WithdrawStatusController::class
        );
        Route::post(
            '/withdraw',
            \App\Http\Controllers\Payment\UserWithdrawController::class
        )->middleware('auth:api');
        Route::get(
            '/wallet_withdraw_status',
            \App\Http\Controllers\Payment\Status\WalletWithdrawStatusController::class
        );
        Route::get(
            '/wallet_invoice_status',
            \App\Http\Controllers\Payment\Status\WalletInvoiceStatusController::class
        );
    }
);

Route::prefix('cabinet')->group(
    function () {
        Route::get(
            '/games',
            \App\Http\Controllers\GameController::class
        );
        Route::get(
            '/refresh_games',
            \App\Http\Controllers\Cabinet\GamesController::class
        );
        Route::post(
            '/start_game',
            \App\Http\Controllers\Cabinet\StartGameController::class
        )->middleware('auth:api');
        Route::post(
            '/demo_game',
            \App\Http\Controllers\Cabinet\DemoGameController::class
        );
        Route::post(
            '/close_session',
            \App\Http\Controllers\Cabinet\CloseSessionController::class
        )->middleware('auth:api');
        Route::get(
            '/strikes',
            \App\Http\Controllers\Cabinet\StrikeModeController::class
        )->middleware('auth:api');
    }
);

Route::prefix('wallet_service')->group(
    function () {
        Route::post(
            '/GetBalance',
            [\App\Http\Controllers\TomHorn\WalletServiceController::class, 'GetBalance']
        );
        Route::post(
            '/Withdraw',
            [\App\Http\Controllers\TomHorn\WalletServiceController::class, 'Withdraw']
        );
        Route::post(
            '/Deposit',
            [\App\Http\Controllers\TomHorn\WalletServiceController::class, 'Deposit']
        );
        Route::post(
            '/RollbackTransaction',
            [\App\Http\Controllers\TomHorn\WalletServiceController::class, 'RollbackTransaction']
        );
    }
);

Route::prefix('mancala_service')->group(
    function () {
        Route::post(
            '/Balance',
            [\App\Http\Controllers\Mancala\MancalaServiceController::class, 'Balance']
        );
        Route::post(
            '/Credit',
            [\App\Http\Controllers\Mancala\MancalaServiceController::class, 'Credit']
        );
        Route::post(
            '/Debit',
            [\App\Http\Controllers\Mancala\MancalaServiceController::class, 'Debit']
        );
        Route::post(
            '/Refund',
            [\App\Http\Controllers\Mancala\MancalaServiceController::class, 'Refund']
        );
    }
);
