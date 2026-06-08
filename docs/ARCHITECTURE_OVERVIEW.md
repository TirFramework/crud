# Tir/CRUD - Architecture Overview & Decision Trees

**Purpose**: Quick visual reference for understanding Tir/CRUD architecture and making implementation decisions.

---

## 🎯 Architecture at a Glance

### Component Relationships

```
┌─────────────────────────────────────────────────────────────────┐
│                  DEVELOPER'S PERSPECTIVE                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Create Scaffolder          →  Extend CrudController  →  Done  │
│  └─ Define model             └─ Set scaffolder class           │
│  └─ Define fields            └─ Optional: add hooks             │
│  └─ Define actions           └─ Optional: access control        │
│  └─ Define buttons           └─ Auto routes generated           │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Type System

```
HTTP Request
    │
    ├─ String data (JSON/form)
    │
    ├─ ProcessRequest (normalize)
    │   └─ Cast to field types
    │
    ├─ Service Layer (validate/transform)
    │   └─ Run field validation
    │
    ├─ Model Operation (persist)
    │   └─ Store to database
    │
    ├─ Accessor/Format (transform)
    │   └─ Format for response
    │
    └─ JSON Response (serialize)
        └─ Return to client
```

---

## 🛠️ How to Choose CRUD Components

### Field Type Decision Tree

```
Need to store data?
│
├─ Text data
│   ├─ Single line?      → Text::make()
│   ├─ Multiple lines?   → Textarea::make()
│   ├─ Email?            → Email::make()
│   └─ Password?         → Password::make()
│
├─ Numeric data
│   ├─ Integer?          → Number::make()
│   ├─ Decimal?          → Decimal::make()
│   └─ Currency?         → Currency::make()
│
├─ Date/Time data
│   ├─ Date only?        → Date::make()
│   ├─ Date + Time?      → DateTime::make()
│   └─ Time only?        → Time::make()
│
├─ Selection data
│   ├─ Single choice?    → Select::make()
│   ├─ Multiple choice?  → Checkbox::make()
│   └─ Yes/No?           → Toggle::make()
│
├─ File data
│   ├─ Image?            → Image::make()
│   ├─ Document?         → File::make()
│   └─ Multiple?         → Gallery::make()
│
├─ Related data
│   ├─ Belongs-to?       → Field::make()->relation('name', 'field', 'belongsTo')
│   ├─ Has-many?         → Field::make()->relation('name', 'field', 'hasMany')
│   └─ Many-to-many?     → Field::make()->relation('name', 'field', 'belongsToMany')
│
└─ Complex data
    ├─ JSON?             → Json::make()
    ├─ HTML?             → RichText::make()
    └─ Custom?           → Extend BaseField
```

### Hook Decision Tree

```
Need to customize behavior?
│
├─ Before action (pre-processing)
│   ├─ Modify request?       → $this->onStore/Update
│   ├─ Additional validation? → $this->onBeforeValidate
│   ├─ Log before?           → $this->onBefore[Action]
│   └─ Security check?       → $this->onAccessCheck
│
├─ After action (post-processing)
│   ├─ Modify response?      → $this->on[Action]
│   ├─ Send notification?    → $this->onAfter[Action]
│   ├─ Log operation?        → $this->onAfter[Action]
│   └─ Sync elsewhere?       → $this->onAfter[Action]
│
├─ Replace action entirely
│   └─ Complete override?    → $this->on[Action] + return early
│
└─ Transform data
    ├─ Format for display?   → Field->format()
    ├─ Custom accessor?      → Field->accessor()
    └─ Modify before save?   → $this->onStore/Update + processRequest
```

### Access Control Decision Tree

```
Need to control access?
│
├─ Disable access control?
│   ├─ For entire app?       → config: 'accessLevelControl' => 'off'
│   ├─ For controller?       → $accessControlEnabled = false
│   └─ For specific method?  → onAccessCheck hook
│
├─ Fine-grained control
│   ├─ Public methods?       → onAccessCheck() + return false
│   ├─ Admin only?           → onAccessCheck() + check isAdmin()
│   ├─ Owner only?           → onAccessCheck() + check ownership
│   └─ Permission-based?     → auth()->user()->permissions
│
├─ Scope-based control
│   ├─ Own records only?     → Modify DataService query
│   ├─ Department records?   → Filter by department_id
│   └─ All (no filter)?      → Leave query as-is
│
└─ Custom permission class
    └─ Implement Interface  → config: 'access_class'
