<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Offer;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\OfferController;
use App\Http\Controllers\API\PassController;
use App\Http\Controllers\API\BusinessController;

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

// Verificación del email
// Auth::routes(['verify' => true]);

// Rutas para Login y Registro
Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    // Route::post('login', 'login')->middleware('verified');
    Route::post('login', 'login');
});

// Ruta para confirmar el email
Route::get(
    '/register/verify/{id}/{code}',
    [RegisterController::class, 'confirm']
)->name('confirm');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para cambiar el Password
Route::controller(PassController::class)->group(function () {
    Route::post('codeModifyPass', 'codeModifyPass');
});

Route::controller(PassController::class)->group(function () {
    Route::post('ModifyPass', 'ModifyPass');
});

// Rutas para obtener las ofertas (sin Middleware)
Route::get('/offers/all', function () {
    return new \App\Http\Resources\Offer(App\Models\Offer::with(["business", "enjoys"])->paginate(30));
});

// Rutas para obtener las Ofertas (con Middleware)
Route::middleware('auth:api')->group( function () {
    Route::controller(OfferController::class)->group(function () {
        Route::post('offersFree', 'offersFree');
    });
    Route::controller(OfferController::class)->group(function () {
        Route::post('newOffersFree', 'newOffersFree');
    });

    // La siguiente ruta almacena una offerta usada por un usuario
    Route::controller(OfferController::class)->group(function () {
        Route::post('userOfferFree', 'userOfferFree');
    });

    // La siguiente ruta crea una offerta
    Route::controller(OfferController::class)->group(function () {
        Route::post('newOffer', 'newOffer');
    });

    // Ruta para obtener el listado de empresas
    Route::controller(BusinessController::class)->group(function () {
        Route::post('getBusinesses', 'getBusinesses');
    });

    // Ruta para crear una nueva empresa
    Route::controller(BusinessController::class)->group(function () {
        Route::post('newBusiness', 'newBusiness');
    });

    // Ruta para cargar nuevos usuarios desde el excel
    Route::controller(RegisterController::class)->group(function () {
        Route::post('addUsersExcel', 'addUsersExcel');
    });

    // La siguiente ruta aumenta en 1 el campo 'count' de la oferta pulsada
    Route::controller(OfferController::class)->group(function () {
        Route::post('addOffersCount', 'addOffersCount');
    });

    // La siguiente ruta envía por email las veces que se ha pinchado en cada oferta
    Route::controller(OfferController::class)->group(function () {
        Route::post('sendStadistics', 'sendStadistics');
    });

    // La siguiente ruta, es posible eliminarla
    Route::resource('offers', OfferController::class);
});

Route::middleware('auth:api')->group( function () {
    // Ruta para obtener el listado de empresas
    // Route::controller(BusinessController::class)->group(function () {
    //     Route::post('getBusinesses', 'getBusinesses');
    // });

    // Ruta para registrar usuarios mediante excel (con Middleware)
    Route::controller(RegisterController::class)->group(function () {
        Route::post('registerExcel', 'registerExcel');
    });

    // Ruta para obtener los datos del Usuario con Token (con Middleware)
    Route::controller(RegisterController::class)->group(function () {
        Route::post('renew', 'renew');
    });

    // Ruta para confirmar la compra de la tarjeta
    Route::controller(RegisterController::class)->group(function () {
        Route::post('cardPayed', 'cardPayed');
    });

    // Ruta para añadir una foto al usuario
    Route::controller(RegisterController::class)->group(function () {
        Route::post('addPhoto', 'addPhoto');
    });

    // Ruta para borrar un usuario
    Route::controller(RegisterController::class)->group(function () {
        Route::post('deleteUser', 'deleteUser');
    });
});
