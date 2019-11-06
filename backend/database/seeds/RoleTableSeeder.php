<?php

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();

        Role::create([
            'name' => '超级管理员',
        ]);

        Role::create([
            'name' => '玩家',
        ]);

        Role::create([
            'name' => '运营',
        ]);

        Role::create([
            'name' => '代理',
        ]);

        for ($i = 0; $i < 100; $i++) {
            Role::create(['name' => $i]);
        }
    }
}
