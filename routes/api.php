<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Offer;

use App\Http\Controllers\API\ElementsController;
use App\Http\Controllers\API\ExercisesController;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\OfferController;
use App\Http\Controllers\API\PassController;
use App\Http\Controllers\API\BusinessController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\GameController;
use App\Http\Controllers\API\NotificationController;

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
	Route::post('loginStudent', 'loginStudent');
	Route::post('firstTime', 'firstTime');
});

// Ruta para confirmar el email
Route::get(
    '/register/verify/{id}/{code}',
    [RegisterController::class, 'confirm']
)->name('confirm');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/////////////////////////////////
//   Rutas de PassController   //
/////////////////////////////////

// Rutas para cambiar el Password
Route::controller(PassController::class)->group(function () {
    Route::post('codeModifyPass', 'codeModifyPass');
});

Route::controller(PassController::class)->group(function () {
    Route::post('ModifyPass', 'ModifyPass');
});

Route::middleware('auth:api')->post('/notifications', [NotificationController::class, 'store']);
Route::post('/setNotificationLimit', [NotificationController::class, 'setNotificationLimit']);
//////////////////////////////////
//   Rutas de OfferController   //
//////////////////////////////////

// Rutas para obtener las ofertas (sin Middleware)
Route::get('/offers/all', function () {
    return new \App\Http\Resources\Offer(App\Models\Offer::with(["business", "enjoys"])->paginate(30));
});


// Ruta que devuelve el id de un alumno a través de su código (sin Middleware)
Route::controller(RegisterController::class)->group(function () {
    Route::post('idStudentByCode', 'idStudentByCode');
});