```

---

## 🔄 Request-Response Lifecycle

### Store (POST) Lifecycle

```
POST /users + JSON body
    │
    ├─ Route dispatches to store()
    │
    ├─ Hook: onStore
    │   └─ process request data
    │
    ├─ Validate
    │   └─ Check field rules
    │
    ├─ StoreService
    │   ├─ Create model instance
    │   ├─ Fill attributes
    │   ├─ Save to database
    │   └─ Hook: onModelCreated
    │
    ├─ Log operation
    │
    └─ Response
        ├─ HTTP 201 Created
        ├─ Return created model
        └─ Include ID for client
```

### Index (GET) Lifecycle

```
GET /users?search=john&status=active&page=1
    │
    ├─ Route dispatches to index()
    │
    ├─ DataService::getData()
    │   ├─ Hook: onInitQuery
    │   ├─ Hook: onBeforeIndexQuery
    │   ├─ Apply search filter
    │   ├─ Apply field filters
    │   ├─ Apply sorting
    │   ├─ Hook: onModifiedQuery
    │   ├─ Apply pagination
    │   ├─ Apply accessors (format values)
    │   └─ Hook: onAfterIndexQuery
    │
    ├─ Prepare response
    │   ├─ Data array
    │   ├─ Pagination info
    │   └─ Filter definitions
    │
    └─ Response
        ├─ HTTP 200 OK
        ├─ data: []
        ├─ pagination: {...}
        └─ filters: [...]
```

---

## 📊 Data Structure Diagrams

### Scaffolder Composition

```
BaseScaffolder (abstract)
    │
    ├─ Module Metadata
    │   ├─ moduleName:    string
    │   ├─ moduleTitle:   string
    │   └─ moduleIcon:    string (optional)
    │
    ├─ Model Reference
    │   └─ model:         Eloquent Model class
    │
    ├─ Fields Collection
    │   └─ fields[]
    │       ├─ Field 1:   Text
    │       ├─ Field 2:   Email
    │       ├─ Field 3:   Relation
    │       └─ Field N:   [Type]
    │
    ├─ Actions
    │   ├─ index:         bool
    │   ├─ show:          bool
    │   ├─ create:        bool
    │   ├─ store:         bool
    │   ├─ edit:          bool
    │   ├─ update:        bool
    │   ├─ destroy:       bool
    │   └─ [custom]:      bool
    │
    └─ Buttons
        ├─ submit:       Button
        ├─ back:         Button
        └─ [custom]:     Button
```

### Field Composition

```
BaseField (abstract)
    │
    ├─ Identity
    │   ├─ name:         string
    │   ├─ type:         string
    │   └─ page:         string (index|create|edit|show)
    │
    ├─ Display Properties
    │   ├─ display:      string
    │   ├─ helpText:     string
    │   ├─ comment:      string
    │   ├─ hidden:       bool
    │   ├─ disabled:     bool
    │   └─ class:        string (CSS)
    │
    ├─ Validation Rules
    │   ├─ required:     bool
    │   ├─ unique:       tuple
    │   ├─ min/max:      number
    │   ├─ email:        bool
    │   ├─ regex:        string
    │   └─ [rule]:       any
    │
    ├─ Data Handling
    │   ├─ default:      any
    │   ├─ format:       callable
    │   ├─ fillValue:    callable
    │   └─ storeAs:      string
    │
    ├─ Relation (if applicable)
    │   ├─ type:         string
    │   ├─ model:        class
    │   ├─ showField:    string
    │   └─ dataSet:      array
    │
    └─ Filtering
        ├─ filterable:   bool
        ├─ filterType:   enum
        └─ filter:       array
```

---

## 🎪 Common Patterns

### Pattern 1: Basic CRUD

```php
// Minimal setup - all defaults
class UserScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string { return 'users'; }
    protected function setModel(): string { return User::class; }
    protected function setFields(): array {
        return [
            Text::make('name')->required(),
            Email::make('email')->required(),
        ];
    }
}

class UserController extends CrudController
{
    protected function setScaffolder(): string { return UserScaffolder::class; }
}
```

### Pattern 2: Custom Hooks

```php
class ProductController extends CrudController
{
    protected function setScaffolder(): string { return ProductScaffolder::class; }

