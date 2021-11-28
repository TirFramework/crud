<?php

namespace Tir\Crud\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Tir\Authorization\Access;

class OwnerScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        if ($model->getModuleName() != 'role' && $model->getModuleName() != 'permission') {
            $access = Access::check($model->getModuleName(), 'index');
            if ($access == 'owner') {
                return $builder->where('user_id', '=', Auth::id());
            }

            if ($access == 'deny') {
                return false;
            }
        }
        return $builder;
    }
}
