<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use App\Models\KnowledgeArticle;


class KnowledgeArticleSearch implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the company knowledge base for policies,
                     procedures, and FAQ answers. Use this when a customer asks about returns,
                     shipping, billing, account issues, or any company policy.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {

        Log::info('used tool KnowledgeArticle');
        $results = KnowledgeArticle::query()
            ->whereVectorSimilarTo('embedding', $request['query'])
            ->limit(5)
             
            ->select('id', 'content', 'category');

        return $results;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required()->description('user question'),
        ];
    }
}
