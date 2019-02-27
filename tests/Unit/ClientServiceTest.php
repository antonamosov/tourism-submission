<?php

namespace Tests\Unit;

use App\Services\ClientService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ClientServiceTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    const USERS_COUNT = 3;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreate()
    {
        $users = [];

        for ($i = 0; $i < self::USERS_COUNT; $i++) {
            $users[] = [
                'name' => $this->faker->name,
                'country' => $this->faker->countryCode,
                'phone' => $this->faker->phoneNumber
            ];
        }

        app()->make(ClientService::class)->create([
            'users' => $users,
        ]);

        foreach ($users as $user) {
            $this->assertDatabaseHas('clients', [
                'name' => $user['name'],
                'phone' => $user['phone'],
                'country_iso' => $user['country'],
            ]);
        }
    }
}
