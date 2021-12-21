<?php

namespace Tests\Feature;

use App\Http\Livewire\IdeasIndex;
use Tests\TestCase;
use App\Models\Idea;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function searching_works_when_more_than_3_characters()
    {
        Idea::factory()->create(['title' => 'My First Idea']);
        Idea::factory()->create(['title' => 'My Second Idea']);
        Idea::factory()->create(['title' => 'My Third Idea']);

        Livewire::test(IdeasIndex::class)
            ->set('search', 'second')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                && $ideas->first()->title === 'My Second Idea';
            });
    }

    /** @test */
    public function does_not_perform_search_if_less_than_3_characters()
    {
        Idea::factory()->create(['title' => 'My First Idea']);
        Idea::factory()->create(['title' => 'My Second Idea']);
        Idea::factory()->create(['title' => 'My Third Idea']);

        Livewire::test(IdeasIndex::class)
            ->set('search', 'ab')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3;
            });
    }

    /** @test */
    public function search_works_correctly_with_category_filters()
    {
       /* $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        Idea::factory()->create([
            'title' => 'My First Idea',
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create([
            'title' => 'My Second Idea',
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create([
            'title' => 'My Third Idea',
            'category_id' => $categoryTwo->id,
        ]);*/

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        Idea::factory()->create([
            'title' => 'First idea',
            'category_id' => $categoryOne->id
        ]);

        Idea::factory()->create([
            'title' => 'Second idea',
            'category_id' => $categoryOne->id
        ]);

        Idea::factory()->create([
            'title' => 'Third idea',
            'category_id' => $categoryTwo->id
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->set('search', 'idea')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                && $ideas->first()->title === 'Second idea'
                && $ideas->first()->category->name === 'Category 1'
                && $ideas->get(1)->title === 'First idea'
                && $ideas->get(1)->category->name === 'Category 1';
            });
    }
}
