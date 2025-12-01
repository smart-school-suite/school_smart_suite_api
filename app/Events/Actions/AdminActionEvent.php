<?php

namespace App\Events\Actions;

use App\Models\Schooladmin;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminActionEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $permissions = [];
    protected array $roles = [];
    protected string $schoolBranchId;
    protected string $feature;
    protected $authAdmin;
    protected $data;
    protected  $message;

    public function __construct(array $options)
    {
        // Required
        $this->schoolBranchId = $options['schoolBranch'] ?? $options['school_branch_id'];
        $this->feature        = $options['feature'];
        $this->message        = $options['message'] ?? 'Action performed';

        // Optional with defaults
        $this->permissions    = $options['permissions'] ?? [];
        $this->roles          = $options['roles'] ?? [];
        $this->data           = $options['data'] ?? [];
        $this->authAdmin      = $options['authAdmin'] ?? auth('schooladmin')->user();

        // Optional: exclude self by default
        $this->authAdmin      = $this->authAdmin ?? throw new \InvalidArgumentException('authAdmin is required');
    }

    public function broadcastOn(): array
    {
        $admins = Schooladmin::where('school_branch_id', $this->schoolBranchId)
            ->when($this->authAdmin, fn($q) => $q->where('id', '!=', $this->authAdmin->id))
            ->get();

        return $admins->filter(fn($admin) => $this->isEligibleAdmin($admin))
            ->map(fn($admin) => new PrivateChannel(
                "schoolBranch.{$this->schoolBranchId}.schoolAdmin.{$admin->id}.action"
            ))
            ->all();
    }

    protected function isEligibleAdmin($admin): bool
    {
        $roleName = $this->getRoleName($admin);

        // schoolAdmin always receives everything in their branch
        if ($roleName === 'schoolAdmin') {
            return true;
        }

        // Must have at least one of the required roles
        if (empty($this->roles) || !in_array($roleName, $this->roles)) {
            return false;
        }

        // Must have ALL required permissions
        return $this->hasAllPermissions($admin);
    }

    protected function getRoleName($admin): ?string
    {
        if (!$admin->role) return null;

        return is_string($admin->role)
            ? $admin->role
            : ($admin->role->name ?? null);
    }

    protected function hasAllPermissions($admin): bool
    {
        if (empty($this->permissions)) return true;
        if (!method_exists($admin, 'hasPermissionTo')) return false;

        foreach ($this->permissions as $permission) {
            if (!$admin->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    public function broadcastWith(): array
    {
        return [
            'feature'             => $this->feature,
            'message'             => $this->message,
            'data'                => $this->data,
            'allowed_roles'       => $this->roles,
            'allowed_permissions' => $this->permissions,
            'school_branch_id'    => $this->schoolBranchId,
            'timestamp'           => now()->toDateTimeString(),
        ];
    }
}
