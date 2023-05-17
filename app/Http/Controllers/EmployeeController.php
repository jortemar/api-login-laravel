<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use DB;
use Illuminate\Http\Request;


class EmployeeController extends Controller
{
    // devuelve una lista paginada de todos los empleados en la base de datos.
    // consultamos por empleado y departamento con un INNER JOIN, para que nos aparezca el nombre del departamento en lugar de solo el ID
    // con el join() unimos las dos tablas mediante sus respectivas claves primarias y foráneas
    // con el método paginate() obtenemos los reusltados de la consulta en grupos de 10 empleados
    // se devuelve una respuesta JSON con los empleados
    public function index()
    {
        $employees = Employee::select('employees.*', 'departments.name as department')
        ->join('departments', 'departments.id', '=', 'employees.department_id')
        ->paginate(10);
        return response()->json($employees);
    }

    // se definen las reglas de validación para los campos del array 'rules'. El objeto Validator valida o no
    // si se valida se crea un nuevo objeto Employee y se guarda en la base de datos.
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|min:1|max:100',
            'email' => 'required|email|max:80',
            'phone' => 'required|max:15',
            'department_id' => 'required|numeric'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $employee = new Employee($request->input());
        $employee->save();
        return response()->json([
            'status' => true,
            'message' => 'Empleado creado satisfactoriamente'
        ], 200);
    }

    // devuelve los detalles de un empleado concreto
    public function show(Employee $employee)
    {
        return response()->json(['status' => true, 'data' => $employee]);
    }

    // se definen reglas de validación, y Validator nos muestra si da error o no.
    // si no da error, se actdualiza el empleado en la base de datos con los datos introducidos
    public function update(Request $request, Employee $employee)
    {
        $rules = [
            'name' => 'required|string|min:1|max:100',
            'email' => 'required|email|max:80',
            'phone' => 'required|max:15',
            'department_id' => 'required|numeric'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $employee->update($request->input());
        return response()->json([
            'status' => true,
            'message' => 'Empleado actualizado satisfactoriamente'
        ], 200);
    }

    // elimina un empleado concreto de la base de datos
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json([
            'status' => true,
            'message' => 'Empleado eliminado satisfactoriamente'
        ], 200);
    }

    // obtiene la cantidad de empleados por departamento y devuelve el resultado en formato JSON.
    // se utiliza la clase DB para construir una consulta SQL personalizada que realiza un conteo de empleados por departamento, y los agrupa por nombre de departamento.
    // luego se ejecuta la consulta utilizando el método get() y se devuelve el resultado como una respuesta JSON
    // usando rightJoin en vez de join podemos ver también los departamentos que no cuentan con empleados. Es decir, por defecto
    // toma el leftJoin, usando como referencia el count(employees.id). Indicándole el right la referencia para mostrar resultados serán los departamentos

    // cuenta los empleados por id, y te da sus detalles y también el nombre del departamento. Se agrupan por departamento
    public function EmployeesByDepartment() {
        $employees = Employee::select(DB::raw('count(employees.id) as count, departments.name'))
        ->rightJoin('departments', 'departments.id', '=', 'employees.department_id') //igualamos las primary y foreign key de las dos tablas
        ->groupBy('departments.name')->get();
        return response()->json($employees);
    }

    // devuelve la información de todos los empleados, incluido el nombre del departamento
    public function all() {
        $employees = Employee::select('employees.*', 'departments.name as department')
        ->join('departments', 'departments.id', '=', 'employees.department_id')
        ->get();
        return response()->json($employees);
    }

    // TODO: intentar optimizar las dos funciones en una sola, con un parámetro para indicar si pagina o no
}
