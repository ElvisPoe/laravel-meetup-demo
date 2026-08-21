---
name: laravel-testing
description: Write clean, best-practice Pest tests for this Laravel application. Always follows the AAA (Arrange-Act-Assert) pattern with // Arrange, // Act, // Assert comments and descriptive behavior-based names like it('should allow user to create payment'). Use when writing, generating, or refactoring tests — feature tests, unit tests, HTTP tests, or when the user mentions Pest, TDD, or test coverage.
---

# Laravel Testing (Pest)

This project uses **Pest 4** with `pest-plugin-laravel` on Laravel 13. Tests run against an in-memory SQLite database (`phpunit.xml`), with array mail/cache/session drivers and a sync queue. Write all tests in Pest style — never PHPUnit class syntax.

## Step 1: Decide Feature vs Unit

- **Feature test** (`tests/Feature/`): anything touching HTTP routes, database, jobs, commands, middleware, policies. This is the default for Laravel — most tests should be feature tests. Feature tests are bound to `Tests\TestCase` via `tests/Pest.php`.
- **Unit test** (`tests/Unit/`): pure logic with no framework boot — value objects, calculators, formatters, enums, small services with mocked dependencies.

## Step 2: Naming — behavior, not implementation

Every test name describes a behavior from the user's or system's perspective, in the form `should <expected behavior> when/if/for <condition>` (condition optional when obvious), inside `it()`:

```php
it('should allow user to create payment', ...)
it('should reject payment when balance is insufficient', ...)
it('should send receipt email after successful payment', ...)
```

Bad names to avoid: `it('works')`, `it('tests payment')`, `it('payment controller store method')`.

## Step 3: Structure — always AAA

Every test body has exactly three blocks separated by blank lines, in this order, each marked with its `// Arrange`, `// Act`, `// Assert` comment. Tests with no setup skip the Arrange block entirely.

```php
it('should allow user to create payment', function () {
    // Arrange
    $user = User::factory()->create(['balance' => 500_00]);

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => 100_00, 'currency' => 'EUR']);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'amount' => 100_00,
    ]);
});
```

Rules:

- One behavior per test. Multiple asserts are fine if they verify the same behavior.
- No logic in tests: no `if`, `foreach`, `try/catch`. Use `with()` datasets for variations.
- Arrange with **factories**, never manual inserts or seeders-per-test. Use factory states (`->suspended()`, `->withBalance()`) instead of repeating attribute arrays.
- Act is ideally a single statement — the one call under test.
- Assert observable outcomes (response, database, dispatched events), not internals.

## Step 4: Laravel best practices

- **Database**: `RefreshDatabase` is NOT enabled globally in `tests/Pest.php`. Any test file that touches the database must declare it at the top: `uses(Illuminate\Foundation\Testing\RefreshDatabase::class);`. Assert with `assertDatabaseHas` / `assertDatabaseMissing` / `assertDatabaseCount` / `assertSoftDeleted`.
- **Fakes over mocks** for framework services: `Mail::fake()`, `Notification::fake()`, `Queue::fake()`, `Event::fake()`, `Storage::fake()`, `Bus::fake()`, `Http::fake()`. Fake in Arrange, assert in Assert (`Mail::assertSent(...)`).
- **External APIs**: always `Http::fake([...])` with explicit URL patterns — never hit real networks. Add `Http::preventStrayRequests()` when possible.
- **Auth**: `$this->actingAs($user)`; test authorization explicitly (`assertForbidden` for the wrong user/role).
- **Time**: freeze with `$this->travelTo(...)` or `Carbon::setTestNow(...)`. Never `sleep()`.
- **HTTP assertions**: prefer specific ones — `assertCreated`, `assertOk`, `assertNoContent`, `assertForbidden`, `assertNotFound`, `assertJsonValidationErrors(['field'])`, `assertJsonPath('data.id', $id)`.
- **Expectations**: in unit tests prefer Pest's `expect($value)->toBe(...)` API over `$this->assert*`.
- **Mock only what you own** (your own services/repositories, via `$this->mock(...)`), and only when faking isn't possible. In feature tests prefer the real container bindings plus fakes.
- **Coverage per feature**: happy path, validation failures, authorization failures, and important edge cases (empty state, boundary values, duplicates).

## Step 5: Variations via datasets

```php
it('should reject payment with invalid amount', function (mixed $amount) {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => $amount, 'currency' => 'EUR']);

    // Assert
    $response->assertJsonValidationErrors(['amount']);
})->with([
    'zero' => 0,
    'negative' => -100,
    'non-numeric' => 'abc',
]);
```

## Step 6: Run and verify

After writing tests, always run them and fix failures before finishing:

```bash
php artisan test --filter=Payment
```

## Full examples

For complete reference tests (feature coverage with auth/validation/mail/events, unit tests, mocked services, Http fakes, time travel), see [examples.md](examples.md).
