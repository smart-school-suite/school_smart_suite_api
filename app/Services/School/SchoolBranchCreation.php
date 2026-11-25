<?php

namespace App\Services\School;

use App\Exceptions\AppException;
use App\Mail\Subscription\SubscriptionSuccessfulMail;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Models\SchoolSubscription;
use App\Models\SchoolTransaction;
use App\Services\Helpers\Actor\ActorHelperService;
use App\Services\Subscription\SharedSubscriptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Adds a branch to the school the signed-in admin belongs to: the branch,
 * its settings, its first admin and its subscription. The subscription is
 * either a share of the current branch's active subscription or a new plan,
 * paid separately. All or nothing.
 */
class SchoolBranchCreation
{
    public function __construct(
        private SharedSubscriptions $shared,
        private SchoolBranchProvisioner $provisioner,
    ) {}

    public function create(array $data, object $currentSchool): array
    {
        $current = Schoolbranches::with('school')->findOrFail($currentSchool->id);
        $school = $current->school;

        $this->refuseDuplicateName($school->id, $data['name']);

        [$plan, $paymentMethod, $source] = $data['subscription'] === 'shared'
            ? [null, null, $this->shareableSubscription($current)]
            : [...$this->newPlan($data, $school), null];

        $result = DB::transaction(function () use ($data, $school, $plan, $paymentMethod, $source) {
            $branch = Schoolbranches::create([
                'school_id' => $school->id,
                'name' => trim($data['name']),
                'abbreviation' => trim($data['abbreviation']),
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            $this->provisioner->provision($branch->id);

            $subscription = $source
                ? $this->shared->share($source, $branch->id)
                : $this->subscribe($branch, $plan, $paymentMethod, $data);

            $admin = $this->createAdmin($branch->id, $data);

            return [$branch, $subscription->load('plan'), $admin];
        }, 3);

        [$branch, $subscription, $admin] = $result;

        if (!$source) {
            $this->sendReceipt($admin, $school->name, $subscription);
        }

        return [
            'branch' => $branch->only(['id', 'name', 'abbreviation', 'city', 'address', 'phone', 'email']),
            'admin' => $admin->only(['id', 'name', 'email', 'username']),
            'subscription' => [
                'mode' => $source ? 'shared' : 'new',
                'plan' => $subscription->plan?->name,
                'end_date' => $subscription->end_date ? date('Y-m-d', strtotime($subscription->end_date)) : null,
            ],
        ];
    }

    private function refuseDuplicateName(string $schoolId, string $name): void
    {
        $taken = Schoolbranches::where('school_id', $schoolId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
            ->exists();

        if ($taken) {
            throw new AppException(
                'A branch with this name already exists.',
                422,
                'Branch Already Exists',
                'This school already has a branch with this name. Use a different name.'
            );
        }
    }

    private function shareableSubscription(Schoolbranches $current): SchoolSubscription
    {
        $subscription = $this->shared->activeFor($current->id);

        if (!$subscription) {
            throw new AppException(
                'There is no active subscription to share.',
                422,
                'Nothing to Share',
                "{$current->name} has no active subscription. Pick a plan for the new branch instead."
            );
        }

        return $this->shared->source($subscription);
    }

    private function newPlan(array $data, $school): array
    {
        $plan = Plan::with('country')->where('id', $data['plan_id'])->where('status', 'active')->first();

        if (!$plan || ($school->country_id && $plan->country_id !== $school->country_id)) {
            throw new AppException(
                'The selected plan is not available.',
                422,
                'Plan Not Available',
                "Pick one of the active plans for your school's country."
            );
        }

        $paymentMethod = PaymentMethod::find($data['payment_method_id']);

        return [$plan, $paymentMethod];
    }

    private function subscribe(Schoolbranches $branch, Plan $plan, PaymentMethod $paymentMethod, array $data): SchoolSubscription
    {
        $startsAt = now();

        $subscription = SchoolSubscription::create([
            'school_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'country_id' => $plan->country_id,
            'status' => 'active',
            'start_date' => $startsAt,
            'end_date' => $startsAt->clone()->addDays($plan->duration_days ?? 365),
        ]);

        SchoolTransaction::create([
            'school_branch_id' => $branch->id,
            'country_id' => $plan->country_id,
            'amount' => $plan->price,
            'type' => 'subscription_purchase',
            'status' => 'completed',
            'payment_method_id' => $paymentMethod->id,
            'payment_ref' => $data['payment_ref'] ?? (string) Str::uuid(),
            'transaction_id' => (string) Str::uuid(),
        ]);

        $this->shared->syncUsage($subscription);

        return $subscription;
    }

    private function createAdmin(string $branchId, array $data): Schooladmin
    {
        $first = trim($data['admin_first_name']);
        $last = trim($data['admin_last_name']);
        $name = trim("{$first} {$last}");

        $admin = Schooladmin::create([
            'name' => $name,
            'first_name' => $first,
            'last_name' => $last,
            'username' => app(ActorHelperService::class)->generateUsername($name, Schooladmin::class),
            'email' => $data['admin_email'],
            'school_branch_id' => $branchId,
            'password' => Hash::make($data['admin_password']),
        ]);

        $admin->assignRole('schoolSuperAdmin');

        return $admin;
    }

    private function sendReceipt(Schooladmin $admin, string $schoolName, SchoolSubscription $subscription): void
    {
        try {
            Mail::to($admin->email)->queue(new SubscriptionSuccessfulMail(
                schoolName: $schoolName,
                planName: $subscription->plan?->name ?? 'Plan',
                amount: number_format((float) $subscription->plan?->price, 2),
                currency: $subscription->plan?->country?->currency ?? '',
                startsAt: $subscription->start_date,
                endsAt: $subscription->end_date,
                paymentReference: (string) Str::uuid(),
                status: 'Active',
                recipientName: $admin->name,
            ));
        } catch (Throwable $e) {
            Log::warning('The new branch subscription receipt could not be queued.', ['message' => $e->getMessage()]);
        }
    }
}
