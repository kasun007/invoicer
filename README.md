# Invoice Management System

A modern, secure REST API-based invoice management system built with Symfony 7, featuring JWT authentication, comprehensive CRUD operations, and automated invoice calculations.

![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue)
![Symfony Version](https://img.shields.io/badge/Symfony-7.1-green)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Authentication & Security](#authentication--security)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

The Invoice Management System is a professional-grade application designed to help businesses manage their invoicing processes efficiently. It provides a complete RESTful API for creating, managing, and tracking invoices, customers, and user accounts with enterprise-level security.

### What Does This Application Do?

This application serves as a backend system for:

1. **User Management**: Secure user registration, authentication, and profile management
2. **Customer Management**: Track and manage customer information
3. **Invoice Generation**: Create detailed invoices with line items
4. **Automated Calculations**: Automatic computation of subtotals, taxes, discounts, and totals
5. **Invoice Tracking**: Monitor invoice status (draft, sent, paid, overdue, cancelled)
6. **Secure API Access**: JWT-based authentication for all operations

### Who Is This For?

- **Freelancers**: Manage clients and generate professional invoices
- **Small Businesses**: Track billing and customer payments
- **Developers**: Use as a backend for invoice management applications
- **Accounting Systems**: Integrate invoice functionality into larger systems

## ✨ Features

### Core Features

- ✅ **User Authentication**
  - Secure user registration with password hashing
  - JWT token-based authentication
  - Session management with token expiration
  - Role-based access control

- ✅ **Customer Management**
  - Create, read, update, and delete customers
  - Unique email validation
  - Customer profile tracking
  - Timestamp tracking for audit trails

- ✅ **Invoice Management**
  - Create invoices with multiple line items
  - Automatic calculation of:
    - Subtotals
    - Tax amounts (based on tax rate)
    - Discounts
    - Total amounts
  - Unique invoice number generation
  - Multiple currency support
  - Invoice status tracking
  - Custom notes and payment terms

- ✅ **Invoice Items**
  - Detailed line items per invoice
  - Quantity and unit price tracking
  - Automatic line total calculation
  - Custom unit types (hours, items, days, etc.)

### Technical Features

- 🔒 **Security**
  - Bcrypt password hashing
  - JWT token authentication
  - Request validation and sanitization
  - CORS protection
  - SQL injection prevention via Doctrine ORM

- 📊 **Database**
  - PostgreSQL with Docker support
  - Automated migrations
  - Entity relationships with foreign keys
  - Soft deletes support (can be implemented)

- 🚀 **API**
  - RESTful architecture
  - JSON request/response format
  - Comprehensive error handling
  - Consistent response structure

- 📝 **Documentation**
  - Complete API documentation
  - Inline code documentation
  - Setup and deployment guides

## 🛠 Technology Stack

### Backend Framework
- **Symfony 7.1** - Modern PHP framework
- **PHP 8.2+** - Latest PHP with type declarations
- **Doctrine ORM** - Database abstraction layer

### Database
- **PostgreSQL 16** - Robust relational database
- **Doctrine Migrations** - Version control for database schema

### Security
- **Firebase JWT** - JSON Web Token implementation
- **Symfony Security** - Password hashing and user authentication

### Development Tools
- **Docker & Docker Compose** - Containerized development environment
- **Composer** - PHP dependency management
- **Symfony CLI** - Development server and tools

### Testing & Quality
- **PHPUnit** - Unit testing framework
- **Symfony Test Pack** - Integration testing tools

## 🏗 Architecture

### Project Structure

```
invoicer/
├── src/
│   ├── Controller/          # API endpoints
│   │   ├── AuthController.php
│   │   ├── CustomerController.php
│   │   ├── InvoiceController.php
│   │   └── UserController.php
│   ├── Entity/              # Database models
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── Invoice.php
│   │   └── InvoiceItem.php
│   ├── Repository/          # Database queries
│   │   ├── UserRepository.php
│   │   ├── CustomerRepository.php
│   │   ├── InvoiceRepository.php
│   │   └── InvoiceItemRepository.php
│   ├── Service/             # Business logic
│   │   └── JwtService.php
│   ├── EventSubscriber/     # Event listeners
│   │   └── JwtAuthenticationSubscriber.php
│   └── Kernel.php           # Application kernel
├── migrations/              # Database migrations
├── config/                  # Configuration files
├── public/                  # Public web directory
├── tests/                   # Test files
├── docker-compose.yml       # Docker configuration
├── .env                     # Environment variables
└── composer.json            # PHP dependencies
```

### Entity Relationships

```
User (Authentication)
  - One-to-Many with Customer (future feature)
  - Stores credentials and profile

Customer
  - One-to-Many with Invoice
  - Stores customer information

Invoice
  - Many-to-One with Customer
  - One-to-Many with InvoiceItem
  - Stores invoice metadata and totals

InvoiceItem
  - Many-to-One with Invoice
  - Stores individual line items
```

### Data Flow

```
1. User Authentication
   Client → Login/Register → JWT Token → Store Token

2. Create Invoice
   Client (with Token) → Create Invoice Request
   → Validate Customer
   → Calculate Totals
   → Save Invoice + Items
   → Return Invoice Data

3. Retrieve Invoice
   Client (with Token) → Get Invoice Request
   → Validate Token
   → Fetch Invoice with Items
   → Return Formatted Data
```

## 🚀 Installation

### Prerequisites

- **Docker Desktop** - For containerized environment
- **PHP 8.2+** - For local development (optional)
- **Composer** - For PHP dependencies
- **Git** - For version control

### Step-by-Step Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/kasun007/invoicer.git
   cd invoicer
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   ```bash
   # Copy environment file
   cp .env .env.local
   
   # Edit .env.local with your settings
   nano .env.local
   ```

4. **Start Docker Services**
   ```bash
   docker compose up -d
   ```

5. **Run Database Migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Start Symfony Server**
   ```bash
   symfony server:start
   # Or use PHP built-in server
   php -S localhost:8000 -t public
   ```

7. **Verify Installation**
   ```bash
   curl http://localhost:8000/api
   ```

## ⚙️ Configuration

### Environment Variables

Edit `.env` or `.env.local`:

```env
# Application
APP_ENV=dev
APP_SECRET=your-secret-key

# Database
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"

# JWT
JWT_SECRET=your-jwt-secret-key-change-this-in-production
JWT_EXPIRATION=86400

# Server
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

### Database Configuration

The PostgreSQL database is configured via Docker Compose:

```yaml
services:
  database:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: app
      POSTGRES_PASSWORD: !ChangeMe!
      POSTGRES_USER: app
    ports:
      - "5432:5432"
```

### JWT Configuration

JWT tokens are configured in `config/services.yaml`:

```yaml
parameters:
    jwt_secret: '%env(JWT_SECRET)%'
    jwt_expiration: '%env(int:JWT_EXPIRATION)%'
```

## 💻 Usage

### Quick Start Guide

1. **Register a User**
   ```bash
   curl -X POST http://localhost:8000/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{
       "email": "user@example.com",
       "password": "SecurePass123!",
       "name": "John Doe"
     }'
   ```

2. **Save the JWT Token**
   ```bash
   TOKEN="eyJ0eXAiOiJKV1QiLCJhbGci..."
   ```

3. **Create a Customer**
   ```bash
   curl -X POST http://localhost:8000/api/customers \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Acme Corp",
       "email": "contact@acme.com"
     }'
   ```

4. **Create an Invoice**
   ```bash
   curl -X POST http://localhost:8000/api/invoices \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "customerEmail": "contact@acme.com",
       "invoiceNumber": "INV-2025-001",
       "issueDate": "2025-10-20",
       "dueDate": "2025-11-20",
       "status": "draft",
       "currency": "USD",
       "taxRate": 10.00,
       "items": [
         {
           "description": "Web Development",
           "quantity": 10,
           "unitPrice": 150.00,
           "unit": "hours"
         }
       ]
     }'
   ```

5. **List All Invoices**
   ```bash
   curl -X GET http://localhost:8000/api/invoices \
     -H "Authorization: Bearer $TOKEN"
   ```

## 📚 API Documentation

Complete API documentation is available in [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md)

### Available Endpoints

#### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login and get JWT token
- `GET /api/auth/me` - Get current user info

#### Customers
- `GET /api/customers` - List all customers
- `GET /api/customers/{id}` - Get customer by ID
- `POST /api/customers` - Create customer
- `PUT /api/customers/{id}` - Update customer
- `DELETE /api/customers/{id}` - Delete customer

#### Invoices
- `GET /api/invoices` - List all invoices with items
- `GET /api/invoices/{id}` - Get invoice by ID with items
- `POST /api/invoices` - Create invoice with items
- `PUT /api/invoices/{id}` - Update invoice
- `DELETE /api/invoices/{id}` - Delete invoice

#### Users
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get user by ID
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

## 🗄 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    roles JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP,
    last_login_at TIMESTAMP
);
```

