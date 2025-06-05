<?php

namespace App\Services;

use App\Models\AnnouncementTag;
use Throwable;

class AnnouncementTagService
{
    public function createTag(array $tag, $currentSchool){
          try{
              $tag = AnnouncementTag::create([
                    'name' => $tag['name'],
                    'school_branch_id' => $currentSchool->id,
              ]);
              return $tag;
          }
          catch(Throwable $e){
            throw $e;
          }
    }

    public function updateTag(array $tagData, $currentSchool, $tagId){
        try{
           $tag = AnnouncementTag::where("school_branch_id", $currentSchool->id)->findOrFail($tagId);
           $filterData = array_filter($tagData);
           $tag->update($filterData);
           return $tag;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function deleteTag($tagId, $currentSchool){
        try{
            $tag = AnnouncementTag::where("school_branch_id", $currentSchool->id)->findOrFail($tagId);
            $tag->delete();
            return $tag;
        }
        catch(Throwable $e){
           throw $e;
        }
    }

    public function getTags($currentSchool){
        try{
           $tags = AnnouncementTag::where("school_branch_id", $currentSchool->id)->get();
           return $tags;
        }
        catch(Throwable $e){
           throw $e;
        }
    }
}
