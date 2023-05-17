<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    // definimos reglas de validación
    // en caso de validación exitosa, se crea un nuevo usuario con los datos proporcionados
    // se devuelve una respuesta JSON con status, mensaje, y un token

    public function create(Request $request) {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $user = User::create([
            'name' => $request->name, 'email' => $request->email,
            'password' => Hash::make($request->password)  // La clase 'Hash' tiene una función 'bcrypt' para cifrar contraseñas
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Usuario creado satisfactoriamente',
            'token' => $user->createToken('API TOKEN')->plainTextToken  // Se crea el token de autenticación. El argumento es el nombre que se le asigna.
        ], 200);                                                        // El método 'plainTextToken' devuelve el valor del token en texto plano

        // TODO: No generar token aquí. Este método debe llamar al login. El token solo se genera allí
    }

    // definimos reglas de validación y se comprueban las credenciales
    // si va bien, se da prioridad al usuario al que corresponda el email, generándose un nuevo token
    // se devuelve status, mensaje, data con el usuario, y el nuevo token

    public function login(Request $request) {
        $rules = [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        if(!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'errors' => ['Unauthorized']
            ], 401);
        }
        $user = User::where('email', $request->email)->first();  // el método first hace que se nos ofrezca el primer resultado de la consulta, es decir para obtener al usuario. Sin él devolvería null
        return response()->json([
            'status' => true,
            'message' => 'Usuario logueado satisfactoriamente',
            'data' => $user,
            'token' => $user->createToken('API TOKEN')->plainTextToken
        ], 200);
    }

    // cerramos sesión eliminando todos los tokens de acceso asociados a la cuenta del usuario

    public function logout() {
        // if (!$request->user()) {
        //     return response()->json([
        //         'status' => false,
        //         'errors' => 'Unauthorized'
        //     ], 401);
        // }
        auth()->user()->tokens()->delete(); // identificamos al usuario logueado, obtenemos sus tokens, y finalmente y los eliminamos
        return response()->json([
            'status' => true,
            'message' => 'Usuario deslogueado satisfactoriamente'
        ], 200);
    }
}
