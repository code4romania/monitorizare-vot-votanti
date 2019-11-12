<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $random_password = $this->generatePassword();
        DB::table('users')->insert([
            'id' => 1,
            'firstName' => 'Admin',
            'lastName' => 'Doe',
            'email' => 'admin@mail.com',
            'role' => 'admin',
            'password' => bcrypt($random_password)
        ]);
        /* DB::table('users')->insert([
            'id' => 2,
            'firstName' => 'Observer',
            'lastName' => 'Doe',
            'email' => 'observer@mail.com',
            'role' => 'observer',
            'password' => bcrypt('123456')
        ]); */
    }
    private function generatePassword() {
        $random_password = str_random(10);
        if (isset($this->command)) {
            $this->command->getOutput()->writeln("<info>Password for admin :</info> $random_password");
        }
        return $random_password;
    }
}
