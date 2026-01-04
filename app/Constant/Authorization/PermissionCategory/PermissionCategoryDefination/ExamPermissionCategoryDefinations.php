<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;

class ExamPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_CANDIDATE_MANAGER,
                "Exam Candidate Manager",
                "Allows managing student eligibility, generating index numbers, and registering students for main examinations."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_EVALUATION_MANAGER,
                "Exam Evaluation Manager",
                "Grants authority to manage marking schemes, assessment criteria, and the internal moderation process for exams."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::CA_EVALUATION_MANAGER,
                "CA Exam Manager",
                "Used to manage Continuous Assessment (CA) records, including mid-term tests, assignments, and practical scores."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                "Exam Grade Scale Manager",
                "Allows defining grade boundaries (e.g., A, B, C) and credit point mappings for main examination sessions."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                "Exam Manager",
                "Provides high-level control over the creation, configuration, and overall lifecycle of main examination periods."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_RESULT_MANAGER,
                "Exam Result Manager",
                "Enables the final verification, approval, and official publication of student examination results."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_SCORE_MANAGER,
                "Exam Score Manager",
                "Allows for the direct entry and modification of raw marks obtained by students in their examinations."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                "Exam Timetable Manager",
                "Allows the creation and management of exam schedules, including dates, times, and venue assignments."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_CANDIDATE_MANAGER,
                "Resit Exam Candidate Manager",
                "Manages the list of students eligible for resit (retake) exams based on previous failures or absences."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_EVALUATION_MANAGER,
                "Resit Evaluation Manager",
                "Handles the marking criteria and evaluation standards specifically for resit/supplementary examinations."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                "Resit Exam Grade Scale Manager",
                "Configures specific grading thresholds for resits, which may differ from standard main exam scales."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_MANAGER,
                "Resit Exam Manager",
                "Provides administrative control over the setup and execution of resit examination sessions."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_MANAGER,
                "Resit Manager",
                "High-level management of the entire resit process, from registration through to final processing."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_RESULT_MANAGER,
                "Resit Result Manager",
                "Allows for the approval and release of grades specifically for students who participated in resit exams."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                "Resit Exam Timetable Manager",
                "Used to schedule dates, rooms, and invigilators specifically for supplementary/resit exam sessions."
            ),
            PermissionCategoryBuilder::make(
                ExamPermissionCategories::SCHOOL_GRADE_SCALE_MANAGER,
                "School Grade Scale Manager",
                "Grants authority to manage the master grading system and GPA calculations used across the entire institution."
            ),
        ];
    }
}
