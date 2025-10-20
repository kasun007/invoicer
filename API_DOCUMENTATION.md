# Invoice Management System - API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All API endpoints (except `/auth/register` and `/auth/login`) require JWT authentication.

Include the JWT token in the `Authorization` header:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

---

## Table of Contents

1. [Authentication](#authentication-endpoints)
2. [Customers](#customer-endpoints)
3. [Invoices](#invoice-endpoints)
4. [Users](#user-endpoints)

---

## Authentication Endpoints

### 1. Register New User

Create a new user account and receive a JWT token.

**Endpoint:** `POST /api/auth/register`

**Headers:**
```json
{
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123!",
  "name": "John Doe"
}
```

**Response:** `201 Created`
```json
{
  "message": "User registered successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "roles": ["ROLE_USER"]
  }
}
```

**Error Responses:**
- `400 Bad Request` - Missing required fields or invalid data
- `409 Conflict` - Email already exists

---

### 2. Login

Authenticate and receive a JWT token.

**Endpoint:** `POST /api/auth/login`

**Headers:**
```json
{
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123!"
}
```

**Response:** `200 OK`
```json
{
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "roles": ["ROLE_USER"]
  }
}
```

**Error Responses:**
- `400 Bad Request` - Missing email or password
- `401 Unauthorized` - Invalid credentials

---

### 3. Get Current User

Get information about the currently authenticated user.

**Endpoint:** `GET /api/auth/me`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "email": "user@example.com",
  "name": "John Doe",
  "roles": ["ROLE_USER"],
  "createdAt": "2025-10-20T10:30:00+00:00"
}
```

**Error Responses:**
- `401 Unauthorized` - Invalid or missing token

---

## Customer Endpoints

### 1. Get All Customers

Retrieve a list of all customers.

**Endpoint:** `GET /api/customers`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
[
  {
    "id": 1,
    "name": "Acme Corporation",
    "email": "contact@acme.com",
    "createdAt": "2025-10-20T10:00:00+00:00"
  },
  {
    "id": 2,
    "name": "Tech Solutions Inc",
    "email": "info@techsolutions.com",
    "createdAt": "2025-10-20T11:00:00+00:00"
  }
]
```

---

### 2. Get Customer by ID

Retrieve a specific customer.

**Endpoint:** `GET /api/customers/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "name": "Acme Corporation",
  "email": "contact@acme.com",
  "createdAt": "2025-10-20T10:00:00+00:00"
}
```

**Error Responses:**
- `404 Not Found` - Customer not found

---

### 3. Create Customer

Create a new customer.

**Endpoint:** `POST /api/customers`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "name": "Acme Corporation",
  "email": "contact@acme.com"
}
```

**Response:** `201 Created`
```json
{
  "id": 1,
  "name": "Acme Corporation",
  "email": "contact@acme.com",
  "createdAt": "2025-10-20T10:00:00+00:00"
}
```

**Error Responses:**
- `400 Bad Request` - Invalid data or missing required fields
- `409 Conflict` - Email already exists

---

### 4. Update Customer

Update an existing customer.

**Endpoint:** `PUT /api/customers/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "name": "Acme Corporation Ltd",
  "email": "contact@acme.com"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "name": "Acme Corporation Ltd",
  "email": "contact@acme.com",
  "createdAt": "2025-10-20T10:00:00+00:00"
}
```

**Error Responses:**
- `404 Not Found` - Customer not found
- `400 Bad Request` - Invalid data

---

### 5. Delete Customer

Delete a customer.

**Endpoint:** `DELETE /api/customers/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `204 No Content`

**Error Responses:**
- `404 Not Found` - Customer not found

---

## Invoice Endpoints

### 1. Get All Invoices

Retrieve a list of all invoices with their items.

**Endpoint:** `GET /api/invoices`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
[
  {
    "id": 1,
    "invoiceNumber": "INV-2025-001",
    "customer": {
      "id": 1,
      "name": "Acme Corporation",
      "email": "contact@acme.com"
    },
    "issueDate": "2025-10-20",
    "dueDate": "2025-11-20",
    "status": "draft",
    "currency": "USD",
    "subtotal": 1515.99,
    "taxRate": 10.00,
    "taxAmount": 151.60,
    "discountAmount": 0.00,
    "totalAmount": 1667.59,
    "notes": "Payment due within 30 days",
    "createdAt": "2025-10-20T10:00:00+00:00",
    "updatedAt": "2025-10-20T10:00:00+00:00",
    "items": [
      {
        "id": 1,
        "description": "Web Development Services",
        "quantity": 10,
        "unit": "hours",
        "unitPrice": 150.00,
        "lineTotal": 1500.00
      },
      {
        "id": 2,
        "description": "Domain Registration",
        "quantity": 1,
        "unit": "year",
        "unitPrice": 15.99,
        "lineTotal": 15.99
      }
    ]
  }
]
```

---

### 2. Get Invoice by ID

Retrieve a specific invoice with its items.

**Endpoint:** `GET /api/invoices/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "invoiceNumber": "INV-2025-001",
  "customer": {
    "id": 1,
    "name": "Acme Corporation",
    "email": "contact@acme.com"
  },
  "issueDate": "2025-10-20",
  "dueDate": "2025-11-20",
  "status": "draft",
  "currency": "USD",
  "subtotal": 1515.99,
  "taxRate": 10.00,
  "taxAmount": 151.60,
  "discountAmount": 0.00,
  "totalAmount": 1667.59,
  "notes": "Payment due within 30 days",
  "createdAt": "2025-10-20T10:00:00+00:00",
  "updatedAt": "2025-10-20T10:00:00+00:00",
  "items": [
    {
      "id": 1,
      "description": "Web Development Services",
      "quantity": 10,
      "unit": "hours",
      "unitPrice": 150.00,
      "lineTotal": 1500.00
    },
    {
      "id": 2,
      "description": "Domain Registration",
      "quantity": 1,
      "unit": "year",
      "unitPrice": 15.99,
      "lineTotal": 15.99
    }
  ]
}
```

**Error Responses:**
- `404 Not Found` - Invoice not found

---

### 3. Create Invoice

Create a new invoice with items.

**Endpoint:** `POST /api/invoices`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "customerEmail": "contact@acme.com",
  "invoiceNumber": "INV-2025-001",
  "issueDate": "2025-10-20",
  "dueDate": "2025-11-20",
  "status": "draft",
  "currency": "USD",
  "taxRate": 10.00,
  "discountAmount": 0.00,
  "notes": "Payment due within 30 days",
  "items": [
    {
      "description": "Web Development Services",
      "quantity": 10,
      "unitPrice": 150.00,
      "unit": "hours"
    },
    {
      "description": "Domain Registration",
      "quantity": 1,
      "unitPrice": 15.99,
      "unit": "year"
    }
  ]
}
```

