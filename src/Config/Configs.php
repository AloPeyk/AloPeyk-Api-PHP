<?php

namespace AloPeyk\Config;

class Configs
{
    const TOKEN = "PUT-YOUR-ACCESS-TOKEN-HERE";


    /*
    |-------------------------------------------------------------------------------------------------------------------
    | PACKAGE CONSTANTS
    |-------------------------------------------------------------------------------------------------------------------
    |
    | Don't edit following values
    |
    */
    const URL = 'https://api.staging.alopeyk.com';
    const API_URL = 'https://api.staging.alopeyk.com/api/v2/';
    const TRACKING_URL = 'https://tracking.staging.alopeyk.com/';
    const PAYMENT_ROUTES = [
        'saman' => 'payments/saman/checkout',
        'zarinpal' => 'payments/zarinpal/checkout'
    ];
    const ADDRESS_TYPES = [
        'origin',
        'destination',
    ];
    const TRANSPORT_TYPES = [
        'motorbike',
        'motor_taxi',
        'cargo',
    ];
    const CITIES = [
        'tehran',
        'shemiranat',
        'rey',
    ];

}

