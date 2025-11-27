# RWAMP API Documentation

**Version:** 1.0  
**Base URL:** `https://your-domain.com/api`  
**Authentication:** Session-based (web) or Sanctum (API)

---

## Table of Contents

1. [Authentication](#authentication)
2. [Public Endpoints](#public-endpoints)
3. [User Endpoints](#user-endpoints)
4. [Admin Endpoints](#admin-endpoints)
5. [Reseller Endpoints](#reseller-endpoints)
6. [Error Responses](#error-responses)

---

## Authentication

### Session Authentication (Web)
Most endpoints use Laravel's session-based authentication. Include CSRF token in requests.

### Sanctum Authentication (API)
For API access, use Laravel Sanctum:

```http
Authorization: Bearer {token}
```

---

## Public Endpoints

### Check Referral Code
**GET** `/api/check-referral-code`

**Query Parameters:**
- `code` (string, required) - Referral code (e.g., RSL1001)

**Response:**
```json
{
  "valid": true,
  "reseller_name": "John Doe",
  "reseller_id": 1001
}
```

### Check Email Availability
**GET** `/api/check-email`

**Query Parameters:**
- `email` (string, required) - Email address to check

**Response:**
```json
{
  "valid": true,
  "exists": false,
  "message": "Email address is valid and available"
}
```

### Check Phone Availability
**GET** `/api/check-phone`

**Query Parameters:**
- `phone` (string, required) - Phone number to check

**Response:**
```json
{
  "valid": true,
  "exists": false,
  "message": "Phone number is valid and available"
}
```

---

## User Endpoints

All user endpoints require authentication.

### Save Wallet Address
**POST** `/api/save-wallet-address`

**Headers:**
- `X-CSRF-TOKEN` (required)

**Body:**
```json
{
  "wallet_address": "0x1234..."
}
```

**Response:**
```json
{
  "success": true
}
```

### Check Payment Status
**POST** `/api/check-payment-status`

**Body:**
```json
{
  "tx_hash": "0xabc123...",
  "network": "TRC20",
  "amount": 1000
}
```

**Response:**
```json
{
  "payment_found": true,
  "status": "pending"
}
```

### Submit Transaction Hash
**POST** `/api/submit-tx-hash`

**Body:**
```json
{
  "tx_hash": "0xabc123...",
  "network": "TRC20",
  "token_amount": 1000,
  "usd_amount": "50.00",
  "pkr_amount": "13900.00"
}
```

**Response:**
```json
{
  "success": true,
  "id": 123
}
```

### Check Auto Payment Status
**POST** `/api/check-auto-payment`

**Body:**
```json
{
  "network": "TRC20",
  "expected_usd": 50
}
```

**Response:**
```json
{
  "detected": false
}
```

### Buy From Reseller
**POST** `/api/user/buy-from-reseller`

**Body:**
```json
{
  "reseller_id": 1001,
  "amount": 1000,
  "otp": "123456",
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Tokens purchased successfully from reseller."
}
```

### Create Buy From Reseller Request
**POST** `/api/user/buy-from-reseller/request`

**Body:**
```json
{
  "reseller_id": 1001,
  "coin_quantity": 1000,
  "otp": "123456",
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Buy request submitted successfully.",
  "request_id": 456
}
```

### Send OTP for Buy Request
**POST** `/api/user/buy-from-reseller/send-otp`

**Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your email."
}
```

### Submit Withdrawal Request
**POST** `/api/user/withdraw`

**Body:**
```json
{
  "wallet_address": "0x1234...",
  "token_amount": 1000,
  "notes": "Withdrawal request"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal request submitted successfully."
}
```

### Get User Withdrawals
**GET** `/api/user/withdrawals`

**Response:**
```json
{
  "withdrawals": [
    {
      "id": 1,
      "wallet_address": "0x1234...",
      "token_amount": 1000,
      "status": "pending",
      "created_at": "2024-01-01 12:00:00"
    }
  ]
}
```

### View Withdrawal Receipt
**GET** `/api/user/withdrawals/{id}/receipt`

**Response:** Image file or JSON with receipt path

### Search Resellers
**GET** `/api/resellers/search`

**Query Parameters:**
- `q` (string, optional) - Search query

**Response:**
```json
[
  {
    "id": 1001,
    "name": "John Doe",
    "email": "reseller@example.com",
    "referral_code": "RSL1001",
    "coin_price": 3.0,
    "is_linked": false
  }
]
```

### Verify OTP
**POST** `/api/verify-otp`

**Body:**
```json
{
  "email": "user@example.com",
  "otp": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP verified successfully."
}
```

### User Lookup by Wallet (Admin/Reseller Only)
**POST** `/api/users/lookup-by-wallet`

**Headers:**
- `X-CSRF-TOKEN` (required)
- Authentication required (admin or reseller role)

**Body:**
```json
{
  "wallet_address": "0x1234..."
}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "token_balance": 5000
  }
}
```

---

## Reseller Endpoints

All reseller endpoints require authentication and `reseller` role.

### Search Users for Selling
**GET** `/api/reseller/search-users`

**Query Parameters:**
- `q` (string, optional) - Search query

**Response:**
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "token_balance": 5000,
    "role": "investor"
  }
]
```

### Send OTP for Sell
**POST** `/api/reseller/send-otp`

**Body:**
```json
{
  "email": "reseller@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your email."
}
```

### Fetch User Payment Proof
**POST** `/api/reseller/fetch-payment-proof`

**Body:**
```json
{
  "user_id": 1,
  "payment_type": "usdt"
}
```

**Response:**
```json
{
  "success": true,
  "proof": {
    "type": "usdt",
    "tx_hash": "0xabc123...",
    "network": "TRC20",
    "amount": 1000,
    "date": "2024-01-01 12:00:00"
  }
}
```

### Sell Coins to User
**POST** `/api/reseller/sell`

**Body:**
```json
{
  "recipient_id": 1,
  "coin_quantity": 1000,
  "price_per_coin": 3.0,
  "otp": "123456",
  "email": "reseller@example.com",
  "payment_received": "yes",
  "payment_type": "usdt",
  "payment_hash": "0xabc123..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Coins transferred successfully!",
  "data": {
    "recipient": "John Doe",
    "quantity": 1000,
    "price_per_coin": 3.0,
    "total_price": 3000
  }
}
```

### Approve Crypto Payment
**POST** `/api/reseller/crypto-payments/{payment}/approve`

**Response:**
```json
{
  "success": true,
  "message": "Payment approved successfully."
}
```

---

## Admin Endpoints

All admin endpoints require authentication, `admin` role, and 2FA.

### Search Users for Admin Sell
**GET** `/api/admin/search-users`

**Query Parameters:**
- `q` (string, optional) - Search query

**Response:**
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "token_balance": 5000,
    "role": "investor"
  }
]
```

### Send OTP for Admin Sell
**POST** `/api/admin/send-otp`

**Body:**
```json
{
  "email": "admin@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your email."
}
```

### Fetch User Payment Proof (Admin)
**POST** `/api/admin/fetch-payment-proof`

**Body:**
```json
{
  "user_id": 1,
  "payment_type": "usdt"
}
```

**Response:**
```json
{
  "success": true,
  "proof": {
    "type": "usdt",
    "tx_hash": "0xabc123...",
    "network": "TRC20"
  }
}
```

### Admin Sell Coins
**POST** `/api/admin/sell-coins`

**Body:**
```json
{
  "recipient_id": 1,
  "coin_quantity": 1000,
  "price_per_coin": 3.0,
  "otp": "123456",
  "email": "admin@example.com",
  "payment_received": "yes",
  "payment_type": "usdt",
  "payment_hash": "0xabc123..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Coins transferred successfully!",
  "data": {
    "recipient": "John Doe",
    "quantity": 1000,
    "price_per_coin": 3.0,
    "total_price": 3000
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "otp": ["The otp must be 6 characters."]
  }
}
```

### Authentication Error (401)
```json
{
  "error": "Authentication required"
}
```

### Authorization Error (403)
```json
{
  "error": "You do not have permission to access this resource"
}
```

### Not Found (404)
```json
{
  "error": "Resource not found"
}
```

### Server Error (500)
```json
{
  "error": "Internal server error",
  "message": "An unexpected error occurred"
}
```

---

## Rate Limiting

- **Login:** 5 attempts per minute
- **Contact Form:** 3 per hour
- **Reseller Application:** 3 per hour
- **Newsletter:** 6 per hour
- **OTP Verification:** Custom throttle
- **OTP Resend:** Custom throttle
- **Wallet Lookup:** 10 per second

---

## Notes

1. All POST requests require CSRF token in headers
2. OTP codes expire after 10 minutes
3. Payment statuses: `pending`, `approved`, `rejected`
4. Withdrawal statuses: `pending`, `approved`, `rejected`
5. Network values: `TRC20`, `ERC20`, `BEP20`, `BTC`, `BNB`
6. Payment types: `usdt`, `bank`, `cash`

---

## Example Usage

### cURL Example
```bash
curl -X POST https://your-domain.com/api/submit-tx-hash \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -H "Cookie: laravel_session=your-session-token" \
  -d '{
    "tx_hash": "0xabc123...",
    "network": "TRC20",
    "token_amount": 1000,
    "usd_amount": "50.00",
    "pkr_amount": "13900.00"
  }'
```

### JavaScript Example
```javascript
fetch('/api/submit-tx-hash', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    tx_hash: '0xabc123...',
    network: 'TRC20',
    token_amount: 1000,
    usd_amount: '50.00',
    pkr_amount: '13900.00'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

**Last Updated:** 2024-01-01  
**API Version:** 1.0

