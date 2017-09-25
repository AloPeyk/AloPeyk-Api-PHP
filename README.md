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

// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
{
  "status": "success",
  "object": {
    "user": {
      "id": 99,
      "phone": "09195071197",
      "firstname": "john",
      "lastname": "doe",
      "type": "CUSTOMER",
      "email": "john_doe@gmail.com",
      "email_verified": 0,
      "verify": 1,
      "found_us": "",
      "referral_code": null,
      "referred_by": null,
      "created_at": "2017-09-16T17:06:28+04:30",
      "updated_at": "2017-09-18T16:07:02+04:30",
      "deleted_at": null,
      "jwt_token": **YOUR_TOKEN**,
      "avatar": {
            "url": "/uploads/user/99/avatar.jpg?var=1505744313"
      },
      "last_online": null,
      "is_online": null,
      "banks": []
    }
  }
}
```


#### 2. Get Address

This endpoint retrieves place information by its latitude and longitude.

```PHP
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Model\Location;

$apiResponse = null;
try {
    $apiResponse = Location::getAddress("35.732595", "51.413379");
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

if ($apiResponse && $apiResponse->status == "success") {
    echo $apiResponse->object->district;
}

// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
{
  "status": "success",
  "message": "findPlace",
  "object": {
    "address": [
        "چهاردهم",
           "وزرا",
          "تهران",
        ""
    ],
    "region": "آرژانتین",
    "district": "منطقه ۶",
    "city": "tehran"
  }
}
```


#### 3. Location Suggestions

This endpoint retrieves suggestions by search input.
The result will be an array of suggestions. Each one includes the region and the name of the retrieved place, and offers coordinates for that item.

```PHP
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Model\Location;

$apiResponse = null;
try {
    // $locationName = null;   // returns AloPeyk Exception
    // $locationName = '';     // returns AloPeyk Exception
    $locationName = "أرژ";
    $apiResponse = Location::getSuggestions($locationName);
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

// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
{
  "status": "success",
  "message": "autoComplete",
  "object": [
    {
      "title": "میدان آرژانتین",
      "region": "آرژانتین",
      "lat": "35.737079296849799",
      "lng": "51.415392387445699",
      "district": "منطقه ۶",
      "city": "tehran"
    },
    {
      "title": "آرژانتین",
      "region": "میدان ولی عصر",
      "lat": "35.703254842325698",
      "lng": "51.413370921404997",
      "district": "منطقه ۶",
      "city": "tehran"
    },
    {
      "title": "ارژنگ",
      "region": "آذربایجان",
      "lat": "35.69489505",
      "lng": "51.3973145",
      "district": "منطقه ۱۱",
      "city": "tehran"
    },
    {
      "title": "ارژنگ",
      "region": "پارک لاله",
      "lat": "35.712177826056703",
      "lng": "51.406424628191502",
      "district": "منطقه ۶",
      "city": "tehran"
    }
  ]
}
```


#### 4. Get Price

Request a quote for an order with origin address and destination address.

```PHP
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Model\Address;
use AloPeyk\Model\Order;

$apiResponse = null;
try {
    /*
     * Create Origin Address
     */
    $origin = new Address('origin', 'tehran', '35.723711', '51.410547');

    /*
     * Create First Destination
     */
    $firstDest = new Address('destination', 'tehran', '35.728457', '51.436969');

    /*
     * Create Second Destination
     */
    $secondDest = new Address('destination', 'tehran', '35.729379', '51.418151');

    /*
     * Create New Order
     */
    $order = new Order('motor_taxi', $origin, [$firstDest, $secondDest]);
    $order->setHasReturn(true);

    $apiResponse = $order->getPrice();

} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}


if ($apiResponse && $apiResponse->status == "success") {
    $addresses = $apiResponse->object->addresses;

    $origin = $addresses[0];
    echo "ORIGIN: {$origin->city} ({$origin->lat} , {$origin->lng})";
    echo "<br/>";
    echo "Transport Type: " . $apiResponse->object->transport_type;
    echo "<hr/>";

    $destinations = array_shift($addresses);

    echo "<table border='1' cellspacing='0'>
            <thead>
                <tr style='background: #bddaf5'>
                    <th>#</th>
                    <th>City</th>
                    <th>Distance</th>
                    <th>Duration</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            ";
    foreach ($addresses as $destination) {
        echo "<tr>
                <td>{$destination->priority}</td>
                <td>{$destination->city}</td>
                <td>{$destination->distance}</td>
                <td>{$destination->duration}</td>
                <td>{$destination->price}</td>
              </tr>";
    }
    echo "<tr style='background: #7ab2a5; text-align: center'>
            <td colspan='2'>Total</td>
            <td>{$apiResponse->object->distance}(meters)</td>
            <td>{$apiResponse->object->duration}(seconds)</td>
            <td>{$apiResponse->object->price}(toman)</td>
          </tr>";
}

// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
{
  "status": "success",
  "message": null,
  "object": {
    "addresses": [
      {
        "city": "tehran",
        "type": "origin",
        "lat": 35.75546,
        "lng": 51.416874,
        "priority": 0
      },
      {
        "city": "tehran",
        "type": "destination",
        "lat": 35.758495,
        "lng": 51.44255,
        "priority": 1,
        "distance": 2341,
        "duration": 288,
        "price": 3000
      },
      {
        "city": "tehran",
        "type": "destination",
        "lat": 35.895452,
        "lng": 51.589632,
        "priority": 2,
        "distance": 20192,
        "duration": 2492,
        "price": 16000
      }
    ],
    "price": 31500,
    "credit": false,
    "distance": 22533,
    "duration": 2780,
    "status": "OK",
    "user_credit": "0",
    "delay": 0,
    "city": "tehran",
    "transport_type": "motor_taxi",
    "has_return": false,
    "cashed": false,
    "price_with_return": 47250
  }
}
```


#### 5. Create Order

Once you calculated your the price of your order, you can use this endpoint in order to create a new order.

```PHP
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Model\Address;
use AloPeyk\Model\Order;

$apiResponse = null;
try {
    /*
     * Create Origin: Behjat Abad
     */
    $origin = new Address('origin', 'tehran', '35.755460', '51.416874');
    $origin->setAddress("... Behjat Abad, Tehran");
    $origin->setDescription("Behjat Abad");                                            // optional                            
    $origin->setUnit("44");                                                            // optional
    $origin->setNumber("1");                                                           // optional
    $origin->setPersonFullname("Leonardo DiCaprio");                                   // optional
    $origin->setPersonPhone("09370000000");                                            // optional

    /*
     * Create First Destination: N Sohrevardi Ave
     */
    $firstDest = new Address('destination', 'tehran', '35.758495', '51.442550');
    $firstDest->setAddress("... N Sohrevardi Ave, Tehran");
    $firstDest->setDescription("N Sohrevardi Ave");                                    // optional
    $firstDest->setUnit("55");                                                         // optional
    $firstDest->setNumber("2");                                                        // optional
    $firstDest->setPersonFullname("Eddie Redmayne");                                   // optional
    $firstDest->setPersonPhone("09380000000");                                         // optional
    

    /*
     * Create Second Destination: Ahmad Qasir Bokharest St
     */
    $secondDest = new Address('destination', 'tehran', '35.895452', '51.589632');
    $secondDest->setAddress("... Ahmad Qasir Bokharest St, Tehran");
    $secondDest->setDescription("Ahmad Qasir Bokharest St");                            // optional
    $secondDest->setUnit("66");                                                         // optional
    $secondDest->setNumber("3");                                                        // optional
    $secondDest->setPersonFullname("Matt Damon");                                       // optional
    $secondDest->setPersonPhone("09390000000");                                         // optional

    $order = new Order('motor_taxi', $origin, [$firstDest, $secondDest]);
    $order->setHasReturn(true);

    $apiResponse = $order->create($order);

} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);


// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
{
  "status": "success",
  "message": null,
  "object": {
    "city": "tehran",
    "transport_type": "motor_taxi",
    "customer_id": 99,
    "status": "new",
    "launched_at": "2017-09-19T12:26:08+04:30",
    "delay": 0,
    "price": 31500,
    "credit": false,
    "cashed": false,
    "has_return": false,
    "distance": 22533,
    "duration": 2780,
    "invoice_number": "LKH3LN",
    "pay_at_dest": false,
    "device_id": null,
    "weight": 20,
    "is_api": true,
    "updated_at": "2017-09-19T12:26:08+04:30",
    "created_at": "2017-09-19T12:26:08+04:30",
    "id": 300,
    "signature": null,
    "order_token": "099c68a4a300ga2165445145a8eg992375433db",
    "nprice": null,
    "subsidy": null,
    "signed_by": null,
    "addresses": [
      {
        "id": 568522,
        "order_id": 300,
        "customer_id": 99,
        "courier_id": null,
        "lat": 35.75546,
        "lng": 51.416874,
        "type": "origin",
        "priority": 0,
        "city": "tehran",
        "status": "pending",
        "address": "address of order s origin",
        "description": "some description for origin",
        "unit": "unit of origin address",
        "number": "number of origin address",
        "person_fullname": "sender s name",
        "person_phone": "sender s phone",
        "signed_by": null,
        "distance": null,
        "google_distance": null,
        "duration": null,
        "google_duration": null,
        "arrived_at": null,
        "handled_at": null,
        "created_at": "2017-09-19T12:26:08+04:30",
        "updated_at": "2017-09-19T12:26:08+04:30",
        "deleted_at": null,
        "arrive_lat": null,
        "arrive_lng": null,
        "handle_lat": null,
        "handle_lng": null,
        "signature": null
      },
      {
        "id": 568523,
        "order_id": 300,
        "customer_id": 99,
        "courier_id": null,
        "lat": 35.758495,
        "lng": 51.44255,
        "type": "destination",
        "priority": 1,
        "city": "tehran",
        "status": "pending",
        "address": "address of order s origin",
        "description": "some description for origin",
        "unit": "unit of origin address",
        "number": "number of origin address",
        "person_fullname": "sender s name",
        "person_phone": "sender s phone",
        "signed_by": null,
        "distance": 2341,
        "google_distance": null,
        "duration": 288,
        "google_duration": null,
        "arrived_at": null,
        "handled_at": null,
        "created_at": "2017-09-19T12:26:08+04:30",
        "updated_at": "2017-09-19T12:26:08+04:30",
        "deleted_at": null,
        "arrive_lat": null,
        "arrive_lng": null,
        "handle_lat": null,
        "handle_lng": null,
        "signature": null
      },
      {
        "id": 568524,
        "order_id": 300,
        "customer_id": 99,
        "courier_id": null,
        "lat": 35.895452,
        "lng": 51.589632,
        "type": "destination",
        "priority": 2,
        "city": "tehran",
        "status": "pending",
        "address": "address of order s origin",
        "description": "some description for origin",
        "unit": "unit of origin address",
        "number": "number of origin address",
        "person_fullname": "sender s name",
        "person_phone": "sender s phone",
        "signed_by": null,
        "distance": 20192,
        "google_distance": null,
        "duration": 2492,
        "google_duration": null,
        "arrived_at": null,
        "handled_at": null,
        "created_at": "2017-09-19T12:26:08+04:30",
        "updated_at": "2017-09-19T12:26:08+04:30",
        "deleted_at": null,
        "arrive_lat": null,
        "arrive_lng": null,
        "handle_lat": null,
        "handle_lng": null,
        "signature": null
      }
    ]
  }
}
```


#### 6. Get Order Detail

In order to get the order details, call this method.

```PHP
use AloPeyk\Model\Order;
use AloPeyk\Exception\AloPeykApiException;

