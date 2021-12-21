<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Idea;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowIdeasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function list_of_ideas_shows_on_main_page()
    {
        Idea::factory()->forStatus(['name' => 'OpenUnique'])->create(['title' => 'First title']);
        Idea::factory()->forStatus(['name' => 'ConsideringUnique'])->create(['title' => 'Second title']);

        $response = $this->get('/');
        $response->assertSuccessful();
        $response->assertSee('First title');
        $response->assertSee('Second title');
        $response->assertSee('ConsideringUnique');
        $response->assertSee('OpenUnique');
        $this->assertEquals(2, Idea::count());
    }

    /** @test */
    public function single_idea_shows_correctly_on_the_show_page()
    {
        $idea = Idea::factory()->forCategory(['name' => 'Category Unique'])
            ->forStatus(['name' => 'OpenUnique'])
            ->create(['title' => 'My idea']);

        $response = $this->get(route('idea.show', $idea));
        $response->assertSuccessful();
        $response->assertSee('My idea');
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
