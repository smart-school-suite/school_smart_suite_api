<?php

namespace App\Services\SchoolEvent;
use App\Models\EventTag;
class EventTagService
{
   public function createEventTag($tagData){
       $tag = EventTag::create([
                 'name' => $tagData['name']
             ]);
            return $tag;
   }

   public function getTags(){
         $tags = EventTag::all();
         return $tags;
   }

   public function deleteTag( $tagId){
         $tag = EventTag::findOrFail($tagId);
         $tag->delete();
         return $tag;

   }

   public function updateTag($tagData, $tagId){
      $tag = EventTag::findOrFail($tagId);
         $cleanData = array_filter($tagData);
         $tag->update($cleanData);
         return $tag;
   }
}
