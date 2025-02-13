<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class paymentManager
{

    public  function __construct(private array $config) {}

    public function getJsSrc($buyerCountry = 'DE', $currency = 'EUR')
    {
        $clientId = $this->config['client_id'];
        $components = implode(',', ['buttons', 'card-fields', 'funding-eligibility', 'marks']);
        $enableFunding = implode(',', ['venmo', 'paylater', 'card', 'sofort']);

        return "https://www.paypal.com/sdk/js?client-id={$clientId}&buyer-country={$buyerCountry}&currency={$currency}&components={$components}&enable-funding={$enableFunding}";
    }

    /**
     * https://developer.paypal.com/docs/api/orders/v2/#orders_patch
     */
    public function updateOrderAmount($orderId, $newAmount, $currency = 'EUR')
    {
        $update = [
            [
                "op" => "replace",
                "path" => "/purchase_units/@reference_id=='default'/amount",
                "value" => [
                    'currency_code' => $currency,
                    'value' => $newAmount
                ]
            ]
        ];

        $token = $this->getAccessToken();
        $options = array(
            CURLOPT_URL => $this->config['api_endpoint'] . "/v2/checkout/orders/{$orderId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($update, JSON_PRETTY_PRINT),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$token}"
            ]
        );

        $res = json_decode($this->execCurl($options), true);

        return $res;
    }

    /**
     * https://developer.paypal.com/docs/api/orders/v2/#orders_get
     */
    public function getOrderDetails($orderId)
    {
        $token = $this->getAccessToken();

        $options = array(
            CURLOPT_URL => $this->config['api_endpoint'] . "/v2/checkout/orders/{$orderId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}"
            ]
        );

        $res = json_decode($this->execCurl($options), true);

        return $res;
    }

    /**
     * https://developer.paypal.com/docs/api/orders/v2/#orders_capture
     */
    public function capturePayment($orderId)
    {
        $token = $this->getAccessToken();

        $options = array(
            CURLOPT_URL => $this->config['api_endpoint'] . "/v2/checkout/orders/{$orderId}/capture",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$token}"
            ]
        );

        $res = json_decode($this->execCurl($options), true);

        return $res['status'];
    }

    /**
     * https://developer.paypal.com/docs/api/orders/v2/#orders_create
     */
    public function createOrder()
    {
        $token = $this->getAccessToken();

        $order = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => 101
                    ]
                ]
            ],
            'payer' => [
                'email_address' => 'tester@tester.de',
                'name' => [
                    'given_name' => 'name1',
                    'surname' => 'name2'
                ],
                'address' => [
                    'address_line_1' => 'Testtstr 1',
                    'admin_area_2' => 'Leipzig',
                    'postal_code' => '04109',
                    'country_code' => 'DE'
                ]
            ]
        ];

        $options = array(
            CURLOPT_URL => $this->config['api_endpoint'] . '/v2/checkout/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($order, JSON_PRETTY_PRINT),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$token}"
            ]
        );

        $res = json_decode($this->execCurl($options), true);

        return $res['id'] ?? throw new Exception("can not create order");
    }

    /**
     * https://developer.paypal.com/api/rest/authentication/
     */
    private function getAccessToken()
    {

        $clientId =  $this->config['client_id'];
        $clientSecret = $this->config['client_secret'];

        $options = array(
            CURLOPT_URL => $this->config['api_endpoint'] . '/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_USERPWD => "{$clientId}:{$clientSecret}",
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        );

        $res = json_decode($this->execCurl($options), true);

        return $res['access_token'] ?? throw new Exception("access_token not included in result");
    }

    private function execCurl(array $options)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode > 300) {
            throw new Exception("curls status code: {$statusCode}; Response: {$result}");
        }

        curl_close($ch);

        return $result;
    }
}
