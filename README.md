# Tir/CRUD - Laravel CRUD Scaffolding Framework

**Version**: 1.0.0 | **License**: MIT | **Framework**: Laravel 12.0+

A powerful, trait-based CRUD scaffolding framework that automates complete CRUD operations for Laravel applications with minimal boilerplate code.

## 🎯 Quick Overview

Tir/CRUD enables rapid development of admin panels and data management systems by:
- ✅ Auto-generating complete CRUD operations from simple configurations
- ✅ Providing flexible field system with validation and relations
- ✅ Managing access control at method and UI levels
- ✅ Supporting multiple databases through adapter pattern
- ✅ Offering extensible hook system for customization
- ✅ Including comprehensive testing suite with 170+ tests

## ✨ Features

### Core CRUD Operations
- **Index** - List records with filtering, searching, and pagination
- **Show** - Display detailed record view
- **Create** - Display creation form
- **Store** - Save new records with validation
- **Edit** - Display edit form
- **Update** - Update existing records
- **Destroy** - Soft delete records
- **Trash** - List soft-deleted records
- **Restore** - Restore soft-deleted records
- **Force Delete** - Permanently delete records

### Advanced Features
- **Scaffolder System** - Configuration-driven metadata management
- **Field System** - 15+ field types with validation and relations
- **Hook System** - Pre/post action callbacks for custom logic
- **Access Control** - Scope-based and method-level permissions
- **Service Layer** - Separated business logic from controllers
- **Database Adapters** - MySQL, PostgreSQL, SQLite, SQL Server support
- **Module Management** - Organized module structure with admin menus
- **Audit Logging** - Track all CRUD operations with user context
- **Multi-Language Support** - English, German, Farsi translations

## 🚀 Quick Start

### 1. Install Package

```bash
composer require tir/crud
```

### 2. Create a Scaffolder

Define CRUD configuration for your model:

```php
<?php

namespace App\Scaffolders;

use App\Models\User;
use Tir\Crud\Support\Scaffold\BaseScaffolder;

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
            Text::make('name')->required(),
            Email::make('email')->required()->unique(),
            Text::make('phone')->nullable(),
        ];
    }
}
```

### 3. Create a Controller

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

### 4. Register Routes

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('users', UserController::class);
});
```

That's it! Your controller now has:
- `GET /users` - List all users
- `GET /users/{id}` - Show user details
- `POST /users` - Create new user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- Plus more...

## 📚 Documentation

### For Users & Developers

| Document | Purpose |
|----------|---------|
| **[docs/README_COMPREHENSIVE.md](docs/README_COMPREHENSIVE.md)** | Complete usage guide with quick start, features, examples, and API reference |
| **[docs/ARCHITECTURE_OVERVIEW.md](docs/ARCHITECTURE_OVERVIEW.md)** | Visual decision trees, quick reference, debugging guide, and common patterns |
| **[docs/QUICK_REFERENCE.md](docs/QUICK_REFERENCE.md)** | Fast lookup for common tasks and configuration |

### For System Design & Architecture

| Document | Purpose |
|----------|---------|
| **[docs/SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md)** | Deep architectural documentation, component design, data flows, and extension points |
| **[docs/ACCESS_CONTROL.md](docs/ACCESS_CONTROL.md)** | Comprehensive guide to the access control and permission system |
| **[TESTING.md](TESTING.md)** | Testing strategies, TDD approach, and test suite structure |

### Quick Navigation

- 🚀 **Getting Started?** → Start with [docs/README_COMPREHENSIVE.md](docs/README_COMPREHENSIVE.md#quick-start)
- 🏗️ **Understanding Architecture?** → Read [docs/SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md)
- 🔍 **Looking for Examples?** → Check [docs/ARCHITECTURE_OVERVIEW.md](docs/ARCHITECTURE_OVERVIEW.md#common-patterns)
- 🔐 **Setting up Permissions?** → See [docs/ACCESS_CONTROL.md](docs/ACCESS_CONTROL.md)
- ⚡ **Quick Lookup?** → Use [docs/QUICK_REFERENCE.md](docs/QUICK_REFERENCE.md)
- 🧪 **Writing Tests?** → Review [TESTING.md](TESTING.md)

---

## 🧪 Testing

The package includes an optimized Docker testing environment with automatic code coverage:

```bash
# Run tests with coverage (recommended)
./test-docker.sh

