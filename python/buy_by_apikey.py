import time
import requests
from typing import Dict, List, Any, Optional

# Configuration
API_KEY = 'your-api-key'
TRONSAVE_API_URL = "https://api.tronsave.io"
RECEIVER_ADDRESS = 'your-receiver-address'
BUY_AMOUNT = 32000
DURATION = 3600  # 1 hour
MAX_PRICE_ACCEPTED = 100
RESOURCE_TYPE = "ENERGY"  # ENERGY or BANDWIDTH

def sleep_ms(ms: int) -> None:
    """Sleep for specified milliseconds"""
    time.sleep(ms / 1000)

def get_order_book() -> Dict[str, Any]:
    """Get order book for resources"""
    url = f"{TRONSAVE_API_URL}/v2/order-book"
    headers = {'apikey': API_KEY}
    params = {'address': RECEIVER_ADDRESS}
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()

def get_account_info() -> Dict[str, Any]:
    """Get account information"""
    url = f"{TRONSAVE_API_URL}/v2/user-info"
    headers = {'apikey': API_KEY}
    
    response = requests.get(url, headers=headers)
    return response.json()

def buy_resource(amount: int, duration_sec: int, max_price_accepted: int) -> Dict[str, Any]:
    """Buy resource"""
    url = f"{TRONSAVE_API_URL}/v2/buy-resource"
    headers = {
        'apikey': API_KEY,
        'Content-Type': 'application/json'
    }
    
    body = {
        'resourceType': RESOURCE_TYPE,
        'unitPrice': "MEDIUM",
        'amount': amount,
        'receiver': RECEIVER_ADDRESS,
        'durationSec': duration_sec,
        'options': {
            'allowPartialFill': True,
            'onlyCreateWhenFulfilled': False,
            'maxPriceAccepted': max_price_accepted,
        }
    }
    
    response = requests.post(url, headers=headers, json=body)
    return response.json()

def get_order_details(order_id: str) -> Dict[str, Any]:
    """Get order details"""
    url = f"{TRONSAVE_API_URL}/v2/order/{order_id}"
    headers = {'apikey': API_KEY}
    
    response = requests.get(url, headers=headers)
    return response.json()

def create_order_by_using_api_key() -> None:
    """Main function to create order"""
    try:
        # Check energy available
        order_book = get_order_book()
        print("Order Book:", order_book)

        need_trx = MAX_PRICE_ACCEPTED * BUY_AMOUNT

        # Check if balance is enough
        account_info = get_account_info()
        print("Account Info:", account_info)

        is_balance_enough = int(account_info['data']['balance']) >= need_trx
        print(f"Is balance enough: {is_balance_enough}")

        if is_balance_enough:
            buy_resource_order = buy_resource(BUY_AMOUNT, DURATION, MAX_PRICE_ACCEPTED)
            print("Buy Resource Order:", buy_resource_order)

            if not buy_resource_order['error']:
                while True:
                    sleep_ms(3000)
                    order_detail = get_order_details(buy_resource_order['data']['orderId'])
                    print("Order Detail:", order_detail)

                    if (order_detail['data']['fulfilledPercent'] == 100 or 
                        order_detail['data']['remainAmount'] == 0):
                        print("Order fulfilled successfully")
                        break
                    else:
                        print("Order not fulfilled, waiting 3s and rechecking...")
            else:
                print("Buy Resource Order:", buy_resource_order)
                raise Exception("Buy Order Failed")

    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    create_order_by_using_api_key() 