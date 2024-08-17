<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Article;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    // Test ini bertujuan untuk memastikan bahwa endpoint POST /articles berfungsi dengan benar.
    public function test_create_article_with_authentication()
    {
        // Register and login user to get token
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Make authenticated request
        $response = $this->postJson('/api/articles', [
            'author' => 'John Doe',
            'title' => 'Sample Title',
            'body' => 'Sample Body',
        ], ['Authorization' => "Bearer $token"]);

        $response->assertStatus(201)
                ->assertJson([
                    'author' => 'John Doe',
                    'title' => 'Sample Title',
                    'body' => 'Sample Body',
                ]);
    }

    public function test_create_article_without_token()
    {
        $response = $this->postJson('/api/articles', [
            'author' => 'John Doe',
            'title' => 'Sample Title',
            'body' => 'Sample Body',
        ]);

        $response->assertStatus(401);
    }


    // Test ini bertujuan untuk memastikan bahwa endpoint GET /articles berfungsi dengan benar.
    public function test_get_articles_with_authentication()
    {
        // Register and login user to get token
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Create articles
        Article::factory()->create(['title' => 'First Article']);
        Article::factory()->create(['title' => 'Second Article']);

        // Make authenticated request
        $response = $this->getJson('/api/articles', ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200)
                ->assertJsonCount(2);
    }

    public function test_get_articles_without_token()
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(401);
    }


}
