<?php

namespace App\Jobs;

use App\Models\Author;
use Faker\Factory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateAndInsertDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 100000;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $authors = Author::query()->get();
            $faker = Factory::create();
            $data = [];
            for ($i = 0; $i < 100000; $i++) {
                $date = $faker->dateTimeThisMonth();
                $data[] = [
                    'title' => $faker->paragraph(2),
                    'content' => $faker->paragraph(13),
                    'view_count' => $faker->numberBetween(100, 10000),
                    'answer_count' => $faker->numberBetween(10, 1000),
                    'author_id' => $authors->random()->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }

            $chunks = array_chunk($data, 2000);
            foreach ($chunks as $chunk) {
                DB::table('large_articles')->insert($chunk);
            }
        } catch (\Throwable $exception) {
            Log::error("Error occurred while creating and inserting data: {$exception->getMessage()}");
        }
    }
}
