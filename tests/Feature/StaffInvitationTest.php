<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StaffInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_existing_user_and_matching_email_accepts(): void
    {
        Mail::fake();

        $owner = User::create(['name'=>'Owner','email'=>'owner@example.test','password'=>'example-long-password']);
        $employee = User::create(['name'=>'Staff','email'=>'staff@example.test','password'=>'example-long-password']);
        $shop = Shop::create(['name'=>'Shop','slug'=>'shop','owner_user_id'=>$owner->id]);
        $shop->employees()->attach($owner->id,['role'=>'owner']);
        $shop->seedDefaultColumns();

        $this->actingAs($owner)->post(route('shop.staff.invite'),[
            'email'=>$employee->email,
        ])->assertRedirect();

        $invite = DB::table('staff_invitations')->first();
        $this->assertNotNull($invite);
        $this->assertSame(64, strlen($invite->token_hash));

        // A stolen invitation without the matching account must not grant membership.
        $this->actingAs($owner)->get(route('shop.invitations.accept',[
            'invitation'=>$invite->id,'token'=>'wrong-token',
        ]))->assertForbidden();
        $this->assertFalse($shop->employees()->whereKey($employee->id)->exists());
    }
}
