<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Taxonomie optionnelle commune à toutes les entités de contenu.
     *
     * @var array<int, string>
     */
    public const CATEGORIES = [
        'Meubles',
        'Outils',
        'Vêtements',
        'Services',
        'Électronique',
        'Livres',
        'Autres',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $nom) {
            Category::updateOrCreate(
                ['slug' => Str::slug($nom)],
                ['nom' => $nom, 'actif' => true],
            );
        }
    }
}