**Field Descriptions:**
- `customerEmail` (required) - Email of the customer (alternative to customerId)
- `customerId` (optional) - Customer ID if you prefer to use ID instead of email
- `invoiceNumber` (required) - Unique invoice number
- `issueDate` (required) - Invoice issue date (YYYY-MM-DD)
- `dueDate` (required) - Payment due date (YYYY-MM-DD)
- `status` (required) - One of: `draft`, `sent`, `paid`, `overdue`, `cancelled`
- `currency` (required) - Three-letter currency code (e.g., USD, EUR, GBP)
- `taxRate` (optional) - Tax percentage (e.g., 10.00 for 10%)
- `discountAmount` (optional) - Fixed discount amount
- `notes` (optional) - Additional notes or payment terms
- `items` (required) - Array of invoice items

**Item Fields:**
- `description` (required) - Item description
- `quantity` (required) - Quantity (integer)
- `unitPrice` (required) - Price per unit (decimal)
- `unit` (optional) - Unit of measure (e.g., hours, items, days)

**Response:** `201 Created`
```json
{
  "id": 1,
  "invoiceNumber": "INV-2025-001",
  "customer": {
    "id": 1,
    "name": "Acme Corporation",
    "email": "contact@acme.com"
  },
  "issueDate": "2025-10-20",
  "dueDate": "2025-11-20",
  "status": "draft",
  "currency": "USD",
  "subtotal": 1515.99,
  "taxRate": 10.00,
  "taxAmount": 151.60,
  "discountAmount": 0.00,
  "totalAmount": 1667.59,
  "notes": "Payment due within 30 days",
  "createdAt": "2025-10-20T10:00:00+00:00",
  "updatedAt": "2025-10-20T10:00:00+00:00",
  "items": [
    {
      "id": 1,
      "description": "Web Development Services",
      "quantity": 10,
      "unit": "hours",
      "unitPrice": 150.00,
      "lineTotal": 1500.00
    },
    {
      "id": 2,
      "description": "Domain Registration",
      "quantity": 1,
      "unit": "year",
      "unitPrice": 15.99,
      "lineTotal": 15.99
    }
  ]
}
```

**Error Responses:**
- `400 Bad Request` - Invalid data or missing required fields
- `404 Not Found` - Customer not found

---

### 4. Update Invoice

Update an existing invoice.

**Endpoint:** `PUT /api/invoices/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "status": "sent",
  "dueDate": "2025-12-20",
  "notes": "Payment terms updated"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "invoiceNumber": "INV-2025-001",
  "customer": {
    "id": 1,
    "name": "Acme Corporation",
    "email": "contact@acme.com"
  },
  "issueDate": "2025-10-20",
  "dueDate": "2025-12-20",
  "status": "sent",
  "currency": "USD",
  "subtotal": 1515.99,
  "taxRate": 10.00,
  "taxAmount": 151.60,
  "discountAmount": 0.00,
  "totalAmount": 1667.59,
  "notes": "Payment terms updated",
  "createdAt": "2025-10-20T10:00:00+00:00",
  "updatedAt": "2025-10-20T15:30:00+00:00",
  "items": [...]
}
```

