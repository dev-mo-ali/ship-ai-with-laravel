<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeDocument extends Model
{
    /**
     * Get all chunks associated with this document.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeDocumentChunk::class);
    }
}
