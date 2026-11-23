<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Frontend assets are built in a separate GitHub Actions job.
        // Blade integration tests should render without requiring a Vite manifest.
        $this->withoutVite();
    }
}
