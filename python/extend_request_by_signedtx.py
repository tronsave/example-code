import time
import requests
from typing import Dict, Any, Optional

# Configuration
TRONSAVE_API_URL = "https://api.tronsave.io"
RECEIVER = "your-receiver-address"
RESOURCE_TYPE = "ENERGY"
REQUESTER_ADDRESS = "TFwUFWr3QV376677Z8VWXxGUAMFSrq1MbM"
PRIVATE_KEY = "your-private-key"
TRON_FULL_NODE = "https://api.trongrid.io"
TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S"

def get_estimate_extend_data(requester: str, extend_to: int, max_price_accepted: int) -> Dict[str, Any]:
    """Get estimate extend data"""
    url = f"{TRONSAVE_API_URL}/v2/get-extendable-delegates"
    body = {
        'extendTo': extend_to,
        'receiver': RECEIVER,
        'requester': requester,
        'maxPriceAccepted': max_price_accepted,
        'resourceType': RESOURCE_TYPE,
    }
    
    response = requests.post(url, json=body)
    return response.json()

def get_signed_tx(total_estimate_trx: int) -> Dict[str, Any]:
    """Get signed transaction"""
    # This is a placeholder. In real implementation, you would use TronWeb Python SDK
    return {
        'visible': False,
        'txID': '...',
        'raw_data': {
            'contract': [{
                'parameter': {
                    'value': {
                        'amount': total_estimate_trx,
                        'owner_address': REQUESTER_ADDRESS,
                        'to_address': TRONSAVE_RECEIVER_ADDRESS
                    },
                    'type_url': 'type.googleapis.com/protocol.TransferContract'
                },
                'type': 'TransferContract'
            }]
        }
    }

def send_extend_request(extend_to: int, max_price_accepted: int) -> Dict[str, Any]:
    """Send extend request"""
    url = f"{TRONSAVE_API_URL}/v2/extend-request"
    
    estimate_response = get_estimate_extend_data(REQUESTER_ADDRESS, extend_to, max_price_accepted)
    extend_data = estimate_response.get('data', {}).get('extendData', [])

    if extend_data:
        total_estimate_trx = estimate_response['data']['totalEstimateTrx']
        signed_tx = get_signed_tx(total_estimate_trx)

        body = {
            'extendData': extend_data,
            'receiver': RECEIVER,
            'signedTx': signed_tx
        }
        
        response = requests.post(url, json=body)
        return response.json()

    return {}

def main() -> None:
    """Main function"""
    try:
        extend_to = int(time.time()) + (3 * 86400)  # Extend to 3 next days
        max_price_accepted = 900
        response = send_extend_request(extend_to, max_price_accepted)
        print("Response:", response)
    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    main() 