### Customers Table
```sql
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP NOT NULL
);
```

### Invoices Table
```sql
CREATE TABLE invoices (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL REFERENCES customers(id),
    invoice_number VARCHAR(20) UNIQUE NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    subtotal NUMERIC(10, 2) NOT NULL,
    tax_rate NUMERIC(5, 2),
    tax_amount NUMERIC(10, 2),
    discount_amount NUMERIC(10, 2),
    total_amount NUMERIC(10, 2) NOT NULL,
    notes TEXT,
    currency VARCHAR(3) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP
);
```

### Invoice Items Table
```sql
CREATE TABLE invoice_items (
    id SERIAL PRIMARY KEY,
    invoice_id INTEGER NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    description VARCHAR(255) NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(10, 2) NOT NULL,
    line_total NUMERIC(10, 2) NOT NULL,
    unit VARCHAR(100)
);
```

## 🔒 Authentication & Security

### JWT Token Structure

```json
{
  "user_id": 1,
  "email": "user@example.com",
  "iat": 1697788800,
  "exp": 1697875200
}
```

### Password Security

- Passwords are hashed using **bcrypt** algorithm
- Minimum 8 characters required (customizable)
- Passwords never stored or returned in plain text
- Automatic salting via Symfony Security

### API Security

- All endpoints (except auth) require valid JWT token
- Token must be included in `Authorization: Bearer {token}` header
- Tokens expire after 24 hours (configurable)
- Invalid tokens return `401 Unauthorized`
- Missing tokens return `401 Unauthorized`

