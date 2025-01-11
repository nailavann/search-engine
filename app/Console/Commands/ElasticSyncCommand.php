<?php

namespace App\Console\Commands;

use App\ElasticService;
use App\Models\LargeArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ElasticSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:elastic-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync data';

    protected ElasticService $elasticService;

    public function __construct(ElasticService $elasticService)
    {
        parent::__construct();
        $this->elasticService = $elasticService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $indexName = 'large_articles';

        $startTime = Carbon::now();

        LargeArticle::with('author')->chunkById(5000, function ($articles) use ($indexName) {
            $bulkBody = [];

            foreach ($articles as $article) {
                $bulkBody[] = json_encode([
                    'index' => [
                        '_index' => $indexName,
                        '_id' => $article->id
                    ]
                ]);

                // Document data
                $bulkBody[] = json_encode([
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'view_count' => $article->view_count,
                    'answer_count' => $article->answer_count,
                    'createdAt' => $article->created_at,
                    'author' => [
                        'id' => $article->author->id,
                        'name' => $article->author->name,
                        'email' => $article->author->email,
                    ]
                ]);
            }

            $bulkRequestBody = implode("\n", $bulkBody) . "\n";

            try {
                $this->elasticService->sendRequest(
                    'POST',
                    "_bulk",
                    $bulkRequestBody,
                    ['Content-Type' => 'application/x-ndjson']
                );

            } catch (\Exception $e) {
                $this->error("\nError during bulk import: " . $e->getMessage());
            }
        });

        $endTime = Carbon::now();

        $executionTime = $startTime->diffInSeconds($endTime);
        $this->info("\nBulk import completed in " . $executionTime . " seconds.");
    }
}
