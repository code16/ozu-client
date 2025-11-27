<?php

use Code16\OzuClient\Tests\Fixtures\DummySettings;
use Illuminate\Support\Facades\Cache;

it('uses default property values when cache is empty', function () {
    config(['cache.default' => 'array']);
    Cache::flush();

    $settings = new DummySettings();

    expect($settings->foo)->toBe('default-foo');
    expect($settings->bar)->toBe('default-bar');
    expect($settings->get('foo'))->toBe('default-foo');
});

it('loads properties from cache using class-based prefix', function () {
    config(['cache.default' => 'array']);
    Cache::flush();

    Cache::set('DummySettings_foo', 'cached-foo');
    Cache::set('DummySettings_bar', 'cached-bar');

    $settings = new DummySettings();

    expect($settings->foo)->toBe('cached-foo');
    expect($settings->bar)->toBe('cached-bar');
    expect($settings->get('bar'))->toBe('cached-bar');
});
