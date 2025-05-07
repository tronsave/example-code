const API_KEY = `your-api-key`;
const TRONSAVE_API_URL = "https://api.tronsave.io"
const RECEIVER = "your-receiver-address"
const RESOURCE_TYPE = "ENERGY"

const GetEstimateExtendData = async (extendTo, maxPriceAccepted) => {
    const url = TRONSAVE_API_URL + `/v2/get-extendable-delegates`
    const body = {
        extendTo, //time in seconds you want to extend to
        receiver: RECEIVER,   //the address that receives resource delegate
        maxPriceAccepted, //Optional. Number maximum price you want to pay to extend
        resourceType: RESOURCE_TYPE, //ENERGY or BANDWIDTH. optional. default is ENERGY
    }
    const data = await fetch(url, {
        method: "POST",
        headers: {
            'apikey': API_KEY,
            "content-type": "application/json",
        },
        body: JSON.stringify(body)
    })
    const response = await data.json()
    /**
     * Example response 
     * @link  
       {
            "error": false,
            "message": "Success",
            "data": {
                "extendOrderBook": [
                    {
                        "price": 784,
                        "value": 1002
                    }
                ],
                "totalDelegateAmount": 5003,
                "totalAvailableExtendAmount": 5003,
                "totalEstimateTrx": 4085783,
                "isAbleToExtend": true,
                "yourBalance": 2377366851,
                "extendData": [
                    {
                        "delegator": "TGGVrYaT8Xoos...6dmSZkohGGcouYL4",
                        "isExtend": true,
                        "extraAmount": 0,
                        "extendTo": 1745833276
                    },
                    {
                        "delegator": "TQBV7xU489Rq8Z...zBhJMdrDr51wA2",
                        "isExtend": true,
                        "extraAmount": 0,
                        "extendTo": 1745833276
                    },
                    {
                        "delegator": "TSHZv6xsYHMRCbdVh...qNozxaPPjDR6",
                        "isExtend": true,
                        "extraAmount": 0,
                        "extendTo": 1745833276
                    }
                ]
            }
        }
     */
    return response
}

/**
 * @param {number} extendTo time in seconds you want to extend to
 * @param {boolean} maxPriceAccepted number maximum price you want to pay to extend
 * @returns 
 */
const SendExtendRequest = async (extendTo, maxPriceAccepted) => {
    const url = TRONSAVE_API_URL + `/v2/extend-request`
    // Get estimate extendable delegates
    const estimateResponse = await GetEstimateExtendData(extendTo, maxPriceAccepted)
    const extendData = estimateResponse.data?.extendData
    if (extendData && extendData.length) { 
        const body = {
            extendData: extendData,
            receiver: RECEIVER,
        }
        const data = await fetch(url, {
            method: "POST",
            headers: {
                'apikey': API_KEY,
                "content-type": "application/json",
            },
            body: JSON.stringify(body)
        })
        const response = await data.json()
        /**
         * Example response 
         {
            error: false,
            message: 'Success',
            data: { orderId: '680b5ac7b09a385fb3d582ff' }
            }
         */
        return response
    }
    return []
}

//Example run code
const ClientCode = async () => { 
    const extendTo = Math.floor(new Date().getTime() / 1000) + 3 * 86400 //Extend to 3 next days
    const maxPriceAccepted = 900
    const response = await SendExtendRequest(extendTo, maxPriceAccepted)
    console.log(response)
}


ClientCode()