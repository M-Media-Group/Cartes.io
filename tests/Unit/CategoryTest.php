<?php

namespace Tests\Unit;

use Tests\TestCase;

class CategoryTest extends TestCase
{
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $post = \App\Models\Category::firstOrCreate();
        $this->category = $post;
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSingleCategoryTest()
    {
        $response = $this->getJson('/api/categories/'.$this->category->id);

        // Assert status is 405
        $response->assertStatus(404);
    }

    /**
     * Test see related categories for a given category.
     *
     * @return void
     */
    public function testSeeRelatedCategoriesTest()
    {
        $response = $this->getJson('/api/categories/'.$this->category->id.'/related');
        $response->assertStatus(200);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllCategoriesTest()
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }

    /**
     * Test created a category.
     *
     * @return void
     */
    public function testCreatecategoryTest()
    {
        $response = $this->postJson('/api/categories');

        // Assert method not allowed - new categories can only be created with markers
        $response->assertStatus(405);
    }

    /**
     * Test deleting a category.
     *
     * @return void
     */
    public function testDeletecategoryTest()
    {
        $response = $this->deleteJson('/api/categories/'.$this->category->id, [
            'token' => $this->category->token,
        ]);

        // Assert method not allowed
        $response->assertStatus(405);
    }
}
