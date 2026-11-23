<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('DrillDeck keeps your shop moving.');
})->purpose('Display a short message');
