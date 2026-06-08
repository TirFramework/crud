# Tir/CRUD Framework - Comprehensive Documentation

**Version**: 1.0.0  
**License**: MIT  
**Author**: Tirdad Abbasi  
**Framework**: Laravel 12.0+  
**Last Updated**: June 2026

---

## 📚 Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Installation & Setup](#installation--setup)
4. [Quick Start](#quick-start)
5. [Architecture](#architecture)
6. [Core Concepts](#core-concepts)
7. [Usage Guide](#usage-guide)
8. [Advanced Features](#advanced-features)
9. [Testing](#testing)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

**Tir/CRUD** is a powerful Laravel CRUD scaffolding framework that automates the creation of complete CRUD (Create, Read, Update, Delete) operations. It provides a trait-based architecture enabling developers to build feature-rich admin panels and data management systems with minimal boilerplate code.

### Why Tir/CRUD?

- **Time-Saving**: Auto-generate complete CRUD operations from simple configurations
- **Type-Safe**: Strongly typed with Laravel's latest features
- **Extensible**: Hook system allows customization at every stage
- **Secure**: Built-in access control and permission management
- **Flexible**: Support for multiple databases and custom business logic
- **Tested**: Comprehensive test suite with 170+ tests

---

## ✨ Features

### Core CRUD Operations
- ✅ **Index** - List records with filtering, searching, and pagination
- ✅ **Show** - Display detailed record view
- ✅ **Create** - Display creation form
- ✅ **Store** - Save new records with validation
- ✅ **Edit** - Display edit form
- ✅ **Update** - Update existing records
- ✅ **Destroy** - Soft delete records
- ✅ **Trash** - List soft-deleted records
- ✅ **Restore** - Restore soft-deleted records
- ✅ **Force Delete** - Permanently delete records

### Advanced Features
- **Scaffolder System**: Configuration-driven metadata management
- **Field System**: Flexible form/table field definitions with validation
- **Hook System**: Extensible action callbacks for custom logic
- **Access Control**: Scope-based and method-level permission control
- **Service Layer**: Separated business logic from controllers
- **Database Adapters**: Support for multiple database systems
- **Module Management**: Organized module structure with admin menus
- **Audit Logging**: Track all CRUD operations with user context
- **Multi-Language Support**: Built-in translations (English, German, Farsi)

---

## 💾 Installation & Setup

### 1. Install Package

```bash
composer require tir/crud
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Tir\Crud\CrudServiceProvider"
```

This creates `config/crud.php` with default settings.

### 3. Configuration

Edit `config/crud.php`:

```php
return [
    // Middleware applied to CRUD routes
    'middlewares' => env('CRUD_MIDDLEWARES', ['auth:sanctum']),

    // Access control level (on/off)
    'accessLevelControl' => env('CRUD_ACCESS_LEVEL_CONTROL', 'on'),

    // Custom access control class
    'access_class' => \Tir\Crud\Support\Acl\Access::class,

    // Enable operation logging
    'enable_logging' => env('CRUD_ENABLE_LOGGING', false)
];
```

---

## 🚀 Quick Start

### Step 1: Create a Scaffolder

Create a scaffolder class that defines CRUD configuration for a model:

```php
<?php

namespace App\Scaffolders;

use App\Models\User;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Email;
use Tir\Crud\Support\Scaffold\Fields\Relation;

class UserScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'users';
    }

    protected function setModel(): string
    {
        return User::class;
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')
                ->display('Name')
                ->required()
                ->max(255),

            Email::make('email')
                ->display('Email')
                ->required()
                ->unique(),

            Text::make('phone')
                ->display('Phone')
                ->nullable(),

            Text::make('role.name')
                ->display('Role')
                ->relation('role', 'name', 'belongsTo')
                ->required(),
        ];
    }
}
```

### Step 2: Create a Controller

Create a controller extending `CrudController`:

```php
<?php

namespace App\Http\Controllers;

use App\Scaffolders\UserScaffolder;
use Tir\Crud\Controllers\CrudController;

class UserController extends CrudController
{
    protected function setScaffolder(): string
    {
        return UserScaffolder::class;
    }
}
```

### Step 3: Register Routes

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::resource('users', UserController::class);
});
```

### Step 4: You're Done! ✨

Your controller now has:
- `GET /admin/users` - List all users
- `GET /admin/users/{id}` - Show user details
- `GET /admin/users/create` - Show creation form
- `POST /admin/users` - Store new user
- `GET /admin/users/{id}/edit` - Show edit form
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete (soft)
- `GET /admin/users/trash` - List soft-deleted users
- Plus more...

---

## 🏗️ Architecture

### Component Hierarchy

```
CrudController (abstract)
    ↓
Crud Trait (composes all CRUD traits)
    ├── CrudInit (initialization)
    ├── Index (listing with filtering)
    ├── Show (detail view)
    ├── Create (creation form)
    ├── Store (save operation)
    ├── Edit (edit form)
    ├── Update (update operation)
    ├── Destroy (soft delete)
    ├── Trash (list deleted)
    ├── Restore (restore deleted)
    └── ForceDelete (permanent delete)
```

### Data Flow

```
Request
    ↓
Controller Action (e.g., store())
    ↓
Hook System (onStore hook if defined)
    ↓
Service Class (StoreService, UpdateService)
    ↓
Model Operation (create, update, delete)
    ↓
Access Control (permission check)
    ↓
Response
```

### Layer Structure

```
Controllers/          ← HTTP request handling, routing
    ↓
Services/            ← Business logic separation
    ↓
Models/              ← Database interaction
    ↓
Database/            ← Adapter pattern for DB compatibility
```

---

## 🔑 Core Concepts

### 1. **Scaffolder**

A scaffolder is a configuration class that defines CRUD metadata for a model:

```php
class ProductScaffolder extends BaseScaffolder
{
    // Define module name (used in routes, permissions)
    protected function setModuleName(): string
    {
        return 'products';
    }

    // Define the Eloquent model class
    protected function setModel(): string
    {
        return Product::class;
    }

    // Define available fields
    protected function setFields(): array
    {
        return [
            Text::make('name'),
            Textarea::make('description'),
            Number::make('price'),
            Select::make('category_id'),
        ];
    }

    // Define available actions (optional)
    protected function setActions(): array
    {
        return [
            'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'
        ];
    }

    // Define custom buttons (optional)
    protected function setButtons(): array
    {
        return [
            Button::make('submit')->display('Save'),
            Button::make('export')->display('Export to CSV'),
        ];
    }
}
```

### 2. **Fields**

Fields define form inputs and table columns:

```php
// Text field with validation
Text::make('name')
    ->display('Full Name')
    ->required()
    ->max(255)
    ->helpText('Enter customer name');

// Email field with unique validation
Email::make('email')
    ->display('Email Address')
    ->required()
    ->unique('users', 'email');

// Select field with relation
Select::make('status')
    ->display('Status')
    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
    ->default('active');

// Relation field (using dot notation with relation() method)
Text::make('department.name')
    ->display('Department')
    ->relation('department', 'name', 'belongsTo') // Show 'name' field from related model
    ->required();

// Date field with formatting
Date::make('birth_date')
    ->display('Date of Birth')
    ->format('Y-m-d')
    ->nullable();

// Filterable field
Text::make('reference_code')
    ->display('Reference')
    ->filterable()
    ->filterType(FilterType::EQUAL);
```

### 3. **Access Control**

Control who can perform which CRUD actions:

```php
class UserController extends CrudController
{
    protected function setScaffolder(): string
    {
        return UserScaffolder::class;
    }

    protected function setup()
    {
        // Method-level access control
        $this->onAccessCheck(function($method) {
            // Make index and show public
            if (in_array($method, ['index', 'show'])) {
                return false;
            }

            // Require admin for destructive operations
            if (in_array($method, ['destroy', 'forceDelete'])) {
                return auth()->user()?->isAdmin() ?? false;
            }

            // Default: check user permissions
            return null;
        });
    }
}
```

User permissions structure:

```php
$user->permissions = [
    'users' => [
        'index' => true,
        'show' => true,
        'create' => false,
        'edit' => true,
        'destroy' => false,
    ],
    'products' => [
        'index' => true,
        'create' => true,
    ],
];
```

### 4. **Hooks System**

Hooks allow you to intercept and modify CRUD operations:

```php
protected function setup()
{
    // Before storing a record
    $this->onStore(function($next, $request) {
        // Custom validation
        if ($request->input('email_verified') && !$request->input('email')) {
            return response()->json(['error' => 'Email required'], 422);
        }

        // Call the actual store operation
        return $next($request);
    });

    // Before updating a record
    $this->onUpdate(function($next, $id, $request) {
        // Log the change
        Log::info("User {$id} being updated by " . auth()->id());

        // Call the actual update operation
        return $next($id, $request);
    });

    // Before deleting a record
    $this->onDestroy(function($next, $id) {
        // Check if user has permission to delete this specific record
        $user = User::find($id);
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Cannot delete admin users'], 403);
        }

        return $next($id);
    });

    // After index data is retrieved
    $this->onIndexData(function($next) {
        $data = $next();
        // Transform data before returning
        return $data;
    });
}
```

### 5. **Services**

Services contain business logic separated from controllers:

```php
// StoreService - handles record creation with validation
$service = new StoreService($scaffolder, $model);
$model = $service->execute($validatedData);

// UpdateService - handles record updates
$service = new UpdateService($scaffolder, $model);
$updated = $service->execute($id, $validatedData);

// DataService - handles querying with filters, search, sorting
$service = new DataService($scaffolder, $model);
$query = $service->getData($onlyTrashed = false);
$results = $query->paginate(15);
```

---

## 📖 Usage Guide

### Working with Forms

#### Create/Edit Forms

Forms are automatically generated from fields in the scaffolder:

```php
// Controller automatically provides form data via create() and edit() actions
// Frontend receives:
{
    "fields": [
        {
            "name": "name",
            "type": "text",
            "display": "Full Name",
            "required": true,
            "validation": { "maxLength": 255 },
            "value": null // populated in edit()
        },
        ...
    ],
    "buttons": [
        { "name": "submit", "display": "Save", "action": "Submit" },
        { "name": "back", "display": "Back", "action": "Cancel" }
    ]
}
```

### Working with Lists

#### Index with Filtering and Searching

```php
// GET /admin/users?search=john&status=active&sort=-created_at&page=1

// Response includes:
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "status": "active"
        },
        ...
    ],
    "pagination": {
        "total": 42,
        "per_page": 15,
        "current_page": 1,
        "last_page": 3
    },
    "filters": [
        {
            "field": "status",
            "type": "select",
            "options": ["active", "inactive"]
        }
    ]
}
```

### Working with Relations

#### One-to-Many Relations

For one-to-many relations, define fields from the related model using dot notation:

```php
class OrderScaffolder extends BaseScaffolder
{
    protected function setFields(): array
    {
        return [
            Text::make('order_number')->required(),
            Text::make('items.product_name')
                ->display('Product')
                ->relation('items', 'product_name', 'hasMany'),
        ];
    }
}
```

#### Many-to-Many Relations

```php
Text::make('roles.name')
    ->display('Assigned Roles')
    ->relation('roles', 'name', 'belongsToMany')
    ->multiple(),
