<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public const CATEGORIES = [
        "Demande d'aide",
        'Objets et Matériel',
        'Services',
        'Événements',
        'Groupes locaux',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $nom) {
            Category::updateOrCreate(['nom' => $nom], ['nom' => $nom]);
        }
    }
}
