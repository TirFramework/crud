# Artisan Command: crud:make

The `crud:make` command is a powerful code generator that scaffolds a complete CRUD module (Model, Controller, and Scaffolder) with a single command.

## Overview

The command is **dynamically configurable** and works with any Laravel app structure—it's not tied to specific folder layouts like panels or panels-based architectures. You can customize where the files are generated and what namespaces they use.

## Installation

The command is automatically registered when the Tir/CRUD package is installed. No additional setup is needed.

## Basic Usage

```bash
php artisan crud:make {name}
```

### Example

```bash
php artisan crud:make Product
```

This generates:
- `App\Models\Product` → `app/Models/Product.php`
- `App\Http\Controllers\ProductController` → `app/Http/Controllers/ProductController.php`
- `App\Scaffolders\ProductScaffolder` → `app/Scaffolders/ProductScaffolder.php`

## Advanced Usage with Options

All paths are relative to `app_path()` and all namespaces start from `App\` by default.

### Options

| Option | Default | Description |
|--------|---------|-------------|
| `--model-path` | `Models` | Directory path for Model files |
| `--controller-path` | `Http/Controllers` | Directory path for Controller files |
| `--scaffolder-path` | `Scaffolders` | Directory path for Scaffolder files |
| `--model-namespace` | `App\Models` | Full namespace for Model class |
| `--controller-namespace` | `App\Http\Controllers` | Full namespace for Controller class |
| `--scaffolder-namespace` | `App\Scaffolders` | Full namespace for Scaffolder class |

### Examples

#### Example 1: API Controllers in Separate Directory

```bash
php artisan crud:make Invoice \
  --controller-path="Http/Controllers/Api" \
  --controller-namespace="App\Http\Controllers\Api"
```

Generates:
- `app/Models/Invoice.php` → `App\Models\Invoice`
- `app/Http/Controllers/Api/InvoiceController.php` → `App\Http\Controllers\Api\InvoiceController`
- `app/Scaffolders/InvoiceScaffolder.php` → `App\Scaffolders\InvoiceScaffolder`

#### Example 2: Panel-Based Architecture

```bash
php artisan crud:make User \
  --controller-path="Panels/Admin/Controllers" \
  --controller-namespace="App\Panels\Admin\Controllers" \
  --scaffolder-path="Panels/Admin/Scaffolders" \
  --scaffolder-namespace="App\Panels\Admin\Scaffolders"
```

Generates:
- `app/Models/User.php` → `App\Models\User`
- `app/Panels/Admin/Controllers/UserController.php` → `App\Panels\Admin\Controllers\UserController`
- `app/Panels/Admin/Scaffolders/UserScaffolder.php` → `App\Panels\Admin\Scaffolders\UserScaffolder`

#### Example 3: Multi-Tenant Setup

```bash
php artisan crud:make Tenant \
  --model-path="Models/Tenants" \
  --model-namespace="App\Models\Tenants" \
  --controller-path="Http/Controllers/Tenants" \
  --controller-namespace="App\Http\Controllers\Tenants" \
  --scaffolder-path="Scaffolders/Tenants" \
  --scaffolder-namespace="App\Scaffolders\Tenants"
```

## Generated Files

### Model

The generated model extends Laravel's base `Model`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [];

    protected $casts = [];
}
```

**Next steps:**
- Add database columns to the migration
- Define `$fillable` attributes for mass assignment
- Add relationships and scopes
- Add any custom methods

### Controller

The generated controller extends `CrudController`:

```php
<?php

namespace App\Http\Controllers;

use App\Scaffolders\ProductScaffolder;
use Tir\Crud\Controllers\CrudController;

class ProductController extends CrudController
{
    protected function setScaffolder(): string
    {
        return ProductScaffolder::class;
    }
}
```

**Features included:**
- All CRUD actions (index, show, create, store, edit, update, destroy)
- Automatic validation
- Access control support
- Hook system for extending functionality

### Scaffolder

The generated scaffolder defines the CRUD interface:

```php
<?php

namespace App\Scaffolders;

use App\Models\Product;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Enums\ActionType;
use Tir\Crud\Support\Scaffold\Actions;

class ProductScaffolder extends BaseScaffolder
{
    protected function setModel(): string
    {
        return Product::class;
    }

    protected function setModuleName(): string
    {
        return 'product';
    }

    protected function setModuleTitle(): string
    {
        return trans('modules.product', 'Product');
    }

    protected function setActions(): array
    {
        return Actions::all();
    }

    protected function setFields(): array
    {
        return [
            // Add your fields here
            // Text::make('name')->required(),
            // Text::make('description'),
        ];
    }

    protected function setTableFields(): array
    {
        return [
            // Define which fields appear in the table
            // Text::make('name'),
            // Text::make('description'),
        ];
    }
}
```

