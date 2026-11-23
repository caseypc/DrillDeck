<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopBallOption;
use App\Models\User;
use App\Models\BowlingBallModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DrillDeckDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local','testing'])) {
            $this->command?->warn('Demo data is restricted to local/testing environments.');
            return;
        }

        $owner=User::firstOrCreate(['email'=>'demo-owner@example.test'],[
            'name'=>'Demo Shop Owner','password'=>Hash::make('demo-password-123456'),
        ]);
        $staff=User::firstOrCreate(['email'=>'demo-staff@example.test'],[
            'name'=>'Demo Employee','password'=>Hash::make('demo-password-123456'),
        ]);
        $shop=Shop::firstOrCreate(['slug'=>'drilldeck-demo'],[
            'name'=>'DrillDeck Demo Pro Shop','owner_user_id'=>$owner->id,
        ]);
        $shop->employees()->syncWithoutDetaching([
            $owner->id=>['role'=>'owner'],
            $staff->id=>['role'=>'staff'],
        ]);

        // Keep the invitation table represented without creating a usable invitation.
        DB::table('staff_invitations')->updateOrInsert(
            ['shop_id'=>$shop->id,'email'=>'expired-invite@example.test'],
            ['invited_by_user_id'=>$owner->id,'role'=>'staff',
                'token_hash'=>hash('sha256','expired-demonstration-only'),
                'expires_at'=>now()->subDay(),'accepted_at'=>now()->subDay(),
                'created_at'=>now(),'updated_at'=>now()]
        );
        if (! $shop->columns()->exists())$shop->seedDefaultColumns();
        ShopBallOption::seedDefaults($shop);
        $received=$shop->columns()->where('name','Received')->firstOrFail();
        $drilling=$shop->columns()->where('name','Drilling')->firstOrFail();
        $ready=$shop->columns()->where('name','Ready')->firstOrFail();

        $shop->rules()->firstOrCreate(['event'=>'assigned'],[
            'name'=>'Begin assigned work','target_column_id'=>$drilling->id,'is_active'=>true,
        ]);

        $fixtures=[
            ['name'=>'Alex Morgan','email'=>'alex@example.test','phone'=>'+13165550101',
                'balls'=>[['Storm','Phaze II',15,'Solid Reactive','Symmetrical'],
                    ['Hammer','Black Widow',14,'Hybrid Reactive','Asymmetrical']]],
            ['name'=>'Jordan Lane','email'=>'jordan@example.test','phone'=>'+13165550102',
                'balls'=>[['Motiv','Venom Shock',15,'Solid Reactive','Symmetrical']]],
            ['name'=>'Taylor Reyes','email'=>'taylor@example.test','phone'=>'+13165550103',
                'balls'=>[['Brunswick','Rhino',12,'Pearl Reactive','Symmetrical']]],
        ];

        foreach($fixtures as $n=>$fixture){
            $customer=$shop->customers()->firstOrCreate(['email'=>$fixture['email']],[
                'name'=>$fixture['name'],'phone'=>$fixture['phone'],
                'notes'=>'Sample customer for local demonstration only.',
            ]);
            $fitting=$customer->fittings()->firstOrCreate(['revision'=>1],[
                'shop_id'=>$shop->id,'created_by'=>$owner->id,'name'=>'Initial fitting',
                'handedness'=>$n%2===0?'right':'left','grip_style'=>'fingertip',
                'middle_span'=>'4 3/8','ring_span'=>'4 1/2','thumb_size'=>'15/16',
                'bridge'=>'1/4','thumb_pitch_reverse'=>'3/16',
                'notes'=>'Sample measurements only; do not use as drilling instructions.',
            ]);

            foreach($fixture['balls'] as $i=>[$brand,$ballName,$weight,$cover,$core]){
                $catalog=BowlingBallModel::firstOrCreate(['manufacturer'=>$brand,'name'=>$ballName],[
                    'core'=>$core,'coverstock'=>$cover,
                ]);
                $ball=$shop->balls()->firstOrCreate([
                    'customer_id'=>$customer->id,'ball_name'=>$ballName,
                ],[
                    'customer_name'=>$customer->name,
                    'bowling_ball_model_id'=>$catalog->id,
                    'manufacturer'=>$brand,'weight_lbs'=>$weight,
                    'coverstock_type'=>$cover,'core_type'=>$core,'surface'=>'Factory Finish',
                ]);
                $order=$shop->orders()->firstOrCreate([
                    'customer_id'=>$customer->id,
                    'title'=>'Sample ball fitting · '.$ballName,
                ],[
                    'customer_name'=>$customer->name,'customer_ball_id'=>$ball->id,
                    'assigned_user_id'=>$staff->id,
                    'priority'=>$n===0?'rush':'normal',
                    'workflow_column_id'=>$n===2?$received->id:($i===0?$drilling->id:$ready->id),
                ]);
                if ($order->customer_ball_id===null)$order->forceFill(['customer_ball_id'=>$ball->id])->save();

                if (! $ball->specs()->exists()){
                    $ball->specs()->create([
                        'shop_id'=>$shop->id,'order_id'=>$order->id,
                        'fitting_spec_id'=>$fitting->id,'created_by'=>$staff->id,'revision'=>1,
                        'drilling_angle'=>'50','pin_to_pap'=>'4 1/2','val_angle'=>'45',
                        'layout'=>'50 x 4.5 x 45',
                        'notes'=>'Illustrative sample. Verify with a qualified ball driller.',
                    ]);
                }
                if (! DB::table('order_status_history')->where('order_id',$order->id)->exists()){
                    DB::table('order_status_history')->insert([
                        'order_id'=>$order->id,'from_column_id'=>null,
                        'to_column_id'=>$order->workflow_column_id,
                        'actor_user_id'=>$owner->id,'created_at'=>now(),'updated_at'=>now(),
                    ]);
                }
            }
        }
        $this->command?->info('Sample shop, staff, customers, ball arsenal, fittings, layouts, orders and workflow rules populated.');
        $this->command?->warn('Local demonstration accounts: demo-owner@example.test and demo-staff@example.test / demo-password-123456');
    }
}
