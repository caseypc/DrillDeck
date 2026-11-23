<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ShopBallOption extends Model
{
    protected $fillable = ['shop_id', 'kind', 'label', 'parent_id', 'active', 'sort_order'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }

    public const DEFAULTS = [
        'brand' => ['Storm','Brunswick','Hammer','Motiv','Roto Grip','900 Global','Ebonite','Track','Columbia 300','DV8','Radical','Pyramid'],
        'core_type' => ['Symmetrical','Asymmetrical','Pancake','Urethane core','Other'],
        'coverstock_type' => ['Solid Reactive','Pearl Reactive','Hybrid Reactive','Urethane','Plastic / Polyester','Other'],
        'surface' => ['Factory Finish','Polished','1000 Grit','2000 Grit','3000 Grit','4000 Grit','5000 Grit'],
    ];

    public static function seedDefaults(Shop $shop): void
    {
        DB::transaction(function () use ($shop): void {
            // Lock the shop during initialization, including parallel signup/seed.
            $shop = Shop::query()->whereKey($shop->id)->lockForUpdate()->firstOrFail();
            if ($shop->ball_catalog_seeded) return;

            $position = [];
            foreach (self::DEFAULTS as $kind => $labels) {
                foreach ($labels as $label) {
                    $position[$kind] = ($position[$kind] ?? -1) + 1;
                    self::query()->firstOrCreate(
                        ['shop_id' => $shop->id, 'kind' => $kind, 'label' => $label, 'parent_id' => null],
                        ['sort_order' => $position[$kind]]
                    );
                }
            }

            $products = [
                'Storm' => ['Phaze II','Hy-Road','Ion Pro'],
                'Hammer' => ['Black Widow','Purple Pearl Urethane'],
                'Brunswick' => ['Mesmerize','Rhino'],
                'Motiv' => ['Venom Shock'],
                'Roto Grip' => ['Hustle','Attention Star'],
                '900 Global' => ['Zen'],
            ];

            foreach ($products as $brand => $names) {
                $parent = self::query()->where('shop_id', $shop->id)
                    ->where('kind', 'brand')->where('label', $brand)->firstOrFail();

                foreach ($names as $name) {
                    $position['ball_name'] = ($position['ball_name'] ?? -1) + 1;
                    self::query()->firstOrCreate(
                        ['shop_id' => $shop->id, 'kind' => 'ball_name',
                            'parent_id' => $parent->id, 'label' => $name],
                        ['sort_order' => $position['ball_name']]
                    );
                }
            }

            $shop->forceFill(['ball_catalog_seeded' => true])->save();
        });
    }
}
