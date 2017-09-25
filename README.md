# AloPeyk/AloPeyk-Api-PHP

[![License](https://poser.pugx.org/alopeyk/alopeyk-api-php/license)](https://packagist.org/packages/alopeyk/alopeyk-api-php)
[![Latest Stable Version](https://poser.pugx.org/alopeyk/alopeyk-api-php/v/stable)](https://packagist.org/packages/alopeyk/alopeyk-api-php)
[![Monthly Downloads](https://poser.pugx.org/alopeyk/alopeyk-api-php/d/monthly)](https://packagist.org/packages/alopeyk/alopeyk-api-php)

This package is built to facilitate application development for AloPeyk RESTful API. For more information about this api, please visit [AloPeyk Documents](https://docs.alopeyk.com/)

## Installation
First of all, You need an [ACCESS-TOKEN](https://alopeyk.com/contact?unit=sales). 
All Alopeyk API endpoints support the JWT authentication protocol. To start sending authenticated HTTP requests you will need to use your JWT authorization token which is sent to you.
Then you can install this package by using [Composer](http://getcomposer.org), running this command:

```sh
composer require alopeyk/alopeyk-api-php
```
Link to Packagist: https://packagist.org/packages/alopeyk/alopeyk-api-php

## Usage

#### 1. Authenticate

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Exception\AloPeykApiException;

$apiResponse = null;
try {
    $apiResponse = AloPeykApiHandler::authenticate();
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

if ($apiResponse && $apiResponse->status == "success") {
    $user = $apiResponse->object->user;
    echo $user->firstname . " " . $user->lastname;
}
```


#### 2. Get Address

This endpoint retrieves place information by its latitude and longitude.

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Exception\AloPeykApiException;

$apiResponse = null;
try {
    $apiResponse = AloPeykApiHandler::getAddress("35.732595", "51.413379");
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

if($apiResponse && $apiResponse->status == "success"){
    echo $apiResponse->object->district;
}
```


#### 3. Location Suggestions

This endpoint retrieves suggestions by search input.
The result will be an array of suggestions. Each one includes the region and the name of the retrieved place, and offers coordinates for that item.

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Exception\AloPeykApiException;

$apiResponse = null;
try {
    // $locationName = null;   // returns AloPeyk Exception
    // $locationName = '';     // returns AloPeyk Exception
    $locationName = "أرژ";
    $apiResponse = AloPeykApiHandler::getLocationSuggestion($locationName);
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

if ($apiResponse && $apiResponse->status == "success") {
    $locations = $apiResponse->object;
    echo "<ol>";
    foreach ($locations as $location) {
        echo "<li>";
        echo $location->region . ": " . $location->title;
        echo "</li>";
    }
    echo "</ol>";
}
```


#### 4. Get Price

Request a quote for an order with origin address and destination address.

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Model\Address;
use AloPeyk\Model\Order;

$apiResponse = null;
try {
    // create origin: Behjat Abad
    $origin = new Address('origin', 'tehran', '35.723711', '51.410547');

    // create first destination: N Sohrevardi Ave
    $firstDest = new Address('destination', 'tehran', '35.728457', '51.436969');

    // create second destination: Ahmad Qasir Bokharest St
    $secondDest = new Address('destination', 'tehran', '35.729379', '51.418151');

    // create new order
    $order = new Order('motor_taxi', $origin, [$firstDest, $secondDest]);
    $order->setHasReturn(true);

    $apiResponse= AloPeykApiHandler::getPrice($order);

} catch (\AloPeyk\Exception\AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);
```


#### 5. Create Order

Once you calculated your the price of your order, you can use this endpoint in order to create a new order.

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Model\Address;
use AloPeyk\Model\Order;

$apiResponse = null;
try {
    /*
     * create origin: Behjat Abad
     */
    $origin = new Address('origin', 'tehran', '35.755460', '51.416874');
    $origin->setAddress("... Behjat Abad, Tehran");
    $origin->setDescription("Behjat Abad");
    $origin->setUnit("44");
    $origin->setNumber("1");
    $origin->setPersonFullname("Leonardo DiCaprio");
    $origin->setPersonPhone("09370000000");

    /*
     * create first destination: N Sohrevardi Ave
     */
    $firstDest = new Address('destination', 'tehran', '35.758495', '51.442550');
    $firstDest->setAddress("... N Sohrevardi Ave, Tehran");
    $firstDest->setDescription("N Sohrevardi Ave");
    $firstDest->setUnit("55");
    $firstDest->setNumber("2");
    $firstDest->setPersonFullname("Eddie Redmayne");
    $firstDest->setPersonPhone("09380000000");

    /*
     * create second destination: Ahmad Qasir Bokharest St
     */
    $secondDest = new Address('destination', 'tehran', '35.895452', '51.589632');
    $secondDest->setAddress("... Ahmad Qasir Bokharest St, Tehran");
    $secondDest->setDescription("Ahmad Qasir Bokharest St");
    $secondDest->setUnit("66");
    $secondDest->setNumber("3");
    $secondDest->setPersonFullname("Matt Damon");
    $secondDest->setPersonPhone("09390000000");

    $order = new Order('motor_taxi', $origin, [$firstDest, $secondDest]);
    $order->setHasReturn(true);

    $apiResponse = AloPeykApiHandler::createOrder($order);

} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);
```


#### 6. Get Order Detail

In order to get the order details, call this method.

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Exception\AloPeykApiException;

$apiResponse = null;
try {
    // $orderID = "   309 ";
    // $orderID = "   309<p>";
    // $orderID = '';
    // $orderID = null;
    $orderID = 309;
    $apiResponse = AloPeykApiHandler::getOrderDetail($orderID);
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);
```


#### 7. Cancel Order

You can cancel any order before courier arrival (before the accepted status)

```PHP
use AloPeyk\AloPeykApiHandler;
use AloPeyk\Exception\AloPeykApiException;

$apiResponse = null;
try {
    // $orderID = "   300 ";     // works fine as 300
    // $orderID = "   300<p>";   // works fine as 300
    // $orderID = '';            // throws AloPeykException
    // $orderID = null;          // throws AloPeykException
    $orderID = 300;
    $apiResponse = AloPeykApiHandler::cancelOrder($orderID);
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);
```




## License

This package is released under the __MIT license__.

Copyright (c) 2012-2017 Markus Poerschke

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
