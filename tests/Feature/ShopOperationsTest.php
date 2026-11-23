<?php

namespace Tests\Feature;

use App\Models\CustomerBall;
use App\Models\Shop;
use App\Models\ShopBallOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function createShop(string $slug = 'test-shop'): array
    {
        $owner=User::create(['name'=>'Test Owner','email'=>$slug.'@example.test','password'=>'some-long-password']);
        $shop=Shop::create(['name'=>'Test Shop','slug'=>$slug,'owner_user_id'=>$owner->id]);
        $shop->employees()->attach($owner->id,['role'=>'owner']);
        $shop->seedDefaultColumns();
        ShopBallOption::seedDefaults($shop);
        return [$owner,$shop];
    }

    public function test_shop_options_seed_repeatedly_without_duplicates(): void
    {
        [$owner,$shop]=$this->createShop();
        $before=$shop->ballOptions()->count();
        ShopBallOption::seedDefaults($shop);
        $this->assertSame($before,$shop->ballOptions()->count());
        $this->assertGreaterThan(0,$before);
    }

    public function test_shop_owner_can_manage_custom_catalog_options(): void
    {
        [$owner,$shop]=$this->createShop();
        $this->actingAs($owner);
        $this->post(route('shop.catalog.store'),[
            'kind'=>'brand','label'=>'Test Brand',
        ])->assertRedirect();
        $option=$shop->ballOptions()->where('label','Test Brand')->firstOrFail();

        $this->patch(route('shop.catalog.update',$option),[
            'label'=>'Renamed Brand','active'=>0,
        ])->assertRedirect();
        $this->assertSame('Renamed Brand',$option->fresh()->label);
        $this->assertFalse($option->fresh()->active);

        $this->get(route('shop.catalog.index'))->assertOk()->assertSee('Renamed Brand');
    }

    public function test_brand_product_and_other_custom_ball_values_are_stored(): void
    {
        [$owner,$shop]=$this->createShop();
        $customer=$shop->customers()->create(['name'=>'Test Bowler']);

        $this->actingAs($owner)->post(route('shop.balls.store'),[
            'customer_id'=>$customer->id,
            'manufacturer'=>'__other__',
            'manufacturer_custom'=>'Small Brand',
            'ball_name'=>'__other__',
            'ball_name_custom'=>'New Prototype',
            'core_type'=>'__other__',
            'core_type_custom'=>'New Core',
            'coverstock_type'=>'Hybrid Reactive',
            'weight_lbs'=>15,
        ])->assertRedirect();

        $ball=CustomerBall::firstOrFail();
        $this->assertSame('Small Brand',$ball->manufacturer);
        $this->assertSame('New Prototype',$ball->ball_name);
        $this->assertSame('New Core',$ball->core_type);
        $this->assertSame('Hybrid Reactive',$ball->coverstock_type);
    }

    public function test_kanban_columns_can_be_reordered_as_json_and_empty_column_deleted(): void
    {
        [$owner,$shop]=$this->createShop();
        $this->actingAs($owner);
        $ids=$shop->columns()->orderBy('sort_order')->pluck('id')->all();
        $reverse=array_reverse($ids);
        $this->postJson(route('shop.workflow.reorder'),[
            'column_ids'=>$reverse,
        ])->assertOk()->assertJson(['ok'=>true]);

        $this->assertSame($reverse,$shop->columns()->orderBy('sort_order')->pluck('id')->all());

        $column=$shop->columns()->firstOrFail();
        $this->delete(route('shop.workflow.destroy',$column))->assertRedirect();
        $this->assertDatabaseMissing('workflow_columns',['id'=>$column->id]);
    }

    public function test_column_with_an_order_cannot_be_deleted(): void
    {
        [$owner,$shop]=$this->createShop();
        $col=$shop->columns()->firstOrFail();
        $shop->orders()->create([
            'customer_name'=>'Test','title'=>'Ball fit','workflow_column_id'=>$col->id,
            'priority'=>'normal',
        ]);
        $this->actingAs($owner)->delete(route('shop.workflow.destroy',$col))
            ->assertSessionHasErrors('column');
        $this->assertDatabaseHas('workflow_columns',['id'=>$col->id]);
    }

    public function test_orders_table_is_shop_scoped(): void
    {
        [$owner,$shop]=$this->createShop();
        [, $other]=$this->createShop('other-shop');
        $stage=$shop->columns()->firstOrFail();
        $otherStage=$other->columns()->firstOrFail();
        $shop->orders()->create(['title'=>'Visible ticket','customer_name'=>'Own Customer',
            'workflow_column_id'=>$stage->id]);
        $other->orders()->create(['title'=>'Hidden ticket','customer_name'=>'Foreign Customer',
            'workflow_column_id'=>$otherStage->id]);

        $this->actingAs($owner)->get(route('shop.orders.index'))
            ->assertOk()->assertSee('Visible ticket')->assertDontSee('Hidden ticket');
    }

    public function test_phone_is_stored_in_e164_and_displayed_in_us_format(): void
    {
        [$owner,$shop]=$this->createShop();
        $this->actingAs($owner)->post(route('shop.customers.store'),[
            'name'=>'Phone Example','phone'=>'(316) 555-0123',
        ])->assertRedirect();

        $customer=$shop->customers()->firstOrFail();
        $this->assertSame('+13165550123',$customer->phone);
        $this->get(route('shop.customers.show',$customer))->assertOk()
            ->assertSee('(316) 555-0123');
    }

    public function test_demo_seeder_populates_work_orders_and_both_specs(): void
    {
        $this->artisan('db:seed', ['--class'=>'Database\Seeders\DrillDeckDemoSeeder'])->assertExitCode(0);
        $this->assertDatabaseHas('shops',['slug'=>'drilldeck-demo']);
        $this->assertDatabaseCount('customers',3);
        $this->assertSame(4,\App\Models\CustomerBall::count());
        $this->assertSame(3,\App\Models\FittingSpec::count());
        $this->assertSame(4,\App\Models\DrillingSpec::count());
        $this->assertSame(4,\App\Models\Order::count());
        $this->assertDatabaseCount('staff_invitations',1);
    }

    public function test_members_of_other_shops_cannot_see_ball_photos(): void
    {
        [$owner,$shop]=$this->createShop();
        [, $other]=$this->createShop('other-shop');
        $foreign=$other->balls()->create(['customer_name'=>'Other','ball_name'=>'Foreign Ball']);
        $this->actingAs($owner)->get(route('shop.balls.photo',$foreign))->assertNotFound();
    }
}
