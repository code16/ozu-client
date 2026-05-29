<?php

use Code16\OzuClient\Client;
use Illuminate\Console\Command;
use Laravel\Prompts\Prompt;

beforeEach(function () {
    config(['ozu-client.website_key' => 'test']);
    Prompt::fake();
});

it('can cancel deployment', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn(null);

    $this->artisan('ozu:deploy')
        ->expectsConfirmation('Are you sure you want to deploy your website?', 'no')
        ->assertExitCode(Command::SUCCESS);
});

it('can sync project before deployment', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn('key');

    $client->shouldReceive('getDeploymentTargets')->andReturn(['data' => [
        ['id' => 1, 'name' => 'Production', 'driver' => 'forge', 'status' => 'ready', 'website_url' => 'https://example.com'],
    ]]);
    $client->shouldReceive('getUndeployedPostsForTarget')->andReturn([]);
    $client->shouldReceive('triggerDeployment')->andReturn(['data' => ['uuid' => 'test-uuid']]);
    $client->shouldReceive('streamDeploymentLogs')->andReturn('logs');
    $client->shouldReceive('fetchDeploymentStatus')->andReturn(['data' => ['status' => 'success']]);

    // Mock ozu:configure command
    Artisan::command('ozu:configure', function () {
        return Command::SUCCESS;
    });

    $this->artisan('ozu:deploy')
        ->expectsQuestion('Are you sure you want to deploy your website?', '1')
        ->expectsConfirmation('Do you want to sync your project with Ozu before deploying?', 'yes')
        ->expectsQuestion('Select a deployment target to deploy to:', '1')
        ->assertExitCode(Command::SUCCESS);
});

it('can deploy without project sync', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn('key');

    $client->shouldReceive('getDeploymentTargets')->andReturn(['data' => [
        ['id' => 1, 'name' => 'Production', 'driver' => 'forge', 'status' => 'ready', 'website_url' => 'https://example.com'],
    ]]);
    $client->shouldReceive('getUndeployedPostsForTarget')->andReturn([]);
    $client->shouldReceive('triggerDeployment')->andReturn(['data' => ['uuid' => 'test-uuid']]);
    $client->shouldReceive('streamDeploymentLogs')->andReturn('logs');
    $client->shouldReceive('fetchDeploymentStatus')->andReturn(['data' => ['status' => 'success']]);

    $this->artisan('ozu:deploy')
        ->expectsQuestion('Are you sure you want to deploy your website?', '1')
        ->expectsConfirmation('Do you want to sync your project with Ozu before deploying?', 'no')
        ->expectsQuestion('Select a deployment target to deploy to:', '1')
        ->assertExitCode(Command::SUCCESS);
});

it('fails when no deployment targets are found', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn('key');

    $client->shouldReceive('getDeploymentTargets')->andReturn([]);

    $this->artisan('ozu:deploy')
        ->expectsQuestion('Are you sure you want to deploy your website?', '1')
        ->expectsConfirmation('Do you want to sync your project with Ozu before deploying?', 'no')
        ->assertExitCode(Command::FAILURE);
});

it('fails when no ready deployment targets are found', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn('key');

    $client->shouldReceive('getDeploymentTargets')->andReturn(['data' => [
        ['id' => 1, 'name' => 'Production', 'driver' => 'forge', 'status' => 'building'],
    ]]);

    $this->artisan('ozu:deploy')
        ->expectsQuestion('Are you sure you want to deploy your website?', '1')
        ->expectsConfirmation('Do you want to sync your project with Ozu before deploying?', 'no')
        ->assertExitCode(Command::FAILURE);
});

it('handles failed deployment trigger', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn('key');

    $client->shouldReceive('getDeploymentTargets')->andReturn(['data' => [
        ['id' => 1, 'name' => 'Production', 'driver' => 'forge', 'status' => 'ready'],
    ]]);
    $client->shouldReceive('getUndeployedPostsForTarget')->andReturn([]);
    $client->shouldReceive('triggerDeployment')->andReturn([]);

    $this->artisan('ozu:deploy')
        ->expectsQuestion('Are you sure you want to deploy your website?', '1')
        ->expectsConfirmation('Do you want to sync your project with Ozu before deploying?', 'no')
        ->expectsQuestion('Select a deployment target to deploy to:', '1')
        ->assertExitCode(Command::FAILURE);
});

it('displays unpublished posts and asks for confirmation', function () {
    $client = Mockery::mock(Client::class);
    $this->app->instance(Client::class, $client);
    $client->shouldReceive('apiKey')->andReturn('key');

    $client->shouldReceive('getDeploymentTargets')->andReturn(['data' => [
        ['id' => 1, 'name' => 'Production', 'driver' => 'forge', 'status' => 'ready'],
    ]]);
    $client->shouldReceive('getUndeployedPostsForTarget')->andReturn(['data' => [
        ['id' => 1, 'title' => 'Post 1', 'collection_key' => 'posts'],
    ]]);
    $client->shouldReceive('triggerDeployment')->andReturn(['data' => ['uuid' => 'test-uuid']]);
    $client->shouldReceive('streamDeploymentLogs')->andReturn('logs');
    $client->shouldReceive('fetchDeploymentStatus')->andReturn(['data' => ['status' => 'success']]);

    $this->artisan('ozu:deploy')
        ->expectsQuestion('Are you sure you want to deploy your website?', '1')
        ->expectsConfirmation('Do you want to sync your project with Ozu before deploying?', 'no')
        ->expectsQuestion('Select a deployment target to deploy to:', '1')
        ->expectsConfirmation('Deploying will publish all unpublished posts, do you want to continue?', 'yes')
        ->assertExitCode(Command::SUCCESS);
});
