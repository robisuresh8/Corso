# Corso E-Learning Backend API

CodeIgniter 4 backend API for the Corso E-Learning platform.

## Features

- User registration and authentication with JWT tokens
- Certificate management (create, list, verify)
- RESTful API endpoints
- CORS enabled for frontend integration
- JWT-based authentication for protected routes

## Quick Start

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Configure Database**
   - Update `.env` file with your database credentials
   - Create database: `CREATE DATABASE corso;`

3. **Run Migrations**
   ```bash
   php spark migrate
   ```

4. **Start Server**
   - Ensure XAMPP Apache and MySQL are running
   - API available at: `http://localhost/backend/public/api/v1`

## API Endpoints

### Authentication

- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/login` - Login and get JWT token

### Certificates

- `GET /api/v1/certificates` - Get user's certificates (requires JWT)
- `POST /api/v1/certificates` - Create certificate (requires JWT)
- `GET /api/v1/certificates/verify?id={id}` - Verify certificate (public)

## Authentication

Protected endpoints require a JWT token in the Authorization header:
```
Authorization: Bearer {your_jwt_token}
```

Tokens are valid for 24 hours.

## Project Structure

```
app/
├── Controllers/
│   ├── AuthController.php      # User registration/login
│   └── Certificates.php        # Certificate management
├── Models/
│   ├── UserModel.php           # User model
│   └── CertificateModel.php   # Certificate model
├── Filters/
│   └── JWTAuthFilter.php       # JWT authentication middleware
├── Libraries/
│   ├── JWTService.php          # JWT token handling
│   └── UserService.php         # Current user service
└── Database/
    └── Migrations/             # Database migrations
```

## Configuration

Key configuration files:
- `.env` - Environment variables (database, JWT secret)
- `app/Config/Routes.php` - API routes
- `app/Config/Filters.php` - Filter configuration

## Testing

Test the API with:
```bash
# Health check
curl http://localhost/backend/public/api/v1/ping

# Register user
curl -X POST http://localhost/backend/public/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# Login
curl -X POST http://localhost/backend/public/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## Frontend Integration

The frontend expects the API base URL:
```
http://localhost/backend/public/api/v1
```

Update `CORSO_API_BASE` in your HTML files if needed.
