<?php

namespace Tests\Unit\Import;

use App\Services\Import\ImportErrorCollector;
use App\Services\Import\ImportErrorReason;
use PHPUnit\Framework\TestCase;

class ImportErrorCollectorTest extends TestCase
{
    public function test_consecutive_rows_are_compressed_into_ranges(): void
    {
        $this->assertSame('2–21, 25, 30–31', ImportErrorCollector::ranges([...range(2, 21), 25, 30, 31]));
        $this->assertSame('7', ImportErrorCollector::ranges([7]));
        $this->assertSame('', ImportErrorCollector::ranges([]));
    }

    public function test_rows_with_the_same_reason_are_reported_as_one_group(): void
    {
        $collector = new ImportErrorCollector();

        foreach (range(2, 21) as $row) {
            $collector->add($row, ImportErrorReason::ALREADY_EXISTS, 'A teacher with this email already exists at this school.', 'email', "t{$row}@example.com");
        }
        $collector->add(22, ImportErrorReason::INVALID_VALUE, 'Gender must be Male or Female.', 'gender', 'x');

        $groups = $collector->groups();

        $this->assertCount(2, $groups);
        $this->assertSame('Rows 2–21: A teacher with this email already exists at this school.', $groups[0]['summary']);
        $this->assertSame(20, $groups[0]['count']);
        $this->assertSame('Already exists', $groups[0]['title']);
        $this->assertCount(5, $groups[0]['examples'], 'Examples are capped.');
        $this->assertSame('Row 22: Gender must be Male or Female.', $groups[1]['summary']);
        $this->assertSame(21, $collector->failedRowCount());
    }

    public function test_a_row_with_two_problems_is_counted_once(): void
    {
        $collector = new ImportErrorCollector();
        $collector->add(3, ImportErrorReason::MISSING_VALUE, 'The email field is required.', 'email');
        $collector->add(3, ImportErrorReason::MISSING_VALUE, 'The first name field is required.', 'first_name');

        $this->assertCount(2, $collector->groups());
        $this->assertSame(1, $collector->failedRowCount());
    }
}
