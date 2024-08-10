<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
          Admin::create([
              'id' => Str::uuid(),
            'name' => 'Salman',
            'username'=>'admin',
            'password' => Hash::make('pastibisa'),
            'phone'=>'08292818',
            'email'=>'slmnstudy@gmail.com'
        ]);
    }
}