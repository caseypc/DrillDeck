<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\FittingSpec;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class FittingRevisionService
{
    public function create(Shop $shop, Customer $customer, array $measurements, int $actorId): FittingSpec
    {
        return DB::transaction(function () use ($shop, $customer, $measurements, $actorId) {
            // Lock parent to allocate monotonic per-customer revisions on MySQL.
            $locked = Customer::where('shop_id', $shop->id)
                ->whereKey($customer->id)->lockForUpdate()->firstOrFail();

            return $locked->fittings()->create([
                ...$measurements,
                'shop_id' => $shop->id,
                'created_by' => $actorId,
                'revision' => ((int) $locked->fittings()->max('revision')) + 1,
            ]);
        }, 3);
    }
}
