const API_KEY = `your-api-key`; // change it
const TRONSAVE_API_URL = "https://api.tronsave.io" //in testnet mode change it to "https://api-dev.tronsave.io"
const RECEIVER_ADDRESS = 'your-receiver-address' // change it
const BUY_AMOUNT = 32000 // change it
const DURATION = 3600 // current value: 1h. change it
const MAX_PRICE_ACCEPTED = 100 // change it
const RESOURCE_TYPE = "ENERGY" // ENERGY or BANDWIDTH

const sleep = async (ms) => {
    await new Promise((resolver, reject) => {
        setTimeout(() => resolver("OK"), ms)
    })
}

const GetOrderBook = async (apiKey, receiverAddress) => {
    const url = `${TRONSAVE_API_URL}/v2/order-book?address=${receiverAddress}`
    const data = await fetch(url, {
        headers: {
            'apikey': apiKey
        }
    })
    const response = await data.json()
    /**
     * Example response 
    {
        error: false,
        message: 'Success',
        data: [
            { price: 54, availableResourceAmount: 2403704 },
            { price: 60, availableResourceAmount: 3438832 },
            { price: 61, availableResourceAmount: 4100301 },
            { price: 90, availableResourceAmount: 7082046 },
            { price: 91, availableResourceAmount: 7911978 }
        ]
    }
     */
    return response
}

const GetAccountInfo = async (apiKey) => {
    const url = `${TRONSAVE_API_URL}/v2/user-info`
    const data = await fetch(url, {
        headers: {
            'apikey': apiKey
        }
    })
    const response = await data.json()
    /**
     * Example response 
    {
        "error": false,
        "message": "Success",
        "data": {
            "id": "67a2e6092...2e8b291da2",
            "balance": "373040535",
            "representAddress": "TTgMEAhuzPch...nm2tCNmqXp13AxzAd",
            "depositAddress": "TTgMEAhuzPch...2tCNmqXp13AxzAd"
        }
    }
     */
    return response
}


const BuyResource = async (apiKey, receiverAddress, amount, durationSec, maxPriceAccepted) => {
    const url = `${TRONSAVE_API_URL}/v2/buy-resource`
    const body = {
        resourceType: RESOURCE_TYPE,
        unitPrice: "MEDIUM", //price in sun or "SLOW"|"MEDIUM"|"FAST"
        amount, //Amount of resource want to buy
        receiver: receiverAddress,
        durationSec, //order duration in sec. Default: 259200 (3 days)
        options: {
            allowPartialFill: true,
            onlyCreateWhenFulfilled: false,
            maxPriceAccepted,
        }
    }
    const data = await fetch(url, {
        method: "POST",
        headers: {
            'apikey': apiKey,
            "content-type": "application/json",
        },
        body: JSON.stringify(body)
    })
    const response = await data.json()
    /**
     * Example response
     * {
     *     "error": false,
     *     "message": "Success",
     *     "data": {
     *         "orderId": "6809fdb7b9...a41d726fd"
     *     }
     * }
     */
    return response
}

const GetOneOrderDetails = async (api_key, order_id) => {
    const url = `${TRONSAVE_API_URL}/v2/order/${order_id}`
    const data = await fetch(url, {
        headers: {
            'apikey': api_key
        }
    })
    const response = await data.json()
    /**
     * Example response 
        {
            "error": false,
            "message": "Success",
            "data": {
                "id": "680b3e9939...600b7734d",
                "requester": "TTgMEAhuzPch...nm2tCNmqXp13AxzAd",
                "receiver": "TAk6jzZqHwNU...yAE1YAoUPk7r2T6h",
                "resourceAmount": 32000,
                "resourceType": "ENERGY",
                "remainAmount": 0,
                "price": 91,
                "durationSec": 3600,
                "orderType": "NORMAL",
                "allowPartialFill": false,
                "payoutAmount": 2912000,
                "fulfilledPercent": 100,
                "delegates": [
                    {
                        "delegator": "TQ5VcQjA7w...Pio485UDhCWAANrMh",
                        "amount": 32000,
                        "txid": "b200e8b7f9130b67ff....403c51d6f7a92acc7c4618906c375b69"
                    }
                ]
            }
        }
     */
    return response
}

