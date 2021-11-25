<?php

namespace Tests\Unit;

use App\Models\Idea;
use App\Models\User;
use App\Models\Status;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_count_of_each_status()
    {
        /*Idea::factory()->count(7)->forUser($user)->forCategory(['name' => 'Open'])->create();
        Idea::factory()->count(15)->forUser($user)->forCategory(['name' => 'Considering'])->create();
            primer iz komentara
        */

        Idea::factory()->count(5)->forUser()->forStatus(['name' => 'Open'])->create();
        Idea::factory()->count(4)->forUser()->forStatus(['name' => 'Considering'])->create();
        Idea::factory()->count(3)->forUser()->forStatus(['name' => 'In Progress'])->create();
        Idea::factory()->count(2)->forUser()->forStatus(['name' => 'Implemented'])->create();
        Idea::factory()->count(1)->forUser()->forStatus(['name' => 'Closed'])->create();

        $this->assertEquals(15, Status::getCount()['all_statuses']);
        $this->assertEquals(5, Status::getCount()['open']);
        $this->assertEquals(4, Status::getCount()['considering']);
        $this->assertEquals(3, Status::getCount()['in_progress']);
        $this->assertEquals(2, Status::getCount()['implemented']);
        $this->assertEquals(1, Status::getCount()['closed']);
    }
}
