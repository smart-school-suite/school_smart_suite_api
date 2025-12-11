<?php

namespace App\Services\PaymentMethod;

use App\Exceptions\AppException;
use App\Models\PaymentMethodCategory;

class PaymentMethodCategoryService
{
    public function createCategory($data)
    {
        $existingCategory = PaymentMethodCategory::where("name", $data["name"])->first();
        if ($existingCategory) {
            throw new AppException(
                "Existing Category",
                409,
                "Existing Category",
                "{$data['name']} already exists please use another name and try again"
            );
        }

        $category =  PaymentMethodCategory::create([
            'name' => $data["name"],
            "description" => $data["description"]
        ]);

        return $category;
    }

    public function updateCategory($data, $categoryId)
    {
        $category = PaymentMethodCategory::findOrFail($categoryId);

        $updates = [];

        if (isset($data['name']) && $data['name'] !== null) {
            if ($data['name'] !== $category->name) {
                $existingCategory = PaymentMethodCategory::where('name', $data['name'])
                    ->where('id', '!=', $categoryId)
                    ->first();

                if ($existingCategory) {
                    throw new AppException(
                        "Existing Category Name",
                        409,
                        "Existing Category Name",
                        "The category name '{$data['name']}' already exists. Please use another name and try again"
                    );
                }
            }
            $updates['name'] = $data['name'];
        }

        if (isset($data['description']) && $data['description'] !== null) {
            $updates['description'] = $data['description'];
        }

        if (empty($updates)) {
            return $category;
        }

        $category->update($updates);

        return $category;
    }

    public function activateCategory($categoryId)
    {
        $category = PaymentMethodCategory::find($categoryId);
        if (!$category) {
            throw new AppException(
                "Category Not Found",
                404,
                "Category Not Found",
                "Category Not Found It Might Have been accidentally deleted please use another name and try again"
            );
        }

        if ($category->status == "active") {
            throw new AppException(
                "Activation Conflict",
                409,
                "Activation Conflict",
                "{$category->name} already activated you cannot activate a category that is already active"
            );
        }

        $category->status = "active";
        $category->save();
        return $category;
    }

    public function deactivateCategory($categoryId)
    {
        $category = PaymentMethodCategory::find($categoryId);
        if (!$category) {
            throw new AppException(
                "Category Not Found",
                404,
                "Category Not Found",
                "Category Not Found It Might Have been accidentally deleted please use another name and try again"
            );
        }

        if ($category->status == "inactive") {
            throw new AppException(
                "Deactivation Conflict",
                409,
                "Deactivation Conflict",
                "{$category->name} already Deactivated you cannot Deactivate a category that is already inactive"
            );
        }

        $category->status = "inactive";
        $category->save();
        return $category;
    }

    public function deleteCategory($categoryId)
    {
        $category = PaymentMethodCategory::find($categoryId);
        if (!$category) {
            throw new AppException(
                "Category Not Found",
                404,
                "Category Not Found",
                "Category Not Found It Might Have been accidentally deleted please use another name and try again"
            );
        }

        $category->delete();
        return $category;
    }

    public function getCategory()
    {
        return PaymentMethodCategory::all();
    }

    public function getCategoryDetails($categoryId)
    {
        $category = PaymentMethodCategory::find($categoryId);
        if (!$category) {
            throw new AppException(
                "Category Not Found",
                404,
                "Category Not Found",
                "Category Not Found It Might Have been accidentally deleted please use another name and try again"
            );
        }
        return $category;
    }
}
