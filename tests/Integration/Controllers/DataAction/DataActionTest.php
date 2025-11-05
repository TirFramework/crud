<?php

namespace Tir\Crud\Tests\Integration\Controllers\DataAction;

require_once __DIR__ . '/DataActionTestBase.php';

/**
 * Tests for main DataAction functionality: data() and trashData() actions
 */
class DataActionTest extends DataActionTestBaseCase
{
    // ============== Tests for data() action ==============

    /**
     * Test that data action returns all active models
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_returns_all_active_models()
    {
        // Create some active models
        $model1 = DataActionTestModel::create([
            'name' => 'Active User 1',
            'email' => 'active1@example.com',
            'status' => 'active'
        ]);

        $model2 = DataActionTestModel::create([
            'name' => 'Active User 2',
            'email' => 'active2@example.com',
            'status' => 'active'
        ]);

        // Create a trashed model (should not appear)
        $trashedModel = DataActionTestModel::create([
            'name' => 'Trashed User',
            'email' => 'trashed@example.com',
            'status' => 'inactive'
        ]);
        $trashedModel->delete();

        $controller = new DataActionTestController();

        // Call the data action
        $response = $controller->data();
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);

        // Assert that only active models are returned
        $this->assertCount(2, $data['data']);

        $names = array_column($data['data'], 'name');
        $this->assertContains('Active User 1', $names);
        $this->assertContains('Active User 2', $names);
        $this->assertNotContains('Trashed User', $names);
    }

    /**
     * Test that data action returns empty when no active models exist
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_returns_empty_when_no_active_models()
    {
        // Create only trashed models
        $model1 = DataActionTestModel::create([
            'name' => 'Trashed User 1',
            'email' => 'trashed1@example.com',
            'status' => 'inactive'
        ]);
        $model1->delete();

        $model2 = DataActionTestModel::create([
            'name' => 'Trashed User 2',
            'email' => 'trashed2@example.com',
            'status' => 'inactive'
        ]);
        $model2->delete();

        $controller = new DataActionTestController();

        $response = $controller->data();
        $data = $response->getData(true);

        // Assert empty result
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that data action includes pagination metadata
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_includes_pagination_metadata()
    {
        // Create multiple active models
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "Active User {$i}",
                'email' => "active{$i}@example.com",
                'status' => 'active'
            ]);
        }

        $controller = new DataActionTestController();

        $response = $controller->data();
        $data = $response->getData(true);

        // Assert pagination structure
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('last_page', $data);
        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);

        // Assert pagination values
        $this->assertEquals(1, $data['current_page']);
        $this->assertEquals(5, $data['total']);
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that data action filters out soft deleted models correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_excludes_soft_deleted_models()
    {
        // Create 3 active models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "Active User {$i}",
                'email' => "active{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Create 2 trashed models
        for ($i = 1; $i <= 2; $i++) {
            $model = DataActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com",
                'status' => 'inactive'
            ]);
            $model->delete();
        }

        $controller = new DataActionTestController();

        $response = $controller->data();
        $data = $response->getData(true);

        // Assert only active models are returned
        $this->assertCount(3, $data['data']);
        $this->assertEquals(3, $data['total']);
    }

    // ============== Tests for trashData() action ==============

    /**
     * Test that trash action returns only soft-deleted models
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_returns_only_soft_deleted_models()
    {
        // Create some models
        $activeModel = DataActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'status' => 'active'
        ]);

        $trashedModel1 = DataActionTestModel::create([
            'name' => 'Trashed User 1',
            'email' => 'trashed1@example.com',
            'status' => 'inactive'
        ]);
        $trashedModel1->delete();

        $trashedModel2 = DataActionTestModel::create([
            'name' => 'Trashed User 2',
            'email' => 'trashed2@example.com',
            'status' => 'inactive'
        ]);
        $trashedModel2->delete();

        $controller = new DataActionTestController();

        // Call the trash action
        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);

        // Assert that only trashed models are returned
        $this->assertCount(2, $data['data']);

        $names = array_column($data['data'], 'name');
        $this->assertContains('Trashed User 1', $names);
        $this->assertContains('Trashed User 2', $names);
        $this->assertNotContains('Active User', $names);
    }

    /**
     * Test that trash action returns empty result when no trashed models exist
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_returns_empty_when_no_trashed_models()
    {
        // Create only active models
        DataActionTestModel::create([
            'name' => 'Active User 1',
            'email' => 'active1@example.com',
            'status' => 'active'
        ]);

        DataActionTestModel::create([
            'name' => 'Active User 2',
            'email' => 'active2@example.com',
            'status' => 'active'
        ]);

        $controller = new DataActionTestController();

        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert empty result
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that trash action includes pagination metadata
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_includes_pagination_metadata()
    {
        // Create multiple trashed models to test pagination
        for ($i = 1; $i <= 5; $i++) {
            $model = DataActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com",
                'status' => 'inactive'
            ]);
            $model->delete();
        }

        $controller = new DataActionTestController();

        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert pagination structure
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('last_page', $data);
        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);

        // Assert pagination values
        $this->assertEquals(1, $data['current_page']);
        $this->assertEquals(5, $data['total']);
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that data and trash actions return different results
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_and_trash_actions_return_different_results()
    {
        // Create 3 active models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "Active User {$i}",
                'email' => "active{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Create 2 trashed models
        for ($i = 1; $i <= 2; $i++) {
            $model = DataActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com",
                'status' => 'inactive'
            ]);
            $model->delete();
        }

        $controller = new DataActionTestController();

        // Get data from both actions
        $dataResponse = $controller->data();
        $dataResults = $dataResponse->getData(true);

        $trashResponse = $controller->trashData();
        $trashResults = $trashResponse->getData(true);

        // Assert different results
        $this->assertCount(3, $dataResults['data']); // Active only
        $this->assertCount(2, $trashResults['data']); // Trashed only

        // Assert no overlap
        $dataNames = array_column($dataResults['data'], 'name');
        $trashNames = array_column($trashResults['data'], 'name');

        $overlap = array_intersect($dataNames, $trashNames);
        $this->assertEmpty($overlap);
    }
}
