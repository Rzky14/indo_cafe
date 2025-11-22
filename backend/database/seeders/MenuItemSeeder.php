<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    /**
     * Seed menu items with Indonesian cafe specialties
     */
    public function run(): void
    {
        $menuItems = [
            // Coffee
            [
                'name' => 'Kopi Susu Gula Aren',
                'slug' => 'kopi-susu-gula-aren',
                'description' => 'Kopi hitam premium dengan susu segar dan gula aren asli',
                'price' => 25000,
                'category' => 'coffee',
                'is_available' => true,
                'stock' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Es Kopi Jahe',
                'slug' => 'es-kopi-jahe',
                'description' => 'Perpaduan unik kopi robusta dengan jahe hangat, disajikan dingin',
                'price' => 22000,
                'category' => 'coffee',
                'is_available' => true,
                'stock' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kopi Tubruk Nusantara',
                'slug' => 'kopi-tubruk-nusantara',
                'description' => 'Kopi tubruk tradisional dari biji kopi pilihan Toraja',
                'price' => 18000,
                'category' => 'coffee',
                'is_available' => true,
                'stock' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Tea
            [
                'name' => 'Teh Tarik Pandan',
                'slug' => 'teh-tarik-pandan',
                'description' => 'Teh tarik dengan aroma pandan yang khas',
                'price' => 20000,
                'category' => 'tea',
                'is_available' => true,
                'stock' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Es Teh Lemongrass',
                'slug' => 'es-teh-lemongrass',
                'description' => 'Teh hijau dengan sereh segar yang menyegarkan',
                'price' => 18000,
                'category' => 'tea',
                'is_available' => true,
                'stock' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Snacks
            [
                'name' => 'Pisang Goreng Keju',
                'slug' => 'pisang-goreng-keju',
                'description' => 'Pisang goreng renyah dengan taburan keju premium',
                'price' => 15000,
                'category' => 'snack',
                'is_available' => true,
                'stock' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Singkong Keju',
                'slug' => 'singkong-keju',
                'description' => 'Singkong goreng dengan keju mozzarella leleh',
                'price' => 17000,
                'category' => 'snack',
                'is_available' => true,
                'stock' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Risoles Mayo',
                'slug' => 'risoles-mayo',
                'description' => 'Risoles isi sayuran dan ayam dengan mayones spesial',
                'price' => 20000,
                'category' => 'snack',
                'is_available' => true,
                'stock' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Main Course
            [
                'name' => 'Nasi Goreng Kampung',
                'slug' => 'nasi-goreng-kampung',
                'description' => 'Nasi goreng pedas khas Indonesia dengan telur ceplok',
                'price' => 35000,
                'category' => 'main_course',
                'is_available' => true,
                'stock' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mie Goreng Jawa',
                'slug' => 'mie-goreng-jawa',
                'description' => 'Mie goreng dengan bumbu kecap manis khas Jawa',
                'price' => 32000,
                'category' => 'main_course',
                'is_available' => true,
                'stock' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Dessert
            [
                'name' => 'Es Cendol Durian',
                'slug' => 'es-cendol-durian',
                'description' => 'Cendol dengan santan dan topping durian medan',
                'price' => 25000,
                'category' => 'dessert',
                'is_available' => true,
                'stock' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Klepon Modern',
                'slug' => 'klepon-modern',
                'description' => 'Klepon dengan isian gula aren dan taburan kelapa parut',
                'price' => 18000,
                'category' => 'dessert',
                'is_available' => true,
                'stock' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('menu_items')->insert($menuItems);
    }
}