```

### Working with Validation

```php
class UserScaffolder extends BaseScaffolder
{
    protected function setFields(): array
    {
        return [
            Text::make('username')
                ->required()
                ->unique('users', 'username') // Unique validation
                ->min(3)
                ->max(50)
                ->regex('/^[a-zA-Z0-9_]+$/', 'Only letters, numbers, and underscore'),

            Email::make('email')
                ->required()
                ->email()
                ->unique('users', 'email'),

            Password::make('password')
                ->required()
                ->confirmed() // password_confirmation field required
                ->min(8)
                ->regex('/[A-Z]/', 'Must contain uppercase')
                ->regex('/[0-9]/', 'Must contain number'),
        ];
    }
}
```

---

## 🔧 Advanced Features

### Custom Field Types

Create custom fields by extending `BaseField`:

```php
<?php

namespace App\Fields;

use Tir\Crud\Support\Scaffold\Fields\BaseField;

class ColorPickerField extends BaseField
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->type = 'colorpicker';
        $this->valueType = 'string';
    }

    public function format($value): string
    {
        return $value ?? '#000000';
    }
}

// Usage
ColorPickerField::make('brand_color')
    ->display('Brand Color')
    ->default('#000000'),
```

### Database Adapters

Support for multiple database systems:

```php
// Automatic adapter detection based on connection type
$adapter = DatabaseAdapterFactory::create($model->getConnection());

