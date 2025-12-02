<?php

namespace App\Builders\Pdf\Tuition;

use App\Builders\Pdf\BasePdfBuilder;
use App\Models\TuitionFees;

class TuitionFeeDebtorPdfBuilder extends BasePdfBuilder
{
    public function getData(): array
    {
        $query = TuitionFees::query()
        ->with(['student', 'specialty', 'level'])
            ->select([
                'student_id',
                'school_branch_id',
                'specialty_id',
                'level_id',
                'amount_paid',
                'amount_left',
                'tution_fee_total',
                'status'
            ]);

        if (!empty($this->context['school_branch_id'])) {
            $query->where('school_branch_id', $this->context['school_branch_id']);
        }

        if(!empty($this->context['specialty_id'])){
             $query->where('specialty_id', $this->context['specialty_id']);
        }
        $tuitionFees = $query->get();

        $tuitionFees = $tuitionFees->map(fn($tuitionFee) => [
            'student_name'            => $tuitionFee->student->name,
            'specialty_name'      => $tuitionFee->specialty->specialty_name,
            'level'       => $tuitionFee->level->level,
            'amount_paid'           => $tuitionFee->amount_paid,
            'amount_left'       => $tuitionFee->amount_left,
            'fee_total'         => $tuitionFee->fee_total
        ]);

        $columns = $this->options['columns'] ?? [
            'student_name',
            'specialty_name',
            'level',
            'amount_paid',
            'amount_left',
            'fee_total'
        ];

        return [
            'title'    => $this->title ?? 'Fee Debtor List',
            'columns'  => $columns,
            'tuitionFees' => $tuitionFees,
        ];
    }

    public function getView(): string
    {
        return 'pdf.tuitionFeeDebtor';
    }
}
