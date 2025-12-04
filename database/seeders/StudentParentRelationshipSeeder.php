<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentParentRelationship;

class StudentParentRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $relationships = [
            "Father",
            "Mother",
            "Stepfather",
            "Stepmother",
            "Guardian",
            "Grandfather",
            "Grandmother",
            "Uncle",
            "Aunt",
            "Older Brother",
            "Older Sister",
            "Foster Parent",
            "Sponsor",
            "Caretaker",
        ];
        foreach ($relationships as $relationship) {
            StudentParentRelationship::create([
                "name" => $relationship
            ]);
        }
    }
}
