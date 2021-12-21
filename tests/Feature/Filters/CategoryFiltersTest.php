<?php

namespace Tests\Feature\Filters;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\Status;
use Livewire\Livewire;
use App\Http\Livewire\IdeasIndex;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function selecting_a_category_filters_correctly()
    {
        Idea::factory(3)->forCategory(['name' => 'Category 1'])->create();
        Idea::factory()->forCategory(['name' => 'Category 2'])->create();

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->category->name === 'Category 1';
            });
    }

    /** @test */
    public function the_category_query_string_filters_correctly()
    {
        Idea::factory(2)->forCategory(['name' => 'Category 1'])->create();
        Idea::factory()->forCategory(['name' => 'Category 2'])->create();

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
        $statusOpen = Status::factory()->create(['name' => 'Open']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        Idea::factory()->create(['category_id' => $categoryOne->id, 'status_id' => $statusOpen->id]);
        Idea::factory()->create(['category_id' => $categoryOne->id, 'status_id' => $statusConsidering->id]);

        Idea::factory()->create(['category_id' => $categoryTwo->id, 'status_id' => $statusOpen->id]);
        Idea::factory()->create(['category_id' => $categoryTwo->id, 'status_id' => $statusConsidering->id]);        

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 2')
            ->set('status', 'Open')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                && $ideas->first()->category->name === 'Category 2'
                && $ideas->first()->status->name === 'Open';
            });
    }

    /** @test */
    public function the_category_query_string_filters_correctly_with_status_and_category()
    {
        $statusOpen = Status::factory()->create(['name' => 'Open']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        Idea::factory()->create(['category_id' => $categoryOne->id, 'status_id' => $statusOpen->id]);
        Idea::factory()->create(['category_id' => $categoryOne->id, 'status_id' => $statusConsidering->id]);

        Idea::factory()->create(['category_id' => $categoryTwo->id, 'status_id' => $statusOpen->id]);
        Idea::factory()->create(['category_id' => $categoryTwo->id, 'status_id' => $statusConsidering->id]);  

        Livewire::withQueryParams(['status' => 'Considering', 'category' => 'Category 2'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                    && $ideas->first()->category->name === 'Category 2'
                    && $ideas->first()->status->name === 'Considering';
            });
    }

    /** @test */
    public function selecting_all_categories_filters_correctly()
    {
        Idea::factory(3)->create();

        Livewire::test(IdeasIndex::class)
            ->set('category', '')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3;
            });
    }
}
