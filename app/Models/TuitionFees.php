<?php

namespace App\Models;

use App\Models\AcademicYear\SchoolAcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A student's tuition obligation for one academic year. What has been paid
 * is never stored: tuition fee transactions are the single source of truth,
 * and amount_paid / amount_left / payment_status are derived from them.
 *
 * Use withAmountPaid() when loading many records, so the totals come from
 * one query instead of one per record.
 */
class TuitionFees extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'school_branch_id',
        'specialty_id',
        'school_academic_year_id',
        'tution_fee_total'
    ];

    protected $casts = [
        'tution_fee_total' => 'float',
    ];
    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'tuition_fees';

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function specialty(){
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_academic_year_id');
    }

    public function tuitionFeeTransactions(): HasMany {
         return $this->hasMany(TuitionFeeTransactions::class, 'tuition_id');
    }

    /** Adds amount_paid, the sum of the record's transactions, to the query. */
    public function scopeWithAmountPaid(Builder $query): Builder
    {
        if ($query->getQuery()->columns === null) {
            $query->select($this->qualifyColumn('*'));
        }

        return $query->addSelect([
            'amount_paid' => TuitionFeeTransactions::selectRaw('COALESCE(SUM(amount), 0)')
                ->whereColumn('tuition_id', $this->qualifyColumn('id')),
        ]);
    }

    /** Only records with an outstanding balance. */
    public function scopeOwing(Builder $query): Builder
    {
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(amount), 0) FROM tuition_fee_transactions WHERE tuition_fee_transactions.tuition_id = tuition_fees.id) < tuition_fees.tution_fee_total'
        );
    }

    /**
     * What the student has paid: from withAmountPaid() when loaded, else from
     * loaded transactions, else summed from the database.
     */
    public function getAmountPaidAttribute($value): float
    {
        if ($value !== null) {
            return round((float) $value, 2);
        }

        if ($this->relationLoaded('tuitionFeeTransactions')) {
            return round((float) $this->tuitionFeeTransactions->sum('amount'), 2);
        }

        return $this->exists ? round((float) $this->tuitionFeeTransactions()->sum('amount'), 2) : 0.0;
    }

    public function getAmountLeftAttribute(): float
    {
        return round(max(0, (float) $this->tution_fee_total - $this->amount_paid), 2);
    }

    /** "completed" once fully paid, otherwise "owing". */
    public function getPaymentStatusAttribute(): string
    {
        return $this->amount_left <= 0 ? 'completed' : 'owing';
    }
}
