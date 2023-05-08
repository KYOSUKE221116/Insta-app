<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function run()
    {
        $this->user->name = 'The Admin';
        $this->user->email = 'admin@admin.com';
        $this->user->password = Hash::make('admin12345');
        $this->user->role_id = 1;
        $this->user->created_at = now();
        $this->user->save();   
    }
}
