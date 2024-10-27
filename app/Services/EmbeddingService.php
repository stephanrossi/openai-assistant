<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Vector;

class EmbeddingService
{
    public function processFile($file)
    {
        // Ler o conteúdo do arquivo (exemplo com arquivos de texto)
        $content = file_get_contents($file->getPathname());

        // Gerar os embeddings utilizando a API da OpenAI
        $embeddings = $this->generateEmbeddings($content);

        // Armazenar os embeddings no banco de dados
        return $this->storeEmbeddings($file->getClientOriginalName(), $embeddings);
    }

    private function generateEmbeddings($text)
    {
        // Chamar a API da OpenAI para gerar embeddings do texto
        $openAiResponse = OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $text,
        ]);

        // Retornar os embeddings gerados
        return $openAiResponse->data[0]->embedding;
    }

    private function storeEmbeddings($fileName, $embeddings)
    {
        // Salvar os embeddings no banco de dados
        $vector = new Vector();
        $vector->file_name = $fileName;
        $vector->embedding = json_encode($embeddings); // Armazenar os vetores como JSON
        $vector->save();

        return $vector;
    }
}
