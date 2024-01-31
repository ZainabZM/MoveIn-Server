<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private $categories = ['Canapés', 'Chaises', 'Décoration', 'Fauteuils', 'Luminaires', 'Meubles', 'Tableaux', 'Tables',];
    public function run(): void
    {
        foreach ($this->categories as $category) {
            Category::create([
                'category' => $category
            ]);
        }
        echo array_rand($this->categories);
    }
}
