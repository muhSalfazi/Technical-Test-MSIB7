<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Devisi; 
use Illuminate\Support\Str;
class DevisiSeeder extends Seeder
{
    public function run()
    {
        $divisions = [
            'Mobile Apps', 'QA', 'Full Stack', 'Backend', 'Frontend', 'UI/UX Designer'
        ];

        foreach ($divisions as $division) {
            Devisi::create([
                  'id' => Str::uuid(),
                'name' => $division,
            ]);
        }
    }
}