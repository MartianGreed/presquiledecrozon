# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Technology Stack

- **Backend**: PHP 8.2+ with Symfony 6.3, Doctrine ORM, PostgreSQL
- **Frontend**: TypeScript, Webpack Encore, Stimulus/Turbo (Hotwire), Tailwind CSS
- **Deployment**: AWS Lambda via Bref (serverless PHP)
- **External Services**: Stripe (payments), BunnyCDN, Google Maps API, Mailjet

## Essential Commands

### Development

```bash
# Start local development environment
make start

# Stop services
make stop

# Watch frontend changes
npm run watch

# Create database
make create_db

# Load database fixtures
make fixtures
```

### Testing & Quality

```bash
# Run PHP tests
make test

# Generate test coverage
make coverage

# Run static analysis
make phpstan

# Fix code style
make lint

# Run JavaScript tests
npm run test
```

### Building & Deployment

```bash
# Build frontend assets
npm run build:dev      # Development
npm run build:staging  # Staging
npm run build:prod     # Production

# Deploy to AWS Lambda
make deploy_staging
make deploy_prod
```

## Architecture Overview

This is a vacation rental platform for the Presqu'île de Crozon region using Domain-Driven Design (DDD) principles.

### Domain Structure

- **Core Entities**: Booking, Rental, User, Subscription, Conversation, Favorite
- **Value Objects**: Price, BookingStatus, RentalStatus, etc.
- **Repositories**: Interface-based data access layer
- **Message Handlers**: Asynchronous processing via Symfony Messenger

### Ubiquitous Language Usage

When working with domain concepts, always use the established terminology:

- **User** is referred to as `persona` in domain relationships (e.g., in Favorite entity)
- **Favorite** (singular) represents a saved rental by a persona
- **FavoriteList** when referring to the collection of favorites
- **Rental** represents a vacation property
- Use domain entity names in test methods (e.g., `testPersonaCanAddRentalToFavoriteList` not `testUserCanAddFavorites`)
- French UI terms: "Coup de coeur" / "Coups de coeur" (favorites in the interface)

### Key Architectural Patterns

1. **Repository Pattern**: All data access through repository interfaces
2. **Command/Query Separation**: Distinct read/write operations
3. **Event-Driven**: Domain events processed by message handlers
4. **Service Layer**: Business logic encapsulated in services

### Frontend Architecture

- **Stimulus Controllers**: Located in `assets/controllers/`
- **Turbo**: For SPA-like navigation without JavaScript complexity
- **Component-based**: Reusable Twig components in `templates/components/`

### Important Directories

- `src/Domain/`: Core business logic and entities
- `src/Infrastructure/`: External service integrations
- `src/MessageHandler/`: Asynchronous event processors
- `src/Controller/Admin/`: EasyAdmin CRUD controllers
- `assets/controllers/`: Stimulus JavaScript controllers

## Development Guidelines

### Working with Entities

- Always use **Ubiquitous Language** already defined in `src/Domain`
- Use exact domain terminology in code, tests, and documentation:
  - Refer to users as `persona` when in domain context (matching entity relationships)
  - Use singular form for entities (e.g., `Favorite` not `Favorites`)
  - Name test methods using domain language (e.g., `testPersonaCanAddRentalToFavoriteList`)
- Always use constructor property promotion
- Implement value objects for domain concepts
- Use Doctrine lifecycle callbacks sparingly

### Creating New Features

1. Start with domain model in `src/Domain/`
2. Create repository interface and implementation
3. Add necessary validators in `src/Validator/`
4. Implement controller actions
5. Create Stimulus controllers for interactive features

### Testing Approach

- Unit tests for domain logic
- Integration tests for repositories
- Functional tests for controllers
- Use fixtures for test data setup
- Follow domain naming conventions in tests:
  - Test class names should match entity names (singular form)
  - Test methods should describe behavior using domain language
  - Variable names should match domain terminology (e.g., `$persona` not `$user` when dealing with Favorite entity)

### Code Quality Standards

- PHPStan level 8 must pass
- Follow Symfony coding standards (enforced by ECS)
- All new code must have tests
- Use strong typing and strict types declaration

### Before Completing Tasks

Always run these checks before considering any task complete:

1. **Run PHPStan** (`make phpstan`) and fix all issues:
   - Handle nullable types properly (use null checks or type assertions)
   - Ensure all method parameters and return types are correctly typed
   - Fix any type mismatches or unsafe operations
   
2. **Run code style fixer** (`make lint`) to ensure consistent formatting

3. **Run tests** (`make test`) to verify functionality hasn't been broken

4. **Ensure files end with a newline** (required by coding standards)

This ensures all work is committable and maintains the project's high quality standards.
