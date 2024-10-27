<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    public function createAssistant(string $name, string $instructions, string $model = 'gpt-3.5-turbo')
    {
        return OpenAI::assistants()->create([
            'name' => $name,
            'instructions' => $instructions,
            'model' => $model,
        ]);
    }

    public function createThread()
    {
        return OpenAI::threads()->create([
            // 'title' => 'Minha nova thread',
            // outros parâmetros, se necessários
        ]);
    }

    public function addMessageToThread(string $threadId, string $role, string $content)
    {
        return OpenAI::threads()->messages()->create($threadId, [
            'role' => $role,
            'content' => $content,
        ]);
    }

    public function runAssistant(string $assistantId, string $threadId)
    {
        $run = OpenAI::threads()->runs()->create($threadId, [
            'assistant_id' => $assistantId,
        ]);

        while (in_array($run->status, ['queued', 'in_progress'])) {
            sleep(1);
            $run = OpenAI::threads()->runs()->retrieve($threadId, $run->id);
        }

        return OpenAI::threads()->messages()->list($threadId);
    }
}
