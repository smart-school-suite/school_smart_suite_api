<?php

namespace App\Constant\Timetable;

class HardTimetableConstraints
{
    private const DAYS_OF_WEEK = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    /**
     * Minimal clean defaults — no optional fields unless needed
     */
    public static function getDefaultJson(): string
    {
        $defaults = [
            "hard_constraints" => [  // ← Fixed: was "hard_constrains"
                "break_period" => [
                    "start_time" => "12:00",
                    "end_time"   => "12:45",
                    "daily"      => true
                ],
                "operational_period" => [
                    "start_time" => "07:30",
                    "end_time"   => "15:00",
                    "daily"      => true
                ],
                "periods" => [
                    "period" => 45,
                    "daily"  => true
                ]
            ]
        ];

        return json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Strict JSON Schema
     */
    public static function getJsonSchema(): string
    {
        $schema = [
            "\$schema" => "https://json-schema.org/draft/2020-12/schema",
            "title" => "Hard Timetable Constraints",
            "type" => "object",
            "required" => ["hard_constraints"],  // ← Root wrapper
            "properties" => [
                "hard_constraints" => [
                    "type" => "object",
                    "required" => ["break_period", "operational_period", "periods"],  // ← Added "periods"
                    "properties" => [
                        "break_period" => [
                            "type" => "object",
                            "required" => ["start_time", "end_time", "daily"],
                            "properties" => [
                                "start_time" => [
                                    "type" => "string",
                                    "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                    "example" => "12:00"
                                ],
                                "end_time" => [
                                    "type" => "string",
                                    "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                    "example" => "12:45"
                                ],
                                "daily" => ["type" => "boolean"],
                                "constrains" => [
                                    "type" => ["object", "null"],
                                    "description" => "ONLY include this if user specifies exceptions or custom breaks",
                                    "required" => ["days_exception", "days_fixed_breaks"],
                                    "properties" => [
                                        "days_exception" => [
                                            "type" => "array",
                                            "minItems" => 1,
                                            "items" => ["type" => "string", "enum" => self::DAYS_OF_WEEK]
                                        ],
                                        "days_fixed_breaks" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "required" => ["day", "start_time", "end_time"],
                                                "properties" => [
                                                    "day" => ["type" => "string", "enum" => self::DAYS_OF_WEEK],
                                                    "start_time" => ["type" => "string", "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$"],
                                                    "end_time"   => ["type" => "string", "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$"]
                                                ],
                                                "additionalProperties" => false
                                            ]
                                        ]
                                    ],
                                    "additionalProperties" => false
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "operational_period" => [
                            "type" => "object",
                            "required" => ["start_time", "end_time", "daily"],
                            "properties" => [
                                "start_time" => ["type" => "string", "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$"],
                                "end_time"   => ["type" => "string", "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$"],
                                "daily"      => ["type" => "boolean"],
                                "days" => [
                                    "type" => ["array", "null"],
                                    "items" => ["type" => "string", "enum" => self::DAYS_OF_WEEK],
                                    "minItems" => 1,
                                    "uniqueItems" => true
                                ],
                                "constrains" => [
                                    "type" => ["array", "null"],
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["day", "start_time", "end_time"],
                                        "properties" => [
                                            "day" => ["type" => "string", "enum" => self::DAYS_OF_WEEK],
                                            "start_time" => ["type" => "string", "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$"],
                                            "end_time"   => ["type" => "string", "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$"]
                                        ],
                                        "additionalProperties" => false
                                    ]
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "periods" => [
                            "type" => "object",
                            "required" => ["period", "daily"],
                            "properties" => [
                                "period" => [
                                    "type" => "number",
                                    "minimum" => 1,
                                    "maximum" => 300,
                                    "example" => 45
                                ],
                                "daily" => ["type" => "boolean"],
                                "constrains" => [
                                    "type" => ["object", "null"],
                                    "description" => "ONLY include this object if the user specifies different period lengths on certain days",
                                    "required" => ["days_fixed_periods"],
                                    "properties" => [
                                        "days_fixed_periods" => [
                                            "type" => "array",
                                            "minItems" => 1,
                                            "items" => [
                                                "type" => "object",
                                                "required" => ["day", "period"],
                                                "properties" => [
                                                    "day" => ["type" => "string", "enum" => self::DAYS_OF_WEEK],
                                                    "period" => ["type" => "number", "minimum" => 1, "maximum" => 300]
                                                ],
                                                "additionalProperties" => false
                                            ]
                                        ]
                                    ],
                                    "additionalProperties" => false
                                ]
                            ],
                            "additionalProperties" => false
                        ]
                    ],
                    "additionalProperties" => false
                ]
            ],
            "additionalProperties" => false
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function getDefaultArray(): array
    {
        return json_decode(self::getDefaultJson(), true)['hard_constraints'];
    }
}
