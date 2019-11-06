<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        app('db')->table('role_user')->truncate();
        $hofa = User::create([
            'username' => 'hofa',
            'password' => Hash::make("a123456"),
        ]);

        $dadong = User::create([
            'username' => 'dadong',
            'password' => Hash::make("a123456"),
        ]);

        $test1 = User::create([
            'username' => 'test1',
            'password' => Hash::make("a123456"),
            'status' => 'Close',
        ]);

        $p = Role::where('name', '玩家')->first();
        $hofa->roles()->attach($p);

        $m = Role::where('name', '管理')->first();
        $dadong->roles()->attach($m);
    }
}
