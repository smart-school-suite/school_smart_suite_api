<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        $tags = [
            'Academics', 'Examinations', 'Continuous Assessment', 'Resit', 'Results', 'Transcripts',
            'Timetable', 'Exam Timetable', 'Class Schedule', 'Course Registration', 'Course Outline', 'Lecture',
            'Tutorial', 'Practicals', 'Laboratory', 'Assignment', 'Project', 'Thesis', 'Internship', 'Field Trip',
            'Seminar', 'Workshop', 'Conference', 'Research', 'Library', 'E-Learning', 'Online Classes',
            'Admissions', 'Enrollment', 'Registration', 'Orientation', 'Matriculation', 'Graduation', 'Convocation',
            'Promotion', 'Repeating', 'Transfer', 'Withdrawal', 'Suspension', 'Expulsion', 'Discipline',
            'Code of Conduct', 'Dress Code', 'Attendance', 'Absence', 'Late Coming', 'Punctuality',
            'Fees', 'School Fees', 'Tuition', 'Installment', 'Payment Deadline', 'Payment Reminder', 'Scholarship',
            'Bursary', 'Financial Aid', 'Refund', 'Receipts', 'Additional Fees', 'Registration Fees',
            'Holiday', 'Public Holiday', 'Mid-Term Break', 'Vacation', 'Resumption', 'Closing', 'School Calendar',
            'Academic Year', 'First Semester', 'Second Semester', 'Term', 'Deadline', 'Reminder', 'Schedule Change',
            'Event', 'Sports', 'Inter-House Sports', 'Football', 'Basketball', 'Athletics', 'Competition', 'Quiz',
            'Debate', 'Cultural Day', 'Prize Giving', 'Awards', 'Excursion', 'Club', 'Association', 'Student Union',
            'Elections', 'Leadership', 'Volunteering', 'Community Service', 'Charity', 'Fundraising',
            'Health', 'Medical', 'Vaccination', 'Wellness', 'Mental Health', 'Counseling', 'Guidance', 'Hygiene',
            'Sanitation', 'Safety', 'Security', 'Emergency', 'Fire Drill', 'Weather Alert', 'Lost and Found',
            'Parents', 'Parent-Teacher Meeting', 'PTA', 'Open Day', 'Visiting Day', 'Staff', 'Staff Meeting',
            'Teachers', 'Recruitment', 'Training', 'Professional Development', 'Appraisal', 'Payroll',
            'Administration', 'Policy', 'Policy Update', 'Rules and Regulations', 'Notice', 'General', 'Update',
            'Important', 'Urgent', 'Information', 'Announcement', 'Congratulations', 'Condolence', 'Celebration',
            'Hostel', 'Accommodation', 'Transport', 'School Bus', 'Cafeteria', 'Canteen', 'Meals',
            'Facilities', 'Maintenance', 'Renovation', 'Construction', 'Electricity', 'Water Supply', 'Internet',
            'IT Support', 'Portal', 'System Maintenance', 'Password Reset', 'Student ID', 'ID Card', 'Uniform',
            'Textbooks', 'Stationery', 'Bookshop', 'Alumni', 'Partnership', 'Career', 'Jobs', 'Entrepreneurship',
            'Science', 'Technology', 'Engineering', 'Mathematics', 'Arts', 'Languages', 'Business', 'Health Sciences',
            'Agriculture', 'Computer Science', 'ICT', 'Music', 'Drama', 'Religion', 'Chapel', 'Environment',
        ];

        $existing = DB::table('tags')->pluck('name')->map(fn ($name) => Str::lower($name))->flip();

        $rows = collect($tags)
            ->unique(fn ($name) => Str::lower($name))
            ->reject(fn ($name) => $existing->has(Str::lower($name)))
            ->map(fn ($name) => [
                'id'         => Str::uuid()->toString(),
                'name'       => $name,
                'status'     => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->values();

        $rows->chunk(500)->each(fn ($chunk) => DB::table('tags')->insert($chunk->values()->all()));
    }
}
