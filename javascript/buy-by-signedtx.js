import { TronWeb } from 'tronweb';

// Configuration constants
const TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S"; //in testnet mode change it to "TATT1UzHRikft98bRFqApFTsaSw73ycfoS"
const PRIVATE_KEY = "your_private_key"; //Change it
const TRON_FULL_NODE = "https://api.trongrid.io"; //in testnet mode change it to "https://api.nileex.io"
const TRONSAVE_API_URL = "https://api.tronsave.io"; //in testnet mode change it to "https://api-dev.tronsave.io"
const RESOURCE_TYPE = "ENERGY"; // ENERGY or BANDWIDTH

const REQUEST_ADDRESS = "your_request_address"; //Change it
const RECEIVER_ADDRESS = "your_receiver_address"; //Change it
const BUY_AMOUNT = 32000; //Chanageable
const DURATION_SEC = 3 * 86400; //3 days.Changeable

// Initialize TronWeb instance
const tronWeb = new TronWeb({
    fullNode: TRON_FULL_NODE,
    solidityNode: TRON_FULL_NODE,
    eventServer: TRON_FULL_NODE,
});

const GetEstimate = async (requestAddress, receiverAddress, amount, durationSec) => {
    const url = TRONSAVE_API_URL + "/v2/estimate-buy-resource";
    const body = {
        amount,
        unitPrice: "MEDIUM",
        resourceType: RESOURCE_TYPE,
        durationSec,
        requester: requestAddress,
        receiver: receiverAddress,
        options: {
            allowPartialFill: true,
        },
    };
    const data = await fetch(url, {
        method: "POST",
        headers: {
            "content-type": "application/json",
        },
        body: JSON.stringify(body),
    });
    const response = await data.json();
    /**
     * Example response 
   {
        "error": false,
        "message": 'Success',
        "data": {
            "unitPrice": 50,
            "durationSec": 259200,
            "estimateTrx": 7680000,
            "availableResource": 32000
        }
    }
     */
    return response;
};

const GetSignedTransaction = async (estimateTrx, requestAddress) => {
    const dataSendTrx = await tronWeb.transactionBuilder.sendTrx(TRONSAVE_RECEIVER_ADDRESS, estimateTrx, requestAddress);
    const signedTx = await tronWeb.trx.sign(dataSendTrx, PRIVATE_KEY);
    return signedTx;
};

const CreateOrder = async (signedTx, receiverAddress, unitPrice, durationSec, options) => {
    const url = TRONSAVE_API_URL + "/v2/buy-resource";
    const body = {
        resourceType: RESOURCE_TYPE,
        unitPrice,
        allowPartialFill: true,
        receiver: receiverAddress,
        durationSec,
        signedTx,
        options
    };
    const data = await fetch(url, {
        method: "POST",
        headers: {
            "content-type": "application/json",
        },
        body: JSON.stringify(body),
    });
    const response = await data.json();
    /**
     * Example response
     * {
     *     "error": false,
     *     "message": "Success",
     *     "data": {
     *         "orderId": "6809fdb7b9ba217a41d726fd"
     *     }
     * }
     */
    return response;
};

/**
 * Main function to buy resource using private key
 * @returns {Promise<void>}
 */
const BuyResourceUsingPrivateKey = async () => {
    //Step 1: Estimate the cost of buy order
    const estimateData = await GetEstimate(REQUEST_ADDRESS, RECEIVER_ADDRESS, BUY_AMOUNT, DURATION_SEC);
    if (estimateData.error) throw new Error(estimateData.message);
    console.log(estimateData);
    const { unitPrice, estimateTrx, durationSec, availableResource } = estimateData.data;
    /*
    {
        "unitPrice": 60,
        "durationSec": 259200,
        "availableResource": 4298470,
        "estimateTrx": 13500000,
    }
     */
    const isReadyFulfilled = availableResource >= BUY_AMOUNT; //if availableResource equal BUY_AMOUNT it means that your order can be fulfilled 100%
    if (isReadyFulfilled) {
        //Step 2: Build signed transaction by using private key
        const signedTx = await GetSignedTransaction(estimateTrx, REQUEST_ADDRESS);
        console.log(signedTx);
        /*
        {
            "visible": false,
            "txID": "446eed36e31249b98b201db2e81a3825b185f1a3d8b2fea348b24fc021e58e0d",
            "raw_data": {
                "contract": [
                {
                    "parameter": {
                    "value": {
                        "amount": 13500000,
                        "owner_address": "417a0d868d1418c9038584af1252f85d486502eec0",
                        "to_address": "41055756f33f419278d9ea059bd2b21120e6add748"
                    },
                    "type_url": "type.googleapis.com/protocol.TransferContract"
                    },
                    "type": "TransferContract"
                }
                ],
                "ref_block_bytes": "0713",
                "ref_block_hash": "6c5f7686f4176139",
                "expiration": 1691465106000,
                "timestamp": 1691465046758
            },
            "raw_data_hex": "0a02071322086c5f7686f417613940d084b5999d315a68080112640a2d747970652e676f6f676c65617069732e636f6d2f70726f746f636f6c2e5472616e73666572436f6e747261637412330a15417a0d868d1418c9038584af1252f85d486502eec0121541055756f33f419278d9ea059bd2b21120e6add74818e0fcb70670e6b5b1999d31",
            "signature": ["xxxxxxxxx"]
        }
         */

        //Step 3: Create order
        const options = {
            allowPartialFill: true,
            maxPriceAccepted: 100,
        }
        const dataCreateOrder = await CreateOrder(signedTx, RECEIVER_ADDRESS, unitPrice, durationSec, options);
        console.log(dataCreateOrder);
    }
};
BuyResourceUsingPrivateKey()