### Best Practices Implemented

✅ Input validation and sanitization  
✅ SQL injection prevention via ORM  
✅ XSS protection  
✅ CORS configuration  
✅ Secure password hashing  
✅ Token-based authentication  
✅ Environment variable protection  

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php bin/phpunit

# Run specific test
php bin/phpunit tests/Controller/InvoiceControllerTest.php

# Run with coverage
php bin/phpunit --coverage-html coverage
```

### Manual Testing

Use the provided Postman collection or curl commands from [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md)

## 🚢 Deployment

### Production Checklist

- [ ] Change `APP_ENV=prod` in `.env`
- [ ] Generate strong `APP_SECRET`
- [ ] Generate strong `JWT_SECRET`
- [ ] Update database credentials
- [ ] Configure CORS for your domain
- [ ] Enable HTTPS
- [ ] Set up database backups
- [ ] Configure logging
- [ ] Set up monitoring

### Docker Production Deployment

```bash
# Build production image
docker compose -f docker-compose.prod.yml build

# Run in production mode
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Traditional Server Deployment

1. Upload files to server
2. Install dependencies: `composer install --no-dev --optimize-autoloader`
3. Configure web server (Apache/Nginx)
4. Run migrations: `php bin/console doctrine:migrations:migrate`
5. Clear cache: `php bin/console cache:clear --env=prod`
6. Set proper file permissions

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Code Style

- Follow PSR-12 coding standard
- Use type declarations
- Write PHPDoc comments
- Keep functions small and focused

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Kasun Wickramanayake**
- Email: kwickramanayake007@gmail.com
- GitHub: [@kasun007](https://github.com/kasun007)

## 🙏 Acknowledgments

- Symfony Framework Team
- Doctrine ORM Team
- Firebase JWT Library
- PostgreSQL Community

## 📞 Support

For issues, questions, or suggestions:

1. Open an issue on GitHub
2. Email: kwickramanayake007@gmail.com
3. Check the [API Documentation](API_DOCUMENTATION.md)

## 🗺 Roadmap

### Upcoming Features

- [ ] PDF invoice generation
- [ ] Email notifications
- [ ] Payment gateway integration
- [ ] Recurring invoices
- [ ] Multi-currency support
- [ ] Invoice templates
- [ ] Advanced reporting
- [ ] Bulk operations
- [ ] Audit logs
- [ ] API rate limiting

## 📊 Project Status

**Current Version:** 1.0.0  
**Status:** Active Development  
**Last Updated:** October 20, 2025

---

Made with ❤️ using Symfony
