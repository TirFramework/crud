<?php

namespace Tir\Crud\Tests\Integration\Scaffolders\TestModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple test model for integration testing
 */
class TestUser extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'status',
        'phone',
        'address'
    ];

    protected $table = 'test_users';
}
