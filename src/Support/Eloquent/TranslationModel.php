<?php

namespace Tir\Crud\Support\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Tir\Crud\Support\Helpers\CrudHelper;

class TranslationModel extends Model
{
 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        /** Show only active or fallback language data */
        static::addGlobalScope('locale', function ($query) {
            $query->whereIn('locale', [CrudHelper::locale(), config('app.fallback_locale')]);
        });
    }
  
}