// Supported databases:
// - MySQL (MySQLAdapter)
// - PostgreSQL (PostgreSQLAdapter)
// - SQLite (SQLiteAdapter)
// - Custom adapters can be registered
```

### Module Management

Organize controllers into modules:

```php
class Module extends BaseModule
{
    public function name(): string
    {
        return 'users';
    }

    public function controllers(): array
    {
        return [
            UserController::class,
            RoleController::class,
            PermissionController::class,
        ];
    }
}

// Modules are auto-registered and appear in admin menu
```

### Audit Logging

Track all CRUD operations:

```php
// Enable in config/crud.php
'enable_logging' => true,

// Log entries include:
{
    "user_id": 5,
    "model": "App\\Models\\User",
    "model_id": 123,
    "action": "UPDATE",
    "changes": {
        "status": { "old": "active", "new": "inactive" }
    },
    "timestamp": "2026-06-08T10:30:00Z"
}
```

---

## 🧪 Testing

### Running Tests

```bash
# Run all tests with coverage
./test-docker.sh

# Interactive debugging shell
./test-docker.sh interactive

# Clean up Docker resources
./test-docker.sh clean
```

### Test Coverage

- **170+ Tests** with **704 assertions**
- **18.42% Line Coverage** of core framework
- **Integration Tests** for complete workflows
- **Unit Tests** for individual components

### Writing Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Scaffolders\UserScaffolder;

class UserCrudTest extends TestCase
{
    public function test_can_list_users()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'pagination']);
    }

    public function test_can_create_user()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }
}
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. **403 Forbidden Errors**

**Problem**: CRUD actions return 403 permission denied.

**Solution**:
```php
// Check user permissions structure
auth()->user()->permissions; // Should be array with module/action keys

