<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Default tags for school events. Safe to run again: tags that already exist
 * (compared without case) are skipped.
 */
class EventTagSeeder extends Seeder
{
    public const TAGS = [
        // Academic
        'Academics', 'Seminar', 'Workshop', 'Conference', 'Lecture', 'Guest Speaker', 'Symposium',
        'Research', 'Science Fair', 'Exhibition', 'Quiz', 'Debate', 'Spelling Bee', 'Hackathon',
        'Innovation', 'Library', 'Book Fair', 'Reading Week', 'Study Tour', 'Field Trip', 'Excursion',
        'Laboratory', 'Practicals', 'Project Defense', 'Thesis Defense', 'Exam Preparation',

        // Ceremonies and milestones
        'Orientation', 'Matriculation', 'Welcome Ceremony', 'Opening Ceremony', 'Closing Ceremony',
        'Graduation', 'Convocation', 'Prize Giving', 'Awards', 'Honours', 'Anniversary',
        'Founders Day', 'Speech Day', 'Send-Off', 'Farewell',

        // Sports
        'Sports', 'Inter-House Sports', 'Athletics', 'Football', 'Basketball', 'Volleyball', 'Handball',
        'Table Tennis', 'Tennis', 'Swimming', 'Marathon', 'Tournament', 'Competition', 'Fitness',

        // Arts and culture
        'Cultural Day', 'Cultural Week', 'Arts', 'Music', 'Concert', 'Drama', 'Theatre', 'Dance',
        'Talent Show', 'Fashion Show', 'Film Screening', 'Poetry', 'Photography', 'Languages',
        'Bilingualism Day', 'Youth Day', 'National Day', 'Women\'s Day', 'Heritage',

        // Student life
        'Club', 'Association', 'Student Union', 'Elections', 'Leadership', 'Mentorship',
        'Volunteering', 'Community Service', 'Charity', 'Fundraising', 'Social', 'Party', 'Picnic',
        'Hangout', 'Games Night', 'Religion', 'Chapel', 'Worship', 'Prayer',

        // Careers
        'Career Fair', 'Careers', 'Internship', 'Jobs', 'Recruitment', 'Entrepreneurship',
        'Alumni', 'Networking', 'Partnership', 'Industry Visit', 'Startup', 'Business',

        // Parents and staff
        'Parents', 'Parent-Teacher Meeting', 'PTA', 'Open Day', 'Visiting Day', 'Staff',
        'Staff Meeting', 'Teachers', 'Training', 'Professional Development', 'Retreat',

        // Health and safety
        'Health', 'Medical', 'Health Screening', 'Vaccination', 'Blood Donation', 'Wellness',
        'Mental Health', 'Counseling', 'Hygiene', 'Sanitation', 'Safety', 'Security',
        'Fire Drill', 'First Aid', 'Awareness', 'Environment', 'Clean-Up', 'Tree Planting',

        // Fields of study
        'Science', 'Technology', 'Engineering', 'Mathematics', 'Computer Science', 'ICT',
        'Health Sciences', 'Agriculture', 'Law', 'Economics', 'Humanities',

        // General
        'General', 'Free Entry', 'Paid Entry', 'Registration Required', 'Outdoor', 'Indoor',
        'Online', 'Hybrid', 'Family Friendly', 'Celebration', 'Holiday', 'Fun',
    ];

    public function run(): void
    {
        $timestamp = now();

        $existing = DB::table('event_tags')->pluck('name')->map(fn ($name) => Str::lower($name))->flip();

        $rows = collect(self::TAGS)
            ->unique(fn ($name) => Str::lower($name))
            ->reject(fn ($name) => $existing->has(Str::lower($name)))
            ->map(fn ($name) => [
                'id'         => Str::uuid()->toString(),
                'name'       => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->values();

        $rows->chunk(500)->each(fn ($chunk) => DB::table('event_tags')->insert($chunk->values()->all()));
    }
}
