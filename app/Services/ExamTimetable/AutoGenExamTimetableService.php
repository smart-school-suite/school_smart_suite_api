<?php

namespace App\Services\ExamTimetable;


use App\Schedular\ExamTimetable\Engine\SchedularEngine;


class AutoGenExamTimetableService
{
    public function generateExamTimetable(): array
    {
 // ~2 weeks of exam period
        $payload = $this->buildPayload();
        $schedularEngine = app(SchedularEngine::class)->run($payload);
        return [
            "timetable" => $schedularEngine
        ];
    }

    protected function buildPayload(): array
    {
        return [
            "start_date" => "2026-10-15",
            "end_date"   => "2026-10-25",
            "specialty_id" => "0d84a028-f49c-49a4-86e4-47442c1f8986",

            "candidate_count" => random_int(50, 90),

            "courses"               => $this->courses(),
            "invigilators"          => $this->invigilators(),
            "invigilator_busy_slots" => $this->invigilatorBusySlots(),
            "halls"                 =>  $this->halls(),
            "hall_busy_slots"       => $this->hallBusySlots(), // Can be extended later

            "hard_constraints" => [
                "required_joint_course_periods" => $this->jointCourses(),
                "operational_hours" => $this->operationalHours(),
                "session_duration"  => $this->sessionDuration(),
            ]
        ];
    }
    public function invigilatorBusySlots(): array
{
    return [
        [
            // Dr. Aristhène Ngueko
            "invigilator_id" => "d1111111-e89b-12d3-a456-426614174000",
            "date" => "2026-10-15",
            "slots" => [
                [
                    "hall_id" => "883e4567-e89b-12d3-a456-426614174001", // Grand Assembly Hall
                    "course_id" => "550e8400-e29b-41d4-a716-446655440000", // Intro to SE
                    "specialty_id" => "spec-swe-001",
                    "start_time" => "09:00",
                    "end_time" => "11:00"
                ]
            ]
        ],
        [
            // Prof. Sarah Jenkins
            "invigilator_id" => "d2222222-e89b-12d3-a456-426614174000",
            "date" => "2026-10-15",
            "slots" => [
                [
                    "hall_id" => "883e4567-e89b-12d3-a456-426614174001", // Grand Assembly Hall
                    "course_id" => "550e8400-e29b-41d4-a716-446655440000", // Intro to SE
                    "specialty_id" => "spec-swe-001",
                    "start_time" => "09:00",
                    "end_time" => "11:00"
                ]
            ]
        ],
        [
            // Engr. Michael Chen
            "invigilator_id" => "d3333333-e89b-12d3-a456-426614174000",
            "date" => "2026-10-15",
            "slots" => [
                [
                    "hall_id" => "993e4567-e89b-12d3-a456-426614174002", // Engineering Block A
                    "course_id" => "a4e2baec-1672-4306-974a-4463205e4d21", // Cyber Security
                    "specialty_id" => "spec-nsc-009",
                    "start_time" => "13:00",
                    "end_time" => "15:00"
                ]
            ]
        ]
    ];
}
public function hallBusySlots(): array
{
    return [
        [
            // Targeting the Grand Assembly Hall (Capacity 300)
            "hall_id" => "883e4567-e89b-12d3-a456-426614174001",
            "date" => "2026-10-15",
            "slots" => [
                [
                    "start_time" => "09:00",
                    "end_time" => "11:00",
                    "invigilators" => [
                        "d1111111-e89b-12d3-a456-426614174000", // Dr. Aristhène Ngueko
                        "d2222222-e89b-12d3-a456-426614174000"  // Prof. Sarah Jenkins
                    ],
                    "candidate_groups" => [
                        [
                            "specialty_id" => "spec-swe-001",
                            "course_id" => "550e8400-e29b-41d4-a716-446655440000", // Intro to SE
                            "candidate_count" => 150
                        ],
                        [
                            "specialty_id" => "spec-swe-002",
                            "course_id" => "6ba7b810-9dad-11d1-80b4-00c04fd430c8", // Data Structures
                            "candidate_count" => 100
                        ]
                    ]
                ]
            ]
        ],
        [
            // Targeting Engineering Block A (Capacity 150)
            "hall_id" => "993e4567-e89b-12d3-a456-426614174002",
            "date" => "2026-10-15",
            "slots" => [
                [
                    "start_time" => "13:00",
                    "end_time" => "15:00",
                    "invigilators" => [
                        "d3333333-e89b-12d3-a456-426614174000" // Engr. Michael Chen
                    ],
                    "candidate_groups" => [
                        [
                            "specialty_id" => "spec-nsc-009",
                            "course_id" => "a4e2baec-1672-4306-974a-4463205e4d21", // Cyber Security
                            "candidate_count" => 60
                        ]
                    ]
                ]
            ]
        ]
    ];
}

