<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Aquí se definen los métodos de siembra 'run()' . Dentro de run() se llama al método factory()
        // de los modelos Department y Employee, y se les pasa los parámetros deseados
        \App\Models\Department::factory(6)->create();
        \App\Models\Employee::factory(25)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
