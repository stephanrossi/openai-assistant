<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArquivoController extends Controller
{
    public function receberArquivoBase64(Request $request)
    {
        try {
            // Obtém o arquivo base64 enviado pelo usuário
            $base64String = $request->input('arquivo');

            // Verifica se o arquivo foi enviado
            if (!$base64String) {
                return response()->json(['erro' => 'Nenhum arquivo fornecido.'], 400);
            }

            // Verifica e extrai o tipo MIME do arquivo
            if (preg_match('/^data:(.*);base64,/', $base64String, $matches)) {
                $mimeType = $matches[1];

                $base64String = substr($base64String, strpos($base64String, ',') + 1);
            } else {
                return response()->json(['erro' => 'Formato base64 inválido.'], 400);
            }

            // Decodifica a string base64
            $arquivoData = base64_decode($base64String);

            if ($arquivoData === false) {
                return response()->json(['erro' => 'Falha na decodificação base64.'], 400);
            }

            // Obtém a extensão do arquivo com base no tipo MIME
            $extensao = $this->obterExtensaoPorMimeType($mimeType);

            // print_r($extensao);die;

            if (!$extensao) {
                return response()->json(['erro' => 'Tipo de arquivo não suportado.'], 400);
            }

            // Gera um nome de arquivo único
            $nomeArquivo = uniqid() . '.' . $extensao;

            // Define o caminho onde o arquivo será salvo
            $caminhoArquivo = 'uploads/' . $nomeArquivo;

            // Salva o arquivo no armazenamento público
            Storage::disk('public')->put($caminhoArquivo, $arquivoData);

            // Gera um link público para o arquivo
            $link = asset('storage/' . $caminhoArquivo);

            // Retorna o link para o usuário
            return response()->json(['link' => $link], 200);
        } catch (\Exception $e) {
            // Trata exceções e retorna um erro genérico
            return response()->json(['erro' => 'Ocorreu um erro ao processar o arquivo.'], 500);
        }
    }

    private function obterExtensaoPorMimeType($mimeType)
    {
        $mapaMimeTipos = [
            'text/csv' => 'csv',
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xls',
            // Adicione outros tipos MIME e extensões conforme necessário
        ];

        return $mapaMimeTipos[$mimeType] ?? null;
    }

    public function converterArquivoParaBase64(Request $request)
    {
        try {

            // print_r($request->file('arquivo'));die;
            // Verifica se o arquivo foi enviado na requisição
            if (!$request->hasFile('arquivo')) {
                return response()->json(['erro' => 'Nenhum arquivo fornecido.'], 400);
            }

            $arquivo = $request->file('arquivo');

            // Verifica se o arquivo é válido
            if (!$arquivo->isValid()) {
                return response()->json(['erro' => 'Arquivo inválido.'], 400);
            }

            // Lê o conteúdo do arquivo
            $conteudoArquivo = file_get_contents($arquivo->getRealPath());

            // Obtém o tipo MIME do arquivo
            $mimeType = $arquivo->getMimeType();

            // Converte o conteúdo do arquivo para base64
            $base64String = 'data:' . $mimeType . ';base64,' . base64_encode($conteudoArquivo);

            // Retorna a string base64 ao usuário
            return response()->json(['base64' => $base64String], 200);
        } catch (\Exception $e) {
            // Trata exceções e retorna um erro genérico
            return response()->json(['erro' => 'Ocorreu um erro ao processar o arquivo.'], 500);
        }
    }
}
