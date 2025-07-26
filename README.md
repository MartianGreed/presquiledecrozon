# Presqu'île de Crozon - Vacation Rental Platform

A vacation rental platform for the Presqu'île de Crozon region, built with Symfony and modern web technologies.

## Technology Stack

### Backend
- **PHP 8.2+** with **Symfony 7.3**
- **Doctrine ORM** with PostgreSQL database
- **AWS Lambda** deployment via Bref (serverless PHP)
- **Domain-Driven Design (DDD)** architecture

### Frontend
- **TypeScript** with Webpack Encore
- **Stimulus/Turbo** (Hotwire) for interactive components
- **Tailwind CSS** for styling
- **Google Maps API** integration

### External Services
- **Stripe** - Payment processing
- **BunnyCDN** - Content delivery
- **Mailjet** - Email delivery
- **Google Maps API** - Geolocation services

## Requirements

- PHP 8.2 or higher
- Node.js 16+ and npm/pnpm
- Docker and Docker Compose
- Symfony CLI
- PostgreSQL 14+

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd presquiledecrozon
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
# or
pnpm install
```

4. Start the local development environment:
```bash
make start
```

5. Load database fixtures:
```bash
make fixtures
```

6. Build frontend assets:
```bash
npm run build:dev
```

The application will be available at `https://127.0.0.1:8000` (Symfony local server).

## Development

### Essential Commands

```bash
# Start/stop local environment
make start
make stop

# Watch frontend changes
npm run watch

# Load database fixtures
make fixtures

# Run tests
make test

# Run static analysis
make phpstan

# Fix code style
make lint
```

### Frontend Development

```bash
# Development server with hot reload
npm run dev

# Watch mode
npm run watch

# Build for different environments
npm run build:dev
npm run build:staging
npm run build:prod
```

## Testing & Code Quality

### PHP Tests
```bash
# Run all tests
make test

# Generate coverage report
make coverage

# Run specific test class
make test_class filter=TestClassName
```

### JavaScript Tests
```bash
# Run tests
npm run test

# Watch mode
npm run test:watch
```

### Code Quality Tools
```bash
# PHPStan static analysis (level 8)
make phpstan

# Code style fixes (Rector + ECS)
make lint
```

## Architecture Overview

The application follows **Domain-Driven Design (DDD)** principles:

### Domain Structure
- **Core Entities**: Booking, Rental, User, Subscription, Conversation
- **Value Objects**: Price, BookingStatus, RentalStatus
- **Repositories**: Interface-based data access layer
- **Message Handlers**: Asynchronous processing via Symfony Messenger

### Key Patterns
1. **Repository Pattern** - All data access through repository interfaces
2. **Command/Query Separation** - Distinct read/write operations
3. **Event-Driven** - Domain events processed by message handlers
4. **Service Layer** - Business logic encapsulated in services

### Frontend Architecture
- **Stimulus Controllers** - Located in `assets/controllers/`
- **Turbo** - SPA-like navigation without JavaScript complexity
- **Component-based** - Reusable Twig components in `templates/components/`

## Deployment

### Staging Deployment
```bash
# Build assets and deploy
make deploy_staging
```

### Production Deployment
```bash
# Build assets and deploy
npm run build:prod
php bin/console app:assets:upload
make deploy_prod
```

The application is deployed as a serverless application on AWS Lambda using Bref.

## Directory Structure

```
├── assets/              # Frontend source files
│   ├── controllers/     # Stimulus controllers
│   ├── styles/         # SCSS styles
│   └── src/            # TypeScript utilities
├── config/             # Symfony configuration
├── migrations/         # Database migrations
├── public/             # Web root
├── src/
│   ├── Controller/     # HTTP controllers
│   ├── Domain/         # Core business logic
│   ├── Entity/         # Doctrine entities
│   ├── Form/           # Symfony forms
│   ├── Infrastructure/ # External integrations
│   ├── MessageHandler/ # Async event handlers
│   ├── Repository/     # Data access layer
│   └── Service/        # Application services
├── templates/          # Twig templates
└── tests/              # Test suites
```

## Domain Models

- **Booking** - Rental reservations and booking management
- **Rental** - Property listings with configuration and pricing
- **User** - User accounts and authentication
- **Subscription** - Owner subscription plans
- **Conversation** - Messaging between owners and guests
- **Data** - Reference data (locations, furniture, etc.)

## Contributing

1. Follow Symfony coding standards (enforced by ECS)
2. Ensure PHPStan level 8 passes
3. Write tests for new features
4. Use strong typing and strict types declaration
5. Follow existing patterns and conventions in the codebase

For more detailed information, see [CLAUDE.md](./CLAUDE.md) for AI-assisted development guidelines.


