<?php

namespace Tir\Crud\Tests\Integration\Controllers\DataAction;

require_once __DIR__ . '/DataActionTestBase.php';

/**
 * Tests for search functionality of DataAction
 */
class DataActionSearchTest extends DataActionTestBaseCase
{
    // ============== Tests for search functionality ==============

    /**
     * Test that search works with no search parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_returns_all_models_when_no_search_parameter()
    {
        // Create test models
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active'
            ]);
        }

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Without search parameter, all models should be returned
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that search filters by name field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_filters_by_name_field()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Johnny Walker',
            'email' => 'johnny@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'John']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 'John Smith' and 'Johnny Walker'
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('John Smith', $names);
        $this->assertContains('Johnny Walker', $names);
    }

    /**
     * Test that search filters by email field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_filters_by_email_field()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'User One',
            'email' => 'john@company.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User Two',
            'email' => 'jane@company.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User Three',
            'email' => 'john@other.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'company']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find users with 'company' in email
        $this->assertCount(2, $data['data']);
        $emails = array_column($data['data'], 'email');
        $this->assertContains('john@company.com', $emails);
        $this->assertContains('jane@company.com', $emails);
    }

    /**
     * Test that search with multiple fields (OR logic)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_applies_or_logic_across_searchable_fields()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Charlie Johnson',
            'email' => 'charlie@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'Johnson']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 'Alice Johnson' and 'Charlie Johnson'
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('Alice Johnson', $names);
        $this->assertContains('Charlie Johnson', $names);
    }

    /**
     * Test that search returns empty when no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_returns_empty_when_no_matches()
    {
        // Create test models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'NonExistentUser']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return empty results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that search is case-insensitive
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_is_case_insensitive()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter in uppercase
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'JOHN']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find the user despite case difference
        $this->assertCount(1, $data['data']);
        $this->assertEquals('John Doe', $data['data'][0]['name']);
    }

    /**
     * Test search with special characters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_special_characters()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => "O'Brien",
            'email' => "obrien@example.com",
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Smith',
            'email' => 'smith@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter containing apostrophe
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => "O'Brien"]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find the user with special character
        $this->assertCount(1, $data['data']);
        $this->assertEquals("O'Brien", $data['data'][0]['name']);
    }

    /**
     * Test search with custom searchQuery callback on status field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_custom_search_query_callback()
    {
        // Create test models with different status values
        DataActionTestModel::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'status' => 'ACTIVE'
        ]);
        DataActionTestModel::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'status' => 'INACTIVE'
        ]);
        DataActionTestModel::create([
            'name' => 'User Three',
            'email' => 'user3@example.com',
            'status' => 'PENDING'
        ]);

        // Mock request with search parameter
        // The status field has a custom searchQuery that converts search term to uppercase
        // and searches with 'like' pattern matching starting with the term
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'ACT']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only the user with status starting with 'ACT' (ACTIVE)
        $this->assertCount(1, $data['data']);
        $this->assertEquals('ACTIVE', $data['data'][0]['status']);
    }

    /**
     * Test search with custom searchQuery callback matches multiple results
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_custom_search_query_callback_multiple_matches()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'status' => 'PENDING'
        ]);
        DataActionTestModel::create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'status' => 'PENDING'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'status' => 'ACTIVE'
        ]);

        // Mock request with search parameter for 'PEN' which should match 'PENDING'
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'PEN']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find both users with status starting with 'PEN' (PENDING)
        $this->assertCount(2, $data['data']);
        foreach ($data['data'] as $item) {
            $this->assertEquals('PENDING', $item['status']);
        }
    }
}