# Interactive debugging shell
./test-docker.sh interactive

# Clean up Docker resources
./test-docker.sh clean
```

### Coverage Reports

Tests automatically generate comprehensive coverage reports:
- **HTML**: `coverage/html/index.html` - Interactive browsable coverage
- **XML**: `coverage/clover.xml` - For CI/CD integration

### Performance

The Docker setup uses a pre-built image with all dependencies for fast test execution (~1.85 seconds total time).

### Test Results

- **170 tests** with **704 assertions**
- **18.42% line coverage** (344/1868 lines)
- **10.91% method coverage** (37/339 methods)

---

## 🏗️ Core Concepts

### Scaffolder
Defines CRUD metadata for a model (fields, actions, buttons)

### Fields
Form/table field definitions with validation, relations, and formatting

### Hooks
Pre/post action callbacks for extending CRUD operations

### Access Control
Scope-based and method-level permission management

### Services
Business logic separation from controllers (Store, Update, Data services)

---

## 💡 Common Use Cases

### Admin Panel
Complete CRUD interface with filters, search, and pagination

### API Backend
RESTful CRUD endpoints with automatic validation

### Data Management
Complex forms with relations and custom business logic

### Multi-tenant Applications
Scope-based access control per tenant

---

## 🔌 Extension Points

- **Custom Fields** - Extend BaseField for domain-specific inputs
- **Custom Hooks** - Intercept and modify any CRUD operation
- **Custom Services** - Replace business logic services
- **Database Adapters** - Add support for new database systems
- **Middleware** - Add custom request processing

---

## 🐛 Troubleshooting

**403 Forbidden?** → Check access control configuration  
**Validation errors?** → Ensure field validation is set correctly  
**Relations not loading?** → Verify model relationship exists  
**Routes not found?** → Check route registration  

See [docs/ARCHITECTURE_OVERVIEW.md - Debugging Checklist](docs/ARCHITECTURE_OVERVIEW.md#debugging-checklist) for detailed solutions.

---

## 📦 Requirements

- Laravel 12.0+
- PHP 8.1+
- Composer

---

## 🔗 Quick Links

| Resource | Link |
|----------|------|
| **Main README** | [README.md](README.md) |
| **Comprehensive Guide** | [docs/README_COMPREHENSIVE.md](docs/README_COMPREHENSIVE.md) |
| **System Design** | [docs/SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md) |
| **Architecture Overview** | [docs/ARCHITECTURE_OVERVIEW.md](docs/ARCHITECTURE_OVERVIEW.md) |
| **Access Control** | [docs/ACCESS_CONTROL.md](docs/ACCESS_CONTROL.md) |
| **Quick Reference** | [docs/QUICK_REFERENCE.md](docs/QUICK_REFERENCE.md) |
| **Testing** | [TESTING.md](TESTING.md) |

---

## 📄 License

MIT License - see LICENSE file for details

## 👨‍💻 Contributing

Contributions are welcome! Please ensure tests pass:

```bash
./test-docker.sh
```

## 📧 Support

- **GitHub Issues** - Report bugs or request features
- **Documentation** - See comprehensive docs above
- **Examples** - Check architecture overview for patterns

---

## 🙏 Credits

Created by Tirdad Abbasi  
Email: abbasi.tirdad@gmail.com

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent ORM Guide](https://laravel.com/docs/eloquent)
- [Testing Laravel Applications](https://laravel.com/docs/testing)
