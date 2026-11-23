<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    private function manageShop(Request $request): Shop
    {
        $shop = $request->user()->shops()->wherePivotIn('role',['owner','manager'])->first();
        abort_unless($shop,403);
        return $shop;
    }

    public function index(Request $request)
    {
        $shop = $this->manageShop($request);
        $employees = $shop->employees()->orderBy('name')->get();
        $invitations = DB::table('staff_invitations')
            ->where('shop_id', $shop->id)->whereNull('accepted_at')
            ->where('expires_at','>',now())->latest()->get();
        return view('shop.staff',compact('shop','employees','invitations'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $shop = $this->manageShop($request);
        $data = $request->validate(['email'=>['required','email','max:255']]);
        $email = Str::lower($data['email']);

        if ($shop->employees()->where('email',$email)->exists()) {
            return back()->withErrors(['email'=>'This employee already belongs to the shop.']);
        }

        $token = Str::random(64);
        $invitationId = DB::table('staff_invitations')->insertGetId([
            'shop_id'=>$shop->id,
            'invited_by_user_id'=>$request->user()->id,
            'email'=>$email,
            'role'=>'staff',
            'token_hash'=>hash('sha256',$token),
            'expires_at'=>now()->addDays(7),
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        $url = route('shop.invitations.accept', ['invitation'=>$invitationId,'token'=>$token]);
        Mail::raw(
            "You are invited to join {$shop->name} on DrillDeck.\n\n".
            "Sign in or register using {$email}, then visit:\n{$url}\n\n".
            "This invitation expires in seven days.",
            fn ($message) => $message->to($email)->subject('DrillDeck staff invitation')
        );

        return back()->with('status','Invitation sent via the configured mail transport.');
    }

    public function accept(Request $request, int $invitation, string $token): RedirectResponse
    {
        $user = $request->user();
        DB::transaction(function () use ($invitation, $token, $user) {
            $row = DB::table('staff_invitations')->where('id',$invitation)->lockForUpdate()->first();
            abort_unless($row && ! $row->accepted_at && $row->expires_at > now(),404);
            abort_unless(hash_equals($row->token_hash,hash('sha256',$token)),403);
            abort_unless(Str::lower($user->email) === Str::lower($row->email),403);

            if (! DB::table('shop_users')->where('shop_id',$row->shop_id)->where('user_id',$user->id)->exists()) {
                DB::table('shop_users')->insert([
                    'shop_id'=>$row->shop_id,
                    'user_id'=>$user->id,
                    'role'=>'staff',
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            DB::table('staff_invitations')->where('id',$row->id)->update([
                'accepted_at'=>now(),'updated_at'=>now(),
            ]);
        });

        return redirect()->route('shop.board')->with('status','You have joined the pro shop.');
    }
}
