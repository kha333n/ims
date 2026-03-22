---
name: DevForce 141 Standards
description: >
  Team-specific conventions for DevForce 141 Laravel projects. Covers the Action
  Pattern with laravel-actions, centralised Session/Cache, semantic entity naming,
  data-attribute JS hooks, Pest architecture tests, UTC timestamps, enums over magic
  strings, and commit discipline. Extends — never replaces — standard Laravel skills.
compatible_agents:
  - Claude Code
  - Cursor
  - Windsurf
tags:
  - laravel
  - php
  - devforce141
  - actions
  - pest
  - livewire
---

# DevForce 141 Standards

## Context

You are working in a **DevForce 141** Laravel project. These rules are additive —
they extend standard Laravel conventions and any other installed skills.
Apply them alongside the project's other skills, not instead of them.

---

## Action Pattern

Use `lorisleiva/laravel-actions` for all non-trivial business operations.
Namespace actions by module under `App\Actions`.

```php
namespace App\Actions\Order;

use Lorisleiva\Actions\Concerns\AsAction;

class PlaceOrder
{
    use AsAction;

    public function handle(Cart $cart, User $user): Order
    {
        // One action = one task.
        // This class can be used as controller, job, command, or listener.
    }
}
```

**Rules:**
- Do NOT use the Repository Pattern. Eloquent models already act as repositories.
  The only exception is a tech-agnostic layer for swapping implementations via contracts.
- Services and Actions coexist: shared/configuration logic → Service; specific operations → Action.
- 5–10 lines directly in a controller is fine. Don't over-engineer small things.

---

## Centralised Session & Cache

All Session and Cache interactions (get / set / forget) must live in dedicated classes.

```
app/Support/SessionStore.php   ← all session::get / set / forget calls
app/Support/CacheStore.php     ← all cache::get / put / forget calls
```

Never scatter `session()` or `cache()` calls across controllers and actions.
Centralising makes refactoring safe and auditing trivial.

---

## Semantic Entity Naming

Name every entity as specifically as possible. Generic names cause collisions with
framework internals and confuse future developers.

| ❌ Avoid | ✅ Prefer | Reason |
|---------|----------|--------|
| `Session` | `MiningSession` | Collides with PHP/Laravel `Session` |
| `Log` | `AuditEntry` | Collides with `Illuminate\Log` |
| `Item` | `OrderLineItem` | "Item" means nothing without context |
| `Plan` | `SubscriptionPlan` | Future roadmap entity could also be a "plan" |

Identify all entities early. Ambiguous names always bite in 3 months.

---

## Enums Over Magic Strings

Every fixed set of values must be a backed PHP enum.

```php
// ✅ Correct
enum OrderStatus: string {
    case Pending   = 'pending';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
}
$order->status = OrderStatus::Shipped;

// ❌ Wrong
$order->status = 'shipped'; // what other values exist? nobody knows.
```

---

## UTC Timestamps

Always store timestamps in UTC. You may also store a timezone offset alongside,
but UTC is the mandatory primary value.

```php
// config/app.php
'timezone' => 'UTC',
```

---

## Data Attributes for JS Hooks

Never use CSS classes as JavaScript selectors. Use `data-*` attributes instead.
CSS classes get renamed for styling reasons; data attributes are stable.

```html
<!-- ✅ Correct -->
<button data-action="submit-order">Place Order</button>

<!-- ❌ Wrong — the class is now load-bearing, too scary to rename -->
<button class="btn-primary submit-order">Place Order</button>
```

---

## Comments — Why, Not What

Write comments for **context and intent**. The code says what; the comment says why.

```php
// ✅ Good — explains intent the code can't
// Raw payload preserved here because the third-party HMAC is computed against
// the exact original bytes — parsing first alters whitespace and breaks the check.
$raw = $request->getContent();

// ❌ Bad — the code already says this
// Get the content
$raw = $request->getContent();
```

If code needs a comment to explain *what* it does, refactor it until it doesn't.

---

## Pest Architecture Tests

Encode team conventions as runnable architecture tests — not just documentation.
Add one for every new pattern introduced to the codebase.

```php
arch('actions are invokable')
    ->expect('App\Actions')
    ->toBeInvokable();

arch('models do not contain business logic')
    ->expect('App\Models')
    ->not->toHavePublicMethodsBesides([
        'relationships', 'scopes', 'casts', 'fillable', 'appends',
    ]);
```

---

## Livewire Component Discipline

Applies when the project uses Livewire.

- One generic base layout. Additional layouts only when genuinely needed, extending the base.
- Prefer PHP attributes for simple, non-dynamic metadata:
  ```php
  #[Title('Dashboard')]
  #[Layout('layouts.app')]
  class Dashboard extends Component { ... }
  ```
- If a component is doing too much, it is probably two components.
  Ask: *"What state needs to lift up? What can be a child?"* before writing.

---

## Commit Discipline

- One commit per task. One feature or one fix — nothing more.
- Never mix formatting changes with logic changes. Formatting gets its own commit.
- AI-generated code is committed exactly like hand-written code: clean, purposeful, clear message.

---

## AI Use

- AI is a pair programmer, not a replacement for understanding.
- Never commit AI-generated code you cannot explain. If you can't explain it, you don't own it.
- Always run AI output through the quality pipeline (Pint → PHPStan → Pest) before committing.
- AI is reliable for: boilerplate, test generation, refactoring, documentation.
- AI is unreliable for: business logic nuances, project-specific conventions, security-sensitive code.

---

## References

- [Laravel Actions](https://www.laravelactions.com/)
- [Spatie Laravel Guidelines](https://spatie.be/guidelines/laravel-php) — baseline this extends
- [DevForce 141 on GitHub](https://github.com/DevForce-141)
- [Code Quality Starter Kit](https://github.com/DevForce-141/Code-Quality-Starter-Kit)
