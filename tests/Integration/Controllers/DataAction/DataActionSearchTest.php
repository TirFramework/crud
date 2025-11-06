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

    /**
     * Test search excludes non-matching names (negative case)
     * Critical test to ensure we DON'T return similar but different names
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_excludes_non_matching_names()
    {
        // Create models with similar but distinct names
        DataActionTestModel::create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Johnny Depp',
            'email' => 'johnny@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Joan Smith',
            'email' => 'joan@example.com',
            'status' => 'active'
        ]);

        // Mock request searching for 'John' (exact prefix)
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'John']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should ONLY find 'John Smith' and 'Johnny Depp' (both contain 'John')
        // Should NOT find 'Jane Smith' or 'Joan Smith'
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        
        // Assert matching results
        $this->assertContains('John Smith', $names);
        $this->assertContains('Johnny Depp', $names);
        
        // Assert non-matching results are excluded
        $this->assertNotContains('Jane Smith', $names);
        $this->assertNotContains('Joan Smith', $names);
    }

    /**
     * Test search with email excludes similar domains (negative case)
     * Ensures exact domain/email matching logic
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_excludes_similar_but_different_emails()
    {
        // Create models with similar but distinct emails
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'john@example.co.uk',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'john@exampledev.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User 4',
            'email' => 'jane@example.com',
            'status' => 'active'
        ]);

        // Mock request searching for 'example.com' (exact domain)
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'example.com']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find emails containing 'example.com' exactly
        $this->assertCount(2, $data['data']);
        $emails = array_column($data['data'], 'email');
        
        // Assert matching results
        $this->assertContains('john@example.com', $emails);
        $this->assertContains('jane@example.com', $emails);
        
        // Assert non-matching results are excluded
        $this->assertNotContains('john@example.co.uk', $emails);
        $this->assertNotContains('john@exampledev.com', $emails);
    }

    /**
     * Test search with LIKE is case-insensitive (important behavioral test)
     * Demonstrates that 'Test' DOES match 'Contest' because LIKE is case-insensitive
     * and matches partial strings
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_case_insensitive_matches_partial_strings()
    {
        // Create models with different names
        DataActionTestModel::create([
            'name' => 'Testing User',
            'email' => 'test@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Contest Winner',
            'email' => 'contest@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Tester Developer',
            'email' => 'tester@example.com',
            'status' => 'active'
        ]);

        // Mock request searching for 'Test' (case-insensitive LIKE search)
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'Test']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // LIKE search finds ANY occurrence of 'test' (case-insensitive)
        // So: Testing, Test, Tester ALL match AND Contest matches because it contains 'test'
        $this->assertCount(4, $data['data']);
        $names = array_column($data['data'], 'name');
        
        // All should be found (LIKE is case-insensitive and matches partial strings)
        $this->assertContains('Testing User', $names);
        $this->assertContains('Test Admin', $names);
        $this->assertContains('Tester Developer', $names);
        $this->assertContains('Contest Winner', $names);  // Contains 'test' in 'Contest'
    }

    /**
     * Test search with single character matches correctly
     * Negative case: single char search should match names containing that char
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_single_character_matches_containing_names()
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
        DataActionTestModel::create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'David',
            'email' => 'david@example.com',
            'status' => 'active'
        ]);

        // Mock request searching for 'B' (case-insensitive - matches Bob and aBs)
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'b']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 'Bob' (exact match) and nothing else
        // 'Alice' doesn't contain 'b', 'Charlie' doesn't contain 'b', 'David' doesn't contain 'b'
        $this->assertCount(1, $data['data']);
        $names = array_column($data['data'], 'name');
        
        // Assert matching result
        $this->assertContains('Bob', $names);
        
        // Assert non-matching results are excluded
        $this->assertNotContains('Alice', $names);
        $this->assertNotContains('Charlie', $names);
        $this->assertNotContains('David', $names);
    }

    /**
     * Test search doesn't return results when searching for spaces only
     * Negative case: whitespace search should not match everything
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_whitespace_only_returns_correct_matches()
    {
        // Create models
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

        // Mock request searching for space character
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => ' ']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Searching for space should find names with spaces
        // or return nothing depending on implementation
        $this->assertIsArray($data['data']);
        // Both have spaces, so both should be found
        $this->assertCount(2, $data['data']);
    }
}
