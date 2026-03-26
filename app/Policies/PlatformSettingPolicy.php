<?php

namespace App\Policies;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlatformSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    protected function isAuthorized(User $user): bool
    {
        return $user->isSuperAdmin() && in_array($user->sub_role, ['owner', 'compliance']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAuthorized($user);
    }

    public function view(User $user, PlatformSetting $platformSetting): bool
    {
        return $this->isAuthorized($user);
    }

    public function create(User $user): bool
    {
        return $this->isAuthorized($user);
    }

    public function update(User $user, PlatformSetting $platformSetting): bool
    {
        return $this->isAuthorized($user);
    }

    public function delete(User $user, PlatformSetting $platformSetting): bool
    {
        return $user->isSuperAdmin() && $user->sub_role === 'owner';
    }

    public function restore(User $user, PlatformSetting $platformSetting): bool
    {
        return $user->isSuperAdmin() && $user->sub_role === 'owner';
    }

    public function forceDelete(User $user, PlatformSetting $platformSetting): bool
    {
        return $user->isSuperAdmin() && $user->sub_role === 'owner';
    }
}
