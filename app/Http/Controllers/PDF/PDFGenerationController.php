<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use App\Http\Requests\PDF\GeneratePdfRequest;
use App\Services\ApiResponseService;
use App\Services\PDF\PDFService;

class PDFGenerationController extends Controller
{
    protected PDFService $pdfservice;

public function __construct(PDFService $pdfservice)
    {
        $this->pdfservice = $pdfservice;
    }

    public function generatePdf(GeneratePdfRequest $request)
    {
        $payload = $request->validated();

        $currentSchool = $request->attributes->get('currentSchool');

        $payload['context'] = [
            "school_branch_id" => $currentSchool->id
        ];

        $pdf = $this->pdfservice->generate($payload);

        return $pdf->download(($payload['type'] ?? 'document') . '.pdf');
    }
}
