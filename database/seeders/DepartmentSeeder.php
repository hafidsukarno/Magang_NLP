<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder {
    public function run() {
        $departments = [
            ['name'=>'Information Technology (IT)', 'quota'=>10],
            ['name'=>'CSR', 'quota'=>3],
            ['name'=>'Finance', 'quota'=>2],
            ['name'=>'Maintenance', 'quota'=>2],
            ['name'=>'Operation', 'quota'=>4],
            ['name'=>'Technical', 'quota'=>3],
        ];
        
        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                ['quota' => $dept['quota']]
            );
        }
    }
}
