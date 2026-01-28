<?php

namespace App\Services\SemesterTimetable;

use App\Models\SemesterTimetable\SemesterTimetablePrompt;

class SemesterTimetableConversationService
{
    public function getConversationHistory(string $schoolSemesterId, object $currentSchool)
    {
        $prompts = SemesterTimetablePrompt::where('school_semester_id', $schoolSemesterId)
            ->where('school_branch_id', $currentSchool->id)
            ->select('id', 'user_prompt', 'ai_output', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($prompt) {
                return [
                    'id'          => $prompt->id,
                    'user_prompt' => $prompt->user_prompt,
                    'ai_output'   => $prompt->ai_output,
                    'created_at'  => $prompt->created_at,
                    'date_label'  => $prompt->created_at->diffForHumans(),
                    'short_date'  => $prompt->created_at->format('M d, Y'),
                ];
            });

        $today       = now()->startOfDay();
        $yesterday   = now()->subDay()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        $buckets = [
            'today'       => $prompts->filter(fn($p) => $p['created_at']->gte($today)),
            'yesterday'   => $prompts->filter(fn($p) => $p['created_at']->gte($yesterday) && $p['created_at']->lt($today)),
            'this_month'  => $prompts->filter(fn($p) => $p['created_at']->gte($startOfMonth) && $p['created_at']->lt($yesterday)),
            'earlier'     => $prompts->filter(fn($p) => $p['created_at']->lt($startOfMonth)),
        ];

        $response = array_filter($buckets, fn($group) => $group->isNotEmpty());

        return $response;
    }
}