// Rutas para obtener las Ofertas (con Middleware)
Route::middleware('auth:api')->group( function () {
	// La siguiente ruta devuelve los Elementos: Estados de ánimo, eventos y emociones
    Route::controller(ElementsController::class)->group(function () {
        Route::get('elements', 'elements');
	});

	// La siguiente ruta devuelve los Estados de ánimo de un usuario
    Route::controller(ElementsController::class)->group(function () {
        Route::get('getMoods', 'getMoods');
	});

	// La siguiente ruta devuelve las Emociones
    Route::controller(ElementsController::class)->group(function () {
        Route::get('emotions', 'emotions');
	});

	// La siguiente ruta devuelve los Estados de Ánimo
    Route::controller(ElementsController::class)->group(function () {
        Route::get('moods', 'moods');
	});

	// La siguiente ruta crea un nuevo elemento para el usuario (evento, emoción, estado de ánimo)
    Route::controller(ElementsController::class)->group(function () {
        Route::post('newElement', 'newElement');
    });

	// La siguiente ruta devuelve todos los Ejercicios disponibles
    Route::controller(ExercisesController::class)->group(function () {
        Route::get('exercises', 'exercises');
	});

	// La siguiente ruta devuelve los Ejercicios realizados por un alumno
    Route::controller(ExercisesController::class)->group(function () {
        Route::get('exercisesByAlum', 'exercisesByAlum');
	});

	// La siguiente ruta guarda que un alumno ha hecho un ejercicio determinado
    Route::controller(ExercisesController::class)->group(function () {
        Route::post('newExerciseMade', 'newExerciseMade');
    });

	// La siguiente ruta elimina un ejercicio realizado por un alumno en la tabla "mades"
    Route::controller(ExercisesController::class)->group(function () {
        Route::post('deleteExerciseMade', 'deleteExerciseMade');
    });

	// La siguiente ruta crea un nuevo Ejercicio
    Route::controller(ExercisesController::class)->group(function () {
        Route::post('newExercise', 'newExercise');
    });

    // La siguiente ruta devuelve los datos de un Ejercicio determinado
    Route::controller(ExercisesController::class)->group(function () {
        Route::post('exerciseById', 'exerciseById');
	});

	// La siguiente ruta devuelve las offertas gratuitas
    Route::controller(OfferController::class)->group(function () {
        Route::post('offersFree', 'offersFree');
    });

    // La siguiente ruta crea una nueva oferta gratuita
    Route::controller(OfferController::class)->group(function () {
        Route::post('newOffersFree', 'newOffersFree');
    });

    // La siguiente ruta almacena una offerta usada por un usuario
    Route::controller(OfferController::class)->group(function () {
        Route::post('userOfferFree', 'userOfferFree');
    });

    // La siguiente ruta crea una offerta permanente
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

    // La siguiente ruta devuelve las Citas de las próximas 2 semanas
    Route::controller(AppointmentController::class)->group(function () {
        Route::get('appointmentsFuture', 'appointmentsFuture');
	});

    // La siguiente ruta devuelve las Citas libres para el mes siguiente
    Route::controller(AppointmentController::class)->group(function () {
        Route::get('getCalendarCounselor', 'getCalendarCounselor');
	});

    // La siguiente ruta devuelve las Citas de un profesor en un mes vista
    Route::controller(AppointmentController::class)->group(function () {
        Route::get('getAppointmentsByTeacher', 'getAppointmentsByTeacher');
	});

    // La siguiente ruta crea una Cita
    Route::controller(AppointmentController::class)->group(function () {
        Route::post('newAppointment', 'newAppointment');
    });

    // La siguiente ruta devuelve los colores de los moods
    Route::controller(ElementsController::class)->group(function () {
        Route::get('getColors', 'getColors');
    });

	// La siguiente ruta crea un nuevo test
    Route::controller(TestController::class)->group(function () {
        Route::post('newTestMade', 'newTestMade');
    });

	// La siguiente ruta obtiene todos los tests realizados por un usuario
    Route::controller(TestController::class)->group(function () {
        Route::post('getTestByUser', 'getTestByUser');
    });

	// La siguiente ruta recupera los test
    Route::controller(TestController::class)->group(function () {
        Route::get('questions', 'questions');
    });

    Route::controller(GameController::class)->group(function () {
        // La siguiente ruta recupera los juegos
        Route::get('games', 'games');
        // La siguiente ruta recupera los juegos
        Route::post('newGameMade', 'newGameMade');
        // La siguiente ruta recupera los juegos hechos correctamente
        Route::post('getPlayByUser', 'getPlayByUser');
    });

    // Route::controller(GameController::class)->group(function () {
    // });
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

    // Ruta para activar un Usuario con Token (con Middleware)
    Route::controller(RegisterController::class)->group(function () {
        Route::post('activate', 'activate');
    });

    // Ruta para desactivar un Usuario con Token (con Middleware)
    Route::controller(RegisterController::class)->group(function () {
        Route::post('deactivate', 'deactivate');
    });

    // Ruta para obtener todos los Usuarios de la BD con Token (con Middleware)
    Route::controller(RegisterController::class)->group(function () {
        Route::get('users', 'users');
    });

    // Ruta para confirmar la compra de la tarjeta
    // Route::controller(RegisterController::class)->group(function () {
    //     Route::post('cardPayed', 'cardPayed');
    // });

    // Ruta para añadir una foto al usuario
    Route::controller(RegisterController::class)->group(function () {
        Route::post('addPhoto', 'addPhoto');
    });

    // Ruta para que un usuario se pueda borrar
    Route::controller(RegisterController::class)->group(function () {
        Route::post('deleteUserSelf', 'deleteUserSelf');
    });

    // Ruta para que el Administrador pueda borrar un usuario
    Route::controller(RegisterController::class)->group(function () {
        Route::post('deleteUser', 'deleteUser');
    });

    // Ruta para que el Administrador pueda borrar un usuario
    Route::controller(RegisterController::class)->group(function () {
        Route::post('updateUser', 'updateUser');
    });

	// BORRAR ESTA RUTA
    Route::controller(RegisterController::class)->group(function () {
        Route::get('python', 'python');
    });
});
