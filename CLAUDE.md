# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture Overview

This is a Laravel 12 + Vue 3 + Inertia.js application using TypeScript and Tailwind CSS with shadcn/ui components. The application follows a full-stack architecture where:

- **Backend**: Laravel serves as the API and handles authentication, routing, and data persistence
- **Frontend**: Vue 3 with TypeScript for reactive UI components
- **Bridge**: Inertia.js eliminates the need for separate API endpoints by enabling server-side rendering with client-side navigation
- **Styling**: Tailwind CSS with custom UI components built on shadcn/ui (via reka-ui)
- **Build System**: Vite for fast development and optimized production builds

### Key Architectural Patterns

- **Page-based routing**: Vue components in `resources/js/pages/` correspond to Laravel routes
- **Layout system**: Nested layouts in `resources/js/layouts/` provide consistent structure
- **Component architecture**: Reusable UI components in `resources/js/components/ui/` following shadcn/ui patterns
- **Authentication**: Laravel Breeze-style authentication with Inertia.js integration
- **Settings system**: Modular settings pages with dedicated routes and controllers

## Development Commands

### Backend (Laravel)
- `composer dev` - Start all development servers (Laravel, queue, logs, Vite)
- `composer dev:ssr` - Start with server-side rendering enabled
- `composer test` - Run PHP tests with PHPUnit
- `php artisan serve` - Start Laravel development server only
- `php artisan test` - Run tests manually
- `php artisan migrate` - Run database migrations

### Frontend (Node.js)
- `npm run dev` - Start Vite development server only
- `npm run build` - Build for production
- `npm run build:ssr` - Build with SSR support
- `npm run lint` - Run ESLint with auto-fix
- `npm run format` - Format code with Prettier
- `npm run format:check` - Check formatting without changes

### Recommended Development Flow
Use `composer dev` to start all services simultaneously (server, queue, logs, and Vite).

## Project Structure

### Backend Structure
- `app/Http/Controllers/` - Laravel controllers organized by feature
- `app/Http/Controllers/Auth/` - Authentication controllers
- `app/Http/Controllers/Settings/` - User settings controllers
- `app/Models/` - Eloquent models
- `routes/` - Route definitions (web.php, auth.php, settings.php)
- `database/migrations/` - Database schema migrations
- `tests/Feature/` - Feature tests for application functionality
- `tests/Unit/` - Unit tests for individual components

### Frontend Structure
- `resources/js/pages/` - Vue pages that correspond to routes
- `resources/js/layouts/` - Layout components for different page types
- `resources/js/components/` - Reusable Vue components
- `resources/js/components/ui/` - Base UI components (shadcn/ui style)
- `resources/js/composables/` - Vue composables for shared logic
- `resources/js/lib/` - Utility functions and helpers
- `resources/js/types/` - TypeScript type definitions

### Key Files
- `vite.config.ts` - Vite configuration with Laravel integration
- `components.json` - shadcn/ui component configuration
- `tsconfig.json` - TypeScript configuration
- `phpunit.xml` - PHP testing configuration

## Testing

### PHP Tests
- Run `composer test` or `php artisan test`
- Tests located in `tests/Feature/` and `tests/Unit/`
- Uses PHPUnit with Laravel testing utilities
- Database tests use SQLite in-memory database

### Frontend Testing
- No frontend testing framework currently configured
- ESLint and Prettier ensure code quality

## Code Quality Tools

- **PHP**: Laravel Pint for code formatting (development dependency)
- **TypeScript**: ESLint with Vue and TypeScript support
- **Formatting**: Prettier with Tailwind CSS plugin
- **Type Checking**: vue-tsc for Vue TypeScript compilation

## Special Considerations

### Inertia.js Integration
- Pages are Vue components that receive props from Laravel controllers
- No need for separate API routes - data flows through Inertia props
- Client-side routing feels like SPA while maintaining server-side rendering benefits

### UI Component System
- Built on reka-ui (headless UI library)
- Components follow shadcn/ui patterns and naming
- Tailwind CSS with custom configuration
- Theme system with dark/light mode support via `useAppearance` composable

### Authentication Flow
- Laravel Breeze-style authentication
- Inertia.js handles form submissions and redirects
- Protected routes use Laravel middleware