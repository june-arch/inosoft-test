<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;

class UserControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_user()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        $response = $this->withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Basic aW5vc2ZvdDphc2RmLXF3ZXItenhjdi1hc2Rm'
        ])->post('/v1/users/register', [
            "fullname" => "juniko",
            "email" => "rssa@gmail.com",
            "no_hp" => "085155338389",
            "password" => "123456"
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            "data" => [
                "fullname" => "juniko",
                "email" => "rss@gmail.com",
                "no_hp" => "085155338389",
                "updated_at" => "2022-11-10T04:11:52.622000Z",
                "created_at" => "2022-11-10T04:11:52.622000Z"
            ],
            "message" => "success register user"
        ]);
    }
}