const CreateOrderByUsingApiKey = async () => {
    //Check energy available
    const orderBook = await GetOrderBook(API_KEY, RECEIVER_ADDRESS)
    console.log(orderBook)
    /*
      {
        error: false,
        message: 'Success',
        data: [
            { price: 54, availableResourceAmount: 2403704 },
            { price: 60, availableResourceAmount: 3438832 },
            { price: 90, availableResourceAmount: 6420577 },
            { price: 91, availableResourceAmount: 7250509 }
        ]
    }
    */
    //Look at response above, we have 177k energy at price less than 30, 331k enegy at price 30 and 2841k energy at price 35
    //Example if want to buy 500k energy in 3 days you have to place order at price at least 35 energy to fulfill your order (the price can be higher if duration of order less than 3 days)

    const needTrx = MAX_PRICE_ACCEPTED * BUY_AMOUNT

    //Check if your internal balance enough to buy
    const accountInfo = await GetAccountInfo(API_KEY)
    console.log(accountInfo)
    /*
     {
        error: false,
        message: 'Success',
        data: {
            id: '67a2e609....e8b291da2',
            balance: '370352535',
            representAddress: 'TTgMEAhuzPc....m2tCNmqXp13AxzAd',
            depositAddress: 'TTgMEAhuzPch....CNmqXp13AxzAd'
        }
    }
    */
    const isBalanceEnough = Number(accountInfo.data.balance) >= needTrx
    console.log({ isBalanceEnough })
    if (isBalanceEnough) {
        const buyResourceOrder = await BuyResource(API_KEY, RECEIVER_ADDRESS, BUY_AMOUNT, DURATION, MAX_PRICE_ACCEPTED)
        console.log(buyResourceOrder)
        /*
          {
            error: false,
            message: 'Success',
            data: { orderId: '680b3e993...600b7734d' }
        }
        */
        //Wait 3-5 seconds after buy then check 
        if (!buyResourceOrder.error) {
            while (true) {
                await sleep(3000)
                const orderDetail = await GetOneOrderDetails(API_KEY, buyResourceOrder.data.orderId)
                console.log(orderDetail)
                /*
                    {
                        "error": false,
                        "message": "Success",
                        "data": {
                            "id": "680b3e9....3600b7734d",
                            "requester": "TTgMEAhuzPchDAL....CNmqXp13AxzAd",
                            "receiver": "TAk6jzZqHwNU...oUPk7r2T6h",
                            "resourceAmount": 32000,
                            "resourceType": "ENERGY",
                            "remainAmount": 0,
                            "price": 91,
                            "durationSec": 3600,
                            "orderType": "NORMAL",
                            "allowPartialFill": false,
                            "payoutAmount": 2912000,
                            "fulfilledPercent": 100,
                            "delegates": [
                                {
                                    "delegator": "TQ5VcQjA7wkUJ7....85UDhCWAANrMh",
                                    "amount": 32000,
                                    "txid": "b200e8b7f9130b67ff29e5e2....d6f7a92acc7c4618906c375b69"
                                }
                            ]
                        }
                    }
                */
                if (orderDetail && orderDetail.data.fulfilledPercent === 100 || orderDetail.data.remainAmount === 0) {
                    console.log(`Your order already fulfilled`)
                    break;
                } else {
                    console.log(`Your order is not fulfilled, wait 3s and recheck`)
                }
            }
        } else {
            console.log({ buyResourceOrder })
            throw new Error(`Buy Order Failed`)
        }
    }
}

CreateOrderByUsingApiKey()