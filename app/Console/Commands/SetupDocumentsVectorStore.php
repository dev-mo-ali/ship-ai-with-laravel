<?php

namespace App\Console\Commands;

use App\Models\KnowledgeDocument;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;
use RuntimeException;

#[Signature('kb:setup-documents-store')]
#[Description('Upload support documents and create a vector store in database')]
class SetupDocumentsVectorStore extends Command
{
    public function handle(): void
    {
        $this->info('Creating documents vector store...');

        $files = Storage::disk('local')->files('docs');
        

        $files = array_filter($files, fn($file) => str_ends_with($file, '.md'));

        if (empty($files)) {
            $this->warn('No documents found in storage/app/private/docs');
            return;
        }

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $filePath) {
            $content = Storage::disk('local')->get($filePath);
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            $title = str($filename)->headline()->toString();

            $document = KnowledgeDocument::updateOrCreate(
                ['title' => $title],
                [
                    'content' => $content,
                    'category' => 'general',
                ]
            );

            // Using the 850-character sliding chunk window (from DocumentProcessingService)
            $chunks = $this->chunks($content);

            $document->chunks()->delete();

            // Batch chunks in groups of 16 for optimal API throughput
            foreach (array_chunk($chunks, 16, true) as $batch) {
                $embeddings = Embeddings::for(array_values($batch))
                    ->dimensions(768)
                    ->generate()
                    ->embeddings;

                if (count($embeddings) !== count($batch)) {
                    throw new RuntimeException('The embedding provider returned an incomplete response.');
                }

                foreach (array_keys($batch) as $position => $index) {
                    $embedding = $embeddings[$position];

                    if (count($embedding) !== 768 || count(array_filter($embedding, is_numeric(...))) !== 768) {
                        throw new RuntimeException('The embedding provider must return 768 numeric dimensions.');
                    }

                    $document->chunks()->create([
                        'chunk_index' => $index,
                        'content' => $batch[$index],
                        'embedding' => $embedding,
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All documents uploaded and indexed successfully!');
    }

    /**
     * Chunk text using an 850 character sliding window with 1000 max size.
     *
     * @return list<string>
     */
    protected function chunks(string $text): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($text === '') {
            return [];
        }

        $chunks = [];
        $length = mb_strlen($text);

        for ($offset = 0; $offset < $length; $offset += 850) {
            $chunks[] = mb_substr($text, $offset, 1000);

            if ($offset + 1000 >= $length) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Single string embedding wrapper.
     *
     * @return array<int, float>
     */
    protected function generateEmbedding(string $text): array
    {
        $response = Embeddings::for($text)->dimensions(768)->generate();
        $embedding = $response->embedding ?? $response->embeddings[0] ?? [];

        if (count($embedding) !== 768 || count(array_filter($embedding, is_numeric(...))) !== 768) {
            throw new RuntimeException('The embedding provider must return 768 numeric dimensions.');
        }

        return $embedding;
    }
}
