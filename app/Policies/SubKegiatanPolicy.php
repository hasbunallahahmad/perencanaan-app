<?php

namespace App\Policies;

use App\Models\SubKegiatan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubKegiatanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SubKegiatan $subKegiatan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return !$user->hasRole('panel_user');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SubKegiatan $subKegiatan): bool
    {
        return !$user->hasRole('panel_user');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SubKegiatan $subKegiatan): bool
    {
        return !$user->hasRole('panel_user');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SubKegiatan $subKegiatan): bool
    {
        return !$user->hasRole('panel_user');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SubKegiatan $subKegiatan): bool
    {
        return !$user->hasRole('panel_user');
    }
}
