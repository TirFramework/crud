<?php

namespace Tir\Crud\Tests\Integration\Controllers\DataAction;

require_once __DIR__ . '/DataActionTestBase.php';

/**
 * Tests for filtering functionality of DataAction
 * 
 * Covers DataService::applyFilters() and filter type detection:
 * - Select filter type (whereIn)
 * - Slider filter type (between)
 * - DatePicker filter type (date range)
 * - Search filter type (like)
 * - Relational filters (whereHas)
 * - Custom filter query callbacks
 */
class DataActionFilterTest extends DataActionTestBaseCase
{
    // ============== Tests for basic filter functionality ==============

    /**
     * Test that filters returns all models when no filter parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filters_returns_all_models_when_no_filters_parameter()
    {
        // Create test models
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'high',
                'score' => 10 + $i
            ]);
        }

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Without filter parameter, all models should be returned
        $this->assertCount(5, $data['data']);
    }

    // ============== Tests for Select filter type ==============

    /**
     * Test that Select filter works correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_select_type()
    {
        // Create test models with different statuses
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'inactive',
            'priority' => 'low',
            'score' => 30
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 40
        ]);

        // Mock request with Select filter (status = 'active')
        $filters = json_encode(['status' => ['active']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only active users
        $this->assertCount(2, $data['data']);
        foreach ($data['data'] as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    /**
     * Test Select filter with multiple values (whereIn)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_select_multiple_values()
    {
        // Create test models with different priorities
        DataActionTestModel::create([
            'name' => 'High Priority',
            'email' => 'high@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 80
        ]);
        DataActionTestModel::create([
            'name' => 'Low Priority',
            'email' => 'low@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 20
        ]);
        DataActionTestModel::create([
            'name' => 'Medium Priority',
            'email' => 'medium@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50
        ]);

        // Mock request with Select filter (priority in ['high', 'low'])
        $filters = json_encode(['priority' => ['high', 'low']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find high and low priority users
        $this->assertCount(2, $data['data']);
        $priorities = array_column($data['data'], 'priority');
        $this->assertContains('high', $priorities);
        $this->assertContains('low', $priorities);
        $this->assertNotContains('medium', $priorities);
    }

    // ============== Tests for Slider filter type ==============

    /**
     * Test that Slider filter works with range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_slider_type()
    {
        // Create test models with different scores
        DataActionTestModel::create([
            'name' => 'Low Score',
            'email' => 'low@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 20
        ]);
        DataActionTestModel::create([
            'name' => 'Medium Score',
            'email' => 'medium@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50
        ]);
        DataActionTestModel::create([
            'name' => 'High Score',
            'email' => 'high@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 80
        ]);

        // Mock request with Slider filter (score between 40 and 70)
        $filters = json_encode(['score' => [40, 70]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only medium score user
        $this->assertCount(1, $data['data']);
        $this->assertEquals(50, $data['data'][0]['score']);
    }

    /**
     * Test that Slider filter works with full range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_slider_type_full_range()
    {
        // Create test models with different scores
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 10 * $i  // 10, 20, 30, 40, 50
            ]);
        }

        // Mock request with Slider filter (score between 0 and 100)
        $filters = json_encode(['score' => [0, 100]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find all users
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that Slider filter returns empty when out of range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_slider_type_no_matches()
    {
        // Create test models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 10 + $i  // 11, 12, 13
            ]);
        }

        // Mock request with Slider filter (score between 50 and 100)
        $filters = json_encode(['score' => [50, 100]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    // ============== Tests for DatePicker filter type ==============

    /**
     * Test DatePicker filter filters by date range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_type()
    {
        // Create test models with different dates
        DataActionTestModel::create([
            'name' => 'Early User',
            'email' => 'early@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-01-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Mid User',
            'email' => 'mid@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Late User',
            'email' => 'late@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-12-15'
        ]);

        // Mock request with DatePicker filter (between 2024-05-01 and 2024-07-31)
        $filters = json_encode(['created_date' => ['2024-05-01', '2024-07-31']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only mid user
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Mid User', $data['data'][0]['name']);
    }

    /**
     * Test DatePicker filter returns all records in wide date range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_wide_range()
    {
        // Create test models with dates in 2024
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 50,
                'created_date' => "2024-0{$i}-15"
            ]);
        }

        // Mock request with DatePicker filter (whole year 2024)
        $filters = json_encode(['created_date' => ['2024-01-01', '2024-12-31']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find all users
        $this->assertCount(3, $data['data']);
    }

    /**
     * Test DatePicker filter returns empty when out of date range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_no_matches()
    {
        // Create test models with dates in 2024
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-01-05'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-01-10'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-01-15'
        ]);

        // Mock request with DatePicker filter (year 2023)
        $filters = json_encode(['created_date' => ['2023-01-01', '2023-12-31']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test DatePicker filter with single date (exact match)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_exact_date()
    {
        // Create test models with different dates
        DataActionTestModel::create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-16'
        ]);

        // Mock request with DatePicker filter (exact date)
        $filters = json_encode(['created_date' => ['2024-06-15', '2024-06-15']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only target user
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Target User', $data['data'][0]['name']);
    }

    // ============== Tests for Search filter type ==============

    /**
     * Test Search filter type (like search filter)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type()
    {
        // Create test models with different descriptions
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'High priority task'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Medium priority task'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Low priority meeting'
        ]);

        // Mock request with Search filter type (contains 'task')
        $filters = json_encode(['description' => 'task']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only tasks (User 1 and User 2)
        $this->assertCount(2, $data['data']);
        $descriptions = array_column($data['data'], 'description');
        $this->assertContains('High priority task', $descriptions);
        $this->assertContains('Medium priority task', $descriptions);
    }

    /**
     * Test Search filter type with no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type_no_matches()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'High priority task'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Medium priority task'
        ]);

        // Mock request with Search filter type (search for non-existent term)
        $filters = json_encode(['description' => 'nonexistent']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test Search filter type with partial match
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type_partial_match()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Implementation of new features'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Bug implementation report'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Documentation review'
        ]);

        // Mock request with Search filter type (partial match 'impl')
        $filters = json_encode(['description' => 'impl']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find users with 'impl' in description
        $this->assertCount(2, $data['data']);
        $descriptions = array_column($data['data'], 'description');
        $this->assertContains('Implementation of new features', $descriptions);
        $this->assertContains('Bug implementation report', $descriptions);
    }

    /**
     * Test Search filter type is case-insensitive
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type_case_insensitive()
    {
        // Create test model
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Urgent Production Issue'
        ]);

        // Mock request with Search filter type (lowercase search for uppercase text)
        $filters = json_encode(['description' => 'urgent']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find the user despite case difference
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Urgent Production Issue', $data['data'][0]['description']);
    }

    // ============== Tests for relational filters ==============

    /**
     * Test relational filter with whereHas for single relation match
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_single_match()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);
        $cat2 = DataActionTestCategory::create(['name' => 'Design']);

        // Create users with categories
        DataActionTestModel::create([
            'name' => 'Developer 1',
            'email' => 'dev1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Backend developer',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Developer 2',
            'email' => 'dev2@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 60,
            'created_date' => '2024-06-15',
            'description' => 'Frontend developer',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Designer 1',
            'email' => 'designer@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 40,
            'created_date' => '2024-06-15',
            'description' => 'UI designer',
            'category_id' => $cat2->id
        ]);

        // Mock request with relational filter (category_id = Development)
        $filters = json_encode(['category_id' => [$cat1->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only developers
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('Developer 1', $names);
        $this->assertContains('Developer 2', $names);
    }

    /**
     * Test relational filter with whereHas for multiple relations
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_multiple_matches()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);
        $cat2 = DataActionTestCategory::create(['name' => 'Design']);
        $cat3 = DataActionTestCategory::create(['name' => 'Marketing']);

        // Create users with categories
        for ($i = 1; $i <= 2; $i++) {
            DataActionTestModel::create([
                'name' => "Dev {$i}",
                'email' => "dev{$i}@example.com",
                'status' => 'active',
                'priority' => 'high',
                'score' => 50,
                'created_date' => '2024-06-15',
                'description' => 'Developer',
                'category_id' => $cat1->id
            ]);
        }
        for ($i = 1; $i <= 2; $i++) {
            DataActionTestModel::create([
                'name' => "Designer {$i}",
                'email' => "designer{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 40,
                'created_date' => '2024-06-15',
                'description' => 'Designer',
                'category_id' => $cat2->id
            ]);
        }
        DataActionTestModel::create([
            'name' => 'Marketer 1',
            'email' => 'marketer@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Marketing specialist',
            'category_id' => $cat3->id
        ]);

        // Mock request with relational filter (category_id in [Development, Design])
        $filters = json_encode(['category_id' => [$cat1->id, $cat2->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find developers and designers (4 total)
        $this->assertCount(4, $data['data']);
    }

    /**
     * Test relational filter returns empty when no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_no_matches()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);
        $cat2 = DataActionTestCategory::create(['name' => 'Design']);
        $cat_unused = DataActionTestCategory::create(['name' => 'Unused']);

        // Create users only with cat1 and cat2
        DataActionTestModel::create([
            'name' => 'Developer 1',
            'email' => 'dev@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Developer',
            'category_id' => $cat1->id
        ]);

        // Mock request with relational filter (category_id = unused)
        $filters = json_encode(['category_id' => [$cat_unused->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test relational filter with null category_id
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_excludes_null()
    {
        // Create category
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);

        // Create users with and without category
        DataActionTestModel::create([
            'name' => 'User with category',
            'email' => 'withcat@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Has category',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'User without category',
            'email' => 'nocat@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'No category',
            'category_id' => null
        ]);

        // Mock request with relational filter (category_id = cat1)
        $filters = json_encode(['category_id' => [$cat1->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only user with category
        $this->assertCount(1, $data['data']);
        $this->assertEquals('User with category', $data['data'][0]['name']);
    }

    // ============== Tests for custom filter query ==============

    /**
     * Test custom filter query callback with score condition and priority matching
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_with_score_and_priority()
    {
        // Create users with different scores and priorities
        DataActionTestModel::create([
            'name' => 'High score high priority',
            'email' => 'high1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 75,
            'created_date' => '2024-06-15',
            'description' => 'Score 75'
        ]);
        DataActionTestModel::create([
            'name' => 'High score medium priority',
            'email' => 'high2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 80,
            'created_date' => '2024-06-15',
            'description' => 'Score 80'
        ]);
        DataActionTestModel::create([
            'name' => 'Low score high priority',
            'email' => 'low1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Score 30'
        ]);
        DataActionTestModel::create([
            'name' => 'Borderline score',
            'email' => 'border@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Score 50'
        ]);

        // Mock request with custom filter query (score > 50 AND priority = high)
        $filters = json_encode(['custom_filter_field' => ['high']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only high score + high priority record
        $this->assertCount(1, $data['data']);
        $this->assertEquals('High score high priority', $data['data'][0]['name']);
        $this->assertEquals(75, $data['data'][0]['score']);
    }

    /**
     * Test custom filter query with multiple priority values
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_multiple_priorities()
    {
        // Create users with different scores and priorities
        DataActionTestModel::create([
            'name' => 'High score high priority',
            'email' => 'high1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 75,
            'created_date' => '2024-06-15',
            'description' => 'Score 75'
        ]);
        DataActionTestModel::create([
            'name' => 'High score medium priority',
            'email' => 'high2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 80,
            'created_date' => '2024-06-15',
            'description' => 'Score 80'
        ]);
        DataActionTestModel::create([
            'name' => 'High score low priority',
            'email' => 'high3@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 60,
            'created_date' => '2024-06-15',
            'description' => 'Score 60'
        ]);
        DataActionTestModel::create([
            'name' => 'Low score high priority',
            'email' => 'low1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Score 30'
        ]);

        // Mock request with custom filter query (score > 50 AND priority in [high, medium])
        $filters = json_encode(['custom_filter_field' => ['high', 'medium']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 2 records: high priority (75) and medium priority (80)
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('High score high priority', $names);
        $this->assertContains('High score medium priority', $names);
    }

    /**
     * Test custom filter query returns empty when no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_no_matches()
    {
        // Create users with low scores
        DataActionTestModel::create([
            'name' => 'Low score high priority',
            'email' => 'low1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Score 30'
        ]);
        DataActionTestModel::create([
            'name' => 'Low score medium priority',
            'email' => 'low2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 40,
            'created_date' => '2024-06-15',
            'description' => 'Score 40'
        ]);

        // Mock request with custom filter query (score > 50 AND priority = high)
        // No records match this criteria (score > 50)
        $filters = json_encode(['custom_filter_field' => ['high']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test custom filter query with specific boundary condition (score = 51)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_boundary_score()
    {
        // Create user at boundary
        DataActionTestModel::create([
            'name' => 'Score 51 low priority',
            'email' => 'boundary@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 51,
            'created_date' => '2024-06-15',
            'description' => 'Just above boundary'
        ]);
        DataActionTestModel::create([
            'name' => 'Score 50 low priority',
            'email' => 'exact@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'At boundary'
        ]);

        // Mock request with custom filter query (score > 50 AND priority = low)
        $filters = json_encode(['custom_filter_field' => ['low']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only score 51 (not 50, because condition is > 50, not >= 50)
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Score 51 low priority', $data['data'][0]['name']);
        $this->assertEquals(51, $data['data'][0]['score']);
    }
}
