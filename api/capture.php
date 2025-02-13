<?php

$config = require_once(dirname(__DIR__) . '/config.php');
require_once(dirname(__DIR__) . '/src/paymentManager.php');

$paymentManager = new paymentManager($config);

$details = $paymentManager->getOrderDetails($_GET['order_id']);
$updateResult = $paymentManager->updateOrderAmount($_GET['order_id'], 106);
$result = $paymentManager->capturePayment($_GET['order_id']);


echo json_encode([
    "result" => $result,
    "update" => $updateResult,
    "details" => $details
]);
