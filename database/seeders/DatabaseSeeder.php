<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed categories
        $categories = ['Breakfast', 'Lunch', 'Dinner', 'Desserts', 'Snacks', 'Drinks', 'Vegetarian', 'Vegan'];
        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }

        // Seed a demo user
        $user = User::create([
            'name'     => 'Demo Chef',
            'email'    => 'chef@example.com',
            'password' => bcrypt('password'),
        ]);

        // Seed a sample recipe
        $recipe = Recipe::create([
            'user_id'      => $user->id,
            'category_id'  => 1,
            'title'        => 'Classic Pancakes',
            'description'  => 'Fluffy and delicious classic pancakes perfect for breakfast.',
            'prep_time'    => 10,
            'cook_time'    => 20,
            'servings'     => 4,
            'difficulty'   => 'easy',
            'instructions' => "1. Mix dry ingredients in a bowl.\n2. Whisk wet ingredients separately.\n3. Combine wet and dry ingredients.\n4. Cook on a greased pan over medium heat until golden.",
            'is_published' => true,
        ]);

        $recipe->ingredients()->createMany([
            ['name' => 'All-purpose flour', 'quantity' => '2', 'unit' => 'cups'],
            ['name' => 'Milk',              'quantity' => '1.5', 'unit' => 'cups'],
            ['name' => 'Eggs',              'quantity' => '2', 'unit' => null],
            ['name' => 'Butter',            'quantity' => '2', 'unit' => 'tbsp'],
            ['name' => 'Sugar',             'quantity' => '2', 'unit' => 'tbsp'],
            ['name' => 'Baking powder',     'quantity' => '1', 'unit' => 'tsp'],
            ['name' => 'Salt',              'quantity' => '0.5', 'unit' => 'tsp'],
        ]);
    }
}
