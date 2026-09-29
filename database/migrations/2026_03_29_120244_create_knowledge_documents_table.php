<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable pgvector extension for PostgreSQL
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('category');
            $table->timestamps();
        });

        Schema::create('knowledge_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_document_id')
                  ->constrained('knowledge_documents')
                  ->cascadeOnDelete();
            $table->integer('chunk_index')->default(0);
            $table->text('content');
            $table->vector('embedding', dimensions: 768)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_document_chunks');
        Schema::dropIfExists('knowledge_documents');
    }
};
