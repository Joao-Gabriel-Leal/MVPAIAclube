<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            PlanSeeder::class,
            ClubDemoSeeder::class,
            MembershipInvoiceSeeder::class,
            AabbPresentationSeeder::class,
        ]);
    }
}
