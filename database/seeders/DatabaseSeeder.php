<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Safe to rerun in production, per-shop configurable catalog values.
        $this->call(ShopBallCatalogSeeder::class);

        // Only local/testing: sample users, shop memberships, work orders, layouts,
        // fitting revisions, customer records, catalog products and status history.
        if (app()->environment(['local','testing'])) {
            $this->call(DrillDeckDemoSeeder::class);
            $this->call(AdditionalDemoShopsSeeder::class);
        }
    }
}
