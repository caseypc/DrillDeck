<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private function shopFor(User $owner, string $name): Shop
    {
        $shop = Shop::create(['name' => $name,'slug' => strtolower($name), 'owner_user_id' => $owner->id]);
        $shop->employees()->attach($owner->id, ['role' => 'owner']);
        $shop->seedDefaultColumns();
        return $shop;
    }

    private function user(string $email): User
    {
        return User::create(['name' => $email,'email'=>$email,'password'=>'a-very-long-password']);
    }

    private function data(array $changes = []): array
    {
        return [
            'customer_name' => 'Bowler',
            'title' => 'Ball drilling',
            'description' => 'Fingertip fitting',
            'priority' => 'normal',
            ...$changes,
        ];
    }

    public function test_employee_can_create_edit_assign_and_move_order(): void
    {
        $owner = $this->user('owner@example.test');
        $employee = $this->user('staff@example.test');
        $shop = $this->shopFor($owner, 'ShopOne');
        $shop->employees()->attach($employee->id, ['role' => 'staff']);
        $this->actingAs($owner);

        $this->post(route('shop.orders.store'), $this->data())
            ->assertRedirect();
        $order = Order::firstOrFail();

        $this->put(route('shop.orders.update', $order), $this->data([
            'title' => 'Redrill',
            'assigned_user_id' => $employee->id,
        ]))->assertRedirect();

        $this->assertSame('Redrill', $order->fresh()->title);
        $this->assertSame($employee->id, $order->fresh()->assigned_user_id);

        $ready = $shop->columns()->where('customer_status','ready')->firstOrFail();
        $this->patchJson(route('shop.orders.move', $order), [
            'workflow_column_id' => $ready->id,
        ])->assertOk();

        $this->assertSame($ready->id, $order->fresh()->workflow_column_id);
        $this->assertDatabaseCount('order_status_history', 2);
    }

    public function test_cross_shop_ticket_edit_and_move_are_denied(): void
    {
        $a = $this->user('a@example.test');
        $b = $this->user('b@example.test');
        $shopA = $this->shopFor($a, 'ShopA');
        $shopB = $this->shopFor($b, 'ShopB');
        $otherOrder = $shopB->orders()->create([
            'customer_name' => 'Hidden',
            'title' => 'Hidden order',
            'priority' => 'normal',
            'workflow_column_id' => $shopB->columns()->firstOrFail()->id,
        ]);

        $this->actingAs($a);
        $this->get(route('shop.orders.show',$otherOrder))->assertNotFound();
        $this->put(route('shop.orders.update',$otherOrder),$this->data())->assertNotFound();
        $this->patchJson(route('shop.orders.move',$otherOrder),[
            'workflow_column_id'=>$shopA->columns()->firstOrFail()->id,
        ])->assertNotFound();
    }

    public function test_employee_cannot_assign_order_to_another_shops_employee(): void
    {
        $a = $this->user('a@example.test');
        $b = $this->user('b@example.test');
        $shopA = $this->shopFor($a, 'ShopA');
        $this->shopFor($b, 'ShopB');
        $this->actingAs($a);

        $this->post(route('shop.orders.store'), $this->data([
            'assigned_user_id' => $b->id,
        ]))->assertSessionHasErrors('assigned_user_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_configured_rules_move_orders_automatically(): void
    {
        $owner = $this->user('owner@example.test');
        $employee = $this->user('staff@example.test');
        $shop = $this->shopFor($owner, 'ShopA');
        $shop->employees()->attach($employee->id, ['role'=>'staff']);
        $drilling = $shop->columns()->where('name','Drilling')->firstOrFail();

        $shop->rules()->create([
            'name'=>'Start assigned work',
            'event'=>'assigned',
            'target_column_id'=>$drilling->id,
        ]);

        $this->actingAs($owner)->post(route('shop.orders.store'),$this->data([
            'assigned_user_id'=>$employee->id,
        ]))->assertRedirect();

        $this->assertSame($drilling->id, Order::firstOrFail()->workflow_column_id);
        $this->assertSame(2, DB::table('order_status_history')->count());
    }

    public function test_customer_cannot_access_employee_work_order_screen(): void
    {
        $this->actingAs($this->user('customer@example.test'));
        $this->get('/shop')->assertForbidden();
    }
}
