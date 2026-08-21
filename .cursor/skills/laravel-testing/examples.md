# Laravel Testing — Reference Examples (Pest)

All examples follow AAA (Arrange-Act-Assert) with `// Arrange`, `// Act`, `// Assert` comments and behavior-based names.

## Feature test (full coverage of one endpoint)

`tests/Feature/PaymentTest.php`

```php
<?php

use App\Models\User;
use App\Models\Payment;
use App\Events\PaymentCreated;
use App\Mail\PaymentReceipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('should allow user to create payment', function () {
    // Arrange
    $user = User::factory()->create(['balance' => 500_00]);

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => 100_00, 'currency' => 'EUR']);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('data.amount', 100_00);
    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'amount' => 100_00,
        'currency' => 'EUR',
    ]);
});

it('should reject payment when balance is insufficient', function () {
    // Arrange
    $user = User::factory()->create(['balance' => 50_00]);

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => 100_00, 'currency' => 'EUR']);

    // Assert
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
    $this->assertDatabaseCount('payments', 0);
});

it('should forbid guest from creating payment', function () {
    // Act
    $response = $this->postJson('/api/payments', ['amount' => 100_00]);

    // Assert
    $response->assertUnauthorized();
});

it('should forbid user from viewing another users payment', function () {
    // Arrange
    $payment = Payment::factory()->create();
    $otherUser = User::factory()->create();

    // Act
    $response = $this->actingAs($otherUser)
        ->getJson("/api/payments/{$payment->id}");

    // Assert
    $response->assertForbidden();
});

it('should send receipt email after successful payment', function () {
    // Arrange
    Mail::fake();
    $user = User::factory()->create(['balance' => 500_00]);

    // Act
    $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => 100_00, 'currency' => 'EUR'])
        ->assertCreated();

    // Assert
    Mail::assertSent(PaymentReceipt::class, fn ($mail) => $mail->hasTo($user->email));
});

it('should dispatch payment created event', function () {
    // Arrange
    Event::fake([PaymentCreated::class]);
    $user = User::factory()->create(['balance' => 500_00]);

    // Act
    $this->actingAs($user)
        ->postJson('/api/payments', ['amount' => 100_00, 'currency' => 'EUR'])
        ->assertCreated();

    // Assert
    Event::assertDispatched(PaymentCreated::class);
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

## Unit test

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

it('should apply minimum fee for small amounts', function () {
    // Arrange
    $calculator = new FeeCalculator();

    // Act
    $fee = $calculator->calculate(amount: 1_00);

    // Assert
    expect($fee)->toBe(FeeCalculator::MINIMUM_FEE);
});
```

## Mocking an owned service (external gateway)

```php
<?php

use App\Models\User;
use App\Services\PaymentGateway;
use App\Exceptions\GatewayException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

## Faking external HTTP

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('should store exchange rate from external provider', function () {
    // Arrange
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

## Time-sensitive behavior

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
