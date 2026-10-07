<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([Role::ADMIN, Role::MEMBER, Role::PARTNER])->each(
            fn (string $name) => Role::query()->firstOrCreate(['name' => $name])
        );
    }
}
