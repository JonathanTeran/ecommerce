<?php

namespace App\Policies;

use App\Models\GlobalIntegration;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GlobalIntegrationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    protected function isAuthorized(User $user): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner', 'billing', 'support']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAuthorized($user);
    }

    public function view(User $user, GlobalIntegration $globalIntegration): bool
    {
        return $this->isAuthorized($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner']); // Only owner can create
    }

    public function update(User $user, GlobalIntegration $globalIntegration): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner', 'billing']);
    }

    public function delete(User $user, GlobalIntegration $globalIntegration): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner']);
    }

    public function restore(User $user, GlobalIntegration $globalIntegration): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner']);
    }

    public function forceDelete(User $user, GlobalIntegration $globalIntegration): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner']);
    }
}
