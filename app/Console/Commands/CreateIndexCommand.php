<?php

namespace App\Console\Commands;

use App\ElasticService;
use Illuminate\Console\Command;

class CreateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Elasticsearch index with predefined mapping';

    protected ElasticService $elasticService;

    public function __construct(ElasticService $elasticService)
    {
        parent::__construct();
        $this->elasticService = $elasticService;
    }

    public function handle()
    {
        $indexName = 'large_articles';

        $mapping = [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0 // single node
            ],
            'mappings' => [
                'properties' => [
                    'id' => [
                        'type' => 'long'
                    ],
                    'title' => [
                        'type' => 'text'
                    ],
                    'content' => [
                        'type' => 'text'
                    ],
                    'view_count' => [
                        'type' => 'long'
                    ],
                    'answer_count' => [
                        'type' => 'long'
                    ],
                    'created_at' => [
                        'type' => 'date'
                    ],
                    'author' => [
                        'properties' => [
                            'id' => [
                                'type' => 'long'
                            ],
                            'name' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => [
                                        'type' => 'keyword'
                                    ]
                                ]
                            ],
                            'email' => [
                                'type' => 'keyword'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->elasticService->sendRequest(
                'PUT',
                '/' . $indexName,
                json_encode($mapping)
            );

            if ($response['status'] === 200) {
                $this->info("Index '$indexName' created successfully!");
            } else {
                $this->error("Failed to create index: " . ($response['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error("Error creating index: " . $e->getMessage());
        }
    }
}
