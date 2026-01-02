<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionVotePermissions
{
    public const VOTE = "election_vote.vote";
    public static function all(): array {
         return [
             self::VOTE
         ];
    }
}
