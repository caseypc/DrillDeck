<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopBallOption;
use Illuminate\Database\Seeder;

class ShopBallCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Shop::query()->each(fn (Shop $shop) => ShopBallOption::seedDefaults($shop));
    }
}