    protected function setup()
    {
        $this->onStore(function($next, $request) {
            // Add created_by before store
            $request->merge(['created_by' => auth()->id()]);
            return $next($request);
        });

        $this->onUpdate(function($next, $id, $request) {
            // Add updated_by before update
            $request->merge(['updated_by' => auth()->id()]);
            return $next($id, $request);
        });
    }
}
```

### Pattern 3: Access Control

```php
class AdminUserController extends CrudController
{
    protected function setScaffolder(): string { return AdminUserScaffolder::class; }

    protected function setup()
    {
        // Only admins can manage admin users
        $this->onAccessCheck(function($method) {
            return auth()->user()?->isAdmin() ?? false;
        });
    }
}
```

### Pattern 4: Complex Relations

```php
class OrderScaffolder extends BaseScaffolder
{
    protected function setFields(): array
    {
        return [
            Text::make('order_number')->required(),
            
            Text::make('customer.name')
                ->display('Customer')
                ->relation('customer', 'name', 'belongsTo')
                ->required(),
            
            Text::make('items.product_name')
                ->display('Items')
                ->relation('items', 'product_name', 'hasMany'),
        ];
    }
}
```

---

## 🔍 Debugging Checklist

### Issue: 403 Forbidden

- [ ] Check `config/crud.php` - is access control enabled?
- [ ] Check controller - `$accessControlEnabled` property?
- [ ] Check hook - `onAccessCheck` returning correct value?
- [ ] Check permissions - `auth()->user()->permissions` structure?
- [ ] Check user - is user logged in (auth()->check())?

### Issue: Validation Errors

- [ ] Check field definition - is validation set?
- [ ] Check field type - is it appropriate?
- [ ] Check unique rule - correct table/column?
- [ ] Check regex pattern - correct syntax?
- [ ] Check response format - errors in correct structure?

### Issue: Relations Not Loading

- [ ] Check scaffolder field - is it defined?
- [ ] Check relation type - belongsTo/hasMany/belongsToMany?
- [ ] Check model - does relationship exist?
- [ ] Check eager loading - is relation eager-loaded?
- [ ] Check response - does data include relation?

### Issue: No Data in List

- [ ] Check DataService - any exceptions?
- [ ] Check pagination - correct page number?
- [ ] Check filters - are they filtering correctly?
- [ ] Check search - is search text correct?
- [ ] Check permissions - can user see data?

### Issue: Routes Not Found

- [ ] Check routes file - is resource route registered?
- [ ] Check route list - `php artisan route:list`
- [ ] Check namespace - correct controller namespace?
- [ ] Check middleware - correct middleware applied?
- [ ] Check service provider - is provider registered?

---

## 📋 Quick Configuration Reference

### Minimal config/crud.php

```php
return [
    'middlewares' => ['auth:sanctum'],
    'accessLevelControl' => 'on',
    'access_class' => \Tir\Crud\Support\Acl\Access::class,
    'enable_logging' => false,
];
```

### Enable All Features

```php
return [
    'middlewares' => ['auth:sanctum', 'verified', 'active'],
    'accessLevelControl' => 'on',
    'access_class' => \App\Custom\AccessChecker::class,
    'enable_logging' => true,
];
```

### Disable Access Control

```php
return [
    'middlewares' => [],
    'accessLevelControl' => 'off',  // Disable globally
    'access_class' => null,
    'enable_logging' => false,
];
```

---

## 🚀 Performance Tips

| Optimization | Benefit | How |
|-------------|---------|-----|
| **Eager Loading** | Avoid N+1 queries | Define relations with `relation()` method |
| **Selective Columns** | Reduce data transfer | Use `select()` in scaffolder |
| **Pagination** | Limit query results | Default 15 per page |
| **Indexing** | Faster database queries | Index foreign keys |
| **Caching** | Reduce scaffolder builds | Cache definitions |
| **Query Hooks** | Custom optimizations | Use `onModifiedQuery` |
| **Lazy Relations** | Load on-demand | Don't eager load everything |
| **Field Filters** | Reduce matches | Make searchable fields filterable |

---

## 📚 Related Documentation

- **README** - Complete usage guide with examples
- **SYSTEM_DESIGN** - Deep dive into architecture
- **ACCESS_CONTROL** - Comprehensive permission system
- **TESTING** - Testing strategies and examples
- **QUICK_REFERENCE** - Fast lookup for common tasks

