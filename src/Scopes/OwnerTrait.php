<?php

namespace Tir\Crud\Scopes;

use Illuminate\Support\Facades\Auth;

trait OwnerTrait
{

    public static function scopeOnlyOwner($query)
    {
        $userId = Auth::id();
        return $query->where('user_id', $userId);
    }
}
