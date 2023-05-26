<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // se obtienen todos los registros de la tabla 'departments', y con el response() se genera una respuesta HTTP con el contenido de $departments en formato JSON
    public function index()
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    // se almacena un nuevo departamento en la base de datos. Primero se definen las reglas de validación para los datos, y se utiliza el objeto Validator para
    // comprobar si la solicitud cumple con las reglas definidas. Si falla la validación se devuelve error 400. Si no, se crea objeto de la clase Department, se
    // almacena en la base de datos, y se devuelve un código de estado HTTP 200, con un mensaje de éxito
    public function store(Request $request)
    {
        $rules = ['name' => 'required|string|min:1|max:100'];
        $validator = \Validator::make($request->input(), $rules);
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $department = new Department($request->input());
        $department->save();
        return response()->json([
            'status' => true,
            'message' => 'Departamento creado satisfactoriamente'
        ], 200);
    }

    // devuelve los detalles de un departamento específico
    public function show(Department $department)
    {
        return response()->json(['status' => true, 'data' => $department]);
    }

    // actualiza un departamento existente en la base de datos. El parámetro $request contiene la solititud HTTP entrante, y $department es el departamento a actualizar
    // se validan las entradas del usuario, y de nuevo error 400 o éxito 200.
    public function update(Request $request, Department $department)
    {
        $rules = ['name' => 'required|string|min:1|max:100'];
        $validator = \Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $department->update($request->input());
        return response()->json([
            'status' => true,
            'message' => 'Departamento actualizado satisfactoriamente'
        ], 200);
    }

    // elimina un departamento de la base de datos y emite respuesta satisfactoria
    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json([
            'status' => true,
            'message' => 'Departamento eliminado satisfactoriamente'
        ], 200);
    }
}
