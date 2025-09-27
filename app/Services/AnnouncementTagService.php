<?php

namespace App\Services;

use App\Models\AnnouncementTag;
use Throwable;

class AnnouncementTagService
{
    public function createTag(array $tag ){
          try{
              $tag = AnnouncementTag::create([
                    'name' => $tag['name'],
              ]);
              return $tag;
          }
          catch(Throwable $e){
            throw $e;
          }
    }

    public function updateTag(array $tagData, $currentSchool, $tagId){
        try{
           $tag = AnnouncementTag::findOrFail($tagId);
           $filterData = array_filter($tagData);
           $tag->update($filterData);
           return $tag;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function deleteTag($tagId){
        try{
            $tag = AnnouncementTag::findOrFail($tagId);
            $tag->delete();
            return $tag;
        }
        catch(Throwable $e){
           throw $e;
        }
    }

    public function getTags(){
        try{
           $tags = AnnouncementTag::all();
           return $tags;
        }
        catch(Throwable $e){
           throw $e;
        }
    }
}
