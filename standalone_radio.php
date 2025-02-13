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

    <ul id="payment_methods"></ul>

    <div id="button_container"></div>


    <!-- Initialize the JS-SDK -->
    <script src="<?php echo $url ?>"></script>

    <script>
        const texts = {
            paylater: 'Später bezahlen',
            sepa: 'Sepa Lastschrift',
            card: 'Debit- oder Kreditkarte'
        };

        paypal.getFundingSources().forEach(function(fundingSource) {
            console.log(fundingSource);
            // Initialize the buttons
            const button = paypal.Buttons({
                fundingSource: fundingSource,
            });

            // Check if the button is eligible
            if (button.isEligible()) {
                console.log(fundingSource, button);

                const li = document.createElement('li');
                const cb = document.createElement('input');
                cb.setAttribute('type', 'radio');
                cb.setAttribute('name', 'payment_methods');
                cb.name = payment_methods;
                cb.value = fundingSource;
                li.appendChild(cb);

                cb.addEventListener('click', () => {
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
                    document.getElementById('button_container').innerText = '';
                    button.render('#button_container');
                });

                const span = document.createElement('span');
                span.innerText = texts[fundingSource] ? texts[fundingSource] : (fundingSource.charAt(0).toUpperCase() + fundingSource.slice(1));
                li.appendChild(span);

                document.getElementById('payment_methods').appendChild(li);
            }
        });
    </script>

</body>

</html>