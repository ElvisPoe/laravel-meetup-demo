# Laravel Meetup TODOs

## Navigate through the code and run this to check coverage
`pest --coverage`

## Fitlered tests
pest tests/Unit/TestExample.php

## Run this to write some tests
Please write me clean tests for this project. Start with all GET endpoints. Use the laravel-testing skill and try to apply all rules.

## Run this to check Pest Agent
pest --agent='$user = \App\Models\User::factory()->create(); $this->actingAs($user)->get("/dashboard")->assertOk();'