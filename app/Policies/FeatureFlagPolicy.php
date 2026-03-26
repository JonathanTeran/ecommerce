<?php

namespace App\Policies;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FeatureFlagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    protected function isAuthorized(User $user): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner', 'support']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAuthorized($user);
    }

    public function view(User $user, FeatureFlag $featureFlag): bool
    {
        return $this->isAuthorized($user);
    }

    public function create(User $user): bool
    {
        return $this->isAuthorized($user);
    }

    public function update(User $user, FeatureFlag $featureFlag): bool
    {
        return $this->isAuthorized($user);
    }

    public function delete(User $user, FeatureFlag $featureFlag): bool
    {
        return $this->isAuthorized($user);
    }

    public function restore(User $user, FeatureFlag $featureFlag): bool
    {
        return $this->isAuthorized($user);
    }

    public function forceDelete(User $user, FeatureFlag $featureFlag): bool
    {
        return $this->isAuthorized($user);
    }
}