**Error Responses:**
- `404 Not Found` - Invoice not found
- `400 Bad Request` - Invalid data

---

### 5. Delete Invoice

Delete an invoice and all its items.

**Endpoint:** `DELETE /api/invoices/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `204 No Content`

**Error Responses:**
- `404 Not Found` - Invoice not found

---

## User Endpoints

### 1. Get All Users

Retrieve a list of all users.

**Endpoint:** `GET /api/users`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
[
  {
    "id": 1,
    "email": "admin@example.com",
    "name": "Admin User",
    "roles": ["ROLE_USER", "ROLE_ADMIN"],
    "isActive": true,
    "createdAt": "2025-10-20T10:00:00+00:00"
  },
  {
    "id": 2,
    "email": "user@example.com",
    "name": "Regular User",
    "roles": ["ROLE_USER"],
    "isActive": true,
    "createdAt": "2025-10-20T11:00:00+00:00"
  }
]
```

---

### 2. Get User by ID

Retrieve a specific user.

**Endpoint:** `GET /api/users/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "email": "admin@example.com",
  "name": "Admin User",
  "roles": ["ROLE_USER", "ROLE_ADMIN"],
  "isActive": true,
  "createdAt": "2025-10-20T10:00:00+00:00",
  "lastLoginAt": "2025-10-20T14:30:00+00:00"
}
```

**Error Responses:**
- `404 Not Found` - User not found

---

### 3. Update User

Update user information.

**Endpoint:** `PUT /api/users/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "name": "Updated Name",
  "email": "newemail@example.com"
}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "email": "newemail@example.com",
  "name": "Updated Name",
  "roles": ["ROLE_USER"],
  "isActive": true,
  "createdAt": "2025-10-20T10:00:00+00:00"
}
```

**Error Responses:**
- `404 Not Found` - User not found
- `400 Bad Request` - Invalid data
- `409 Conflict` - Email already exists

---

### 4. Delete User

Delete a user account.

**Endpoint:** `DELETE /api/users/{id}`

**Headers:**
```json
{
  "Authorization": "Bearer YOUR_JWT_TOKEN"
}
```

**Response:** `204 No Content`

**Error Responses:**
- `404 Not Found` - User not found

---

## Status Codes

- `200 OK` - Request succeeded
- `201 Created` - Resource created successfully
- `204 No Content` - Request succeeded with no content to return
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid authentication token
- `404 Not Found` - Resource not found
- `409 Conflict` - Resource conflict (e.g., duplicate email)
- `500 Internal Server Error` - Server error

---

## Common Error Response Format

```json
{
  "error": "Error message description"
}
```

or for validation errors:

```json
{
  "error": "Validation failed",
  "errors": {
    "field1": "Error message for field1",
    "field2": "Error message for field2"
  }
}
```

---

## Example Workflow

### 1. Register and Login

```bash
# Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!",
    "name": "John Doe"
  }'

# Save the token from the response
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 2. Create a Customer

```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Corporation",
    "email": "contact@acme.com"
  }'
```

### 3. Create an Invoice

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
    "notes": "Payment due within 30 days",
    "items": [
      {
        "description": "Web Development Services",
        "quantity": 10,
        "unitPrice": 150.00,
        "unit": "hours"
      }
    ]
  }'
```

### 4. Get All Invoices

```bash
curl -X GET http://localhost:8000/api/invoices \
  -H "Authorization: Bearer $TOKEN"
```

### 5. Update Invoice Status

```bash
curl -X PUT http://localhost:8000/api/invoices/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "sent"
  }'
```

---

## Invoice Status Values

- `draft` - Invoice is being prepared
- `sent` - Invoice has been sent to customer
- `paid` - Invoice has been paid
- `overdue` - Payment is past due date
- `cancelled` - Invoice has been cancelled

---

## JWT Token

The JWT token expires after 24 hours. After expiration, you'll need to login again to get a new token.

The token contains the following claims:
- `user_id` - User's database ID
- `email` - User's email address
- `exp` - Expiration timestamp

---

## Notes

- All dates should be in `YYYY-MM-DD` format
- All timestamps are in ISO 8601 format with timezone
- Decimal values (prices, amounts) support 2 decimal places
- Currency codes follow ISO 4217 standard (3-letter codes)
- Email addresses must be unique in the system
- Invoice numbers must be unique

---

## Testing with Postman

You can import these examples into Postman:

1. Create a new collection
2. Set up an environment variable `{{token}}` 
3. After login/register, save the token to the environment
4. Use `{{token}}` in the Authorization header for all protected endpoints
