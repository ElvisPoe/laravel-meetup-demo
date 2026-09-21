# Laravel Testing — Reference Examples (Pest 5)

All examples use AAA with `// Arrange`, `// Act`, `// Assert` and `it('should ...')` names. Write GET happy paths first, then create, then updates.

Docs: https://pestphp.com/docs/pest5-now-available

## 1. GET happy path

`tests/Feature/ProjectTest.php`

```php
<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('should show a project the user can access', function () {
    // Arrange
    $user = User::factory()->create();
    $project = Project::factory()->for($user, 'owner')->create();

    // Act
    $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}");

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.id', $project->id);
});
```

## 2. Create happy path

```php
it('should allow the owner to create a project', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/projects', ['name' => 'Meetup board']);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Meetup board');
    $this->assertDatabaseHas('projects', [
        'user_id' => $user->id,
        'name' => 'Meetup board',
    ]);
});
```

## 3. Update happy path

```php
it('should allow the owner to update the project name', function () {
    // Arrange
    $user = User::factory()->create();
    $project = Project::factory()->for($user, 'owner')->create(['name' => 'Old']);

    // Act
    $response = $this->actingAs($user)
        ->putJson("/api/projects/{$project->id}", ['name' => 'New']);

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.name', 'New');
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'New',
    ]);
});
```

## 4. Custom helper — rare

Keep Arrange in the test. Use a helper only when the same few lines are copied so often that repeating them is harder to read. Not on the first repeat.

In `tests/Pest.php`:

```php
function actingAsProjectOwner(): array
{
    $owner = \App\Models\User::factory()->create();
    $project = \App\Models\Project::factory()->for($owner, 'owner')->create();

    return compact('owner', 'project');
}
```

In the test file, once repeating the scene is harder to read than calling it:

```php
it('should list tasks on the owners project', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner();
    $task = Task::factory()->for($project)->create(['title' => 'Ship slides']);

    // Act
    $response = $this->actingAs($owner)
        ->getJson("/api/projects/{$project->id}/tasks");

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.0.id', $task->id)
        ->assertJsonPath('data.0.title', 'Ship slides');
});
```

## 5. Isolation — fakes (mail / events)

```php
use App\Mail\MemberAddedToProjectMail;
use Illuminate\Support\Facades\Mail;

it('should email the member when they are added to a project', function () {
    // Arrange
    Mail::fake();
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'owner')->create();
    $member = User::factory()->create();

    // Act
    $this->actingAs($owner)
        ->postJson("/api/projects/{$project->id}/members", [
            'user_id' => $member->id,
        ])
        ->assertCreated();

    // Assert
    Mail::assertSent(MemberAddedToProjectMail::class, fn ($mail) => $mail->hasTo($member->email));
});
```

## 6. Isolation — mock an owned client

```php
use App\Services\PaymentGateway;
use App\Exceptions\GatewayException;

it('should mark payment as failed when gateway declines', function () {
    // Arrange
    $user = User::factory()->create(['balance' => 500_00]);
    $this->mock(PaymentGateway::class)
        ->shouldReceive('charge')
        ->once()
        ->andThrow(new GatewayException('Card declined'));

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => 100_00, 'currency' => 'EUR']);

    // Assert
    $response->assertUnprocessable();
    $this->assertDatabaseHas('payments', ['user_id' => $user->id, 'status' => 'failed']);
});
```

## 7. Isolation — fake outbound HTTP

```php
use Illuminate\Support\Facades\Http;

it('should store exchange rate from external provider', function () {
    // Arrange
    Http::preventStrayRequests();
    Http::fake([
        'api.rates.test/*' => Http::response(['EUR' => 1.08], 200),
    ]);

    // Act
    $this->artisan('rates:sync')->assertSuccessful();

    // Assert
    $this->assertDatabaseHas('exchange_rates', ['currency' => 'EUR', 'rate' => 1.08]);
    Http::assertSentCount(1);
});
```

## 8. Auth / validation (after happy paths)

```php
it('should forbid guests from listing projects', function () {
    // Act
    $response = $this->getJson('/api/projects');

    // Assert
    $response->assertUnauthorized();
});

it('should reject a project without a name', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/projects', []);

    // Assert
    $response->assertJsonValidationErrors(['name']);
});

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

## 9. Unit test

`tests/Unit/FeeCalculatorTest.php`

```php
<?php

use App\Services\FeeCalculator;

it('should calculate two percent fee for standard payments', function () {
    // Arrange
    $calculator = new FeeCalculator();

    // Act
    $fee = $calculator->calculate(amount: 100_00);

    // Assert
    expect($fee)->toBe(2_00);
});
```

## 10. Time travel

```php
it('should expire payment link after 24 hours', function () {
    // Arrange
    $this->travelTo(now());
    $link = PaymentLink::factory()->create();

    // Act
    $this->travel(25)->hours();
    $response = $this->getJson("/pay/{$link->token}");

    // Assert
    $response->assertGone();
});
```

## 11. Architecture (only when a convention needs a guard)

`tests/Feature/ArchitectureTest.php`

```php
<?php

arch('models extend eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('actions are invokable')
    ->expect('App\Actions')
    ->toBeClasses()
    ->toHaveMethod('handle');

arch('app does not debug dump')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'die']);
```

## 12. Pest Agent probes (UI / one-off)

Agent + Browser are installed. Prefer `tests/Browser` for checks that should stay.

```bash
./vendor/bin/pest --agent='visit("/")->assertSee("Your projects");'
./vendor/bin/pest --agent='$user = \App\Models\User::factory()->create(); $project = \App\Models\Project::factory()->for($user, "owner")->create(["name" => "Meetup board"]); visit("/")->assertSee("Meetup board");'
./vendor/bin/pest --agent='visit("/")->on()->mobile()->screenshot(filename: "board-mobile");'
```

Backend-only:

```bash
./vendor/bin/pest --agent='$this->get("/")->assertOk();'
```
