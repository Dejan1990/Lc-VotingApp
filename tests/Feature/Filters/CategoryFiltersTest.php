<?php

namespace Tests\Feature\Filters;

use App\Http\Livewire\IdeasIndex;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Category;
use App\Models\Idea;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class CategoryFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function selecting_a_category_filters_correctly()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        Idea::factory()->create(['category_id' => $categoryOne->id]);
        Idea::factory()->create(['category_id' => $categoryOne->id]);
        Idea::factory()->create(['category_id' => $categoryTwo->id]);

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->category->name === 'Category 1';
            });
    }

    /** @test */
    public function the_category_query_string_filters_correctly()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        Idea::factory()->create(['category_id' => $categoryOne->id]);
        Idea::factory()->create(['category_id' => $categoryOne->id]);
        Idea::factory()->create(['category_id' => $categoryTwo->id]);

        Livewire::withQueryParams(['category' => 'Category 1'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->category->name === 'Category 1';
            });
    }

    /** @test */
    public function selecting_a_status_and_a_category_filters_correctly()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $statusOpen = Status::factory()->create(['name' => 'Open']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);

        $ideaOne = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id
        ]);

        $ideaTwo = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusConsidering->id
        ]);

        $ideaThree = Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'status_id' => $statusOpen->id
        ]);

        $ideaFour = Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'status_id' => $statusConsidering->id
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 2')
            ->set('status', 'Considering')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                    && $ideas->first()->category->name === 'Category 2'
                    && $ideas->first()->status->name === 'Considering';
            });
    }

    /** @test */
    public function the_category_query_string_filters_correctly_with_status_and_category()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $statusOpen = Status::factory()->create(['name' => 'Open']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);

        $ideaOne = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id
        ]);

        $ideaTwo = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusConsidering->id
        ]);

        $ideaThree = Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'status_id' => $statusOpen->id
        ]);

        $ideaFour = Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'status_id' => $statusConsidering->id
        ]);

        Livewire::withQueryParams(['status' => 'Open', 'category' => 'Category 2'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                    && $ideas->first()->category->name === 'Category 2'
                    && $ideas->first()->status->name === 'Open';
            });
    }

    /** @test */
    public function selecting_all_categories_filters_correctly()
    {
        Idea::factory()->create();
        Idea::factory()->create();
        Idea::factory()->create();

        Livewire::test(IdeasIndex::class)
            ->set('category', '')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3;
            });
    }
}