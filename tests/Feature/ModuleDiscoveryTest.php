<?php

use Modules\Shared\Infrastructure\Providers\SharedServiceProvider;

it('registers module service providers discovered in the modules directory', function () {
    expect(app()->providerIsLoaded(SharedServiceProvider::class))->toBeTrue();
});
