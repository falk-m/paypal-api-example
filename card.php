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
    <div id="card-form" class="card_container">
        <div id="card-name-field-container"></div>
        <div id="card-number-field-container"></div>
        <div id="card-expiry-field-container"></div>
        <div id="card-cvv-field-container"></div>
    </div>

    <button id="card-field-submit-button" type="button">
        Pay now with Card
    </button>

    <!-- Initialize the JS-SDK -->
    <script src="<?php echo $url ?>"></script>

    <script type="module">
        // Render each field after checking for eligibility
        const cardField = window.paypal.CardFields({
            createOrder: async function(data, actions) {
                const response = await fetch("/api/createOrder.php");

                const orderData = await response.json();


                if (orderData.id) {
                    console.log('create order', orderData);
                    return orderData.id;
                }
            },
            onApprove: () => {
                console.log('onApprove');
            },
            onError: (err) => {
                console.log('err', err);
            },
            style: {
                input: {
                    "font-size": "16px",
                    "font-family": "courier, monospace",
                    "font-weight": "lighter",
                    color: "#ccc",
                },
                ".invalid": {
                    color: "purple"
                },
            },
        });

        if (cardField.isEligible()) {
            const nameField = cardField.NameField({
                style: {
                    input: {
                        color: "blue"
                    },
                    ".invalid": {
                        color: "purple"
                    }
                },
            });
            nameField.render("#card-name-field-container");

            const numberField = cardField.NumberField({
                style: {
                    input: {
                        color: "blue"
                    }
                },
            });
            numberField.render("#card-number-field-container");

            const cvvField = cardField.CVVField({
                style: {
                    input: {
                        color: "blue"
                    }
                },
            });
            cvvField.render("#card-cvv-field-container");

            const expiryField = cardField.ExpiryField({
                style: {
                    input: {
                        color: "blue"
                    }
                },
            });
            expiryField.render("#card-expiry-field-container");

            document
                .getElementById("card-field-submit-button")
                .addEventListener("click", () => {
                    cardField
                        .submit()
                        .then(() => {
                            console.log('succsess');
                            // submit successful
                        }).catch((err) => {
                            console.log('err2', err);
                        });
                });
        }
    </script>

</body>

</html>