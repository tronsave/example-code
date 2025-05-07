# TronSave API Integration Examples

This repository contains example implementations for interacting with the TronSave API using JavaScript. The examples demonstrate different methods of buying and extending energy resources on the TRON network.

## Prerequisites

- Node.js (v12 or higher)
- TronWeb library
- API Key from TronSave (for API key authentication)
- TRON wallet with private key (for signed transaction authentication)

## Configuration

Before running any examples, you need to set up your configuration:

```javascript
// API Configuration
const API_KEY = "your-api-key";
const TRONSAVE_API_URL = "https://api.tronsave.io"; // Use "https://api-dev.tronsave.io" for testnet
const TRON_FULL_NODE = "https://api.trongrid.io"; // Use "https://api.nileex.io" for testnet

// Wallet Configuration
const PRIVATE_KEY = "your-private-key";
const REQUEST_ADDRESS = "your-request-address";
const RECEIVER_ADDRESS = "your-receiver-address";
```

## Examples

### 1. Buy Energy Using API Key
File: `buy-by-apikey.js`

This example demonstrates how to:
- Check available energy in the order book
- Verify account balance
- Create and monitor energy purchase orders
- Handle order fulfillment

```javascript
const BUY_AMOUNT = 32000;
const DURATION = 3600; // 1 hour
const MAX_PRICE_ACCEPTED = 100;
```

### 2. Buy Energy Using Signed Transaction
File: `buy-by-signedtx.js`

This example shows how to:
- Estimate transaction costs
- Create signed transactions using TronWeb
- Submit orders with signed transactions
- Handle order creation and monitoring

```javascript
const DURATION_SEC = 3 * 86400; // 3 days
```

### 3. Extend Energy Using API Key
File: `extend-request-by-apikey.js`

This example demonstrates:
- Getting extendable delegates
- Estimating extension costs
- Sending extension requests
- Handling extension responses

### 4. Extend Energy Using Signed Transaction
File: `extend-request-by-signedtx.js`

This example shows how to:
- Get extension estimates
- Create signed transactions for extensions
- Submit extension requests with signed transactions
- Handle extension responses

## API Endpoints

### Order Book
```javascript
GET /v2/order-book?address=${receiverAddress}
```

### User Info
```javascript
GET /v2/user-info
```

### Buy Resource
```javascript
POST /v2/buy-resource
```

### Estimate Buy Resource
```javascript
POST /v2/estimate-buy-resource
```

### Get Extendable Delegates
```javascript
POST /v2/get-extendable-delegates
```

### Extend Request
```javascript
POST /v2/extend-request
```

## Response Examples

### Order Book Response
```javascript
{
    error: false,
    message: 'Success',
    data: [
        { price: 54, availableResourceAmount: 2403704 },
        { price: 60, availableResourceAmount: 3438832 }
    ]
}
```

### Account Info Response
```javascript
{
    error: false,
    message: "Success",
    data: {
        id: "67a2e6092...2e8b291da2",
        balance: "373040535",
        representAddress: "TTgMEAhuzPch...nm2tCNmqXp13AxzAd",
        depositAddress: "TTgMEAhuzPch...2tCNmqXp13AxzAd"
    }
}
```

## Error Handling

All API responses include an `error` field indicating success or failure:
- `error: false` - Request successful
- `error: true` - Request failed, check `message` for details

## Best Practices

1. Always check account balance before creating orders
2. Use appropriate price limits to avoid overpayment
3. Monitor order status after creation
4. Handle API errors appropriately
5. Use testnet for development and testing

## Security Notes

- Never commit API keys or private keys to version control
- Use environment variables for sensitive data
- Always verify transaction details before signing
- Use appropriate error handling for failed transactions

## Support

For API support or questions, contact TronSave support team.

## License

This project is licensed under the MIT License - see the LICENSE file for details.