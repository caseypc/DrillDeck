<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\ShopBallOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrillingWorkspaceCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function shop(string $slug): array
    {
        $owner = User::create([
            'name' => 'Owner '.$slug,
            'email' => $slug.'@example.test',
            'password' => 'long-password-123456',
        ]);
        $shop = Shop::create([
            'name' => 'Test Pro Shop '.$slug,
            'slug' => $slug,
            'owner_user_id' => $owner->id,
        ]);
        $shop->employees()->attach($owner->id, ['role' => 'owner']);
        $shop->seedDefaultColumns();
        ShopBallOption::seedDefaults($shop);

        return [$owner, $shop];
    }

    public function test_ball_workspace_keeps_photo_and_fingers_above_thumb_but_no_visualizer(): void
    {
        [$owner, $shop] = $this->shop('workspace');
        $customer = $shop->customers()->create(['name' => 'Test Bowler']);
        $ball = $shop->balls()->create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'ball_name' => 'Test Ball',
            'manufacturer' => 'Storm',
            'weight_lbs' => 15,
        ]);

        $this->actingAs($owner)->get(route('shop.balls.show', $ball))
            ->assertOk()
            ->assertSee('Photograph this bowling ball')
            ->assertSee('Grip specification reference')
            ->assertSee('Middle finger')
            ->assertSee('Fingers at top')
            ->assertSee('Save new layout revision')
            ->assertDontSee('Ball layout visualizer')
            ->assertDontSee('data-layout-visualizer');
    }

    public function test_read_only_layout_worksheet_keeps_photo_but_no_canvas_visualizer(): void
    {
        [$owner, $shop] = $this->shop('revision');
        $ball = $shop->balls()->create([
            'customer_name' => 'Walk-in', 'ball_name' => 'Legacy Ball',
        ]);
        $spec = $ball->specs()->create([
            'shop_id' => $shop->id,
            'created_by' => $owner->id,
            'revision' => 1,
            'drilling_angle' => '40',
            'pin_to_pap' => '4 1/4',
        ]);

        $this->actingAs($owner)->get(route('shop.balls.specs.show', [$ball, $spec]))
            ->assertOk()
            ->assertSee('Photograph this bowling ball')
            ->assertSee('Drilling angle')
            ->assertDontSee('Ball layout visualizer')
            ->assertDontSee('data-layout-visualizer');
    }

    public function test_owner_reorders_catalog_and_ball_dropdown_respects_that_order(): void
    {
        [$owner, $shop] = $this->shop('reorder');
        $ids = $shop->ballOptions()->where('kind', 'brand')
            ->orderBy('sort_order')->pluck('id')->all();
        $reversed = array_reverse($ids);

        $this->actingAs($owner)->postJson(route('shop.catalog.reorder'), [
            'kind' => 'brand',
            'option_ids' => $reversed,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertSame($reversed, $shop->ballOptions()->where('kind', 'brand')
            ->orderBy('sort_order')->pluck('id')->all());

        $this->get(route('shop.catalog.index'))->assertOk()
            ->assertSee('data-catalog-list', false)
            ->assertSee('data-catalog-status', false);

        $firstTwo = $shop->ballOptions()->where('kind', 'brand')
            ->orderBy('sort_order')->limit(2)->pluck('label')->all();

        $this->get(route('shop.balls.index'))->assertOk()->assertSeeInOrder($firstTwo);
    }

    public function test_reorder_rejects_missing_or_cross_shop_options(): void
    {
        [$owner, $shop] = $this->shop('first');
        [, $other] = $this->shop('other');
        $ids = $shop->ballOptions()->where('kind', 'core_type')->pluck('id')->all();
        $foreign = $other->ballOptions()->where('kind', 'core_type')->firstOrFail();

        $this->actingAs($owner)->postJson(route('shop.catalog.reorder'), [
            'kind' => 'core_type',
            'option_ids' => [$ids[0], $foreign->id],
        ])->assertUnprocessable();

        $this->postJson(route('shop.catalog.reorder'), [
            'kind' => 'core_type',
            'option_ids' => array_slice($ids, 0, 2),
        ])->assertUnprocessable();
    }

    public function test_catalog_delete_preserves_customer_ball_text_and_seeder_does_not_restore_option(): void
    {
        [$owner, $shop] = $this->shop('delete');
        $option = $shop->ballOptions()->where('kind', 'surface')->firstOrFail();
        $ball = $shop->balls()->create([
            'customer_name' => 'Taylor',
            'ball_name' => 'Test Ball',
            'surface' => $option->label,
        ]);

        $this->actingAs($owner)->delete(route('shop.catalog.destroy', $option))
            ->assertRedirect();

        $this->assertDatabaseMissing('shop_ball_options', ['id' => $option->id]);
        $this->assertSame($option->label, $ball->fresh()->surface);

        ShopBallOption::seedDefaults($shop);
        $this->assertDatabaseMissing('shop_ball_options', [
            'shop_id' => $shop->id,
            'kind' => 'surface',
            'label' => $option->label,
        ]);
    }

    public function test_brand_with_products_cannot_be_deleted(): void
    {
        [$owner, $shop] = $this->shop('manufacturer');
        $brand = $shop->ballOptions()->where('kind', 'brand')
            ->where('label', 'Storm')->firstOrFail();

        $this->actingAs($owner)->delete(route('shop.catalog.destroy', $brand))
            ->assertSessionHasErrors('option');

        $this->assertDatabaseHas('shop_ball_options', ['id' => $brand->id]);
    }

    public function test_staff_without_management_role_cannot_reorder_catalog(): void
    {
        [, $shop] = $this->shop('roles');
        $staff = User::create([
            'name' => 'Staff Member',
            'email' => 'staff@example.test',
            'password' => 'long-password-123456',
        ]);
        $shop->employees()->attach($staff->id, ['role' => 'staff']);
        $ids = $shop->ballOptions()->where('kind', 'brand')->pluck('id')->all();

        $this->actingAs($staff)->postJson(route('shop.catalog.reorder'), [
            'kind' => 'brand',
            'option_ids' => $ids,
        ])->assertForbidden();
    }
    public function test_drilling_workspace_displays_enumerated_hand_and_grip_dropdowns_and_saves_values(): void
    {
        [$owner, $shop] = $this->shop('grip-selects');
        $customer = $shop->customers()->create(['name' => 'Grip Test']);
        $ball = $shop->balls()->create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'ball_name' => 'Grip Ball',
        ]);

        $this->actingAs($owner)->get(route('shop.balls.show', $ball))
            ->assertOk()
            ->assertSee('id="drill-handedness"', false)
            ->assertSee('name="handedness"', false)
            ->assertSee('Right Hand')
            ->assertSee('Left Hand')
            ->assertSee('id="drill-grip-style"', false)
            ->assertSee('Fingertip')
            ->assertSee('Conventional')
            ->assertSee('No Thumb');

        $this->post(route('shop.balls.specs.store', $ball), [
            'handedness' => 'left',
            'grip_style' => 'no-thumb',
            'drilling_angle' => '45',
        ])->assertRedirect();

        $this->assertDatabaseHas('drilling_specs', [
            'customer_ball_id' => $ball->id,
            'handedness' => 'left',
            'grip_style' => 'no-thumb',
        ]);

        $this->get(route('shop.balls.show', $ball))
            ->assertOk()
            ->assertSee('value="left" selected', false)
            ->assertSee('value="no-thumb" selected', false);
    }

}
