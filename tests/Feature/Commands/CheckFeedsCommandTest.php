<?php

it('has a check feeds command', function () {
    $this->assertContains('newsharvest:check-feeds', array_keys(\Artisan::all()));
});

it('can run a check feeds command', function () {
    $this->artisan('newsharvest:check-feeds')->assertExitCode(0);
});
