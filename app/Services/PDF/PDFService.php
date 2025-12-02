<?php

namespace App\Services\PDF;

use App\Builders\Pdf\PdfBuilderFactory;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{
    public function generate(array $payload)
    {
        $builder = PdfBuilderFactory::make($payload['type']);

        $builder
            ->setTitle($payload['title'] ?? null)
            ->setFilters($payload['filters'] ?? [])
            ->setOptions($payload['options'] ?? [])
            ->setContext($payload['context'] ?? []);

        $data = $builder->getData();
        $view = $builder->getView();

        return Pdf::loadView($view, $data);
    }
}