// Or disable access control for testing
protected $accessControlEnabled = false;
```

#### 2. **Validation Errors Not Showing**

**Problem**: Form submission fails silently.

**Solution**:
```php
// Ensure field validation is set correctly
Text::make('email')
    ->email()  // Must call email() method
    ->required(); // Must explicitly set required

// Check response format
// Validation errors returned as:
{ "email": ["The email field must be a valid email"] }
```

#### 3. **Relations Not Loading**

**Problem**: Related models don't appear in forms.

**Solution**:
```php
// Ensure relation is properly defined in scaffolder using dot notation
Text::make('category.name')
    ->display('Category')
    ->relation('category', 'name', 'belongsTo')

// Verify model relationship exists with correct name
// In User model, method name must match relation() first parameter:
public function category()  // Must match ->relation('category')
{
    return $this->belongsTo(Category::class);
}
```

#### 4. **Routes Not Registered**

**Problem**: CRUD endpoints return 404.

**Solution**:
```php
// Ensure routes are registered properly
Route::resource('users', UserController::class);

// Check route list
php artisan route:list | grep users

// Verify service provider is registered in config/app.php
'providers' => [
    ...
    \Tir\Crud\CrudServiceProvider::class,
]
```

---

## 📚 Additional Resources

- [System Design Documentation](SYSTEM_DESIGN.md)
- [Access Control Guide](docs/ACCESS_CONTROL.md)
- [Testing Documentation](TESTING.md)
- [Quick Reference](docs/QUICK_REFERENCE.md)

---

## 📝 License

MIT License - see LICENSE file for details

## 👨‍💻 Contributing

Contributions welcome! Please ensure tests pass:

```bash
./test-docker.sh
```

---

## 📧 Support

For issues and questions:
- GitHub Issues: [GitHub Issues](https://github.com/tirdad/crud)
- Email: abbasi.tirdad@gmail.com

