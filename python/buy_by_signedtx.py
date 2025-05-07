import requests
from typing import Dict, Any, Optional

# Configuration
TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S"
PRIVATE_KEY = "your_private_key"
TRON_FULL_NODE = "https://api.trongrid.io"
TRONSAVE_API_URL = "https://api.tronsave.io"
RESOURCE_TYPE = "ENERGY"

REQUEST_ADDRESS = "your_request_address"
RECEIVER_ADDRESS = "your_receiver_address"
BUY_AMOUNT = 32000
DURATION_SEC = 3 * 86400  # 3 days

def get_estimate() -> Dict[str, Any]:
    """Get estimate for buying resource"""
    url = f"{TRONSAVE_API_URL}/v2/estimate-buy-resource"
    body = {
        'amount': BUY_AMOUNT,
        'unitPrice': "MEDIUM",
        'resourceType': RESOURCE_TYPE,
        'durationSec': DURATION_SEC,
        'requester': REQUEST_ADDRESS,
        'receiver': RECEIVER_ADDRESS,
        'options': {
            'allowPartialFill': True,
        }
    }
    
    response = requests.post(url, json=body)
    return response.json()

def get_signed_transaction(estimate_trx: int) -> Dict[str, Any]:
    """Get signed transaction"""
    # This is a placeholder. In real implementation, you would use TronWeb Python SDK
    return {
        'visible': False,
        'txID': '...',
        'raw_data': {
            'contract': [{
                'parameter': {
                    'value': {
                        'amount': estimate_trx,
                        'owner_address': REQUEST_ADDRESS,
                        'to_address': TRONSAVE_RECEIVER_ADDRESS
                    },
                    'type_url': 'type.googleapis.com/protocol.TransferContract'
                },
                'type': 'TransferContract'
            }]
        }
    }

def create_order(signed_tx: Dict[str, Any], receiver_address: str, 
                unit_price: int, duration_sec: int, options: Dict[str, Any]) -> Dict[str, Any]:
    """Create order"""
    url = f"{TRONSAVE_API_URL}/v2/buy-resource"
    body = {
        'resourceType': RESOURCE_TYPE,
        'unitPrice': unit_price,
        'allowPartialFill': True,
        'receiver': receiver_address,
        'durationSec': duration_sec,
        'signedTx': signed_tx,
        'options': options
    }
    
    response = requests.post(url, json=body)
    return response.json()

def buy_resource_using_private_key() -> None:
    """Main function to buy resource using private key"""
    try:
        # Step 1: Estimate the cost
        estimate_data = get_estimate()
        if estimate_data['error']:
            raise Exception(estimate_data['message'])
        print("Estimate Data:", estimate_data)

        unit_price = estimate_data['data']['unitPrice']
        estimate_trx = estimate_data['data']['estimateTrx']
        duration_sec = estimate_data['data']['durationSec']
        available_resource = estimate_data['data']['availableResource']

        is_ready_fulfilled = available_resource >= BUY_AMOUNT
        if is_ready_fulfilled:
            # Step 2: Build signed transaction
            signed_tx = get_signed_transaction(estimate_trx)
            print("Signed Transaction:", signed_tx)

            # Step 3: Create order
            options = {
                'allowPartialFill': True,
                'maxPriceAccepted': 100,
            }
            data_create_order = create_order(
                signed_tx, RECEIVER_ADDRESS, unit_price, duration_sec, options)
            print("Create Order Response:", data_create_order)

    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    buy_resource_using_private_key() 