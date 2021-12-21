<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use Livewire\Livewire;
use App\Models\Category;
use App\Http\Livewire\CreateIdea;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateIdeaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_idea_form_does_not_show_when_logged_out()
    {
        $response = $this->get('/');

        $response->assertSuccessful();
        $response->assertSee('Please login to create an idea.');
        $response->assertDontSee('Let us know what you would like and we\'ll take a look over!', false);
    }

    /** @test */
    public function create_idea_form_does_show_when_logged_in()
    {
        $response = $this->actingAs(User::factory()->create())->get('/');
        $response->assertSuccessful();
        $response->assertSee('Let us know what you would like and we\'ll take a look over!', false);
        $response->assertDontSee('Please login to create an idea.');
    }

    /** @test */
    public function main_page_contains_create_idea_livewire_component()
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertSeeLivewire('create-idea');
    }

    /** @test */
    public function create_idea_form_validation_work()
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateIdea::class)
            ->set('title', '')
            ->set('description', '')
            ->set('category', '')
            ->call('createIdea')
            ->assertHasErrors(['title', 'description', 'category'])
            ->assertSee('The title field is required');
    }

    /** @test */
    public function creating_an_idea_works_correctly()
    {
        $user = User::factory()->create();
        $categoryOne = Category::factory()->create();
        $statusConsidering = Status::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateIdea::class)
            ->set('title', 'My first idea')
            ->set('description', 'My first description')
            ->set('category', $categoryOne->id)
            ->call('createIdea')
            ->assertRedirect('/');

        $response = $this->actingAs($user)->get('/');
        $response->assertSuccessful();
        $response->assertSee('My first idea');
        $response->assertSee('My first description');

        $this->assertDatabaseHas('ideas', [
            'title' => 'My first idea'
        ]);

        $this->assertDatabaseHas('votes', [
            'idea_id' => 1,
            'user_id' => 1,
        ]);
    }
}
