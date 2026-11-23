<?php

namespace Database\Seeders;

use App\Models\BowlingBallModel;
use App\Models\Shop;
use App\Models\ShopBallOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdditionalDemoShopsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Additional demo shops are restricted to local/testing.');
            return;
        }

        $shops = [
            [
                'slug' => 'strike-zone-pro-shop',
                'name' => 'Strike Zone Pro Shop',
                'owner' => ['Morgan Brooks', 'strike-owner@example.test'],
                'staff' => ['Jamie Rivera', 'strike-staff@example.test'],
                'customers' => [
                    ['Avery Parker', 'avery.strike@example.test', '+13165550201', [
                        ['Storm', 'Summit', 15, 'Hybrid Reactive', 'Symmetrical'],
                        ['Hammer', 'Black Widow 3.0', 15, 'Solid Reactive', 'Asymmetrical'],
                    ]],
                    ['Blake Carter', 'blake.strike@example.test', '+13165550202', [
                        ['Motiv', 'Venom Shock', 14, 'Solid Reactive', 'Symmetrical'],
                    ]],
                    ['Casey Nguyen', 'casey.strike@example.test', '+13165550203', [
                        ['Brunswick', 'Rhino', 13, 'Pearl Reactive', 'Symmetrical'],
                    ]],
                ],
            ],
            [
                'slug' => 'pinline-bowling-lab',
                'name' => 'Pinline Bowling Lab',
                'owner' => ['Sam Ellis', 'pinline-owner@example.test'],
                'staff' => ['Riley Foster', 'pinline-staff@example.test'],
                'customers' => [
                    ['Drew Johnson', 'drew.pinline@example.test', '+17025550301', [
                        ['Roto Grip', 'Hustle', 14, 'Pearl Reactive', 'Symmetrical'],
                        ['Storm', 'Phaze II', 15, 'Solid Reactive', 'Symmetrical'],
                    ]],
                    ['Emerson Lee', 'emerson.pinline@example.test', '+17025550302', [
                        ['Hammer', 'Raw', 12, 'Hybrid Reactive', 'Symmetrical'],
                    ]],
                    ['Finley Adams', 'finley.pinline@example.test', '+17025550303', [
                        ['Motiv', 'Venom Shock', 15, 'Solid Reactive', 'Symmetrical'],
                    ]],
                ],
            ],
        ];

        foreach ($shops as $fixture) {
            DB::transaction(function () use ($fixture) {
                [$ownerName, $ownerEmail] = $fixture['owner'];
                [$staffName, $staffEmail] = $fixture['staff'];
                $owner = User::firstOrCreate(['email' => $ownerEmail], [
                    'name' => $ownerName, 'password' => Hash::make('demo-password-123456'),
                ]);
                $staff = User::firstOrCreate(['email' => $staffEmail], [
                    'name' => $staffName, 'password' => Hash::make('demo-password-123456'),
                ]);
                $shop = Shop::firstOrCreate(['slug' => $fixture['slug']], [
                    'name' => $fixture['name'], 'owner_user_id' => $owner->id,
                ]);
                $shop->employees()->syncWithoutDetaching([
                    $owner->id => ['role' => 'owner'],
                    $staff->id => ['role' => 'staff'],
                ]);
                if (! $shop->columns()->exists()) {
                    $shop->seedDefaultColumns();
                }
                ShopBallOption::seedDefaults($shop);

                $columns = $shop->columns()->pluck('id', 'name');
                foreach ($fixture['customers'] as $customerIndex => [$name, $email, $phone, $balls]) {
                    $customer = $shop->customers()->firstOrCreate(['email' => $email], [
                        'name' => $name, 'phone' => $phone,
                        'notes' => 'Fictional customer: local demo data only.',
                    ]);
                    $fitting = $customer->fittings()->firstOrCreate(['revision' => 1], [
                        'shop_id' => $shop->id, 'created_by' => $owner->id,
                        'name' => 'Initial fitting', 'handedness' => $customerIndex === 1 ? 'left' : 'right',
                        'grip_style' => 'fingertip', 'middle_span' => '4 3/8',
                        'ring_span' => '4 1/2', 'thumb_size' => '15/16',
                        'bridge' => '1/4', 'notes' => 'Illustrative only; not drilling advice.',
                    ]);
                    foreach ($balls as $ballIndex => [$brand, $ballName, $weight, $cover, $core]) {
                        $catalog = BowlingBallModel::firstOrCreate(
                            ['manufacturer' => $brand, 'name' => $ballName],
                            ['core' => $core, 'coverstock' => $cover]
                        );
                        $ball = $shop->balls()->firstOrCreate([
                            'customer_id' => $customer->id, 'ball_name' => $ballName,
                        ], [
                            'customer_name' => $name, 'bowling_ball_model_id' => $catalog->id,
                            'manufacturer' => $brand, 'weight_lbs' => $weight,
                            'coverstock_type' => $cover, 'core_type' => $core, 'surface' => 'Factory Finish',
                        ]);
                        $stage = ['Received', 'Measured', 'Drilling', 'Ready'][($customerIndex + $ballIndex) % 4];
                        $order = $shop->orders()->firstOrCreate([
                            'customer_id' => $customer->id,
                            'title' => 'Demo fitting and drilling · '.$ballName,
                        ], [
                            'customer_name' => $name, 'assigned_user_id' => $staff->id,
                            'priority' => $customerIndex === 0 ? 'rush' : 'normal',
                            'workflow_column_id' => $columns[$stage],
                        ]);
                        if ($order->customer_ball_id !== $ball->id) {
                            $order->forceFill(['customer_ball_id' => $ball->id])->save();
                        }
                        $ball->specs()->firstOrCreate(['revision' => 1], [
                            'shop_id' => $shop->id, 'order_id' => $order->id,
                            'fitting_spec_id' => $fitting->id, 'created_by' => $staff->id,
                            'drilling_angle' => '50', 'pin_to_pap' => '4 1/2', 'val_angle' => '45',
                            'layout' => '50 x 4.5 x 45',
                            'notes' => 'Illustrative only; verify with a qualified ball driller.',
                        ]);
                        if (! DB::table('order_status_history')->where('order_id', $order->id)->exists()) {
                            DB::table('order_status_history')->insert([
                                'order_id' => $order->id, 'from_column_id' => null,
                                'to_column_id' => $columns[$stage], 'actor_user_id' => $owner->id,
                                'created_at' => now(), 'updated_at' => now(),
                            ]);
                        }
                    }
                }
            });
        }

        $this->command?->info('Two additional demo shops with independent staff, customers, fittings, balls and orders seeded.');
        $this->command?->warn('Demo credentials use the password demo-password-123456 (local/testing only).');
    }
}
