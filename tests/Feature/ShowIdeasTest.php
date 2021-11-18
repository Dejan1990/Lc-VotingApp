<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Idea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowIdeasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function list_of_ideas_shows_on_main_page()
    {
        $categoryOne = Category::factory()->create([
            'name' => 'Category 1'
        ]);

        $categorytwo = Category::factory()->create([
            'name' => 'Category 2'
        ]);

        $ideaOne = Idea::factory()->create([
            'title' => 'My first idea',
            'category_id' => $categoryOne->id,
            'description' => 'Description of my first idea'
        ]);

        $ideaTwo = Idea::factory()->create([
            'title' => 'My second idea',
            'category_id' => $categorytwo->id,
            'description' => 'Description of my second idea'
        ]);

        $response = $this->get(route('idea.index'));

        $response->assertSuccessful();
        $response->assertSee($ideaOne->title);
        $response->assertSee($categoryOne->name);
        $response->assertSee($ideaOne->description);
        $response->assertSee($ideaTwo->title);
        $response->assertSee($categorytwo->name);
        $response->assertSee($ideaTwo->description);
    }

    /** @test */
    public function single_idea_shows_correctly_on_the_show_page()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea1 = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'title' => 'My first idea',
            //'description' => 'My description'
        ]);

        $idea2 = Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'title' => 'My second idea'
        ]);

        $response = $this->get(route('idea.show', $idea1));

        $response->assertSuccessful();
        $response->assertSee($idea1->title);
        $response->assertSee($categoryOne->name);
        //$response->assertDontSee($idea2->title);
        //$response->assertDontSee($categoryTwo->name);
    }

    /** @test */
    public function ideas_pagination_works()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        Idea::factory(Idea::PAGINATION_COUNT + 1)->create([
            'category_id' => $categoryOne->id,
        ]);

        $ideaOne = Idea::find(1);
        $ideaOne->title = 'My First Idea';
        $ideaOne->save();

        $ideaEleven = Idea::find(11);
        $ideaEleven->title = 'My Eleventh Idea';
        $ideaEleven->save();

        $response = $this->get('/');

        $response->assertSee($ideaOne->title);
        $response->assertDontSee($ideaEleven->title);

        $response = $this->get('/?page=2');

        $response->assertSee($ideaEleven->title);
        $response->assertDontSee($ideaOne->title);
    }
}
