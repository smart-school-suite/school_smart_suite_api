<?php

namespace App\Services\Announcement;

use App\Models\AnnouncementLabel;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AppException;

class AnnouncementLabelService
{
    public function createLabel($data)
    {
        $labelName = $data['name'];

        $existingLabel = AnnouncementLabel::where('name', $labelName)->first();

        if ($existingLabel) {
            throw new AppException(
                "An announcement label with the name '{$labelName}' already exists.",
                409,
                "Duplicate Label Name 📛",
                "A label with this exact name already exists. Please choose a unique name.",
                null
            );
        }

        try {
            $label = AnnouncementLabel::create([
                'name' => $labelName
            ]);
            return $label;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create announcement label '{$labelName}'. Error: " . $e->getMessage(),
                500,
                "Label Creation Failed 🛑",
                "A system error occurred while trying to save the new label. Please try again or contact support.",
                null
            );
        }
    }
    public function updateLabel($data, $labelId)
    {
        try {
            $label = AnnouncementLabel::findOrFail($labelId);
            $filterData = array_filter($data);

            if (isset($filterData['name'])) {
                $newName = $filterData['name'];
                $existingLabel = AnnouncementLabel::where('name', $newName)
                    ->where('id', '!=', $labelId)
                    ->first();

                if ($existingLabel) {
                    throw new AppException(
                        "An announcement label with the name '{$newName}' already exists.",
                        409,
                        "Duplicate Label Name 📛",
                        "The name you entered is already in use by another label. Please choose a unique name.",
                        null
                    );
                }
            }

            $label->update($filterData);
            return $label;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Announcement Label ID '{$labelId}' not found.",
                404,
                "Label Not Found 🔎",
                "The label you are trying to update could not be found. It may have been deleted.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update announcement label ID '{$labelId}'. Error: " . $e->getMessage(),
                500,
                "Label Update Failed 🛑",
                "A system error occurred while trying to save the label changes. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteLabel($labelId)
    {
        $labelName = 'Unknown Label';
        try {
            $label = AnnouncementLabel::findOrFail($labelId);
            $labelName = $label->name;

            $label->delete();
            return $label;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Announcement Label ID '{$labelId}' not found for deletion.",
                404,
                "Label Not Found 🗑️",
                "The label you are trying to delete could not be found. It may have already been deleted.",
                null
            );
        } catch (Throwable $e) {
            $message = "Failed to delete announcement label '{$labelName}' (ID: {$labelId}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the label '{$labelName}' because it is currently linked to announcements. Please remove all associations first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred while trying to delete the label. Please try again or contact support.",
                null
            );
        }
    }
    public function getLabels()
    {
        try {
            $labels = AnnouncementLabel::all();

            if ($labels->isEmpty()) {
                throw new AppException(
                    "No announcement labels were found in the system.",
                    404,
                    "No Labels Found 🏷️",
                    "There are currently no announcement labels defined. Please create new labels to categorize announcements.",
                    null
                );
            }

            return $labels;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve announcement labels. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the labels. Please try again or contact support.",
                null
            );
        }
    }
}
