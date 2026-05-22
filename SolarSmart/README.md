# SolarSmart - Solar Energy Monitoring System

A comprehensive full-stack Laravel application for monitoring, managing, and analyzing solar energy production systems in real-time.

## Features

### Core Functionality
- **User Authentication**: Secure login/registration with role-based access (Admin, User, Technician)
- **Solar System Management**: Add and manage multiple solar energy systems
- **Panel Management**: Track individual solar panels with real-time monitoring
- **Production Tracking**: Monitor daily and monthly energy production
- **Alert System**: Automated alerts for low production, panel faults, and maintenance needs
- **Technician Interface**: Dedicated dashboard for maintenance staff
- **Interactive Charts**: Visualize energy data with Chart.js

### Dashboard Features
- Real-time energy production statistics
- Daily and monthly production charts
- Active alerts summary
- System efficiency metrics
- Weather condition tracking

### Testing Implementation
The application demonstrates professional software testing methodologies:

#### Unit Tests
- Model relationship testing
- Business logic validation
- Service class testing

#### Feature Tests
- Authentication flow testing
- CRUD operation testing
- API endpoint testing
- Authorization testing

#### Integration Tests
- Database interaction testing
- API integration testing

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Sanctum (for API)

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5.3
- **Icons**: Bootstrap Icons
- **Charts**: Chart.js
- **Font**: Figtree (Google Fonts)

### Testing
- **Framework**: PHPUnit
- **Factories**: Laravel Model Factories
- **Faker**: Test data generation

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL or SQLite

### Setup Steps

1. **Clone the repository**
```bash
cd SolarSmart
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=sqlite
# or
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solarsmart
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Seed database (optional)**
```bash
php artisan db:seed
```

7. **Start development server**
```bash
php artisan serve
```

## Usage

### Accessing the Application

1. **Web Interface**: Visit `http://localhost:8000`
2. **Login**: Use registered credentials or create a new account
3. **Dashboard**: View energy statistics and manage solar systems

### User Roles

#### Regular User
- View own solar systems
- Monitor production data
- Receive alerts
- Schedule maintenance

#### Technician
- View assigned interventions
- Update panel status
- Complete maintenance tasks
- Access technician dashboard

#### Admin
- Full system access
- Manage all users and systems
- View all alerts and interventions

## API Documentation

The application provides a RESTful API for external integrations.

### Authentication
All API endpoints require authentication via Laravel Sanctum.

### Endpoints

#### Solar Systems
- `GET /api/solar-systems` - List all systems
- `POST /api/solar-systems` - Create system
- `GET /api/solar-systems/{id}` - Get system details
- `PUT /api/solar-systems/{id}` - Update system
- `DELETE /api/solar-systems/{id}` - Delete system
- `GET /api/solar-systems/{id}/production-summary` - Production statistics
- `GET /api/solar-systems/{id}/production-trend` - Production trends

#### Productions
- `GET /api/solar-systems/{id}/productions` - List productions
- `POST /api/solar-systems/{id}/productions` - Add production
- `GET /api/solar-systems/{id}/productions/statistics/summary` - Statistics
- `GET /api/solar-systems/{id}/productions/chart/data` - Chart data

#### Alerts
- `GET /api/alerts` - List alerts
- `POST /api/alerts/{id}/acknowledge` - Acknowledge alert
- `POST /api/alerts/{id}/resolve` - Resolve alert
- `GET /api/alerts/active/count` - Active alert count
- `GET /api/alerts/summary` - Alert summary

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Services/ProductionCalculatorTest.php

# Run with coverage
php artisan test --coverage

# Run feature tests only
php artisan test --filter Feature

# Run unit tests only
php artisan test --filter Unit
```

### Test Structure
```
tests/
├── Unit/
│   ├── Models/
│   │   └── UserTest.php
│   └── Services/
│       └── ProductionCalculatorTest.php
├── Feature/
│   ├── Auth/
│   │   └── AuthenticationTest.php
│   ├── Api/
│   │   └── ApiTest.php
│   └── SolarSystemManagementTest.php
```

## Database Schema

### Tables
- **users**: User accounts with roles
- **solar_systems**: Solar energy systems
- **panels**: Individual solar panels
- **productions**: Energy production records
- **alerts**: System alerts and notifications
- **interventions**: Maintenance records

## Business Logic

### Production Calculation
- Expected daily production = Capacity (kW) × Peak Sun Hours (5)
- Efficiency = (Actual / Expected) × 100
- CO2 Offset = Energy Produced × 0.4 kg/kWh

### Alert Generation
- Low production alerts (efficiency < 50%)
- Panel fault detection
- Maintenance scheduling
- System offline detection

## Security Features

- Password hashing with bcrypt
- CSRF protection
- Role-based access control
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade escaping)

## Performance Optimization

- Database indexing on frequently queried columns
- Eager loading for relationships
- Query optimization with scopes
- Cached calculations where appropriate

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is open-source and available under the MIT License.

## Support

For issues and feature requests, please use the GitHub issue tracker.

## Acknowledgments

- Laravel Framework
- Bootstrap
- Chart.js
- Laravel Community
