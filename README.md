# Paya Transfer System

A Laravel-based API for a bank transfer system inspired by Iran's Paya (ACH) system. This project allows users to create, list, and update transfer requests between accounts using Sheba numbers (Iranian IBANs). It supports basic banking operations like balance checks, reserving funds, and transaction logging.

## Table of Contents
- [Features](#features)
- [Technologies](#technologies)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)

## Features
- Create transfer requests with source and destination Sheba numbers.
- Update transfer request status (confirmed or canceled) with balance adjustments.
- List transfer requests sorted by creation date.
- Validate Sheba numbers and ensure sufficient account balance.
- Log transactions for debits, credits, and refunds.
- Use UUIDs for unique identification of accounts and requests.
- Database indexing on `sheba_number` and `uuid` for performance.

## Technologies
- **Laravel**: 10.x
- **PHP**: 8.1+
- **MySQL**: 8.0+
- **Docker**: For local development
- **Postman**: For API testing

## Project Structure
```
app/
├── DTOs/
│   └── PayaRequestDTO.php        # Data Transfer Object for request validation
├── Enums/
│   └── PayaRequestStatus.php     # Enum for transfer request statuses
│   └── TransactionType.php       # Enum for transaction statuses
├── Http/
│   ├── Controllers/
│   │   └── PayaController.php    # API controller for transfer requests
├── Models/
│   ├── Account.php               # Model for bank accounts
│   ├── PayaRequest.php           # Model for transfer requests
│   ├── Transaction.php           # Model for transaction logs
│   └── User.php                  # Model for users
├── Services/
│   └── PayaService.php           # Business logic for transfers
database/
├── migrations/                   # Database schema migrations
└── seeders/
    └── DatabaseSeeder.php        # Seeder for test data
```

## Setup Instructions
1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd paya-transfer-system
   ```

2. **Set Up Environment**:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update `.env` with your database credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=mysql
     DB_PORT=3306
     DB_DATABASE=laravel
     DB_USERNAME=laravel
     DB_PASSWORD=laravel
     ```

3. **Run Docker**:
   - Start the containers:
     ```bash
     docker-compose up -d
     ```

4. **Install Dependencies**:
   ```bash
   docker-compose exec app composer install
   ```

5. **Run Migrations**:
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **Seed the Database** (optional for test data):
   ```bash
   docker-compose exec app php artisan db:seed
   ```

7. **Start the Application**:
   - The API will be available at `http://localhost:8081`.
