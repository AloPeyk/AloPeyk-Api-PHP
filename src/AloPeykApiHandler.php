<?php

namespace AloPeyk;

use AloPeyk\Config\Configs;
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Validator\AloPeykValidator;

class AloPeykApiHandler
{

    private static $localToken;

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
        throw new \Exception('AloPeyk API: This Function Does Not Exist!');
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
        $accessToken = empty(self::$localToken) ? Configs::TOKEN : self::$localToken;

        $curlOptions = [
            CURLOPT_URL => Configs::API_URL . $endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FAILONERROR => true,
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
            return json_decode($response);
        }
    }

    /**
     * @param $localToken
     */
    public static function setToken($localToken)
    {
        self::$localToken = $localToken;
    }

    /** ----------------------------------------------------------------------------------------------------------------
     * public functions
     * ---------------------------------------------------------------------------------------------------------------- */

    /**
     * Authentication
     */
    public static function authenticate()
    {
        $curl = curl_init();

        curl_setopt_array($curl, self::getCurlOptions());

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
    public static function getLocationSuggestion($locationName)
    {
        $curl = curl_init();

        $locationName = AloPeykValidator::sanitize($locationName);
        if (empty($locationName)) {
            throw new AloPeykApiException('Location Name can not be empty!');
        }

        curl_setopt_array($curl, self::getCurlOptions("locations?input=$locationName"));

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

        curl_setopt_array($curl, self::getCurlOptions("orders/{$orderID}?columns=*,addresses,screenshot,progress,courier,customer,last_position_minimal,eta_minimal"));

        return self::getApiResponse($curl);
    }

    /**
     * @param $orderID
     * @return mixed
     * @throws AloPeykApiException
     */
    public static function cancelOrder($orderID)
    {
        $curl = curl_init();

        $orderID = AloPeykValidator::sanitize($orderID);
        if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
            throw new AloPeykApiException('OrderID must be integer!');
        }

        curl_setopt_array($curl, self::getCurlOptions("orders/{$orderID}/cancel"));

        return self::getApiResponse($curl);
    }
}