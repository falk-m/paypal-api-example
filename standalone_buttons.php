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

    <style>
        .container {
            border: 1px solid red;
            padding: 1rem;
            margin: 1rem;
        }
    </style>
</head>

<body>

    paypal <div class="container" id="container-paypal"></div>
    sepa <div class="container" id="container-sepa"></div>
    trustly <div class="container" id="container-trustly"></div>
    satispay <div class="container" id="container-satispay"></div>
    card <div class="container" id="container-card"></div>
    paylater <div class="container" id="container-paylater"></div>


    <!-- Initialize the JS-SDK -->
    <script src="<?php echo $url ?>"></script>

    <script>
        paypal.getFundingSources().forEach(function(fundingSource) {
            console.log(fundingSource);
            // Initialize the buttons
            const button = paypal.Buttons({
                style: {
                    shape: 'pill'
                },
                fundingSource: fundingSource,
                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            "amount": {
                                "currency_code": "EUR",
                                "value": 101
                            }
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    return actions.order.capture().then(function(details) {
                        alert('Transaction completed by ' + details.payer.name.given_name + '!');
                    });
                }
            });

            // Check if the button is eligible
            if (button.isEligible()) {
                console.log(fundingSource, button);
                // Render the standalone button for that funding source
                button.render('#container-' + fundingSource);
            }
        });
    </script>

</body>

</html>