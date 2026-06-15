<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Tests\TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

// Custom expectations can be added here if needed.

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function actingAsAdmin(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function actingAsManager(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    $user->assignRole('manager');

    return $user;
}

function actingAsEmployee(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    $user->assignRole('employee');

    return $user;
}
