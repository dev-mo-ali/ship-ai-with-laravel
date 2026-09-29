<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use App\Models\KnowledgeDocumentChunk;
use Illuminate\Support\Facades\Log;

class KnowledgeDocumentSearch implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the company documents base for detailed policy questions,
                use to find specific documentation. Use this when a customer asks about returns,
                shipping, billing, account issues, or any company policy.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {

        Log::info('used tool KnowledgeDocumentChunk');
        $results = KnowledgeDocumentChunk::query()
            ->whereVectorSimilarTo('embedding', $request['query'])
            ->limit(5)
            ->select(
                'id',
                'knowledge_document_id',
                'chunk_index',
                'content'
            );

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