   public function invigilators(): array
{
    return [
        [
            "invigilator_id" => "d1111111-e89b-12d3-a456-426614174000",
            "name" => "Dr. Aristhène Ngueko",
            "course_taught" => [
                "550e8400-e29b-41d4-a716-446655440000", // Intro to SE
                "f47ac10b-58cc-4372-a567-0e02b2c3d479"  // System Architecture
            ]
        ],
        [
            "invigilator_id" => "d2222222-e89b-12d3-a456-426614174000",
            "name" => "Prof. Sarah Jenkins",
            "course_taught" => [
                "6ba7b810-9dad-11d1-80b4-00c04fd430c8", // Data Structures
                "e10e8400-e29b-41d4-a716-446655440000"  // Functional English
            ]
        ],
        [
            "invigilator_id" => "d3333333-e89b-12d3-a456-426614174000",
            "name" => "Engr. Michael Chen",
            "course_taught" => [
                "25db8220-7a31-4d3e-8c6c-8a9d1f3b20c1", // Distributed Systems
                "1b9d6bcd-bbfd-4b2d-9b5d-ab8dfbbd4bed"  // Full-Stack Dev
            ]
        ],
        [
            "invigilator_id" => "d4444444-e89b-12d3-a456-426614174000",
            "name" => "Barrister Elena Rodriguez",
            "course_taught" => [
                "l30e8400-e29b-41d4-a716-446655442222", // IP Law
                "a4e2baec-1672-4306-974a-4463205e4d21"  // Cyber Security
            ]
        ]
    ];
}
       public function jointCourses(): array
{
    return [
        [
            "course_id" => "e10e8400-e29b-41d4-a716-446655440000",
            "course_name" => "Functional English",
            "date" => "2026-10-15",
            "start_time" => "09:00",
            "end_time" => "11:00"
        ],
        [
            "course_id" => "f20e8400-e29b-41d4-a716-446655441111",
            "course_name" => "Professional French",
            "date" => "2026-10-16",
            "start_time" => "12:00",
            "end_time" => "13:00"
        ],
        [
            "course_id" => "l30e8400-e29b-41d4-a716-446655442222",
            "course_name" => "Intellectual Property Law",
            "date" => "2026-10-18",
            "start_time" => "09:00",
            "end_time" => "11:00"
        ]
    ];
}
    public function operationalHours() {
          return  [
               "start_time" => "09:00",
            "end_time" => "17:00",
            "date_exceptions" => [
                [
                    "date" => "2026-10-15",
                    "start_time" => "10:00",
                    "end_time"=>  "16:00"
                ]
            ]
          ];
    }
    public function halls(): array
{
    return [
        [
            "hall_id" => "883e4567-e89b-12d3-a456-426614174001",
            "hall_name" => "Grand Assembly Hall",
            "capacity" => 300
        ],
        [
            "hall_id" => "993e4567-e89b-12d3-a456-426614174002",
            "hall_name" => "Engineering Block A",
            "capacity" => 150
        ],
        [
            "hall_id" => "aa3e4567-e89b-12d3-a456-426614174003",
            "hall_name" => "IT Complex Lab 1",
            "capacity" => 100
        ],
        [
            "hall_id" => "bb3e4567-e89b-12d3-a456-426614174004",
            "hall_name" => "Science Lecture Theater",
            "capacity" => 200
        ],
        [
            "hall_id" => "cc3e4567-e89b-12d3-a456-426614174005",
            "hall_name" => "Post-Grad Seminar Room",
            "capacity" => 50
        ]
    ];
}
    public function courses(): array
{
    return [
        [
            "course_id" => "550e8400-e29b-41d4-a716-446655440000",
            "course_name" => "Introduction to Software Engineering",
            "candidate_count" => 280
        ],
        [
            "course_id" => "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
            "course_name" => "Data Structures & Advanced Algorithms",
            "candidate_count" => 120
        ],
        [
            "course_id" => "f47ac10b-58cc-4372-a567-0e02b2c3d479",
            "course_name" => "High-Level System Architecture & Design",
            "candidate_count" => 85
        ],
        [
            "course_id" => "25db8220-7a31-4d3e-8c6c-8a9d1f3b20c1",
            "course_name" => "Distributed Systems & Cloud Computing",
            "candidate_count" => 150
        ],
        [
            "course_id" => "9e107d9d-3cc1-470a-9102-27072450b7a6",
            "course_name" => "Discrete Mathematics for Engineers",
            "candidate_count" => 210
        ],
        [
            "course_id" => "1b9d6bcd-bbfd-4b2d-9b5d-ab8dfbbd4bed",
            "course_name" => "Full-Stack Web Development (Laravel/React)",
            "candidate_count" => 95
        ],
        [
            "course_id" => "a4e2baec-1672-4306-974a-4463205e4d21",
            "course_name" => "Network Security & Cryptography",
            "candidate_count" => 60
        ],
        [
            "course_id" => "7c9e66ab-1d45-420a-953e-3f538562d93e",
            "course_name" => "Artificial Intelligence & Machine Learning",
            "candidate_count" => 180
        ]
    ];
}

public function sessionDuration(){
     return [
         "duration_minutes" => 60,
            "course_exceptions" => [
                [
                    "course_id" => "7c9e66ab-1d45-420a-953e-3f538562d93e",
                    "duration_minutes" => 120
                ]
            ]
     ];
}

}