$apiResponse = null;
try {
    // $orderID = "   309 ";
    // $orderID = "   309<p>";
    // $orderID = '';
    // $orderID = null;
    $orderID = 309;
    $apiResponse = Order::getDetails($orderID);
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);

// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
"status": "success",
  "message": null,
  "object": {
    "id": 309,
    "invoice_number": "DT63AL",
    "customer_id": 99,
    "device_id": null,
    "courier_id": 130,
    "cancelled_by": null,
    "status": "delivered",
    "distance": 22533,
    "duration": 2780,
    "price": 31500,
    "credit": false,
    "cashed": false,
    "has_return": false,
    "pay_at_dest": false,
    "delay": 0,
    "transport_type": "motor_taxi",
    "city": "tehran",
    "is_api": true,
    "weight": 20,
    "accept_lat": 35.748778183994,
    "accept_lng": 51.411911191127,
    "rate": 0,
    "comment": null,
    "scheduled_at": null,
    "launched_at": "2017-09-20T11:32:34+04:30",
    "accepted_at": "2017-09-20T11:32:37+04:30",
    "delivered_at": "2017-09-20T11:39:38+04:30",
    "finished_at": null,
    "stopped_at": null,
    "removed_at": null,
    "created_at": "2017-09-20T11:32:34+04:30",
    "updated_at": "2017-09-20T11:39:38+04:30",
    "deleted_at": null,
    "picking_at": "2017-09-20T11:33:37+04:30",
    "delivering_at": "2017-09-20T11:34:37+04:30",
    "addresses": [
      {
          "lat": "35.75546",
        "lng": "51.416874",
        "type": "origin",
        "priority": 0,
        "arrived_at": "2017-09-20T11:33:37+04:30",
        "handled_at": "2017-09-20T11:34:37+04:30",
        "id": 568548,
        "city": "tehran",
        "order_id": "309",
        "customer_id": "99",
        "courier_id": "130",
        "status": "handled",
        "address": "address of order s origin",
        "description": "some description for origin",
        "unit": "unit of origin address",
        "number": "number of origin address",
        "person_fullname": "sender s name",
        "person_phone": "sender s phone",
        "signed_by": "",
        "distance": "868",
        "duration": "107",
        "created_at": "2017-09-20T11:32:34+04:30",
        "updated_at": "2017-09-20T11:34:37+04:30",
        "deleted_at": "",
        "arrive_lat": "35.750245509478695",
        "arrive_lng": "51.397214392844006",
        "handle_lat": "35.7501112063621",
        "handle_lng": "51.41630904680925",
        "signature": {
          "url": "/uploads/order/309/address/568548/signature.jpg?var=1506000110"
        }
      },
      {
          "lat": "35.758495",
        "lng": "51.44255",
        "type": "destination",
        "priority": 1,
        "arrived_at": "2017-09-20T11:35:38+04:30",
        "handled_at": "2017-09-20T11:36:38+04:30",
        "id": 568549,
        "city": "tehran",
        "order_id": "309",
        "customer_id": "99",
        "courier_id": "130",
        "status": "handled",
        "address": "address of order s origin",
        "description": "some description for origin",
        "unit": "unit of origin address",
        "number": "number of origin address",
        "person_fullname": "sender s name",
        "person_phone": "sender s phone",
        "signed_by": "",
        "distance": "2341",
        "duration": "288",
        "created_at": "2017-09-20T11:32:34+04:30",
        "updated_at": "2017-09-20T11:36:38+04:30",
        "deleted_at": "",
        "arrive_lat": "35.7581975033594",
        "arrive_lng": "51.41919184233538",
        "handle_lat": "35.76113384396972",
        "handle_lng": "51.414348540631",
        "signature": {
          "url": "/uploads/order/309/address/568549/signature.jpg?var=1506000110"
        }
      },
      {
          "lat": "35.895452",
        "lng": "51.589632",
        "type": "destination",
        "priority": 2,
        "arrived_at": "2017-09-20T11:37:38+04:30",
        "handled_at": "2017-09-20T11:39:38+04:30",
        "id": 568550,
        "city": "tehran",
        "order_id": "309",
        "customer_id": "99",
        "courier_id": "130",
        "status": "handled",
        "address": "address of order s origin",
        "description": "some description for origin",
        "unit": "unit of origin address",
        "number": "number of origin address",
        "person_fullname": "sender s name",
        "person_phone": "sender s phone",
        "signed_by": "",
        "distance": "20192",
        "duration": "2492",
        "created_at": "2017-09-20T11:32:34+04:30",
        "updated_at": "2017-09-20T11:39:38+04:30",
        "deleted_at": "",
        "arrive_lat": "35.76024827733142",
        "arrive_lng": "51.3950500507545",
        "handle_lat": "35.75058415169185",
        "handle_lng": "51.40547057923416",
        "signature": {
          "url": "/uploads/order/309/address/568550/signature.jpg?var=1506000110"
        }
      }
    ],
    "screenshot": {
        "url": "https:screenshots.alopeyk.com/?size=640x330&maptype=roadmap&language=fa&markers=icon:https:api.alopeyk.com/images/marker-origin.png%7C35.75546,51.416874&markers=icon:https:api.alopeyk.com/images/marker-destination.png%7C35.758495,51.44255&markers=icon:https:api.alopeyk.com/images/marker-destination.png%7C35.895452,51.589632"
    },
    "progress": "1.0000",
    "courier": {
        "id": 130,
      "phone": "09499359023",
      "firstname": "محمد رضا",
      "lastname": "نورشی",
      "email": "",
      "avatar": {
            "url": "/uploads/user/130/avatar.jpg?var=1506000110"
      },
      "last_online": null,
      "is_online": null
    },
    "customer": {
        "id": 99,
      "phone": "09195071197",
      "firstname": "mohammad hassan",
      "lastname": "daneshvar",
      "email": "daneshvar.email@gmail.com",
      "avatar": {
            "url": "/uploads/user/99/avatar.jpg?var=1506000110"
      },
      "last_online": null,
      "is_online": null
    },
    "last_position_minimal": null,
    "eta_minimal": {
        "id": 46,
      "last_position_id": 55,
      "duration": 0,
      "distance": 0,
      "action": "handle",
      "address_id": "568550",
      "updated_at": "2017-09-20 11:36:39"
    },
    "signature": {
        "url": "/uploads/order/309/address/568550/signature.jpg?var=1506000110"
    },
    "order_token": "69b9e3f08309g2b19e2458fa429g99734670660",
    "nprice": null,
    "subsidy": null,
    "signed_by": ""
  }
}
```


#### 7. Cancel Order

You can cancel any order before courier arrival (before the accepted status)

```PHP
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Model\Order;

$apiResponse = null;
try {
    // $orderID = "   300 ";     // works fine as 300
    // $orderID = "   300<p>";   // works fine as 300
    // $orderID = '';            // throws AloPeykException
    // $orderID = null;          // throws AloPeykException
    $orderID = 300;
    $apiResponse = Order::cancel($orderID);
} catch (AloPeykApiException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($apiResponse);

// SAMPLE API RESPONSE: ------------------------------------------------------------------------------------------------
{
  "status": "success",
  "message": null,
  "object": {
    "id": 300,
    "status": "cancelled",
    "courier_id": 121,
    "customer_id": 99,
    "signature": {
        "url": "/uploads/order/300/signature.jpg?var=1505807816"
    },
    "order_token": null,
    "nprice": null,
    "subsidy": null,
    "signed_by": ""
  }
}
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
