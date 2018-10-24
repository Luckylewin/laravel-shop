<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 给每个用户建立 1~3 个收货地址
        User::all()->each(function(User $user) {
            factory(\App\Models\UserAddress::class, random_int(1,3))->create(['user_id' => $user->id]);
        });
    }
}