**Customize:**
- Add form fields in `setFields()`
- Define table columns in `setTableFields()`
- Control available actions in `setActions()`
- Set permissions and access control

## Docker Usage

When running in Docker, use `docker compose exec`:

```bash
docker compose exec app php artisan crud:make Product
```

Or with options:

```bash
docker compose exec app php artisan crud:make Product \
  --controller-path="Http/Controllers/Api" \
  --controller-namespace="App\Http\Controllers\Api"
```

## Next Steps After Generation

1. **Define the Database Schema**
   - Create a migration for your model
   - Run migrations: `php artisan migrate`

2. **Configure Fields**
   - Edit the scaffolder's `setFields()` method
   - Add form fields with validation
   - Add relationships using `relation()` method

3. **Set Table Columns**
   - Define which fields appear in the table
   - Set sorting, filtering, and searching

4. **Add Translations**
   - Add module translation to `resources/lang/{locale}/modules.php`
   - Format: `'product' => 'Product'`

5. **Register Routes**
   - Add to your routes file:
   ```php
   Route::resource('products', ProductController::class);
   ```

6. **Configure Access Control** (optional)
   - Add permission checks in the scaffolder
   - Define roles and scopes

## Troubleshooting

### "Module name must start with uppercase"
The module name must be a valid class name starting with uppercase:
```bash
# ✓ Correct
php artisan crud:make Product
php artisan crud:make UserProfile

# ✗ Incorrect
php artisan crud:make product        # lowercase
php artisan crud:make user-profile   # contains hyphen
```

### "File already exists"
The command checks if files exist before creating them. Delete the existing file or use a different name.

### Namespace Issues
If you get "Class not found" errors:
- Verify the namespace matches the directory structure
- Run `composer dump-autoload` to refresh the autoloader
- Check that namespaces follow PSR-4 conventions

## Real-World Scenarios

### Scenario 1: Standard Laravel App
```bash
php artisan crud:make BlogPost
```
Creates standard Laravel structure with models in `app/Models`.

### Scenario 2: Multi-Tenant SaaS
```bash
php artisan crud:make Subscription \
  --controller-path="Http/Controllers/Billing" \
  --controller-namespace="App\Http\Controllers\Billing" \
  --scaffolder-path="Scaffolders/Billing" \
  --scaffolder-namespace="App\Scaffolders\Billing"
```

### Scenario 3: Admin Panel with Multiple Modules
```bash
# Create user management module
php artisan crud:make User \
  --controller-path="Panels/Admin/Controllers" \
  --controller-namespace="App\Panels\Admin\Controllers" \
  --scaffolder-path="Panels/Admin/Scaffolders" \
  --scaffolder-namespace="App\Panels\Admin\Scaffolders"

# Create settings module
php artisan crud:make Setting \
  --controller-path="Panels/Admin/Controllers" \
  --controller-namespace="App\Panels\Admin\Controllers" \
  --scaffolder-path="Panels/Admin/Scaffolders" \
  --scaffolder-namespace="App\Panels\Admin\Scaffolders"

# Create reports module
php artisan crud:make Report \
  --controller-path="Panels/Admin/Controllers" \
  --controller-namespace="App\Panels\Admin\Controllers" \
  --scaffolder-path="Panels/Admin/Scaffolders" \
  --scaffolder-namespace="App\Panels\Admin\Scaffolders"
```

## Tips & Best Practices

1. **Use consistent naming conventions**
   - Singular model names: `Product`, `Invoice`, `User`
   - Consistent namespace structure across your app

2. **Generate first, then customize**
   - Use the command to scaffold the base structure
   - Then edit each file for your specific needs

3. **Batch generate modules**
   - Create multiple modules in sequence
   - Keep related modules in the same namespace

4. **Version control**
   - Commit generated files to track changes
   - Use git to manage controller customizations

5. **Document customizations**
   - Add comments when extending generated code
   - Keep changes separate from auto-generated sections

## See Also

- [Scaffolder System](SYSTEM_DESIGN.md#scaffolder-system)
- [Field Types](README_COMPREHENSIVE.md#field-types)
- [Access Control](ACCESS_CONTROL.md)
- [CRUD Actions](SYSTEM_DESIGN.md#request-processing-pipeline)
