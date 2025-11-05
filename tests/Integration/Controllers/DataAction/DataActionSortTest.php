<?php

namespace Tir\Crud\Tests\Integration\Controllers\DataAction;

require_once __DIR__ . '/DataActionTestBase.php';

/**
 * Tests for sorting functionality of DataAction (applySort method)
 * 
 * Covers DataService::applySort() lines 241-247:
 * - Default sort by created_at DESC
 * - Custom field sorting
 * - Sort order (ASC/DESC)
 * - Invalid sorter handling
 */
class DataActionSortTest extends DataActionTestBaseCase
{
    // ============== Tests for sorting functionality ==============

    /**
     * Test that default sort is by created_at DESC when no sorter parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_default_by_created_at_descending()
    {
        // Create models with different created_at times
        $user1 = DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active'
        ]);
        sleep(1);
        
        $user2 = DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active'
        ]);
        sleep(1);
        
        $user3 = DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active'
        ]);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Without sorter parameter, should return in DESC order (newest first)
        $this->assertCount(3, $data['data']);
        $this->assertEquals('User 3', $data['data'][0]['name']);
        $this->assertEquals('User 2', $data['data'][1]['name']);
        $this->assertEquals('User 1', $data['data'][2]['name']);
    }

    /**
     * Test sorting by name field in ascending order
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_name_ascending()
    {
        // Create models with different names
        DataActionTestModel::create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'status' => 'active'
        ]);

        // Mock request with sorter parameter (ascending)
        $sorter = json_encode(['field' => 'name', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted alphabetically ascending
        $this->assertCount(3, $data['data']);
        $this->assertEquals('Alice', $data['data'][0]['name']);
        $this->assertEquals('Bob', $data['data'][1]['name']);
        $this->assertEquals('Charlie', $data['data'][2]['name']);
    }

    /**
     * Test sorting by name field in descending order
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_name_descending()
    {
        // Create models with different names
        DataActionTestModel::create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'status' => 'active'
        ]);

        // Mock request with sorter parameter (descending)
        $sorter = json_encode(['field' => 'name', 'order' => 'descend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted alphabetically descending
        $this->assertCount(3, $data['data']);
        $this->assertEquals('Charlie', $data['data'][0]['name']);
        $this->assertEquals('Bob', $data['data'][1]['name']);
        $this->assertEquals('Alice', $data['data'][2]['name']);
    }

    /**
     * Test sorting by email field ascending
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_email_ascending()
    {
        // Create models with different emails
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'zebra@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'alpha@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'beta@example.com',
            'status' => 'active'
        ]);

        // Mock request with sorter parameter
        $sorter = json_encode(['field' => 'email', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted by email ascending
        $this->assertCount(3, $data['data']);
        $this->assertEquals('alpha@example.com', $data['data'][0]['email']);
        $this->assertEquals('beta@example.com', $data['data'][1]['email']);
        $this->assertEquals('zebra@example.com', $data['data'][2]['email']);
    }

    /**
     * Test sorting by numeric field (priority level)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_numeric_field_ascending()
    {
        // Create models with different scores
        DataActionTestModel::create([
            'name' => 'High Score',
            'email' => 'high@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 95
        ]);
        DataActionTestModel::create([
            'name' => 'Low Score',
            'email' => 'low@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 25
        ]);
        DataActionTestModel::create([
            'name' => 'Medium Score',
            'email' => 'medium@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50
        ]);

        // Mock request with sorter by score ascending
        $sorter = json_encode(['field' => 'score', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted by score ascending (lowest first)
        $this->assertCount(3, $data['data']);
        $this->assertEquals(25, $data['data'][0]['score']);
        $this->assertEquals(50, $data['data'][1]['score']);
        $this->assertEquals(95, $data['data'][2]['score']);
    }

    /**
     * Test sorting by numeric field descending
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_numeric_field_descending()
    {
        // Create models with different scores
        DataActionTestModel::create([
            'name' => 'High Score',
            'email' => 'high@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 95
        ]);
        DataActionTestModel::create([
            'name' => 'Low Score',
            'email' => 'low@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 25
        ]);
        DataActionTestModel::create([
            'name' => 'Medium Score',
            'email' => 'medium@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50
        ]);

        // Mock request with sorter by score descending
        $sorter = json_encode(['field' => 'score', 'order' => 'descend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted by score descending (highest first)
        $this->assertCount(3, $data['data']);
        $this->assertEquals(95, $data['data'][0]['score']);
        $this->assertEquals(50, $data['data'][1]['score']);
        $this->assertEquals(25, $data['data'][2]['score']);
    }

    /**
     * Test sorting by date field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_date_field_ascending()
    {
        // Create models with different created_date values
        DataActionTestModel::create([
            'name' => 'Recent Date',
            'email' => 'recent@example.com',
            'status' => 'active',
            'created_date' => '2024-12-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Old Date',
            'email' => 'old@example.com',
            'status' => 'active',
            'created_date' => '2024-01-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Mid Date',
            'email' => 'mid@example.com',
            'status' => 'active',
            'created_date' => '2024-06-15'
        ]);

        // Mock request with sorter by created_date ascending
        $sorter = json_encode(['field' => 'created_date', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted by date ascending (oldest first)
        $this->assertCount(3, $data['data']);
        // Dates are returned as ISO 8601 format with timestamps
        $this->assertStringContainsString('2024-01-15', $data['data'][0]['created_date']);
        $this->assertStringContainsString('2024-06-15', $data['data'][1]['created_date']);
        $this->assertStringContainsString('2024-12-15', $data['data'][2]['created_date']);
    }

    /**
     * Test sorting when sorter has no field property (should default to created_at DESC)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_invalid_sorter_defaults_to_created_at()
    {
        // Create models
        $user1 = DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active'
        ]);
        sleep(1);
        $user2 = DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active'
        ]);

        // Mock request with invalid sorter (no field)
        $sorter = json_encode(['order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should default to created_at DESC
        $this->assertCount(2, $data['data']);
        $this->assertEquals('User 2', $data['data'][0]['name']);
        $this->assertEquals('User 1', $data['data'][1]['name']);
    }

    /**
     * Test sorting with status field (text field)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_status_field_ascending()
    {
        // Create models with different statuses
        DataActionTestModel::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'status' => 'PENDING'
        ]);
        DataActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'status' => 'ACTIVE'
        ]);
        DataActionTestModel::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'status' => 'INACTIVE'
        ]);

        // Mock request with sorter by status ascending
        $sorter = json_encode(['field' => 'status', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted alphabetically by status
        $this->assertCount(3, $data['data']);
        $this->assertEquals('ACTIVE', $data['data'][0]['status']);
        $this->assertEquals('INACTIVE', $data['data'][1]['status']);
        $this->assertEquals('PENDING', $data['data'][2]['status']);
    }

    /**
     * Test sorting converts 'ascend' to 'ASC' and 'descend' to 'DESC'
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_converts_order_parameters()
    {
        // Create models
        DataActionTestModel::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'status' => 'active'
        ]);

        // Test with 'ascend' parameter (should convert to ASC)
        $sorter = json_encode(['field' => 'name', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted ascending (Alice before Bob)
        $this->assertEquals('Alice', $data['data'][0]['name']);
        $this->assertEquals('Bob', $data['data'][1]['name']);
    }

    /**
     * Test sorting with priority field (multiple values of same type)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_by_priority_ascending()
    {
        // Create models with different priorities
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'low'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'high'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'medium'
        ]);

        // Mock request with sorter by priority ascending
        $sorter = json_encode(['field' => 'priority', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['sorter' => $sorter]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should be sorted alphabetically by priority
        $this->assertCount(3, $data['data']);
        $this->assertEquals('high', $data['data'][0]['priority']);
        $this->assertEquals('low', $data['data'][1]['priority']);
        $this->assertEquals('medium', $data['data'][2]['priority']);
    }

    /**
     * Test sorting maintains pagination
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sort_with_pagination()
    {
        // Create 20 models
        for ($i = 1; $i <= 20; $i++) {
            DataActionTestModel::create([
                'name' => "User " . str_pad($i, 2, '0', STR_PAD_LEFT),
                'email' => "user{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Mock request with sorter and page limit
        $sorter = json_encode(['field' => 'name', 'order' => 'ascend']);
        $request = \Illuminate\Http\Request::create('/', 'GET', [
            'sorter' => $sorter,
            'result' => 10
        ]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should have pagination info
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('per_page', $data);
        $this->assertEquals(20, $data['total']);
        $this->assertEquals(10, $data['per_page']);
        $this->assertCount(10, $data['data']);

        // First page should be sorted
        $this->assertEquals('User 01', $data['data'][0]['name']);
        $this->assertEquals('User 10', $data['data'][9]['name']);
    }
}
