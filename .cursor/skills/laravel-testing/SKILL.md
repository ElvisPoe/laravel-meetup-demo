---
name: laravel-testing
description: Write clean Pest 5 tests for this Laravel app using AAA, factories, fakes, and GET-then-create-then-update happy paths. Tests and the setup around them stay very simple. Use when writing, generating, or refactoring tests — feature, unit, HTTP, architecture, coverage, TIA, --parallel, --filter, Pest Agent, or TDD.
---

# Laravel Testing (Pest 5)

Write **Pest** tests only — never PHPUnit class syntax. Always use Laravel's best practices and write clean, maintainable code. Follow the [laravel-best-practices](../laravel-best-practices/SKILL.md) skill for application code under test.

**ALWAYS write tests and the logic around them VERY simple. No over engineering in tests or their setup.**

A test is a factory (or two), one action, and the outcome. Setup that needs a tour is too much. Leave the lines in the test. Do not invent helpers, datasets, custom expectations, base classes, traits, or `Pest.php` functions to hold a scene you could read inline. Reach for those only when the same few lines are copied so often that repeating them is harder to read, and the extracted function is obvious in one glance.

**Pest 5 docs (always):** [pestphp.com/docs/pest5-now-available](https://pestphp.com/docs/pest5-now-available)

This app is Laravel 13 + Pest 5 (`pest-plugin-laravel`). Tests use in-memory SQLite, array mail/cache/session, and a sync queue (`phpunit.xml`). `php artisan test` runs Pest.

## Standing rules

These apply every time tests are written, changed, or run.

| Rule | Do this |
| --- | --- |
| Simple | ALWAYS write tests and the logic around them VERY simple. No over engineering in tests or their setup. |
| Order | Start with **GET** endpoints. Then **create**. Then **updates**. Happy paths first. |
| Clean tests | Always AAA (`// Arrange`, `// Act`, `// Assert`). Names a developer can read with almost no effort. |
| Data | Always **create and use factories** for resources. Never manual inserts or seeders-per-test. |
| Isolation | Always **mocks and fakes**. Never real HTTP, mail, storage, or third-party clients. |
| Helpers | Keep Arrange in the test. Extract a helper only when the same few lines are copied so often that repeating them is harder to read. |
| Coverage | Aim for **decent coverage**. Never chase 100%. |
| Architecture | Set up Pest `arch()` tests **when needed** to protect conventions. |
| Local runs | Prefer `--filter`, `--parallel`, and `--tia`. |
| UI checks | Run **Pest Agent** when the UI must look or behave as expected. |
| CI | **Never commit and never push** if any CI step is failing. |
| Code | Always Laravel best practices. Always clean, maintainable code. |
| Docs | Always Pest 5 docs: https://pestphp.com/docs/pest5-now-available |

Copy this checklist while working:

```
- [ ] Tests and setup are VERY simple — no over engineering
- [ ] GET happy paths first
- [ ] Then create happy paths
- [ ] Then update happy paths
- [ ] AAA + readable it('should ...') names
- [ ] Factories for all resources
- [ ] Fakes/mocks — no real clients
- [ ] Arrange stays in the test unless a tiny helper is clearly easier to read
- [ ] Decent coverage — stop before 100% chasing
- [ ] arch() only if a convention needs a guard
- [ ] Narrowest local run (--filter / --parallel / --tia)
- [ ] Pest Agent if UI must be verified
- [ ] Do not commit or push on a failing CI step
```

## What to write, and in what order

1. List routes: `php artisan route:list --path=api --except-vendor` (also web routes that hit the DB).
2. **GET happy paths** — index and show return the right status and payload for an authorized user.
3. **Create happy paths** — store persists and returns the created resource.
4. **Update happy paths** — update persists and returns the changed resource.
5. Only then add guests, forbidden users, validation, and important edges (empty list, duplicate, domain exception). Skip low-value branches. Decent coverage is enough.

Group tests by resource (`tests/Feature/ProjectTest.php`, `TaskTest.php`). One behavior per `it()`.

## Feature vs unit

- **Feature** (`tests/Feature/`): HTTP, DB, jobs, commands, middleware, policies. Default. Bound to `Tests\TestCase` in `tests/Pest.php`.
- **Unit** (`tests/Unit/`): pure logic — enums, formatters, calculators, small services with mocked deps. No Laravel HTTP/DB boot unless the file already does.

## Names

`it('should <expected behavior> when/if/for <condition>')`. Condition optional when obvious.

```php
it('should list projects for the authenticated user', ...)
it('should allow the owner to create a project', ...)
it('should allow the owner to update the project name', ...)
```

Avoid: `it('works')`, `it('tests project')`, `it('project controller index')`.

## AAA — every test

Three blocks, blank line between them, comments required. Skip Arrange only when there is no setup.

```php
it('should list projects for the authenticated user', function () {
    // Arrange
    $user = User::factory()->create();
    $project = Project::factory()->for($user, 'owner')->create(['name' => 'Board']);

    // Act
    $response = $this->actingAs($user)->getJson('/api/projects');

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.0.id', $project->id)
        ->assertJsonPath('data.0.name', 'Board');
});
```

- One behavior per test. Several asserts are fine if they prove that one behavior.
- No `if`, `foreach`, or `try/catch` in tests. Use `->with()` datasets for variations.
- Act is one call under test when possible.
- Assert outcomes (status, JSON, DB, faked mail/events) — not private internals.

Full GET / create / update / helper / isolation / `arch()` samples: [examples.md](examples.md).

## Factories

Always factories for users, projects, members, tasks, comments, and any other model.

- Prefer factory states and relationships (`->for($project)`, `->open()`, `Project::factory()->hasTasks(2)`) over repeating attribute arrays.
- If a factory is missing, create it (`php artisan make:factory --no-interaction`) and use it — do not `Model::query()->create([...])` in tests.

## Isolation — mocks and fakes

Keep the suite off real clients.

- Framework: `Mail::fake()`, `Notification::fake()`, `Queue::fake()`, `Event::fake()`, `Storage::fake()`, `Bus::fake()`, `Http::fake()`. Fake in Arrange, assert in Assert.
- Outbound HTTP: `Http::fake([...])` with URL patterns. Prefer `Http::preventStrayRequests()`. Never hit the network.
- Owned services (gateways, SDKs): `$this->mock(TheClient::class)`.
- Time: `$this->travelTo(...)` / `Carbon::setTestNow(...)`. Never `sleep()`.
- Auth: `$this->actingAs($user)`.

In feature tests prefer the real app + fakes. Mock only what you own, and only when a fake cannot replace it.

## Custom helpers

Default is no helper. Write the factory calls in the test.

Extract a helper in the `Functions` section of `tests/Pest.php` (or a dedicated test helper file if one already exists) only when the same few Arrange lines are copied so often that repeating them is harder to read. Name it after the scene, not the test. The helper is a few obvious lines, not a setup framework.

```php
function actingAsProjectOwner(): array
{
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'owner')->create();

    return compact('owner', 'project');
}
```

Do not extract after a single repeat. Do not hide the Act/Assert behind a mega-helper.

## Database

`RefreshDatabase` is **not** global (`tests/Pest.php` has it commented out). Every file that touches the DB must start with:

```php
uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
```

Assert with `assertDatabaseHas` / `assertDatabaseMissing` / `assertDatabaseCount` / `assertSoftDeleted`.

## HTTP asserts

Prefer `assertOk`, `assertCreated`, `assertNoContent`, `assertUnauthorized`, `assertForbidden`, `assertNotFound`, `assertUnprocessable`, `assertJsonValidationErrors(['field'])`, `assertJsonPath(...)`.

Unit tests: Pest `expect($value)->toBe(...)`.

## Datasets

Use `->with()` for input variations (invalid amounts, missing fields). Keep AAA inside the closure.

## Coverage

Aim for the behaviors that can break: happy GET/create/update, auth, validation, and a few domain edges.

Do **not** add tests only to raise a percentage. Do **not** chase 100%. Skip glue, getters, and framework behavior.

```bash
./vendor/bin/pest --coverage
```

Needs **pcov** or **Xdebug 3**. If coverage is skipped, say so; do not invent numbers.

## Architecture tests (when needed)

Use Pest `arch()` when a convention should fail the suite if someone breaks it (e.g. models extend `Model`, Actions stay invokable, no `dd`/`dump` in `App`). Put them in `tests/Feature` or `tests/Unit` as `*ArchTest.php`.

Do not add architecture tests by default. Add them when the user asks or when a layout rule is easy to violate (for this app: `App\Actions\{Resource}\{Action}`).

```php
arch('models extend eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('app does not debug dump')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'die']);
```

Presets exist (`arch()->preset()->php()`, `->security()`). See Pest 5 architecture docs via the URL above.

## Local run optimisations

Always run the **narrowest** set that covers the change, then fix failures before finishing.

| Goal | Command |
| --- | --- |
| One name / file | `php artisan test --compact --filter=should-list-projects` |
| File | `php artisan test --compact tests/Feature/ProjectTest.php` |
| Parallel | `php artisan test --compact --parallel` |
| TIA (changed tests only) | `./vendor/bin/pest --tia` |
| Coverage | `./vendor/bin/pest --coverage` |
| UI tests | `./vendor/bin/pest tests/Browser --compact` |
| UI probe | `./vendor/bin/pest --agent='visit("/")->assertSee("Your projects");'` |

`--tia` needs pcov or Xdebug 3. If Pest says TIA is skipped, run `--filter` instead.

`php artisan test` accepts Pest flags (`--filter`, `--parallel`, `--compact`, `--coverage`). Use `./vendor/bin/pest` when a flag is Pest-only (`--tia`, `--agent`).

## Pest Agent (UI and one-off probes)

This project has **Pest Agent** (`pestphp/pest-plugin-agent`) and **browser tests** (`pestphp/pest-plugin-browser` + Playwright Chromium). Use them when the **UI must look or behave as expected** (Blade, CSS, responsive layout, clicks).

Lasting UI checks live in `tests/Browser`. That folder uses `TestCase` + `RefreshDatabase` (see `tests/Pest.php`). Call `assertNoJavaScriptErrors()` on every browser test. Build frontend assets first if the UI looks unstyled (`npm run build`).

Always wrap `--agent` snippets in **single quotes** so the shell does not eat `$user`:

```bash
./vendor/bin/pest --agent='visit("/")->assertSee("Your projects");'
./vendor/bin/pest --agent='$user = \App\Models\User::factory()->create(); $project = \App\Models\Project::factory()->for($user, "owner")->create(["name" => "Meetup board"]); visit("/")->assertSee("Meetup board");'
./vendor/bin/pest --agent='visit("/")->on()->mobile()->screenshot(filename: "board-mobile");'
```

Backend-only probe (no browser):

```bash
./vendor/bin/pest --agent='$user = \App\Models\User::factory()->create(); $this->actingAs($user)->get("/")->assertOk();'
```

Run committed UI tests with:

```bash
./vendor/bin/pest tests/Browser --compact
```

`--headed` watches the browser; `--debug` pauses on failure. Failure screenshots go to `tests/Browser/Screenshots` (gitignored).

Agent is a **probe**, not a replacement for `tests/Feature` or `tests/Browser`. If the behavior should stay, write a real test. Do not add more Pest plugins unless the user asks.

## CI and git

- If any CI step is failing, **do not commit** and **do not push**.
- Fix the failure (or the tests) first, then commit only if the user asked.

## After writing tests

```bash
php artisan test --compact --filter=YourTestFileOrName
```

Ask the user to run the full suite (`php artisan test --compact`) once the focused run is green.
