# Tir/CRUD Framework - System Design & Architecture

**Last Updated**: June 2026  
**Purpose**: Comprehensive guide to the internal design, architecture, and data flow of the Tir/CRUD framework for developers extending or maintaining the system.

---

## 📋 Table of Contents

1. [System Architecture Overview](#system-architecture-overview)
2. [Component Design](#component-design)
3. [Data Flow Diagrams](#data-flow-diagrams)
4. [Scaffolder System](#scaffolder-system)
5. [Field System](#field-system)
6. [Service Layer](#service-layer)
7. [Hook System](#hook-system)
8. [Access Control System](#access-control-system)
9. [Database Adapter Pattern](#database-adapter-pattern)
10. [Module Management](#module-management)
11. [Request Processing Pipeline](#request-processing-pipeline)
12. [Configuration System](#configuration-system)
13. [Testing Architecture](#testing-architecture)

---

## 🏛️ System Architecture Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        HTTP LAYER                               │
│  Laravel Routes → Resource Routes (auto-registered)             │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CONTROLLER LAYER                             │
│  CrudController (abstract base)                                 │
│  Uses: Crud trait (composition of CRUD traits)                  │
│  Actions: index, show, create, store, edit, update, destroy    │
└────────────────────────┬────────────────────────────────────────┘
                         │
          ┌──────────────┼──────────────┐
          │              │              │
          ▼              ▼              ▼
      ┌────────┐  ┌────────────┐  ┌────────────┐
      │Scaffold│  │   Hooks    │  │   Access   │
      │ System │  │   System   │  │  Control   │
      └────────┘  └────────────┘  └────────────┘
          │              │              │
          └──────────────┼──────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                     SERVICE LAYER                               │
│  StoreService, UpdateService, DataService                       │
│  Handles: Validation, Business Logic, Transformation            │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                     MODEL LAYER                                 │
│  Eloquent Models with Database Interactions                     │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                 DATABASE ADAPTER LAYER                          │
│  Abstracts database-specific logic (MySQL, PostgreSQL, etc.)    │
└─────────────────────────────────────────────────────────────────┘
```

### Core Namespaces

```
Tir\Crud\
├── Controllers/
│   ├── CrudController.php          # Base controller facade
│   └── Traits/
│       ├── Crud.php                # Composes all CRUD traits
│       ├── CrudInit.php            # Initialization
│       ├── Index.php               # List with data retrieval
│       ├── Show.php                # Detail view
│       ├── Create.php              # Creation form
│       ├── Store.php               # Record storage
│       ├── Edit.php                # Edit form
│       ├── Update.php              # Record update
│       ├── Destroy.php             # Soft delete
│       ├── Trash.php               # List soft-deleted
│       ├── Restore.php             # Restore deleted
│       ├── ForceDelete.php         # Permanent delete
│       └── ProcessRequest.php      # Request processing
├── Services/
│   ├── StoreService.php            # Create logic
│   ├── UpdateService.php           # Update logic
│   └── DataService.php             # Query building/filtering
├── Support/
│   ├── Scaffold/
│   │   ├── BaseScaffolder.php      # Model configuration base
│   │   ├── Fields/                 # Field type definitions
│   │   ├── Actions.php             # Action definitions
│   │   └── FieldsHandler.php       # Field processing
│   ├── Hooks/
│   │   ├── BaseHooks.php           # Hook callback system
│   │   ├── CreateHooks.php         # Create action hooks
│   │   ├── StoreHooks.php          # Store action hooks
│   │   └── [*Hooks.php]            # Hooks for each action
│   ├── Acl/
│   │   └── Access.php              # Permission checking
│   ├── Database/
│   │   ├── DatabaseAdapterInterface.php
│   │   ├── DatabaseAdapterFactory.php
│   │   └── Adapters/               # Adapter implementations
│   └── Module/
│       ├── Module.php              # Base module class
│       ├── Modules.php             # Module collection
│       └── AdminMenu.php           # Menu generation
└── Config/
    └── crud.php                    # Configuration
```

---

## 🔧 Component Design

### CrudController Architecture

```php
abstract CrudController extends Controller
    └── use Crud (main trait)
        ├── use CrudInit
        │   ├── setScaffolder()      [abstract]
        │   ├── scaffolder()         [concrete]
        │   ├── model()              [concrete]
        │   └── setup()              [hook entry point]
        │
        ├── use Index
        │   └── index()              [lists records]
        │
        ├── use Show
        │   └── show($id)            [detail view]
        │
        ├── use Create
        │   └── create()             [create form]
        │
        ├── use Store
        │   └── store(Request)       [save new]
        │
        ├── use Edit
        │   └── edit($id)            [edit form]
        │
        ├── use Update
        │   └── update($id, Request) [update existing]
        │
        ├── use Destroy
        │   └── destroy($id)         [soft delete]
        │
        ├── use Trash
        │   └── trash()              [list deleted]
        │
        ├── use Restore
        │   └── restore($id)         [restore deleted]
        │
        └── use ForceDelete
            └── forceDelete($id)     [permanent delete]
```

### Trait Composition Strategy

The framework uses trait composition to:

1. **Organize Code**: Each trait handles one action
2. **Enable Selective Use**: Can use individual traits if needed
3. **Share Logic**: Common logic extracted to base traits (Data, ProcessRequest)
4. **Maintain Clarity**: Each action's code is in one place

### Scaffolder System

The `BaseScaffolder` class manages model metadata:

```php
abstract BaseScaffolder
{
    // Abstract methods (must implement)
    abstract function setModuleName(): string
    abstract function setModel(): string
    abstract function setFields(): array

    // Optional overrides
    protected function setModuleTitle(): string    // Default: module name
    protected function setActions(): array         // Default: all actions
    protected function setButtons(): array         // Default: back + submit

    // Public interface
    public function scaffold($page): self          // Build for specific page
    public function moduleName(): string           // Get module name
    public function moduleTitle(): string          // Get display title
    public function model(): Model                 // Instantiate model
    public function fields(): array                // Get all fields
    public function actions(): array               // Get available actions
    public function buttons(): array               // Get form buttons
}
```

**Key Design Decisions:**

- **Immutable Configuration**: Fields are defined once during initialization
- **Lazy Loading**: Models and fields are instantiated on-demand
- **Extensible Hooks**: Can override methods or use trait hooks for customization
- **Type Safety**: Uses strict typing and returns appropriate types

---

## 📊 Data Flow Diagrams

### Create/Store Flow

```
Request → store()
    │
    ├─→ [Hook] onStore
    │       │
    │       └─→ processRequest()
    │           ├─ Extract fields from request
    │           ├─ Normalize data types
    │           └─ Return processed request
    │
    ├─→ [Hook] before validation
    │
    ├─→ validateCreateRequest()
    │   └─ Run field validation rules
    │
    ├─→ [Hook] after validation
    │
    └─→ StoreService::execute()
        ├─ Create model instance
        ├─ Fill attributes
        ├─ Save to database
        ├─ [Hook] onModelCreated
        ├─ Log operation
        └─ Return created model
            │
            └─→ Response (201 + created model)
```

### Index/List Flow

```
Request (filters, search, sort, page)
    │
    └─→ index()
        │
        ├─→ Data from scaffolder
        │
        └─→ DataService::getData()
            │
            ├─→ initQuery()
            │   └─ [Hook] onInitQuery
            │       └─ Start base query
            │
            ├─→ select()
            │   └─ Eager load relations
            │
            ├─→ applySearch()
            │   └─ Apply search filter from request
            │
            ├─→ applyFilters()
            │   └─ Apply field filters from request
            │
            ├─→ applySort()
            │   └─ Apply sorting from request
            │
            ├─→ applyModifiedQuery()
            │   └─ [Hook] onModifiedQuery
            │       └─ Custom query modifications
            │
            ├─→ applyPaginate()
            │   └─ Paginate results
            │
            ├─→ applyAccessors()
            │   └─ Transform field values
            │
            └─→ Response (data + pagination + filters)
```

### Update Flow

```
Request → update($id, Request)
    │
    ├─→ [Hook] onUpdate
    │       │
    │       └─→ processRequest()
    │
    ├─→ Find model by ID
    │   └─ Check if exists
    │
    ├─→ validateUpdateRequest()
    │   └─ Run field validation
    │
    └─→ UpdateService::execute($id, data)
        ├─ Fetch model
        ├─ Store original state (for diff)
        ├─ Fill attributes
        ├─ Save to database
        ├─ [Hook] onModelUpdated
        ├─ Log changes
        └─ Return updated model
            │
            └─→ Response (200 + updated model)
```

### Access Control Flow

```
HTTP Request
    │
    └─→ callAction() middleware
        │
        ├─→ [Access Control Check]
        │   │
        │   ├─ Check config: 'accessLevelControl' setting
        │   │
        │   ├─ Check controller: $accessControlEnabled property
        │   │
        │   ├─ Check hook: onAccessCheck callback
        │   │
        │   └─ Check permissions: user->permissions array
        │
        ├─ PASS (null/true/user has permission)
        │   │
        │   └─→ Execute controller action
        │
        └─ FAIL (false/permission denied)
            │
            └─→ Response 403 Forbidden
```

---

## 🏗️ Scaffolder System

### Scaffolder Lifecycle

```
┌─────────────────────────────────────────┐
│    UserScaffolder Instance Created      │
└──────────────┬──────────────────────────┘
               │
               ▼
    ┌─────────────────────────┐
    │  Calls setModuleName()  │
    │  Calls setModel()       │
    └──────────┬──────────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │  scaffold($page) Called      │
    │  e.g., scaffold('index')    │
    └──────────┬──────────────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │  Calls setFields()          │
    │  Process each field         │
    │  Apply page-specific rules  │
    └──────────┬──────────────────┘
               │
               ▼
    ┌─────────────────────────┐
    │  Return Scaffolder      │
    │  Ready for use          │
    └─────────────────────────┘
```

### Field Processing

```
Field::make('email')
    .email()              ← Set field type/rules
    .required()           ← Add validation
    .display('Email')     ← Set label
    .helpText('...')      ← Set helper
    │
    └─→ In form context:
        │
        ├─ Generate input element
        ├─ Set HTML attributes
        ├─ Show validation errors
        └─ Display helper text
        │
        └─→ In list context:
            │
            ├─ Generate column definition
            ├─ Add filtering capability
            ├─ Add sorting
            └─ Show data with accessor
```

---

## 📋 Field System

### BaseField Architecture

```php
abstract BaseField
{
    // Constructor
    __construct($name, $type)

    // Configuration Methods
    display($label)          // Field label
    nullable()              // Allow null values
    required()              // Field required
    default($value)         // Default value
    helpText($text)         // Helper text below field
    comment($text)          // Tooltip comment

    // Validation Methods
    email()                 // Email validation
    url()                   // URL validation
    min($length)            // Minimum length/value
    max($length)            // Maximum length/value
    unique($table, $col)    // Uniqueness validation
    regex($pattern)         // Regex pattern validation
    confirmed()             // Field_confirmation required
    custom($rule, $msg)     // Custom validation rule

    // Relation Methods
    relation($name, $field, $type, $primaryKey) // Define relation
    appends(...$columns)                         // Mark computed field dependencies

    // Data Methods
    dataSet($data)          // Dataset for select options
    dataKey($key)           // Data array key
    dataField($field)       // Data field name
    dataset()               // Get current dataset

    // Display Methods
    hidden()                // Hide from forms
    disabled()              // Disable input
    readonly()              // Read-only input
    class($css)             // CSS classes
    attributes($attrs)      // HTML attributes

    // Filtering Methods
    filterable()            // Make column filterable
    filterType($type)       // Filter type (equal, like, etc.)
    filter($filters)        // Filter definitions

    // Helper Methods
    fillValue($model)       // Extract value from model
    format($value)          // Format value for display
}
```

### Field Type Hierarchy

```
BaseField (abstract)
├── Text                      (single line text)
├── Email                     (email input)
├── Password                  (password input)
├── Number                    (numeric input)
├── Textarea                  (multi-line text)
├── Date                      (date input)
├── DateTime                  (datetime input)
├── Time                      (time input)
├── Select                    (select dropdown)
├── Checkbox                  (checkbox)
├── Radio                     (radio buttons)
├── File                      (file upload)
├── Image                     (image upload)
├── Json                      (JSON storage)
├── Toggle                    (boolean toggle)
├── RichText                  (rich text editor)
└── [Custom Fields]           (user-defined)

**Note**: All field types support relations via the `relation()` method for loading
and displaying data from related models (belongsTo, hasMany, belongsToMany)
```

### Working with Relations

Relations are defined on any field using the `relation()` method:

```php
public function relation(string $name, string $field = '', string $type = '', string $primaryKey = 'id'): static
{
    // Parameters:
    // $name        - Name of the relation method on the model
    // $field       - Which field to display from the related model
    // $type        - Relation type: 'belongsTo', 'hasMany', 'belongsToMany'
    // $primaryKey  - Primary key of the related model (default: 'id')
}
```

#### Relation Examples

```php
// Belongs-to relation using dot notation (field inferred)
Text::make('profile.name')
    ->display('Profile Name')
    ->relation('profile')  // 'name' field inferred from dot notation

// Belongs-to with explicit field
Text::make('category_id')
    ->display('Category')
    ->relation('category', 'name', 'belongsTo')

// Has-many relation
Text::make('items.product_name')
    ->display('Items')
    ->relation('items', 'product_name', 'hasMany')

// Many-to-many relation
Text::make('roles.name')
    ->display('Roles')
    ->relation('roles', 'name', 'belongsToMany')
    ->multiple()
```

#### How Relation Data Flows

```
fillValue() execution
    │
    ├─→ extractRawValue()      # Get value from model property
    │
    ├─→ extractRelationalValue()  # If relation defined:
    │   ├─ Call setRelationalValue()
    │   ├─ Fetch related model(s)
    │   ├─ Extract specified field
    │   └─ Return array or value
    │
    └─→ applyAccessor()        # Apply custom transformation if defined
```

---

## 🔧 Service Layer

### Service Architecture

```
BaseService (abstract)
├── StoreService          (create new records)
├── UpdateService         (update existing records)
└── DataService           (query building & filtering)
```

### StoreService Responsibilities

```php
class StoreService
{
    // 1. Validate input data
    function validate($data)

    // 2. Process/transform data
    function processData($data)

    // 3. Create model instance
    function createInstance($data)

    // 4. Handle relations
    function attachRelations($model, $data)

    // 5. Execute hooks
    function executeHooks($model)

    // 6. Save to database
    function save($model)

    // 7. Log operation
    function logOperation($model)
}
```

### DataService Query Building

```php
class DataService
{
    // Core query building methods
    private function initQuery()          // Start query
    private function select($query)       // Select columns
    private function getRelations($query) // Eager load relations
    private function applySearch($query)  // Text search
    private function applyFilters($query) // Field filters
    private function applySort($query)    // Sorting
    private function applyModifiedQuery() // Custom modifications
    private function applyPaginate()      // Pagination
    private function applyAccessors()     // Value transformation

    // Chaining
    public function getData($onlyTrashed) // Main entry point
}
```

---

## 🪝 Hook System

### Hook Architecture

```
BaseHooks trait
├── callHook(name, args)           # Call specific hook if defined
├── hasHook(name)                  # Check if hook exists
├── executeWithHook(name, default) # Execute hook or default
└── Hook Registration
    └── crudHookCallbacks array    # Storage for hooks
```

### Available Hooks

```php
// Lifecycle hooks
$this->onCrudInit($callback)           // Before CRUD initialization
$this->onSetup($callback)              // After setup() called

// Index hooks
$this->onBeforeIndexQuery($callback)   // Before index query execution
$this->onIndexData($callback)          // After index data retrieved
$this->onAfterIndexQuery($callback)    // After index data processing

// Create hooks
$this->onCreate($callback)             // Before create form display

// Store hooks
$this->onBeforeValidate($callback)     // Before validation
$this->onStore($callback)              // Replace entire store operation
$this->onAfterValidate($callback)      // After validation

// Edit hooks
$this->onEdit($callback)               // Before edit form display

// Update hooks
$this->onUpdate($callback)             // Replace entire update operation

// Destroy hooks
$this->onDestroy($callback)            // Replace destroy operation
$this->onBeforeDestroy($callback)      // Before delete
$this->onAfterDestroy($callback)       // After delete

// Access control hooks
$this->onAccessCheck($callback)        # Control access per method
```

### Hook Callback Signature

```php
// Default pattern: ($default, ...$args)
$this->onStore(function($next, $request) {
    // $next is the default/original behavior
    // $request is the HTTP request

    // Do something before
    $request->merge(['updated_by' => auth()->id()]);

    // Call the original behavior
    return $next($request);
});
```

---

## 🔐 Access Control System

### Access Control Flow

```
Request to CRUD action
    │
    ├─→ 1. Check Global Config
    │       config('crud.accessLevelControl')
    │       ├─ 'on'  → Check permissions
    │       └─ 'off' → Skip all checks
    │
    ├─→ 2. Check Controller Property
    │       $accessControlEnabled
    │       ├─ true  → Check permissions
    │       ├─ false → Allow access
    │       └─ null  → Use config setting
    │
    ├─→ 3. Check Access Hook
    │       onAccessCheck($method)
    │       ├─ true  → Check user permissions
    │       ├─ false → Skip access check (public)
    │       └─ null  → Continue to next check
    │
    ├─→ 4. Check User Permissions
    │       auth()->user()->permissions[$module][$method]
    │       ├─ true  → Allow access
    │       ├─ false → Deny access (403)
    │       └─ null  → Deny by default
    │
    └─→ 5. Result
        ├─ Access Allowed → Execute action
        └─ Access Denied  → 403 Forbidden Response
```

### Permission Structure

```php
// User permissions format
$user->permissions = [
    'users' => [
        'index'    => true,   // Can list users
        'show'     => true,   // Can view user details
        'create'   => true,   // Can create users
        'store'    => true,   // Can save new users
        'edit'     => true,   // Can view edit form
        'update'   => true,   // Can update users
        'destroy'  => false,  // Cannot delete users
        'restore'  => false,  // Cannot restore users
        'forceDelete' => false, // Cannot permanently delete
    ],
    'products' => [
        'index'    => true,
        // ... other permissions
    ],
];
```

### Access Control Class

```php
class Access
{
    /**
     * Check if user can perform action
     */
    static function check($user, $module, $action): bool
    {
        // 1. Check if access control is enabled
        // 2. Check user permissions structure
        // 3. Return allow/deny decision
    }

    /**
     * Get available actions for user
     */
    static function getAvailableActions($user, $module): array
    {
        // Return only actions user has permission for
    }
}
```

---

## 🗄️ Database Adapter Pattern

### Adapter Interface

```php
interface DatabaseAdapterInterface
{
    /**
     * Get SQL representation of query
     */
    public function getSql(Builder $query): string;

    /**
     * Get database type
     */
    public function getDatabaseType(): string;

    /**
     * Format value for database
     */
    public function formatValue($value, $type): mixed;

    /**
     * Handle database-specific operations
     */
    public function execute(string $operation, ...$args): mixed;
}
```

### Adapter Factory

```php
class DatabaseAdapterFactory
{
    /**
     * Create appropriate adapter based on connection
     */
    static function create(Connection $connection): DatabaseAdapterInterface
    {
        // Detect driver and instantiate correct adapter
        match($connection->getDriverName()) {
            'mysql'      => new MySQLAdapter($connection),
            'pgsql'      => new PostgreSQLAdapter($connection),
            'sqlite'     => new SQLiteAdapter($connection),
            'sqlserver'  => new SqlServerAdapter($connection),
            default      => throw new Exception('Unsupported database'),
        };
    }
}
```

### Supported Adapters

```
DatabaseAdapter
├── MySQLAdapter          (for MySQL/MariaDB)
├── PostgreSQLAdapter     (for PostgreSQL)
├── SQLiteAdapter         (for SQLite)
├── SqlServerAdapter      (for SQL Server)
└── [Custom Adapters]     (user-defined)
```

---

## 📦 Module Management

### Module System

```php
abstract BaseModule
{
    // Define module metadata
    abstract function name(): string
    abstract function description(): string
    abstract function controllers(): array

    // Optional configurations
    public function routes(): array      // Custom routes
    public function migrations(): array  // Database migrations
    public function seeders(): array     // Database seeders
    public function assets(): array      // JS/CSS assets
    public function permissions(): array // Default permissions
}
```

### Module Registration

```
Service Provider
    │
    └─→ CrudServiceProvider::boot()
        │
        ├─→ Discover all modules
        │
        ├─→ Register routes
        │
        ├─→ Register menu items
        │
        └─→ Register migrations
```

### Module Structure

```
app/Modules/
├── Users/
│   ├── Controllers/
│   │   └── UserController.php
│   ├── Models/
│   │   └── User.php
│   ├── Scaffolders/
│   │   └── UserScaffolder.php
│   ├── Migrations/
│   ├── Seeders/
│   ├── UserModule.php
│   └── routes.php
│
├── Products/
│   └── [similar structure]
│
└── Reports/
    └── [similar structure]
```

---

## 🔄 Request Processing Pipeline

### ProcessRequest Trait

```php
trait ProcessRequest
{
    /**
     * Process incoming request data
     */
    protected function processRequest(Request $request): Request
    {
        // 1. Extract field data
        $data = $this->extractFieldData($request);

        // 2. Type casting
        $data = $this->castFieldTypes($data);

        // 3. Relation handling
        $data = $this->processRelations($data);

        // 4. Custom processing
        $data = $this->callHook('onProcessRequest', $data);

        // 5. Return processed request
        return $request->merge($data);
    }

    private function extractFieldData($request)
    {
        // Extract only fields defined in scaffolder
        $fields = $this->scaffolder()->fields();
        $data = [];

        foreach($fields as $field) {
            if($request->has($field->name)) {
                $data[$field->name] = $request->input($field->name);
            }
        }

        return $data;
    }

    private function castFieldTypes($data)
    {
        // Cast to appropriate PHP types
        // string -> int, string -> boolean, etc.
    }

    private function processRelations($data)
    {
        // Handle relation data specially
        // belongsToMany needs pivot data, etc.
    }
}
```

---

## ⚙️ Configuration System

### Configuration File Structure

```php
// config/crud.php

return [
    // Enable/disable access control globally
    'accessLevelControl' => env('CRUD_ACCESS_LEVEL_CONTROL', 'on'),

    // Middleware stack applied to CRUD routes
    'middlewares' => env('CRUD_MIDDLEWARES', ['auth:sanctum']),

    // Custom access control class
    'access_class' => \Tir\Crud\Support\Acl\Access::class,

    // Enable operation logging
    'enable_logging' => env('CRUD_ENABLE_LOGGING', false),

    // Default pagination per page
    'per_page' => env('CRUD_PER_PAGE', 15),

    // Field-specific configurations
    'fields' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['jpg', 'png', 'pdf'],
    ],

    // Response configuration
    'response' => [
        'include_pagination' => true,
        'include_filters' => true,
        'include_metadata' => true,
    ],
];
```

### Environment Variables

```bash
# Access control
CRUD_ACCESS_LEVEL_CONTROL=on

# Middleware
CRUD_MIDDLEWARES=auth:sanctum,admin

# Logging
CRUD_ENABLE_LOGGING=false

# Pagination
CRUD_PER_PAGE=15
```

---

## 🧪 Testing Architecture

### Test Structure

```
tests/
├── Unit/
│   ├── Fields/                      # Field system tests
│   │   ├── BaseField/
│   │   │   ├── BaseFieldTest.php
│   │   │   ├── FieldDataAndFilterTest.php
│   │   │   ├── FieldFillValueStep1Test.php
│   │   │   ├── FieldFillValueStep2Test.php
│   │   │   └── FieldFillValueStep3Test.php
│   │   └── [FieldTypeTests]
│   └── Services/                    # Service layer tests
│
├── Integration/
│   ├── Controllers/                 # Controller integration
│   │   ├── CrudEndpointsTest.php
│   │   ├── CrudBusinessLogicTest.php
│   │   └── CrudIntegrationTest.php
│   └── Scaffolders/                 # Scaffolder tests
│       └── FieldIntegrationTest.php
│
└── Feature/
    └── [Feature tests for complete workflows]
```

### TDD Approach

The framework uses step-by-step testing for complex methods:

```php
// Example: fillValue() method has 3 steps
// Step 1: Extract raw value
FieldFillValueStep1Test::class

// Step 2: Process relations
FieldFillValueStep2Test::class

// Step 3: Apply accessors
FieldFillValueStep3Test::class

// Integration: All steps together
FieldFillValueTest::class
```

### Testing Utilities

```php
class TestCase extends \Orchestra\Testbench\TestCase
{
    // Test scaffolder setup
    protected function createTestScaffolder()
    {
        return new class extends BaseScaffolder {
            protected function setModuleName() { return 'test'; }
            protected function setModel() { return TestModel::class; }
            protected function setFields() { return []; }
        };
    }

    // Test model creation
    protected function createTestModel()
    {
        return TestModel::factory()->create();
    }

    // Test database queries
    protected function assertQueryCount($count, $callback)
    {
        // Assert exact number of database queries
    }
}
```

---

## 🔌 Extension Points

### How to Extend the Framework

#### 1. Custom Field Types

```php
class CustomField extends BaseField
{
    public function __construct($name)
    {
        parent::__construct($name, 'custom');
    }

    public function format($value): string
    {
        // Custom formatting logic
    }
}
```

#### 2. Custom Services

```php
class CustomService extends BaseService
{
    public function execute($data)
    {
        // Custom business logic
    }
}
```

#### 3. Custom Middleware

```php
class CustomCrudMiddleware
{
    public function handle($request, Closure $next)
    {
        // Custom request handling
        return $next($request);
    }
}
```

#### 4. Custom Hooks

```php
protected function setup()
{
    $this->onStore(function($next, $request) {
        // Custom store logic
        return $next($request);
    });
}
```

---

## 📈 Performance Considerations

### Query Optimization

1. **Eager Loading**: Relations are eager-loaded in DataService
2. **Selective Columns**: Only required columns selected
3. **Indexing**: Database indexes recommended on foreign keys
4. **Pagination**: Results are always paginated to limit data size

### Caching Opportunities

1. **Scaffold Caching**: Cache scaffolder definitions
2. **Field Definitions**: Cache field metadata
3. **Permission Caching**: Cache user permissions

### Best Practices

- Use pagination on large datasets
- Define indexes on foreign key columns
- Eager load relations to avoid N+1 queries
- Use DataService hooks to optimize queries

---

## 🚀 Future Enhancements

Potential improvements for the framework:

1. **Caching Layer**: Add caching for scaffolder definitions
2. **Batch Operations**: Support bulk insert/update/delete
3. **API Versioning**: Built-in API version management
4. **Webhooks**: Trigger webhooks on CRUD events
5. **Audit Logging**: Comprehensive audit trail system
6. **Advanced Filtering**: Support for complex filter expressions
7. **Export/Import**: Built-in data export/import functionality
8. **Soft Tenancy**: Multi-tenant support

---

## 📚 Related Documentation

- [README - Comprehensive Usage Guide](README_COMPREHENSIVE.md)
- [Access Control - Detailed Permissions](docs/ACCESS_CONTROL.md)
- [Quick Reference - Fast Lookup](docs/QUICK_REFERENCE.md)
- [Testing Documentation](TESTING.md)

