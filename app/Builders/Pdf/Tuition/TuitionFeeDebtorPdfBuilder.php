<?php

namespace App\Builders\Pdf\Tuition;

use App\Builders\Pdf\BasePdfBuilder;
use App\Models\TuitionFees;

class TuitionFeeDebtorPdfBuilder extends BasePdfBuilder
{
    public function getData(): array
    {
        // Debtors are the records whose transactions do not cover the tuition.
        $query = TuitionFees::query()
            ->withAmountPaid()
            ->owing()
            ->with(['student', 'specialty.level']);

        if (!empty($this->context['school_branch_id'])) {
            $query->where('school_branch_id', $this->context['school_branch_id']);
        }

        if (!empty($this->context['specialty_id'])) {
            $query->where('specialty_id', $this->context['specialty_id']);
        }

        $tuitionFees = $query->get()->map(fn(TuitionFees $tuitionFee) => [
            'student_name'   => $tuitionFee->student?->name,
            'specialty_name' => $tuitionFee->specialty?->specialty_name,
            'level'          => $tuitionFee->specialty?->level?->level,
            'amount_paid'    => $tuitionFee->amount_paid,
            'amount_left'    => $tuitionFee->amount_left,
            'fee_total'      => (float) $tuitionFee->tution_fee_total,
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
