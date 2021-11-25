<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Status;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowIdeasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function list_of_ideas_shows_on_main_page()
    {
        $statusOpen = Status::factory()->create(['name' => 'OpenUnique']);
        $statusConsidering = Status::factory()->create(['name' => 'ConsideringUnique']);
        $statusImplemented = Status::factory()->create(['name' => 'ImplementedUnique']);

        Idea::factory()->create(['title' => 'First title', 'status_id' => $statusOpen->id]);
        Idea::factory()->create(['title' => 'Second title', 'status_id' => $statusConsidering->id]);

        $response = $this->get('/');
        $response->assertSuccessful();
        $response->assertSee('First title');
        $response->assertSee('Second title');
        $response->assertSee('ConsideringUnique');
        $response->assertSee('OpenUnique');
        $response->assertDontSee('ImplementedUnique');
    }

    /** @test */
    public function single_idea_shows_correctly_on_the_show_page()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category Unique']);
        $statusOpen = Status::factory()->create(['name' => 'OpenUnique']);

        $idea = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id,
            'title' => 'My First Idea',
        ]);
        $response = $this->get(route('idea.show', $idea));
        $response->assertSuccessful();
        $response->assertSee('My First Idea');
        $response->assertSee('Category Unique');
        $response->assertSee('OpenUnique');
    }

    /** @test */

    public function in_app_back_button_works_when_index_page_visited_first()
    {
        $idea = Idea::factory()->create();
        
        $response = $this->get('?category=Category%202&status=Considering');
        $response = $this->get(route('idea.show', $idea));

        $this->assertStringContainsString('?category=Category%202&status=Considering', $response['backUrl']);
    }

    /** @test */
    public function in_app_back_button_works_when_show_page_only_page_visited()
    {
        $idea = Idea::factory()->create();

        $response = $this->get(route('idea.show', $idea));
        $this->assertEquals(route('idea.index'), $response['backUrl']);
    }
}
