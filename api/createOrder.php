<?php

$config = require_once(dirname(__DIR__) . '/config.php');
require_once(dirname(__DIR__) . '/src/paymentManager.php');

$paymentManager = new paymentManager($config);

$id = $paymentManager->createOrder();

echo json_encode([
    "id" => $id
]);
