<?php

namespace Tir\Crud\Tests\Integration\Controllers\DataAction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tir\Crud\Support\Enums\FilterType;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\DatePicker;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Fields\Text;

/**
 * Test related model for relational filtering
 */
class DataActionTestCategory extends Model
{
    protected $table = 'data_action_test_categories';
    protected $fillable = ['name'];
}

/**
 * Test related model for many-to-many filtering
 */
class DataActionTestTag extends Model
{
    protected $table = 'data_action_test_tags';
    protected $fillable = ['name'];
}

/**
 * Test model for Data action integration testing
 */
class DataActionTestModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'status', 'priority', 'score', 'created_date', 'description', 'category_id', 'custom_filter_field'];
    protected $casts = [
        'created_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(DataActionTestCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(DataActionTestTag::class, 'data_action_test_model_tag', 'model_id', 'tag_id');
    }
}

/**
 * Test scaffolder for Data action integration testing
 */
class DataActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'data-action-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules('required|string|max:255')->searchable(),
            Text::make('email')->rules('required|email')->searchable(),
            Text::make('status')->rules('required|string')
                ->searchable()
                ->searchQuery(function($query, $searchTerm) {
                    // Custom search: convert search term to uppercase and search status
                    return $query->orWhere('status', 'like', strtoupper($searchTerm) . '%');
                })
                ->filter()
                ->filterType(FilterType::Select),
            Text::make('priority')->rules('required|string')
                ->filter()
                ->filterType(FilterType::Select),
            Text::make('score')->rules('required|integer')
                ->filter()
                ->filterType(FilterType::Slider),
            DatePicker::make('created_date')->rules('nullable|date')
                ->filter()
                ->filterType(FilterType::DatePicker),
            Text::make('description')->rules('nullable|string')
                ->filter()
                ->filterType(FilterType::Search),
            Select::make('category_id')
                ->relation('category', 'name', 'id')
                ->filter()
                ->filterType(FilterType::Select),
            Text::make('custom_filter_field')->rules('nullable|string')
                ->filterQuery(function($query, $value) {
                    // Custom filter: find records where score > 50 AND priority matches value
                    return $query->where('score', '>', 50)->whereIn('priority', (array)$value);
                }),
        ];
    }

    protected function setModel(): string
    {
        return DataActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for Data action integration testing
 */
class DataActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Data,
        \Tir\Crud\Controllers\Traits\Trash;

    protected function setScaffolder(): string
    {
        return DataActionTestScaffolder::class;
    }
}

/**
 * Base test class with common setup for all DataAction tests
 */
class DataActionTestBaseCase extends \Tir\Crud\Tests\TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the categories test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create the tags test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_tags', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status');
            $table->string('priority')->default('medium');
            $table->integer('score')->default(0);
            $table->date('created_date')->nullable();
            $table->text('description')->nullable();
            $table->string('custom_filter_field')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('data_action_test_categories')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        // Create the pivot table for many-to-many relationship
        \Illuminate\Support\Facades\Schema::create('data_action_test_model_tag', function ($table) {
            $table->id();
            $table->foreignId('model_id')->constrained('data_action_test_models')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('data_action_test_tags')->onDelete('cascade');
            $table->timestamps();
        });
    }
}
