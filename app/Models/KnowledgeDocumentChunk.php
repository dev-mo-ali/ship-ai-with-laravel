<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeDocumentChunk extends Model
{
    use HasFactory;



    protected $casts = [
        // Cast the embedding column back to a PHP array when retrieving from DB.
        // If you are using pgvector, this might need to be cast as a specific vector type
        // provided by your database driver package (e.g., Pgvector\Laravel\Vector).
        'embedding' => 'array',
        // 'embedding' => AsVector::class,
    ];

    /**
     * Get the knowledge document that owns this chunk.
     *
     * @return BelongsTo<KnowledgeDocument, KnowledgeDocumentChunk>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(KnowledgeDocument::class, 'knowledge_document_id');
    }
}
