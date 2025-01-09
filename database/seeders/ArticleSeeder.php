<?php

namespace Database\Seeders;

use App\Jobs\CreateAndInsertDataJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $queue = 10;
        for ($i = 0; $i < $queue; $i++) {
            CreateAndInsertDataJob::dispatch();
        }
    }
}
