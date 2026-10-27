<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuarios
        $users = [
            [
                'name' => 'Administrador UniFood',
                'email' => 'admin@unifood.bo',
                'rol' => 'admin',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Empleado Tienda',
                'email' => 'empleado@unifood.bo',
                'rol' => 'empleado',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Juan Pérez',
                'email' => 'estudiante@unifood.bo',
                'rol' => 'estudiante',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Productos
        $productos = [
            [
                'nombre' => 'Café Americano',
                'descripcion' => 'Café negro tradicional recién filtrado.',
                'precio' => 8.00,
                'categoria' => 'bebidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Capuchino',
                'descripcion' => 'Café espresso con leche espumada y canela.',
                'precio' => 12.00,
                'categoria' => 'bebidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Jugo Natural',
                'descripcion' => 'Jugo fresco de frutas de la temporada.',
                'precio' => 10.00,
                'categoria' => 'bebidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Agua Mineral',
                'descripcion' => 'Agua mineral embotellada 500ml.',
                'precio' => 5.00,
                'categoria' => 'bebidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Salteña',
                'descripcion' => 'Tradicional salteña de carne con caldo jugoso.',
                'precio' => 7.00,
                'categoria' => 'comidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Empanada',
                'descripcion' => 'Empanada horneada de queso fundido.',
                'precio' => 6.00,
                'categoria' => 'comidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Sándwich de Pollo',
                'descripcion' => 'Sándwich con pechuga desmenuzada y vegetales frescos.',
                'precio' => 15.00,
                'categoria' => 'comidas',
                'disponible' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Hamburguesa Simple',
                'descripcion' => 'Carne a la parrilla, queso, lechuga y tomate.',
                'precio' => 18.00,
                'categoria' => 'comidas',
                'disponible' => true,
                'activo' => true,
            ],
        ];

        foreach ($productos as $productoData) {
            Producto::updateOrCreate(
                ['nombre' => $productoData['nombre']],
                $productoData
            );
        }
    }
}
