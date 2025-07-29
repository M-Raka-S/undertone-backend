<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Parameter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $category = Category::create([
            'name' => 'characters',
        ]);
        Parameter::create([
            'name' => 'name',
            'identifier' => true,
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'history',
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'gender',
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'age',
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'appearance',
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'personality',
            'category_id' => $category->id,
        ]);

        $category = Category::create([
            'name' => 'factions'
        ]);
        Parameter::create([
            'name' => 'name',
            'identifier' => true,
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'goals',
            'category_id' => $category->id,
        ]);

        $category = Category::create(attributes: [
            'name' => 'powers'
        ]);
        Parameter::create([
            'name' => 'name',
            'identifier' => true,
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'effect',
            'category_id' => $category->id,
        ]);

        $category = Category::create([
            'name' => 'locations'
        ]);
        Parameter::create([
            'name' => 'name',
            'identifier' => true,
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'environment',
            'category_id' => $category->id,
        ]);

        $category = Category::create([
            'name' => 'items'
        ]);
        Parameter::create([
            'name' => 'name',
            'identifier' => true,
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'appearance',
            'category_id' => $category->id,
        ]);
         Parameter::create([
            'name' => 'purpose',
            'category_id' => $category->id,
        ]);

        $category = Category::create(attributes: [
            'name' => 'concepts'
        ]);
        Parameter::create([
            'name' => 'name',
            'identifier' => true,
            'category_id' => $category->id,
        ]);
        Parameter::create([
            'name' => 'description',
            'category_id' => $category->id,
        ]);
    }
}
