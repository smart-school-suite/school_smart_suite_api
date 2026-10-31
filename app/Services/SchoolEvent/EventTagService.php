<?php

namespace App\Services\SchoolEvent;
use App\Models\SchoolEvent\SchoolEventTag;
class EventTagService
{
   public function createEventTag($tagData){
       $tag = SchoolEventTag::create([
                 'name' => $tagData['name']
             ]);
            return $tag;
   }

   public function getTags(){
         $tags = SchoolEventTag::all();
         return $tags;
   }

   public function deleteTag( $tagId){
         $tag = SchoolEventTag::findOrFail($tagId);
         $tag->delete();
         return $tag;

   }

   public function updateTag($tagData, $tagId){
      $tag = SchoolEventTag::findOrFail($tagId);
         $cleanData = array_filter($tagData);
         $tag->update($cleanData);
         return $tag;
   }
}
