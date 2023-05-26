<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;

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

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

// En primer lugar definimos las rutas de autenticación para que los usuarios puedan registrar y autenticarse en la aplicación
// la función login no va a estar restringinda con un token, sino que también va a generar un token
Route::post('auth/register', [AuthController::class, 'create']);
Route::post('auth/login', [AuthController::class, 'login']);

// Después definimos un grupo de rutas que están protegidas por el middleware de autenticación 'auth:sanctum',
// asegurando que las rutas solo sean accesibles para usuarios autenticados con un token válido
Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('auth/users', [AuthController::class, 'index']); // igual no hay que poner el auth delante del update en el argumento del método put
    Route::get('auth/user/{id}', [AuthController::class, 'getUser']); // le pasamos el id como parámetro en la url, en lugar de en la request

    Route::put('auth/update', [AuthController::class, 'update']);
    Route::put('auth/updatepassword', [AuthController::class, 'updatePassword']);
    Route::post('auth/updatephoto', [AuthController::class, 'updatePhoto']);
    Route::post('auth/deletephoto', [AuthController::class, 'deletePhoto']);

    Route::resource('/departments', DepartmentController::class);
    Route::resource('/employees', EmployeeController::class);
    Route::get('/employeesall', [EmployeeController::class, 'all']);
    Route::get('/employeesbydepartment', [EmployeeController::class, 'employeesByDepartment']);

    // Finalmente definimos una ruta de logout que elimina el token de autenticación del usuario
    Route::get('auth/logout', [AuthController::class, 'logout']);
});




// usando el método 'resource' definimos todas las rutas del CRUD de una sola vez ('POST', 'GET', 'PUT'/'PATCH' Y 'DELETE')
// el resto de métodos que hemos generado en los controladores los llamamos con 'get'
