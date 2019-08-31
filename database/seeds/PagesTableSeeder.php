<?php

use App\Page;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 50) as $index)
        {
            Page::create([
                'title' => $faker->text(255),
                'status' => $faker->randomElement(['Active', 'Inactive']),
                'description' => $faker->realText(500),
                'user_id' => 1
            ]);
        }
    }
}
