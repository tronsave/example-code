import requests
from typing import Dict, Any

# Configuration constants
TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S"
PRIVATE_KEY = "your_private_key"
TRON_FULL_NODE = "https://api.trongrid.io"
TRONSAVE_API_URL = "https://api.tronsave.io"
RESOURCE_TYPE = "ENERGY"

REQUEST_ADDRESS = "your_request_address"
RECEIVER_ADDRESS = "your_receiver_address"
BUY_AMOUNT = 32000
DURATION_SEC = 3600  # 1 hour

def get_estimate(resource_amount: int, duration_sec: int) -> Dict[str, Any]:
    url = f"{TRONSAVE_API_URL}/v2/estimate-buy-resource"
    body = {
        'resourceAmount': resource_amount,
        'unitPrice': "MEDIUM",
        'resourceType': RESOURCE_TYPE,
        'durationSec': duration_sec,
    }
    
    response = requests.post(url, json=body)
    return response.json()

def get_signed_transaction(estimate_trx: int, request_address: str) -> Dict[str, Any]:
    # This is a placeholder. In real implementation, you would use TronWeb Python SDK
    return {
        'visible': False,
        'txID': '...',
        'raw_data': {
            'contract': [{
                'parameter': {
                    'value': {
                        'amount': estimate_trx,
                        'owner_address': request_address,
                        'to_address': TRONSAVE_RECEIVER_ADDRESS
                    },
                    'type_url': 'type.googleapis.com/protocol.TransferContract'
                },
                'type': 'TransferContract'
            }]
        }
    }

def create_order(resource_amount: int, signed_tx: Dict[str, Any], 
                receiver_address: str, unit_price: int, duration_sec: int) -> Dict[str, Any]:
    url = f"{TRONSAVE_API_URL}/v2/buy-resource"
    body = {
        'resourceType': RESOURCE_TYPE,
        'resourceAmount': resource_amount,
        'unitPrice': unit_price,
        'allowPartialFill': True,
        'receiver': receiver_address,
        'durationSec': duration_sec,
        'signedTx': signed_tx
    }
    
    response = requests.post(url, json=body)
    return response.json()

def buy_resource_using_private_key() -> None:
    try:
        # Step 1: Estimate the cost
        estimate_data = get_estimate(BUY_AMOUNT, DURATION_SEC)
        if estimate_data['error']:
            raise Exception(estimate_data['message'])
        print(estimate_data)

        data = estimate_data['data']
        unit_price = data['unitPrice']
        estimate_trx = data['estimateTrx']
        duration_sec = data['durationSec']
        available_resource = data['availableResource']

        is_ready_fulfilled = available_resource >= BUY_AMOUNT
        if is_ready_fulfilled:
            # Step 2: Build signed transaction
            signed_tx = get_signed_transaction(estimate_trx, REQUEST_ADDRESS)
            print(signed_tx)

            # Step 3: Create order
            data_create_order = create_order(
                BUY_AMOUNT, signed_tx, RECEIVER_ADDRESS, unit_price, duration_sec)
            print(data_create_order)

    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    buy_resource_using_private_key()