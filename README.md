# Account Management System

A Laravel-based Account Management System with secure API endpoints for managing accounts and transactions.

## Features

- User Authentication with Laravel Sanctum
- Account Management (Create, Read, Update, Deactivate)
- Transaction Management
- Luhn Algorithm for Account Number Generation
- Rate Limiting
- Input Validation
- Authorization Checks
- Fund Transfers between Accounts
- Webhook Notifications
- PDF Account Statements

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd account_management_system
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=account_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. Start the development server:
```bash
php artisan serve
```

## API Documentation

### Authentication

#### Register User
```
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login
```
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Account Management

#### Create Account
```
POST /api/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
    "account_name": "Savings Account",
    "account_type": "Personal",
    "currency": "USD",
    "initial_balance": 1000
}
```

#### Get Account Details
```
GET /api/accounts/{account_number}
Authorization: Bearer {token}
```

#### Update Account
```
PUT /api/accounts/{account_number}
Authorization: Bearer {token}
Content-Type: application/json

{
    "account_name": "Updated Account Name",
    "account_type": "Business",
    "currency": "EUR"
}
```

#### Deactivate Account
```
DELETE /api/accounts/{account_number}
Authorization: Bearer {token}
```

### Transaction Management

#### Create Transaction
```
POST /api/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
    "account_number": "123456789012",
    "type": "deposit",
    "amount": 100.00,
    "description": "Initial deposit",
    "date": "2024-03-20"
}
```

#### List Transactions
```
GET /api/transactions
Authorization: Bearer {token}
```

### Fund Transfers

#### Transfer Funds
```
POST /api/transfers
Authorization: Bearer {token}
Content-Type: application/json

{
    "from_account": "123456789012",
    "to_account": "987654321098",
    "amount": 50.00,
    "description": "Transfer to savings"
}
```

### Account Statements

#### Generate PDF Statement
```
GET /api/accounts/{account_number}/statement
Authorization: Bearer {token}
Query Parameters:
- start_date: YYYY-MM-DD
- end_date: YYYY-MM-DD
```

### Webhooks

The system supports webhook notifications for various events. Configure webhook URLs in your account settings:

```
POST /api/webhooks
Authorization: Bearer {token}
Content-Type: application/json

{
    "url": "https://your-domain.com/webhook",
    "events": ["transaction.created", "account.updated"]
}
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Security

- All API endpoints are protected with Laravel Sanctum authentication
- Rate limiting is implemented (60 requests per minute)
- Input validation and sanitization
- Authorization checks for all operations
- Secure password hashing
- CSRF protection

## Rate Limiting

- Public routes: 60 requests per minute
- Protected routes: 60 requests per minute per user

## Error Responses

All error responses follow this format:
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License.