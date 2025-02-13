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
    <div id="paypal-button-container"></div>

    <!-- Initialize the JS-SDK -->
    <script src="<?php echo $url ?>"></script>

    <script>
        // Render the button component
        paypal
            .Buttons({
                // Sets up the transaction when a payment button is clicked
                createOrder: async function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            "amount": {
                                "currency_code": "EUR",
                                "value": 102
                            }
                        }]
                    });
                },
                onApprove: async (data, actions) => {
                    return actions.order.capture().then(function(details) {
                        alert('Transaction completed by ' + details.payer.name.given_name + '!');
                    });
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