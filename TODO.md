# Live demo

## 1. Walkthrough the code
```
Navigate through routes, controllers and models and run the app to see the UI.
```

```
php artisan dev
```

## 2. Check the coverage
```bash
pest --coverage --compact
```

---

## 3. Ask AI to write the tests

```
Write clean Pest tests for this project. Use the laravel-testing skill and apply all of its rules. Start with every GET endpoint, happy paths only.
```

---

## 4. Coverage after

```bash
pest --coverage --compact
```

Same command, different story: decent coverage, still not 100%. That is the point.

---

## 5. Parallel

Run the suite in parallel to run the suite faster.

```bash
pest --parallel
```
---

## 6. Filter

Do not re-run the whole suite to check one behavior.

```bash
pest tests/Feature
```
---

## 7. Pest Agent

Keep this short — that is the wow:
```bash
pest --agent='visit("/")->assertSee("Your projects");'
```

---

## 8. Catch the mobile bug
```
Write a Pest browser test for the homepage board. A task with status In progress must show that label on mobile.
```
```
pest tests/Browser
```