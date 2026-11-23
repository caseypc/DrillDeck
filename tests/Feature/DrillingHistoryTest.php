<?php

namespace Tests\Feature;

use App\Models\CustomerBall;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrillingHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function shop(string $slug): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $slug.'@example.test', 'password' => 'long-password']);
        $shop = Shop::create(['name' => $slug, 'slug' => $slug, 'owner_user_id' => $owner->id]);
        $shop->employees()->attach($owner->id, ['role' => 'owner']);
        $shop->seedDefaultColumns();
        return [$owner, $shop];
    }

    private function order(Shop $shop, string $customer = 'Taylor Customer'): Order
    {
        return $shop->orders()->create([
            'customer_name' => $customer, 'title' => 'Drill', 'priority' => 'normal',
            'workflow_column_id' => $shop->columns()->firstOrFail()->id,
        ]);
    }

    private function ball(Shop $shop, string $customer = 'Taylor Customer'): CustomerBall
    {
        return $shop->balls()->create([
            'customer_name' => $customer, 'ball_name' => 'Phaze II', 'weight_lbs' => 15,
        ]);
    }

    public function test_employee_can_link_ball_and_save_immutable_revisions(): void
    {
        [$owner, $shop] = $this->shop('a');
        $order = $this->order($shop);
        $ball = $this->ball($shop);
        $this->actingAs($owner);

        $this->post(route('shop.orders.ball.link', $order), ['customer_ball_id' => $ball->id])
            ->assertRedirect();

        $this->assertSame($ball->id, $order->fresh()->customer_ball_id);

        $this->post(route('shop.balls.specs.store', $ball), [
            'order_id' => $order->id, 'middle_span' => '4 1/8',
        ])->assertRedirect();
        $this->post(route('shop.balls.specs.store', $ball), [
            'order_id' => $order->id, 'middle_span' => '4 1/4',
        ])->assertRedirect();

        $first = $ball->specs()->where('revision', 1)->firstOrFail();
        $second = $ball->specs()->where('revision', 2)->firstOrFail();

        $this->assertSame('4 1/8', $first->middle_span);
        $this->assertSame('4 1/4', $second->middle_span);
        $this->assertSame($order->id, $second->order_id);
        $this->assertDatabaseCount('drilling_specs', 2);

        $this->get(route('shop.orders.show', $order))
            ->assertOk()->assertSee('Revision 2')->assertSee('4 1/8')->assertSee('4 1/4');
        $this->get(route('shop.balls.specs.show', [$ball, $first]))
            ->assertOk()->assertSee('4 1/8');
    }

    public function test_ball_cannot_be_linked_to_different_customer(): void
    {
        [$owner, $shop] = $this->shop('a');
        $order = $this->order($shop, 'Correct');
        $ball = $this->ball($shop, 'Different');

        $this->actingAs($owner)
            ->post(route('shop.orders.ball.link', $order), ['customer_ball_id' => $ball->id])
            ->assertSessionHasErrors('customer_ball_id');

        $this->assertNull($order->fresh()->customer_ball_id);
    }

    public function test_cross_shop_ball_links_and_history_are_denied(): void
    {
        [$owner, $shop] = $this->shop('a');
        [, $other] = $this->shop('b');
        $order = $this->order($shop);
        $foreignBall = $this->ball($other);

        $this->actingAs($owner)
            ->post(route('shop.orders.ball.link', $order), ['customer_ball_id' => $foreignBall->id])
            ->assertSessionHasErrors('customer_ball_id');
        $this->get(route('shop.balls.show', $foreignBall))->assertNotFound();
        $this->post(route('shop.balls.specs.store', $foreignBall), [
            'middle_span' => '4 1/4',
        ])->assertNotFound();
    }

    public function test_revision_requires_order_linked_to_the_ball(): void
    {
        [$owner, $shop] = $this->shop('a');
        $order = $this->order($shop);
        $ball = $this->ball($shop);

        $this->actingAs($owner)->post(route('shop.balls.specs.store', $ball), [
            'order_id' => $order->id, 'middle_span' => '4 1/8',
        ])->assertSessionHasErrors('order_id');

        $this->assertDatabaseCount('drilling_specs', 0);
    }

    public function test_unlinking_ball_preserves_drilling_history(): void
    {
        [$owner, $shop] = $this->shop('a');
        $order = $this->order($shop);
        $ball = $this->ball($shop);
        $this->actingAs($owner);

        $this->post(route('shop.orders.ball.link', $order), ['customer_ball_id' => $ball->id]);
        $this->post(route('shop.balls.specs.store', $ball), [
            'order_id' => $order->id, 'layout' => '45 x 4.5 x 35',
        ])->assertRedirect();
        $this->delete(route('shop.orders.ball.unlink', $order))->assertRedirect();

        $this->assertNull($order->fresh()->customer_ball_id);
        $this->assertSame($order->id, $ball->specs()->firstOrFail()->order_id);
    }

    public function test_customer_cannot_access_employee_bowling_ball_records(): void
    {
        [, $shop] = $this->shop('a');
        $ball = $this->ball($shop);
        $customer = User::create(['name' => 'Customer', 'email' => 'customer@example.test', 'password' => 'long-password']);
        $this->actingAs($customer);

        $this->get(route('shop.balls.index'))->assertForbidden();
        $this->get(route('shop.balls.show', $ball))->assertForbidden();
        $this->post(route('shop.balls.specs.store', $ball), ['middle_span' => '4 1/8'])->assertForbidden();
    }

    public function test_creating_ball_from_order_copies_customer_and_links_ball(): void
    {
        [$owner, $shop] = $this->shop('a');
        $order = $this->order($shop, 'Walk-in Bowler');
        $this->actingAs($owner)->post(route('shop.balls.store'), [
            'order_id' => $order->id, 'ball_name' => 'Hammer Black Widow', 'weight_lbs' => 14,
        ])->assertRedirect(route('shop.orders.show', $order));

        $ball = CustomerBall::firstOrFail();
        $this->assertSame('Walk-in Bowler', $ball->customer_name);
        $this->assertSame($ball->id, $order->fresh()->customer_ball_id);
    }
}
