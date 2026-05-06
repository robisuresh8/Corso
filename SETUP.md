# Corso E-Learning Backend Setup Guide

## Prerequisites
- XAMPP installed with PHP 8.2+
- MySQL/MariaDB running
- Composer installed

## Database Setup

1. Create the database:
```sql
CREATE DATABASE corso CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Run migrations:
```bash
cd c:\xampp\htdocs\backend
php spark migrate
```

## Configuration

1. Update `.env` file with your database credentials:
```
database.default.hostname = '127.0.0.1'
database.default.database = corso
database.default.username = root
database.default.password = ''
```

2. Update JWT secret in `.env`:
```
jwt.secret = 'your-very-long-random-secret-key-here'
```

## API Endpoints

### Public Endpoints (No Authentication)

- `POST /api/v1/auth/register` - Register new user
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }
  ```

- `POST /api/v1/auth/login` - Login user
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```
  Returns:
  ```json
  {
    "token": "jwt_token_here",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
  ```

- `GET /api/v1/certificates/verify?id={certificate_id}` - Verify certificate (public)

### Protected Endpoints (Require JWT Token)

Include `Authorization: Bearer {token}` header for these endpoints:

- `GET /api/v1/certificates` - Get user's certificates
- `POST /api/v1/certificates` - Create new certificate
  ```json
  {
    "id": "certificate_id",
    "name": "John Doe",
    "course": "Python Basics",
    "score": 8,
    "total": 10
  }
  ```

## Testing

1. Start XAMPP Apache and MySQL services
2. Access API at: `http://localhost/backend/public/api/v1/ping`
3. Should return: `pong`

## Frontend Integration

The frontend expects the API base URL to be:
```
http://localhost/backend/public/api/v1
```

Make sure this matches your XAMPP setup. If your frontend files are in a different location, update the `CORSO_API_BASE` variable in your HTML files.
