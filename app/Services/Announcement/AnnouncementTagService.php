<?php

namespace App\Services\Announcement;

use App\Models\AnnouncementTag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AppException;
use Throwable;

class AnnouncementTagService
{
    public function createTag(array $tagData)
    {
        $tagName = $tagData['name'];
        $existingTag = AnnouncementTag::where('name', $tagName)->first();

        if ($existingTag) {
            throw new AppException(
                "An announcement tag with the name '{$tagName}' already exists.",
                409,
                "Duplicate Tag Name 📛",
                "A tag with this exact name already exists. Please choose a unique name.",
                null
            );
        }

        try {
            $tag = AnnouncementTag::create([
                'name' => $tagName,
            ]);
            return $tag;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create announcement tag '{$tagName}'. Error: " . $e->getMessage(),
                500,
                "Tag Creation Failed 🛑",
                "A system error occurred while trying to save the new tag. Please try again or contact support.",
                null
            );
        }
    }
    public function updateTag(array $tagData, $tagId)
    {
        try {
            $tag = AnnouncementTag::findOrFail($tagId);
            $filterData = array_filter($tagData);

            if (isset($filterData['name'])) {
                $newName = $filterData['name'];
                $existingTag = AnnouncementTag::where('name', $newName)
                    ->where('id', '!=', $tagId)
                    ->first();

                if ($existingTag) {
                    throw new AppException(
                        "An announcement tag with the name '{$newName}' already exists.",
                        409,
                        "Duplicate Tag Name 📛",
                        "The name you entered is already in use by another tag. Please choose a unique name.",
                        null
                    );
                }
            }

            $tag->update($filterData);
            return $tag;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Announcement Tag ID '{$tagId}' not found.",
                404,
                "Tag Not Found 🔎",
                "The tag you are trying to update could not be found. It may have been deleted.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update announcement tag ID '{$tagId}'. Error: " . $e->getMessage(),
                500,
                "Tag Update Failed 🛑",
                "A system error occurred while trying to save the tag changes. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteTag($tagId)
    {
        $tagName = 'Unknown Tag';
        try {
            $tag = AnnouncementTag::findOrFail($tagId);
            $tagName = $tag->name;

            $tag->delete();
            return $tag;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Announcement Tag ID '{$tagId}' not found for deletion.",
                404,
                "Tag Not Found 🗑️",
                "The tag you are trying to delete could not be found. It may have already been deleted.",
                null
            );
        } catch (Throwable $e) {
            $message = "Failed to delete announcement tag '{$tagName}' (ID: {$tagId}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the tag '{$tagName}' because it is currently linked to announcements. Please remove all associations first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred while trying to delete the tag. Please try again or contact support.",
                null
            );
        }
    }
    public function getTags()
    {
        try {
            $tags = AnnouncementTag::all();

            if ($tags->isEmpty()) {
                throw new AppException(
                    "No announcement tags were found in the system.",
                    404,
                    "No Tags Found 🏷️",
                    "There are currently no announcement tags defined. Please create new tags to categorize announcements.",
                    null
                );
            }

            return $tags;
        } catch (AppException $e) {

            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve announcement tags. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the tags. Please try again or contact support.",
                null
            );
        }
    }
}
