<?php

namespace App\Services\SchoolExpenses;

use App\Exceptions\AppException;
use App\Models\Schoolexpensescategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * A school branch's expense categories (e.g. "Utilities", "Maintenance").
 *
 * Every method is scoped to the branch, and every refusal is an AppException
 * with a title and a description that says what to do next:
 *  - names are unique per branch, ignoring case and surrounding spaces;
 *  - a category that expenses are filed under can't be deleted; mark it
 *    inactive instead, so it stays on past expenses.
 */
class ExpenseCategoryService
{
    public const STATUSES = ['active', 'inactive'];

    public function createSchoolExpense(array $data, object $currentSchool, ?object $authAdmin = null): Schoolexpensescategory
    {
        $name = $this->cleanName($data['name'] ?? '');
        $this->assertNameIsFree($currentSchool, $name);

        try {
            $category = new Schoolexpensescategory();
            $category->school_branch_id = $currentSchool->id;
            $category->name = $name;
            $category->description = $this->cleanDescription($data['description'] ?? null);
            $category->status = $data['status'] ?? 'active';
            $category->save();

            return $category->refresh();
        } catch (Throwable $e) {
            throw $this->unexpected('create the expense category', $e, $currentSchool);
        }
    }

    /**
     * Changes only the fields sent: a name (never blank), a description
     * (null or "" clears it) and a status.
     */
    public function updateSchoolExpenseCategory(array $data, string $categoryId, object $currentSchool, ?object $authAdmin = null): Schoolexpensescategory
    {
        $category = $this->findForBranch($categoryId, $currentSchool);
        $changes = [];

        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $name = $this->cleanName($data['name']);
            if ($name !== $category->name) {
                $this->assertNameIsFree($currentSchool, $name, $category->id);
                $changes['name'] = $name;
            }
        }
        if (array_key_exists('description', $data)) {
            $changes['description'] = $this->cleanDescription($data['description']);
        }
        if (!empty($data['status'])) {
            $changes['status'] = $data['status'];
        }

        if ($changes) {
            try {
                $category->update($changes);
            } catch (Throwable $e) {
                throw $this->unexpected('update the expense category', $e, $currentSchool);
            }
        }

        return $category->refresh();
    }

    public function deleteSchoolExpenseCategory(string $categoryId, object $currentSchool, ?object $authAdmin = null): Schoolexpensescategory
    {
        $category = $this->findForBranch($categoryId, $currentSchool);

        $expenses = $category->schoolexpenses()->count();
        if ($expenses > 0) {
            throw new AppException(
                "The expense category \"{$category->name}\" is in use and cannot be deleted.",
                409,
                'Category In Use',
                ($expenses === 1 ? '1 expense is' : "{$expenses} expenses are") . " filed under \"{$category->name}\". Move "
                    . ($expenses === 1 ? 'it' : 'them') . ' to another category first, or mark this category inactive so it is no longer offered for new expenses.'
            );
        }

        try {
            $category->delete();
            return $category;
        } catch (Throwable $e) {
            throw $this->unexpected('delete the expense category', $e, $currentSchool);
        }
    }

    /**
     * The branch's expense categories, by name, each with how many expenses
     * are filed under it. An empty list is a normal answer, not an error.
     *
     * Filters: status ("active" | "inactive") and search (matches the name
     * or the description).
     */
    public function getSchoolCategoryExpenses(object $currentSchool, array $filters = []): Collection
    {
        $status = $filters['status'] ?? null;
        if ($status !== null && !in_array($status, self::STATUSES, true)) {
            throw new AppException(
                "\"{$status}\" is not a valid status.",
                422,
                'Invalid Status Filter',
                'Filter expense categories by "active" or "inactive", or leave the status out to list them all.'
            );
        }
        $search = trim((string) ($filters['search'] ?? ''));

        try {
            return Schoolexpensescategory::query()
                ->where('school_branch_id', $currentSchool->id)
                ->when($status, fn($query) => $query->where('status', $status))
                ->when($search !== '', function ($query) use ($search) {
                    $term = '%' . mb_strtolower($search) . '%';
                    $query->where(fn($match) => $match
                        ->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$term]));
                })
                ->withCount('schoolexpenses as expenses_count')
                // The same order on every database, whatever its collation.
                ->orderByRaw('LOWER(name)')
                ->get();
        } catch (Throwable $e) {
            throw $this->unexpected('load the expense categories', $e, $currentSchool);
        }
    }

    private function findForBranch(string $categoryId, object $currentSchool): Schoolexpensescategory
    {
        $category = Schoolexpensescategory::where('school_branch_id', $currentSchool->id)->find($categoryId);

        if (!$category) {
            throw new AppException(
                'The expense category was not found.',
                404,
                'Expense Category Not Found',
                'This expense category does not exist in your school. It may have been deleted; refresh the list and try again.'
            );
        }

        return $category;
    }

    /** Names are unique per branch, ignoring case and surrounding spaces. */
    private function assertNameIsFree(object $currentSchool, string $name, ?string $exceptId = null): void
    {
        if ($name === '') {
            throw new AppException(
                'The expense category name is empty.',
                422,
                'Name Required',
                'Give the expense category a name, e.g. "Utilities" or "Maintenance".'
            );
        }

        $taken = Schoolexpensescategory::where('school_branch_id', $currentSchool->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->when($exceptId, fn($query) => $query->where('id', '!=', $exceptId))
            ->exists();

        if ($taken) {
            throw new AppException(
                "An expense category named \"{$name}\" already exists.",
                409,
                'Category Already Exists',
                "Your school already has an expense category called \"{$name}\". Use that one, or choose a different name."
            );
        }
    }

    private function cleanName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function cleanDescription(?string $description): ?string
    {
        $description = trim((string) $description);
        return $description === '' ? null : $description;
    }

    /** A failure we didn't foresee: logged with its cause, reported without internals. */
    private function unexpected(string $action, Throwable $e, object $currentSchool): AppException
    {
        Log::error("Failed to {$action}.", [
            'school_branch_id' => $currentSchool->id ?? null,
            'exception' => $e,
        ]);

        return new AppException(
            "An unexpected error occurred while trying to {$action}.",
            500,
            'Something Went Wrong',
            "We couldn't {$action} because of a server problem. Please try again; if it keeps happening, contact support."
        );
    }
}
