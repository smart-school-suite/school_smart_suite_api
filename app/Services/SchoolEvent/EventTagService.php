<?php

namespace App\Services\SchoolEvent;
use App\Models\EventTag;
class EventTagService
{
    public function getEventTags(){
        return EventTag::all();
    }
}
