# Testing Checklist for Claude

This checklist MUST be followed before writing any test code.

## Pre-Writing Checklist

### 1. Check Existing Test Traits
**Location:** `tests/Traits/`

Before creating test data, check if a trait already exists:

| Trait | Purpose |
|-------|---------|

**Action:** Read the relevant trait file to understand the method signatures and required parameters.

### 2. Find Relevant Enums
**Location:** `app/Enums/`

Common enums to check:

| Enum | Example Values |
|------|----------------|

**Action:** Always use `EnumClass::CASE->value` instead of hardcoded strings.

### 3. Check Relevant Factories
**Locations:**
- `database/factories` - Standard models

**Action:** Read the factory to understand:
- What relations are automatically created
- What states are available
- What default values are set

### 4. Check Database Schema
**Location:** `database/schema/mysql-schema.sql`

**Action:** Verify:
- Exact column names (snake_case)
- Column types and NOT NULL constraints
- Required foreign keys
- Enum column values

**Important:** When creating records with `firstOrCreate()` or similar methods, always check which columns have `NOT NULL` constraints. All required columns must be included, not just the minimum to satisfy foreign keys.

### 5. Find Similar Existing Tests
**Locations:**
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests

**Action:** Search for tests of similar functionality to copy patterns.

## Required Test Structure

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Path\To\Test;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
// Import traits, enums, factories as needed

class ExampleTest extends TestCase
{
    use DatabaseTransactions;
    // Add NeedsCustomer, NeedsLead, etc. as needed

    #[Test]
    public function it_does_something(): void
    {
        // Arrange
        // ... setup code

        // Act
        // ... execute code

        // Assert
        // ... assertions
    }
}
```

## Common Mistakes to Avoid

1. **Using hardcoded strings instead of enums**
   - Wrong: `'currency' => 'USD'`
   - Right: `'currency' => CurrencyEnum::USD->value`

2. **Creating test data manually instead of using traits**
   - Wrong: Creating a customer with manual factory calls
   - Right: `$this->createCustomer(...)` from `NeedsCustomer` trait

3. **Using `RefreshDatabase` instead of `DatabaseTransactions`**
   - Wrong: `use RefreshDatabase;`
   - Right: `use DatabaseTransactions;`

4. **Missing required relations**
   - Always check what relations a model needs
   - Use traits that setup complete object graphs

5. **Wrong column names**
   - Always verify column names in schema
   - Database uses snake_case: `currency_code`, not `currencyCode`

6. **Not mocking external services**
   - Always mock HTTP clients, external APIs
   - Use Guzzle MockHandler for HTTP tests

7. **Using `DB::table()` inserts instead of factories**
   - Wrong: `DB::table('table_name')->insert([...])`
   - Right: `Model::factory()->create([...])`
   - Always check if a factory exists first in:
     - `database/factories/`
   - Factories handle casts, value objects, and relations correctly
   - If no factory exists, create one rather than using raw DB inserts

8. **Using `$this->app->make()` instead of `app()`**
   - Wrong: `$this->app->make(SomeService::class)`
   - Right: `app(SomeService::class)`
   - The `app()` helper is cleaner and more consistent across the codebase

9. **Creating private helper methods instead of extending traits**
   - Wrong: Creating `private function createCustomerWithX()` in test class
   - Right: Add reusable methods to existing traits (`NeedsCustomer`, `NeedsSubscription`, etc.)
   - Before creating a private helper method, check if:
     - A similar method already exists in a trait
     - The helper could be useful in other tests (then add to trait)
   - Benefits of using traits:
     - Code reuse across multiple test classes
     - Consistent test data creation
     - Single place to update when requirements change

10. **Mocking internal services in reactor tests**
    - Wrong: Mocking `OrderService`, `EtsyService`, etc.
    - Right: Use real implementations from the container with `app(ServiceClass::class)`
    - Only mock external services (HTTP APIs, payment providers)
    - See the "Reactor Tests" section below for detailed patterns

12. **Placing dependency setup in the wrong location**
    - Each factory should handle its own dependencies
    - If a factory has a hardcoded foreign key (e.g., `'billing_country' => 'NL'`), the factory itself should ensure that dependency exists
    - Don't put dependency setup in test traits that aren't responsible for that entity
    - **Always check the database schema** (`database/schema/mysql-schema.sql`) for NOT NULL constraints when creating dependency records with `firstOrCreate()`
    - Example:
      ```php
      // Wrong - putting Country setup in NeedsOrder trait
      // NeedsOrder is for orders, not countries
      trait NeedsOrder
      {
          private function createOrder(): Order
          {
              Country::firstOrCreate(['alpha2' => 'NL']); // Wrong location!
              return OrderFactory::new()->create();
          }
      }

      // Right - OrderFactory ensures its own country dependency exists
      class OrderFactory extends Factory
      {
          public function configure(): static
          {
              return $this->afterMaking(function (Address $address) {
                  Country::firstOrCreate(
                      ['alpha2' => $order->billing_country],
                  );
              });
          }

          public function definition(): array
          {
              return [
                  'billing_country' => 'NL', // Factory is responsible for this
                  // ...
              ];
          }
      }
      ```
    - Benefits:
      - Single responsibility: each factory handles its own dependencies
      - Works everywhere the factory is used, not just in specific traits
      - Easier to maintain and understand

### What TO Mock

- External HTTP APIs (use Guzzle MockHandler)
- Third-party services (payment providers, email services)
- Time-sensitive operations (use `Carbon::setTestNow()`)

### What NOT to Mock

- Internal services (`OrderService`, `EtsyService`, etc.)

## Quick Reference Commands

```bash
# Find all test traits
ls tests/Traits/

# Find all enums
ls app/Enums/

# Search for similar tests
grep -r "ClassName" tests/

# Check factory
cat database/factories/CustomerFactory.php

# Run specific test
pnpm morpheus xphp ./vendor/bin/phpunit --filter=TestClassName
```
