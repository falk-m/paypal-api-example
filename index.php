<!DOCTYPE html>
<html>

<?php

$config = require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/src/paymentManager.php');

$paymentManager = new paymentManager($config);

$url = $paymentManager->getJsSrc();
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PayPal JS SDK Standard Integration</title>
</head>

<body>

    <ul>
        <li>
            <a href="/without_server.php">Without server interaction</a>
        </li>
        <li>
            <a href="/standalone_buttons.php">render buttons standalone</a>
        </li>
        <li>
            <a href="/standalone_radio.php">render buttons when need</a>
        </li>
    </ul>

    <div id="paypal-button-container"></div>
    <p id="result-message"></p>

    paypal-marks-container
    <div id="paypal-marks-container"></div>

    <!-- Initialize the JS-SDK -->
    <script src="<?php echo $url ?>"></script>

    <script>
        paypal.Marks().render('#paypal-marks-container');

        // Render the button component
        paypal
            .Buttons({
                // Sets up the transaction when a payment button is clicked
                createOrder: async function(data, actions) {
                    /*return actions.order.create({
                        purchase_units: [{
                            "amount": {
                                "currency_code": "EUR",
                                "value": 102
                            }
                        }]
                    });*/
                    const response = await fetch("/api/createOrder.php");

                    const orderData = await response.json();


                    if (orderData.id) {
                        console.log('create order', orderData);
                        return orderData.id;
                    }
                },
                onApprove: async (data, actions) => {
                    console.log('onApprove', data);

                    const response = await fetch("/api/capture.php?order_id=" + data.orderID);

                    const orderData = await response.json();

                    console.log('onApprove result', orderData);
                },
                onError: function(error) {
                    // Do something with the error from the SDK
                },

                style: {
                    shape: "rect",
                    layout: "vertical",
                    color: "gold",
                    label: "paypal",
                },

                //displayOnly: ['']
            })
            .render("#paypal-button-container");
    </script>

</body>

</html>