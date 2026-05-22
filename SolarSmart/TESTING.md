# SolarSmart Testing Documentation

## Overview

This document outlines the software testing methodologies and practices implemented in the SolarSmart application. Testing is a critical component of ensuring system reliability, performance, and correctness.

## Types of Tests Implemented

### 1. Unit Testing

Unit tests focus on testing individual components in isolation.

#### Model Tests (`tests/Unit/Models/`)
- **UserTest.php**: Tests user model functionality, role checking, and relationships
- Tests cover:
  - User creation and attributes
  - Role verification (admin, technician, user)
  - Relationship definitions
  - Password hashing
  - Attribute casting

#### Service Tests (`tests/Unit/Services/`)
- **ProductionCalculatorTest.php**: Tests business logic calculations
- Tests cover:
  - Expected production calculations
  - Efficiency calculations
  - Production trends (daily, weekly, monthly)
  - Financial savings calculations
  - CO2 offset calculations
  - Underperforming panel detection

### 2. Feature Testing

Feature tests verify that larger portions of the application work correctly.

#### Authentication Tests (`tests/Feature/Auth/`)
- **AuthenticationTest.php**: Tests user authentication flow
- Tests cover:
  - Login screen rendering
  - Successful authentication
  - Failed authentication (invalid password, email)
  - Form validation
  - User registration
  - Logout functionality
  - Role-based redirects

#### Solar System Management Tests (`tests/Feature/`)
- **SolarSystemManagementTest.php**: Tests CRUD operations
- Tests cover:
  - Viewing solar systems
  - Creating solar systems with validation
  - Updating solar systems
  - Deleting solar systems
  - Authorization checks
  - Input validation

#### API Tests (`tests/Feature/Api/`)
- **ApiTest.php**: Tests RESTful API endpoints
- Tests cover:
  - Authentication requirements
  - CRUD operations via API
  - JSON responses
  - Validation errors
  - Production summary endpoints
  - Chart data endpoints

### 3. Integration Testing

Integration tests verify that different parts of the application work together:
- Database interactions
- Model relationships
- Service class integrations
- API endpoint integrations

## Testing Tools Used

### PHPUnit
- Primary testing framework for PHP
- Configured in `phpunit.xml`
- Run tests with: `php artisan test`

### Laravel Testing Features
- **RefreshDatabase**: Resets database between tests
- **Factory**: Creates test data
- **Faker**: Generates fake data for testing
- **Sanctum**: API authentication testing

### Test Coverage Areas

| Component | Coverage Type |
|-----------|---------------|
| Models | Unit Tests |
| Services | Unit Tests |
| Controllers | Feature Tests |
| Authentication | Feature Tests |
| API Endpoints | Feature Tests |
| Validation | Feature Tests |

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test File
```bash
php artisan test tests/Unit/Services/ProductionCalculatorTest.php
```

### Run With Coverage
```bash
php artisan test --coverage
```

### Run Feature Tests Only
```bash
php artisan test --filter Feature
```

### Run Unit Tests Only
```bash
php artisan test --filter Unit
```

## Test Data Factories

Factories provide a convenient way to generate test data:

- `UserFactory`: Creates user records with different roles
- `SolarSystemFactory`: Creates solar system records
- `PanelFactory`: Creates panel records
- `ProductionFactory`: Creates production records

Example usage:
```php
$user = User::factory()->create();
$admin = User::factory()->admin()->create();
$system = SolarSystem::factory()->create(['user_id' => $user->id]);
```

## API Testing with Postman

The RESTful API can be tested using Postman or similar tools:

### Authentication
- All API endpoints require authentication via Sanctum
- Obtain token through login endpoint
- Include token in Authorization header

### Available Endpoints

#### Solar Systems
- `GET /api/solar-systems` - List all systems
- `POST /api/solar-systems` - Create new system
- `GET /api/solar-systems/{id}` - Get system details
- `PUT /api/solar-systems/{id}` - Update system
- `DELETE /api/solar-systems/{id}` - Delete system

#### Productions
- `GET /api/solar-systems/{id}/productions` - List productions
- `POST /api/solar-systems/{id}/productions` - Add production record
- `GET /api/solar-systems/{id}/productions/chart/data` - Get chart data

#### Alerts
- `GET /api/alerts` - List alerts
- `POST /api/alerts/{id}/acknowledge` - Acknowledge alert
- `POST /api/alerts/{id}/resolve` - Resolve alert

## Performance Testing

For performance testing, consider using:
- **Laravel Telescope**: Monitor application performance
- **Laravel Debugbar**: Development debugging
- **Load Testing Tools**: Apache JMeter, k6

## Continuous Integration

Tests should be run:
1. Before committing code
2. During pull request reviews
3. Before deployment
4. On a scheduled basis (nightly builds)

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Descriptive Names**: Test names should describe what they test
3. **Arrange-Act-Assert**: Structure tests clearly
4. **Factory Usage**: Use factories for test data
5. **Database Transactions**: Use RefreshDatabase trait
6. **Edge Cases**: Test boundary conditions and error cases

## Future Testing Enhancements

1. Browser/End-to-End testing with Laravel Dusk
2. Performance benchmarks
3. Security testing
4. Load testing scripts
5. Automated visual regression testing
