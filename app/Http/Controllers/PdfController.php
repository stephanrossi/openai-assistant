<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\URL;

class PdfController extends Controller
{
    /**
     * Gera um arquivo PDF ou Excel a partir de um código HTML fornecido.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gerarArquivo(Request $request)
    {
        try {
            // Recupera o conteúdo JSON enviado na requisição
            $htmlContent = $request->input('html_content');

            // Verifica se o conteúdo HTML foi fornecido
            if (empty($htmlContent)) {
                return response()->json(['erro' => 'O conteúdo HTML é obrigatório.'], 400);
            }

            // Detecta se o conteúdo HTML contém tags de tabela
            if (stripos($htmlContent, '<table') !== false) {
                // Gera uma planilha Excel
                $filename = 'documento_' . Str::random(10) . '.xlsx';

                // Converte o HTML em um array de dados para o Excel
                $data = $this->parseHtmlTable($htmlContent);

                // Cria a planilha
                $spreadsheet = new Spreadsheet();
                $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(12);

                // Define a localidade para pt-br
                \PhpOffice\PhpSpreadsheet\Settings::setLocale('pt_br');

                $sheet = $spreadsheet->getActiveSheet();

                // Preenche os dados na planilha
                $rowNumber = 1;
                foreach ($data as $row) {
                    $columnLetter = 'A';
                    foreach ($row as $cell) {
                        $sheet->setCellValue($columnLetter . $rowNumber, $cell);
                        $columnLetter++;
                    }
                    $rowNumber++;
                }

                // Salva o arquivo Excel no storage
                $writer = new Xlsx($spreadsheet);
                $filePath = 'public/' . $filename;
                $writer->save(storage_path('app/' . $filePath));

                // Gera o link completo para download
                $url = URL::to('/') . Storage::url($filename);

                return response()->json(['url' => $url], 200);
            } else {
                // Gera um arquivo PDF
                // Adiciona a declaração de charset UTF-8 no HTML para garantir a codificação correta
                $htmlContent = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $htmlContent;

                $pdf = Pdf::loadHTML($htmlContent);

                // Salva o PDF em storage/app/public usando o mesmo método do Excel
                $filename = 'documento_' . Str::random(10) . '.pdf';
                $filePath = 'public/' . $filename;
                file_put_contents(storage_path('app/' . $filePath), $pdf->output());

                // Gera o link completo para download
                $url = URL::to('/') . Storage::url($filename);

                return response()->json(['url' => $url], 200);
            }
        } catch (\Exception $e) {
            // Loga o erro para análise posterior
            Log::error('Erro ao gerar arquivo: ' . $e->getMessage());

            // Retorna uma resposta de erro genérica ao usuário
            return response()->json(['erro' => 'Ocorreu um erro ao gerar o arquivo.'], 500);
        }
    }

    /**
     * Converte uma tabela HTML em um array de dados.
     *
     * @param string $htmlContent
     * @return array
     */
    private function parseHtmlTable($htmlContent)
    {
        // Inicializa o DOMDocument e desabilita warnings
        $dom = new \DOMDocument();

        // Configura a codificação para UTF-8
        $dom->encoding = 'UTF-8';

        // Carrega o HTML, adicionando a declaração de encoding
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);

        // Cria uma instância do DOMXPath
        $xpath = new \DOMXPath($dom);

        $data = [];

        // Seleciona todas as tabelas
        $tables = $dom->getElementsByTagName('table');

        foreach ($tables as $table) {
            // Seleciona todas as linhas (tr) dentro da tabela
            $rows = $xpath->query('.//tr', $table);

            foreach ($rows as $row) {
                $rowData = [];

                // Seleciona todas as células (th e td) diretamente sob o tr
                $cells = $xpath->query('./th|./td', $row);

                foreach ($cells as $cell) {
                    // Obtém o texto com encoding correto
                    $cellText = $cell->textContent;
                    $cellText = html_entity_decode($cellText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    $rowData[] = trim($cellText);
                }

                if (!empty($rowData)) {
                    $data[] = $rowData;
                }
            }
        }

        return $data;
    }

    public function ping()
    {
        try {
            return response()->json([
                'status' => 'ok',
                'answer' => 'pong'
            ], 200);
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }
}
