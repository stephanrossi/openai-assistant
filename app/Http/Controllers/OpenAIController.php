<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;

class OpenAIController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function createAssistant(Request $request)
    {
        $assistant = $this->openAIService->createAssistant(
            $request->input('name'),
            $request->input('instructions'),
            $request->input('model', 'gpt-4o'),
            $request->input('tools')
        );

        return response()->json($assistant);
    }

    public function sendMessage(Request $request)
    {
        $thread = $this->openAIService->createThread();
        $this->openAIService->addMessageToThread($thread->id, 'user', $request->input('message'));

        $response = $this->openAIService->runAssistant($request->input('assistant_id'), $thread->id);

        return response()->json($response);
    }
}
