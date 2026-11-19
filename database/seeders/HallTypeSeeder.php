<?php

namespace Database\Seeders;

use App\Models\HallType;
use Illuminate\Database\Seeder;

/**
 * The hall types every school picks from when it sets up its halls.
 *
 * Exam and resit timetable generation only place papers in halls of type
 * exam.hall, so that key must exist and never change.
 *
 * Safe to run on every deploy (DatabaseSeeder calls it): a type is found by
 * its key, or by its name when it was created through the API without the
 * key (that type then takes the standard key); missing types are
 * created, and on existing ones only empty fields are filled, so edits made
 * by an administrator are kept.
 */
class HallTypeSeeder extends Seeder
{
    public const HALL_TYPES = [
        [
            'key' => 'lecture.hall',
            'name' => 'Lecture Hall',
            'description' => 'A room for teaching: lectures, tutorials and other timetabled classes.',
            'text_color' => '#22aca5',
            'background_color' => '#d0f7f1',
        ],
        [
            'key' => 'exam.hall',
            'name' => 'Exam Hall',
            'description' => 'A room for sitting examinations, with a seating capacity for candidates. Exam and resit timetables only use halls of this type.',
            'text_color' => '#a987cb',
            'background_color' => '#e7e1f3',
        ],
        [
            'key' => 'practical.hall',
            'name' => 'Practical Hall',
            'description' => 'A laboratory or workshop for practical sessions, such as science experiments, computer work or technical training.',
            'text_color' => '#e74db8',
            'background_color' => '#fbe8f7',
        ],
    ];

    public function run(): void
    {
        foreach (self::HALL_TYPES as $definition) {
            $hallType = HallType::where('key', $definition['key'])->first()
                ?? HallType::whereRaw('LOWER(name) = ?', [strtolower($definition['name'])])->first();

            if (!$hallType) {
                HallType::create($definition + ['status' => 'active']);
                continue;
            }

            // Keep what an administrator set; only fill what is missing. The key
            // is the exception: code finds hall types by it, so a type matched
            // by its name takes the standard key.
            $missing = collect($definition + ['status' => 'active'])
                ->filter(fn($value, $field) => $field === 'key' ? $hallType->key !== $value : blank($hallType->{$field}))
                ->all();
            if ($missing) {
                $hallType->update($missing);
            }
        }
    }
}
