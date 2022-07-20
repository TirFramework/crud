<?php

namespace Tir\Crud\Support\Eloquent;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Tir\Authorization\Access;

trait HasAccessLevel
{

    public function scopeAccessLevel($query) {
            $access = Access::check($this->getModuleName(), 'index');
            if ($access == 'owner') {
                return $query->where('user_id', '=', Auth::id());
            }

            if ($access == 'deny') {
                abort(403);
            }
        return $query;
    }

}
