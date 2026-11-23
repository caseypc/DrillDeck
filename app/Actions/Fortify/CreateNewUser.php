<?php

namespace App\Actions\Fortify;

use App\Models\Shop;
use App\Models\ShopBallOption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'shop_name' => ['nullable', 'string', 'max:150'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            if (filled($input['shop_name'] ?? null)) {
                $shop = Shop::create([
                    'name' => $input['shop_name'],
                    'slug' => Str::slug($input['shop_name']).'-'.Str::lower(Str::random(6)),
                    'owner_user_id' => $user->id,
                ]);
                $shop->employees()->attach($user->id, ['role' => 'owner']);
                $shop->seedDefaultColumns();
                ShopBallOption::seedDefaults($shop);
            }

            return $user;
        });
    }
}
