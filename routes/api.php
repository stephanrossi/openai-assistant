<?php

use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\OpenAIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rota para criar um novo assistente
Route::post('/openai/create-assistant', [OpenAIController::class, 'createAssistant']);

// Rota para enviar uma mensagem para uma thread existente
Route::post('/openai/send-message', [OpenAIController::class, 'sendMessage']);

// Rota para executar o assistente e obter a resposta
Route::post('/openai/run-assistant', [OpenAIController::class, 'runAssistant']);

Route::get('/base64', [ArquivoController::class, 'receberArquivoBase64']);
Route::post('/base642', [ArquivoController::class, 'converterArquivoParaBase64']);
