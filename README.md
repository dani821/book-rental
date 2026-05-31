<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## API Documentation

This project ships an OpenAPI 3.1 specification for the **Library API**, generated from the
code by [Scramble](https://scramble.dedoc.co) (static analysis — the code is the source of
truth, so there are no hand-written endpoint annotations to drift out of sync).

- **Interactive UI:** [`/docs/api`](http://book-rental.test/docs/api)
- **Raw spec (served):** [`/docs/api.json`](http://book-rental.test/docs/api.json)
- **Static spec (committed):** [`openapi.json`](openapi.json) at the repo root — open it in
  [Swagger Editor](https://editor.swagger.io) or Redoc to browse the API without booting the app.

Regenerate the static spec after changing the API:

```bash
composer docs   # → php artisan scramble:export, writes openapi.json
```

### Access control

The docs routes are **not** publicly exposed in production. Scramble's `RestrictedDocsAccess`
middleware serves them freely in the `local` environment; in any other environment it defers to
the `viewApiDocs` gate, which is defined in `AppServiceProvider` to allow **admin users only**.

### Authentication

The API authenticates with a **Sanctum bearer token** obtained from `POST /register` or
`POST /login` (returned in the response `meta.token`). The docs register an HTTP bearer security
scheme applied to every `auth:sanctum` route, so the UI's "Try it out" sends
`Authorization: Bearer <token>`. `register` and `login` are documented as public (no auth).

Responses use the JSON:API media type `application/vnd.api+json`: success payloads are wrapped in
a `data` envelope (collections add top-level `links`/`meta`), and errors use a top-level `errors[]`
array (`{ status, title, detail, source.pointer }`), matching `App\Support\JsonApi`.

## Frontend (Vue SPA)

The web UI is a standalone **Vue 3 SPA** (TypeScript, Composition API with `<script setup>`,
Pinia, vue-router, axios, Tailwind v4 + shadcn-vue components). It consumes the JSON:API over
**Sanctum bearer-token** auth — it is **not** Inertia and uses no session/cookie auth. The SPA
lives under `resources/js/` and is served same-origin by Laravel: `routes/web.php` renders
`resources/views/app.blade.php` (a Vite-mounted shell) for every non-API path, so client-side
history routing works on a full page load.

### Layout

```
resources/js/
  api/         axios client (bearer + JSON:API error normalization) + per-domain modules (auth, books)
  stores/      Pinia (auth session)
  composables/ useAuth, useApiError, useBooks (data access goes through these, never axios in a view)
  components/  ui/ (shadcn-vue primitives), common/, forms/, layout/, books/
  views/       auth, dashboard, account, books
  router/      routes + auth/guest/admin navigation guards
  types/       JSON:API envelopes + domain types
```

### Configuration

The API base URL is read from `VITE_API_BASE_URL` (see `.env.example`). Because the SPA is served
same-origin, the default is the relative path `/api/v1`, which avoids CORS entirely:

```dotenv
VITE_API_BASE_URL="/api/v1"
```

### Install & run

JS dependencies live in the **root** `package.json` (shared `node_modules`, Vite config):

```bash
npm install            # install JS dependencies
composer run dev        # serve Laravel + Vite (HMR) together — then open the Herd URL
# or run the dev server alone:
npm run dev             # Vite dev server (assets only; the page is served by Laravel/Herd)

npm run build           # production build → public/build
npm run lint            # eslint --fix
npm run type-check      # vue-tsc --noEmit
npm run format          # prettier --write
```

Open the app at the Herd URL (e.g. `http://book-rental.test`). The seeded accounts are
`admin@example.com` and `member@example.com` (password `password`); the sidebar menu and available
actions are role-based.

> **Local development & Vue Devtools.** Run `composer run dev` (or `npm run dev`) so the page is
> served by the Vite dev server in **development mode** — this is when the Vue Devtools browser
> extension can inspect the component tree. The production build (`npm run build`, which is what's
> served when no dev server is running) intentionally runs Vue in production mode with Devtools
> disabled.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
