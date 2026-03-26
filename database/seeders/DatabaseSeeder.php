<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un admin
        User::create([
            'name'     => 'Admin Restomate',
            'email'    => 'admin@restomate.com',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
            'phone'    => '+22997000001',
            'is_active'=> true,
        ]);

        // Créer un client de test
        User::create([
            'name'     => 'Alex AGO',
            'email'    => 'alex@restomate.com',
            'password' => bcrypt('password123'),
            'role'     => 'client',
            'phone'    => '+22997000002',
            'is_active'=> true,
        ]);

        // Créer 2 restaurants avec leurs tables
        $restaurants = [
            [
                'name'         => 'Chez Mama Africa',
                'description'  => 'Restaurant africain traditionnel au cœur de Cotonou',
                'address'      => 'Rue des Cocotiers, Cadjehoun',
                'city'         => 'Cotonou',
                'phone'        => '+22997111111',
                'opening_time' => '08:00:00',
                'closing_time' => '23:00:00',
                'is_active'    => true,
            ],
            [
                'name'         => 'Le Beau Rivage',
                'description'  => 'Restaurant vue sur mer, cuisine internationale',
                'address'      => 'Boulevard de la Marina',
                'city'         => 'Cotonou',
                'phone'        => '+22997222222',
                'opening_time' => '10:00:00',
                'closing_time' => '22:00:00',
                'is_active'    => true,
            ],
        ];

        foreach ($restaurants as $data) {
            $restaurant = Restaurant::create($data);

            // Créer 5 tables pour chaque restaurant
            $locations = ['intérieur', 'terrasse', 'bar', 'privé', 'intérieur'];
            $capacities = [2, 4, 4, 6, 8];

            for ($i = 1; $i <= 5; $i++) {
                Table::create([
                    'restaurant_id' => $restaurant->id,
                    'name'          => "Table {$i}",
                    'capacity'      => $capacities[$i - 1],
                    'location'      => $locations[$i - 1],
                    'is_active'     => true,
                ]);
            }
        }
    }
}