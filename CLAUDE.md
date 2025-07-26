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

- **Core Entities**: Booking, Rental, User, Subscription, Conversation
- **Value Objects**: Price, BookingStatus, RentalStatus, etc.
- **Repositories**: Interface-based data access layer
- **Message Handlers**: Asynchronous processing via Symfony Messenger

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

### Code Quality Standards

- PHPStan level 8 must pass
- Follow Symfony coding standards (enforced by ECS)
- All new code must have tests
- Use strong typing and strict types declaration

