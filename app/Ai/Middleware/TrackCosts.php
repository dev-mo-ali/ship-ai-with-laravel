<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use App\Models\AgentUsage;

class TrackCosts
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        return $next($prompt)->then(function (AgentResponse $response) use ($prompt) {
            $inputTokens = $response->usage->promptTokens ?? 0;
            $outputTokens = $response->usage->completionTokens ?? 0;

            AgentUsage::create([
                'user_id'       => auth()->id(),
                'agent'         => get_class($prompt->agent),
                'provider'      => $response->meta->provider,
                'model'         => $response->meta->model,
                'input_tokens'  => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens'  => $inputTokens + $outputTokens,
                // Cost per 1M Tokens:  $0.5
                'cost' => ((($inputTokens + $outputTokens) * 0.5) / 1000000)
            ]);

            Log::info([
                'user_id' => auth()->id(),
                'agent' => get_class($prompt->agent),
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
                'input_tokens' => $response->usage->promptTokens,
                'output_tokens' => $response->usage->completionTokens,
            ]);
        });
    }
}
