<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => ['login' => 'login', 'two-factor' => null],
    'views' => true,
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
    ],
];
