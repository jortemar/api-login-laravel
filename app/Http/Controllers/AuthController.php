<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    // definimos reglas de validación
    // en caso de validación exitosa, se crea un nuevo usuario con los datos proporcionados
    // se devuelve una respuesta JSON con status, mensaje, y un token

    public function index(Request $request) {
        // $users = User::all();
        $page = $request->query('page', 1);
        $users = User::paginate(5, ['*'], 'page', $page);
        // param 1: usuarios,  param 2: columnas (en este caso todas)
        // param 3: nombre parámetro URL, param 4: número página actual
        return response()->json($users);
    }

    public function getUser($id) {  // el id entra como parámetro en la url en lugar de en la request
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    public function create(Request $request) {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)  // La clase 'Hash' tiene una función 'bcrypt' para cifrar contraseñas
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Usuario creado satisfactoriamente',
            'token' => $user->createToken('API TOKEN')->plainTextToken  // Se crea el token de autenticación. El argumento es el nombre que se le asigna.
        ], 200);                                                        // El método 'plainTextToken' devuelve el valor del token en texto plano

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

    public function updatePhoto(Request $request) {
        $rules = [
            'email' => 'required|string|email|max:100',
            'photo' => 'mimes:jpeg,jpg,png|max:10240'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = $photo->getClientOriginalName();
            $path = $photo->storeAs('/user-avatars', $photoName, 'avatars');
            // $photo->storeAs('public/images', $photoName);
            // $imageUrl = Storage::url('images/' . $photoName);
            $imageUrl = Storage::disk('avatars')->url($path);

            $user->photo = $imageUrl;
            $user->save();
        }


        // $user->photo = $request->input('photo');
        // $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Imagen actualizada satisfactoriamente',
            'data' => $user,
        ], 200);
    }

    public function deletePhoto(Request $request) {
        $rules = [
            'email' => 'required|string|email|max:100'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!empty($user->photo)) {
            if(Storage::disk('avatars')->exists($user->photo)) {
                Storage::disk('avatars')->delete($user->photo);
            }

            $user->photo = null;
            $user->save();
        }


        return response()->json([
            'status' => true,
            'message' => 'Imagen eliminada satisfactoriamente',
            'data' => $user,
        ], 200);
    }


    public function updatePassword(Request $request) {
        $rules = [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
            'newPassword' => 'required|string|min:6'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (Hash::check($request->input('password'), $user->password)) {
            $user->password = Hash::make($request->input('newPassword'));
        } else {
            return response()->json([
                'status' => false,
                'message' => 'La contraseña introducida no es correcta'
            ], 400);
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Contraseña actualizada satisfactoriamente',
            'data' => $user,
        ], 200);
    }

    public function update(Request $request) {
        $rules = [
            'name' => 'required|string|max:100',
            'surname' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:100',
            'newEmail' => 'required|string|email|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'boolean'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        // realizamos la búsqueda del usuario por email, que también es único
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->email = $request->input('newEmail');
        $user->address = $request->input('address');
        $user->phone = $request->input('phone');
        $user->is_admin = $request->input('is_admin');

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Usuario actualizado satisfactoriamente',
            'data' => $user,
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
        auth()->user()->tokens()->delete(); // identificamos al usuario logueado, obtenemos sus tokens y los eliminamos
        return response()->json([
            'status' => true,
            'message' => 'Usuario deslogueado satisfactoriamente'
        ], 200);
    }
}


// docker
// laravel
// nuxt, quasar, vue
// mysql
// mongodb (no relacional)
// git
// node.js
