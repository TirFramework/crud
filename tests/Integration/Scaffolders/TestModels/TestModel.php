<?php

namespace Tir\Crud\Tests\Integration\Scaffolders\TestModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple test model for BaseScaffolder integration testing
 */
class TestModel extends Model
{
    protected $fillable = ['name', 'email'];
}
