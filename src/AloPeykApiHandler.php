<?php

namespace AloPeyk;

use AloPeyk\Config\Configs;
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Validator\AloPeykValidator;

class AloPeykApiHandler
{
    private static $localToken;
    private static $env;
    private static $endpoint;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return self::$name($arguments);
        }
        throw new AloPeykApiException('AloPeyk API: This Function Does Not Exist!');
    }

    /**
     * @param string $endPoint
     * @param string $method
     * @param null $postFields
     * @return array
     * @throws AloPeykApiException
     */
    private static function getCurlOptions($endPoint = '', $method = 'GET', $postFields = null)
    {
        /*
         * Throw Exception If User Machine DOES NOT ABLE To Use 'openssl'
         */
        if (!extension_loaded('openssl')) {
            throw new AloPeykApiException('AloPeyk API Needs The Open SSL PHP Extension! please enable it on your server.');
        }
        /*
         * Get ACCESS-TOKEN
         */
        $accessToken = self::getToken();
        if (!$accessToken) {
            throw new AloPeykApiException('Invalid ACCESS-TOKEN! 
            All AloPeyk API endpoints support the JWT authentication protocol. 
            To start sending authenticated HTTP requests you will need to use your JWT authorization token which is sent to you.
            Put it in: vendor/alopeyk/alopeyk-api-php/src/Config/Configs.php : TOKEN const 
            ');
        }
        $endpoint = self::getEndpoint();
        $curlOptions = [
            CURLOPT_URL => $endpoint['api_url'] . $endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json; charset=utf-8',
                'X-Requested-With: XMLHttpRequest'
            ],
        ];
        if ($method == 'GET') {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'GET';
        } else {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'POST';
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($postFields);
        }
        return $curlOptions;
    }

    /**
     * @param $curlObject
     * @return mixed
     * @throws AloPeykApiException
     */
    private static function getApiResponse($curlObject)
    {
        $response = curl_exec($curlObject);
        $err = curl_error($curlObject);
        curl_close($curlObject);
        if ($err) {
            throw new AloPeykApiException($err);
        } else {
            $response = json_decode($response);
            if ( $response && $response->status == 'fail' ) {
                $err_msg = '';
                if ( isset( $response->message ) ) {
                    $err_msg = $response->message;
                } elseif ( isset( $response->object ) ) {
                    if ( isset( $response->object->error_msg ) ) {
                        $err_msg = $response->object->error_msg;
                    } elseif ( isset( $response->object->message ) ) {
                        $err_msg = $response->object->message;
                        $error_json = json_decode( $err_msg );
                        if ( json_last_error() == JSON_ERROR_NONE ) {
                            foreach ( $error_json as $value ) {
                                $err_msg = $value[0];
                                break;
                            }
                        }
                    }
                }
                $response->message = $err_msg;
            }
            return $response;
        }
    }

    /**
     * @param $localToken
     */
    public static function setToken($localToken)
    {
        self::$localToken = $localToken;
    }

    /**
     * @return string
     */
    public static function getToken()
    {
        $accessToken = empty(self::$localToken) ? Configs::TOKEN : self::$localToken;
        if (empty($accessToken) || $accessToken == "PUT-YOUR-ACCESS-TOKEN-HERE") {
            return false;
        }
        return $accessToken;
    }

    /**
     * @param $env
     * @param $endpoint
     */
    public static function setEndpoint($env = 'sandbox', $endpoint = null)
    {
        self::$env = $env;
        $endpoints = Configs::ENDPOINTS;
        if ($env == 'sandbox') {
            $endpoint = $endpoints['sandbox'];
        } elseif ($env == 'production') {
            $endpoint = $endpoints['production'];
        } elseif ($env == 'custom') {
            if (!is_array($endpoint) || !isset($endpoint['url']) || !isset($endpoint['api_url']) || !isset($endpoint['tracking_url'])) {
                throw new AloPeykApiException('Endpoint is not correct');
            }
        } else {
            $endpoint['url']          = str_replace('***', $env, $endpoints['custom']['url']);
            $endpoint['api_url']      = str_replace('***', $env, $endpoints['custom']['api_url']);
            $endpoint['tracking_url'] = str_replace('***', $env, $endpoints['custom']['tracking_url']);
        }
        self::$endpoint = $endpoint;
    }

    /**
     * @return array
     */
    public static function getEndpoint($endpoint = null)
    {
        $endpoints = Configs::ENDPOINTS;
        if (is_null($endpoint)) {
            $endpoint = empty(self::$endpoint) ? $endpoints['sandbox'] : self::$endpoint;
        } else {
            $endpoint = $endpoints[$endpoint];
        }
        return $endpoint;
    }

    /** ----------------------------------------------------------------------------------------------------------------
     * public functions
     * ---------------------------------------------------------------------------------------------------------------- */

    /**
     * Authentication
     * @param  $withConfig
     */
    public static function authenticate($withConfig = false, $with = false)
    {
        $curl = curl_init();
        $config = "?withconfig={$withConfig}";
        if ( $with && is_array($with) ) {
            $config = $config . '&' . http_build_query( $with );
        }
        curl_setopt_array($curl, self::getCurlOptions($config));

        return self::getApiResponse($curl);
    }

    /**
     * @param $latitude
     * @param $longitude
     * @return mixed
     * @throws AloPeykApiException
     */
    public static function getAddress($latitude, $longitude)
    {
        $curl = curl_init();


        if (!AloPeykValidator::validateLatitude($latitude)) {
            throw new AloPeykApiException('Latitude is not correct');
        }
        if (!AloPeykValidator::validateLongitude($longitude)) {
            throw new AloPeykApiException('Longitude is not correct');
        }
        
        curl_setopt_array($curl, self::getCurlOptions("locations?latlng=$latitude,$longitude"));

        return self::getApiResponse($curl);
    }

    /**
     * @param $locationName
     * @return mixed
     * @throws AloPeykApiException
     */
    public static function getLocationSuggestion($locationName, $latlng)
    {
        $curl = curl_init();

        $locationName = AloPeykValidator::sanitize($locationName);
        if (empty($locationName)) {
            throw new AloPeykApiException('Location Name can not be empty!');
        }

        $name = urlencode($locationName);

        curl_setopt_array($curl, self::getCurlOptions("locations?input={$name}&location={$latlng}"));

        return self::getApiResponse($curl);
    }

    /**
     * @param $order
     * @return mixed
     */
    public static function getPrice($order)
    {
        $curl = curl_init();

        curl_setopt_array($curl, self::getCurlOptions('orders/price/calc', 'POST', $order->toArray('getPrice')));

        return self::getApiResponse($curl);
    }

    /**
     * @param $order
     * @return mixed
     */
    public static function createOrder($order)
    {
        $curl = curl_init();

        curl_setopt_array($curl, self::getCurlOptions('orders', 'POST', $order->toArray('createOrder')));

        return self::getApiResponse($curl);
    }

    /**
     * @param $orderID
     * @return mixed
     * @throws AloPeykApiException
     */
    public static function getOrderDetail($orderID)
    {
        $curl = curl_init();

        $orderID = AloPeykValidator::sanitize($orderID);
        if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
            throw new AloPeykApiException('OrderID must be integer!');
        }

        curl_setopt_array($curl, self::getCurlOptions("orders/{$orderID}?columns=*,addresses,screenshot,progress,courier_info,next_address_any,customer,last_position_minimal,eta_minimal"));

        return self::getApiResponse($curl);
    }

    /**
     * @param $orderID
     * @return mixed
     * @throws AloPeykApiException
     */
    public static function cancelOrder($orderID, $comment)
    {
        $curl = curl_init();

        $orderID = AloPeykValidator::sanitize($orderID);
        if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
            throw new AloPeykApiException('OrderID must be integer!');
        }

        curl_setopt_array($curl, self::getCurlOptions("orders/{$orderID}/cancel?comment={$comment}"));

        return self::getApiResponse($curl);
    }

    /**
     * @param $orderID
     * @param  $params parameters of comment and rate which are passed inside the $params array
     * @return mixed
     * @throws AloPeykApiException
     */
    public static function finishOrder($orderID, $params)
    {
        $curl = curl_init();

        $orderID = AloPeykValidator::sanitize($orderID);
        if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
            throw new AloPeykApiException('OrderID must be integer!');
        }

        curl_setopt_array($curl, self::getCurlOptions("orders/{$orderID}/finish", "POST", $params));

        return self::getApiResponse($curl);
    }

    /**
     * User Profile
     * @return  mixed
     */
    public static function getUserProfile()
    {
        $curl = curl_init();

        curl_setopt_array($curl, self::getCurlOptions("show-profile?columns=*,credit"));

        return self::getApiResponse($curl);
    }    

    /**
     * Coupon Validation
     * @return  mixed
     */
    public static function validateCoupon($code)
    {
        $curl = curl_init();

        curl_setopt_array($curl, self::getCurlOptions("coupons", "POST", $code));

        return self::getApiResponse($curl);
    }

    /**
     * Payment Route List
     * @return array
     */
    public static function getPaymentGateways()
    {
        return array_keys(Configs::PAYMENT_ROUTES);
    }

    /**
     * Transport Type List
     * @return array
     */
    public static function getTransportTypes()
    {
        return Configs::TRANSPORT_TYPES;
    }

    /**
     * Credit Top-Up
     * @param $user_id
     * @param $amount
     * @return string
     */
    public static function getPaymentRoute($user_id, $amount, $gateway = 'saman')
    {
        $endpoint = self::getEndpoint();
        return $endpoint['api_url'] . Configs::PAYMENT_ROUTES[$gateway] . "?user_id={$user_id}&amount={$amount}";
    }

    /**
     * Retrieve tracking url for the order
     * @param $orderToken
     * @return string
     */
    public static function getTrackingUrl($orderToken)
    {
        $endpoint = self::getEndpoint();
        return $endpoint['tracking_url'] . "#/{$orderToken}";
    }

    /**
     * Retrieve the print invoice for the order
     * @param $orderId
     * @param $orderToken
     * @return string
     */
    public static function getPrintInvoice($orderId, $orderToken)
    {
        $endpoint = self::getEndpoint();
        return $endpoint['url'] . "order/{$orderId}/print?token={$orderToken}";
    }

    /**
     * Retrieve the application configuration array
     * @return mixed
     */
    public static function getAppConfig()
    {
        $curl = curl_init();

        curl_setopt_array($curl, self::getCurlOptions("config"));

        return self::getApiResponse($curl);
    }

    /**
     * Retrieve the full path of the uploaded signature
     * @return string
     */
    public static function getSignaturePath($relativePath)
    {
        $endpoint = self::getEndpoint();
        return $endpoint['url'] . $relativePath;
    }

    /**
     * Return list of customers loyalty products OR Submit customer loyalty product buy request
     * @return  mixed
     */
    public static function CustomerLoyaltyProducts($productId = '', $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt_array($curl, self::getCurlOptions('loyalty/customer/products/' . $productId, $method));
        return self::getApiResponse($curl);
    }
}