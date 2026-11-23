<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerBall;
use App\Models\FittingSpec;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFittingTest extends TestCase
{
    use RefreshDatabase;

    private function shop(string $slug): array
    {
        $user = User::create(['name'=>'Owner','email'=>$slug.'@example.test','password'=>'long-password']);
        $shop = Shop::create(['name'=>$slug,'slug'=>$slug,'owner_user_id'=>$user->id]);
        $shop->employees()->attach($user->id,['role'=>'owner']);
        $shop->seedDefaultColumns();
        return [$user,$shop];
    }

    private function profile(Shop $shop, string $name = 'Test Bowler'): Customer
    {
        return $shop->customers()->create(['name'=>$name]);
    }

    public function test_customer_profile_can_own_multiple_balls_with_one_shared_baseline_fit(): void
    {
        [$owner,$shop] = $this->shop('one');
        $this->actingAs($owner);
        $profile = $this->profile($shop);

        $this->post(route('shop.balls.store'),[
            'customer_id'=>$profile->id, 'ball_name'=>'Phaze II', 'weight_lbs'=>15,
        ])->assertRedirect();
        $this->post(route('shop.balls.store'),[
            'customer_id'=>$profile->id, 'ball_name'=>'Black Widow', 'weight_lbs'=>14,
        ])->assertRedirect();

        $this->post(route('shop.customers.fittings.store',$profile),[
            'handedness'=>'right', 'middle_span'=>'4 1/8', 'thumb_pitch'=>'1/8 reverse',
        ])->assertRedirect();

        $this->assertSame(2,$profile->balls()->count());
        $this->assertSame(1,$profile->fittings()->count());
        $this->get(route('shop.customers.show',$profile))
            ->assertOk()->assertSee('Phaze II')->assertSee('Black Widow')->assertSee('4 1/8');
    }

    public function test_new_fitting_revision_never_overwrites_previous_measurement(): void
    {
        [$owner,$shop] = $this->shop('one');
        $profile = $this->profile($shop);
        $this->actingAs($owner);

        $this->post(route('shop.customers.fittings.store',$profile),[
            'middle_span'=>'4 1/8',
        ])->assertRedirect();
        $this->post(route('shop.customers.fittings.store',$profile),[
            'middle_span'=>'4 1/4',
        ])->assertRedirect();

        $this->assertSame('4 1/8',$profile->fittings()->where('revision',1)->firstOrFail()->middle_span);
        $second = $profile->fittings()->where('revision',2)->firstOrFail();
        $this->assertSame('4 1/4',$second->middle_span);
        $this->assertSame($owner->id,$second->created_by);
        $this->get(route('shop.customers.fittings.show',[$profile,$second]))->assertOk()->assertSee('4 1/4');
    }

    public function test_ball_layout_revision_can_reference_customer_fitting_without_modifying_it(): void
    {
        [$owner,$shop] = $this->shop('one');
        $profile = $this->profile($shop);
        $ball = $shop->balls()->create([
            'customer_id'=>$profile->id,'customer_name'=>$profile->name,'ball_name'=>'Phaze II',
        ]);
        $fitting = $profile->fittings()->create([
            'shop_id'=>$shop->id,'created_by'=>$owner->id,'revision'=>1,'middle_span'=>'4 1/8',
        ]);

        $this->actingAs($owner)->post(route('shop.balls.specs.store',$ball),[
            'fitting_spec_id'=>$fitting->id, 'layout'=>'45 x 4.5 x 35',
        ])->assertRedirect();

        $spec = $ball->specs()->firstOrFail();
        $this->assertSame($fitting->id,$spec->fitting_spec_id);
        $this->assertSame('45 x 4.5 x 35',$spec->layout);
        $this->assertSame('4 1/8',$profile->latestFitting()->middle_span);
        $this->get(route('shop.balls.specs.show',[$ball,$spec]))->assertOk()
            ->assertSee('Baseline customer fitting revision 1');
    }

    public function test_cannot_use_another_customers_fitting_even_inside_same_shop(): void
    {
        [$owner,$shop] = $this->shop('one');
        $one = $this->profile($shop,'Customer One');
        $two = $this->profile($shop,'Customer Two');
        $ball = $shop->balls()->create([
            'customer_id'=>$one->id,'customer_name'=>$one->name,'ball_name'=>'Phaze II',
        ]);
        $fitting = $two->fittings()->create([
            'shop_id'=>$shop->id,'created_by'=>$owner->id,'revision'=>1,
        ]);
        $this->actingAs($owner)->post(route('shop.balls.specs.store',$ball),[
            'fitting_spec_id'=>$fitting->id,'layout'=>'New layout',
        ])->assertSessionHasErrors('fitting_spec_id');
        $this->assertDatabaseCount('drilling_specs',0);
    }

    public function test_cross_shop_customer_and_fitting_access_is_denied(): void
    {
        [$owner,$first] = $this->shop('one');
        [, $other] = $this->shop('two');
        $profile = $this->profile($other);
        $this->actingAs($owner);

        $this->get(route('shop.customers.show',$profile))->assertNotFound();
        $this->put(route('shop.customers.update',$profile),['name'=>'Wrong'])->assertNotFound();
        $this->post(route('shop.customers.fittings.store',$profile),[
            'middle_span'=>'4 1/8',
        ])->assertNotFound();
        $this->post(route('shop.balls.store'),[
            'customer_id'=>$profile->id, 'ball_name'=>'Foreign',
        ])->assertSessionHasErrors('customer_id');
    }

    public function test_order_can_reference_profile_and_only_its_balls(): void
    {
        [$owner,$shop] = $this->shop('one');
        $profile = $this->profile($shop,'Bowler A');
        $other = $this->profile($shop,'Bowler B');
        $allowedBall = $shop->balls()->create([
            'customer_id'=>$profile->id,'customer_name'=>'Bowler A','ball_name'=>'A Ball',
        ]);
        $deniedBall = $shop->balls()->create([
            'customer_id'=>$other->id,'customer_name'=>'Bowler B','ball_name'=>'B Ball',
        ]);

        $this->actingAs($owner)->post(route('shop.orders.store'),[
            'customer_id'=>$profile->id,'title'=>'Drill','priority'=>'normal',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame($profile->id,$order->customer_id);
        $this->assertSame('Bowler A',$order->customer_name);
        $this->post(route('shop.orders.ball.link',$order),[
            'customer_ball_id'=>$deniedBall->id,
        ])->assertSessionHasErrors('customer_ball_id');
        $this->post(route('shop.orders.ball.link',$order),[
            'customer_ball_id'=>$allowedBall->id,
        ])->assertRedirect();
        $this->assertSame($allowedBall->id,$order->fresh()->customer_ball_id);
    }

    public function test_legacy_customer_ball_and_order_are_not_automatically_merged_by_name(): void
    {
        [$owner,$shop] = $this->shop('one');
        $profile = $this->profile($shop,'Same Name');
        $legacy = $shop->balls()->create(['customer_name'=>'Same Name','ball_name'=>'Old ball']);
        $this->assertNull($legacy->customer_id);
        $this->assertSame(0,$profile->balls()->count());

        $this->actingAs($owner)->put(route('shop.balls.customer.update',$legacy),[
            'customer_id'=>$profile->id,
        ])->assertRedirect();

        $this->assertSame($profile->id,$legacy->fresh()->customer_id);
    }

    public function test_customer_portal_order_creates_shop_specific_profile(): void
    {
        [, $shop] = $this->shop('one');
        $customerUser = User::create([
            'name'=>'Customer Online','email'=>'online@example.test','password'=>'long-password',
        ]);
        $this->actingAs($customerUser)->post(route('portal.orders.store',$shop),[
            'title'=>'Finger inserts',
        ])->assertRedirect();

        $profile = $shop->customers()->firstOrFail();
        $this->assertSame($customerUser->id,$profile->user_id);
        $this->assertSame($profile->id,$shop->orders()->firstOrFail()->customer_id);
        $this->assertSame('Customer Online',$profile->name);
    }

    public function test_customer_cannot_view_employee_customer_profiles(): void
    {
        [, $shop] = $this->shop('one');
        $profile = $this->profile($shop);
        $user = User::create(['name'=>'Guest','email'=>'guest@example.test','password'=>'long-password']);
        $this->actingAs($user)->get(route('shop.customers.show',$profile))->assertForbidden();
    }
}
