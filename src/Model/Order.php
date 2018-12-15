<?php

namespace AloPeyk\Model;

use AloPeyk\AloPeykApiHandler;
use AloPeyk\Config\Configs;
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Validator\AloPeykValidator;

class Order
{
    // Attributes ------------------------------------------------------------------------------------------------------

    private $transportType;
    private $originAddress;
    private $destinationsAddress;
    private $hasReturn;
    private $cashed;
    private $scheduled_at;
    private $discount_coupon;

    public function __construct($transportType, $originAddress, $destinationsAddress, $scheduled_at = null, $discount_coupon=null)
    {
        $this->setTransportType($transportType);
        $this->addOriginAddress($originAddress);
        $this->setHasReturn(false);
        $this->setCashed(false);

        if($scheduled_at)
        {
            $this->setScheduledAt($scheduled_at);
        }

        if($discount_coupon)
        {
            $this->setDiscountCoupon($discount_coupon);
        }

        $this->destinationsAddress = [];
        if (!is_array($destinationsAddress)) {
            $this->addDestinationsAddress($destinationsAddress);
        } else {
            foreach ($destinationsAddress as $destAddress) {
                $this->addDestinationsAddress($destAddress);
            }
        }
    }

    // Setters ---------------------------------------------------------------------------------------------------------

    /**
     * @param $transportType
     * @throws AloPeykApiException
     */
    public function setTransportType($transportType)
    {
        $transportType = AloPeykValidator::sanitize($transportType);
        if (!in_array($transportType, array_keys(Configs::TRANSPORT_TYPES))) {
            throw new AloPeykApiException('Transport Type is not correct');
        }

        $this->transportType = $transportType;
    }

    /**
     * Set scheduled_at attribute
     * @param $scheduledAt
     */
    public function setScheduledAt($scheduled_at)
    {
        $this->scheduled_at = $scheduled_at;
    }

    /**
     * Set discount_coupon attribute
     * @param $discountCoupon
     */
    public function setDiscountCoupon($discount_coupon)
    {
        $this->discount_coupon = $discount_coupon;
    }

    /**
     * @param $originAddress
     * @throws AloPeykApiException
     */
    public function addOriginAddress($originAddress)
    {
        if (!$originAddress instanceof Address) {
            throw new AloPeykApiException('Origin Address is not valid!');
        }

        if ($originAddress->getType() != 'origin') {
            throw new AloPeykApiException('Type Of Origin Address is not correct! please change it to `origin`.');
        }

        $this->originAddress = $originAddress;
    }

    /**
     * @param $newDestinationsAddress
     * @throws AloPeykApiException
     */
    public function addDestinationsAddress($newDestinationsAddress)
    {
        if (!$newDestinationsAddress instanceof Address) {
            throw new AloPeykApiException('Destination Address is not valid!');
        }

        if ($newDestinationsAddress->getType() != 'destination') {
            throw new AloPeykApiException('Type Of Destination Address is not correct! please change it to `destination`.');
        }

        array_push($this->destinationsAddress, $newDestinationsAddress);
    }

    /**
     * @param mixed $hasReturn
     */
    public function setHasReturn($hasReturn)
    {
        $this->hasReturn = $hasReturn;
    }

    /**
     * @param mixed $cashed
     */
    public function setCashed($cashed)
    {
        $this->cashed = $cashed;
    }

    // Getters ---------------------------------------------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function getTransportType()
    {
        return $this->transportType;
    }

    /**
     * @return mixed
     */
    public function getOriginAddress()
    {
        return $this->originAddress;
    }

    /**
     * @return mixed
     */
    public function getDestinationsAddress()
    {
        return $this->destinationsAddress;
    }

    /**
     * @return mixed
     */
    public function getDestinationsAddressArray()
    {
        $addresses = [];
        foreach ($this->getDestinationsAddress() as $address) {
            array_push($addresses, $address->toArray('destination'));
        }
        return $addresses;
    }

    /**
     * @return mixed
     */
    public function getHasReturn()
    {
        return $this->hasReturn;
    }

    /**
     * @return mixed
     */
    public function getCashed()
    {
        return $this->cashed;
    }

    // Actions ---------------------------------------------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function create()
    {
        return AloPeykApiHandler::createOrder($this);
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return AloPeykApiHandler::getPrice($this);
    }

    public function getScheduledAt()
    {
        return $this->scheduled_at;
    }

    public function getDiscountCoupon()
    {
        return $this->discount_coupon;
    }

    /**
     * @param $orderID
     * @return mixed
     */
    public static function cancel($orderID, $comment)
    {
        return AloPeykApiHandler::cancelOrder($orderID, $comment);
    }

    /**
     * @param $orderID
     * @return mixed
     */
    public static function finish($orderID, $params)
    {
        return AloPeykApiHandler::finishOrder($orderID, $params);
    }

    /**
     * @param $orderID
     * @return mixed
     */
    public static function getDetails($orderID)
    {
        return AloPeykApiHandler::getOrderDetail($orderID);
    }

    // Utilities -------------------------------------------------------------------------------------------------------

    /**
     * @param $endPoint
     * @return array
     */
    public function toArray($endPoint)
    {
        $this->isValid();

        $orderArray = [
            'transport_type' => $this->getTransportType(),
            'has_return' => $this->getHasReturn(),
            'cashed' => $this->getCashed(),
            'scheduled_at' => $this->getScheduledAt(),
            'discount_coupon' => $this->getDiscountCoupon()
        ];

        $orderArray['addresses'] = array_merge(
            [$this->getOriginAddress()->toArray($endPoint)],
            $this->getDestinationsAddressArray()
        );

        return $orderArray;
    }

    /**
     * @return bool
     * @throws AloPeykApiException
     */
    private function isValid()
    {

        // CHECK TRANSPORT_TYPE
        if (!in_array($this->getTransportType(), array_keys(Configs::TRANSPORT_TYPES))) {
            throw new AloPeykApiException('Transport Type is not correct!');
        }

        // CHECK ORIGIN
        if (!$this->getOriginAddress()) {
            throw new AloPeykApiException('Each Order Requires One Origin Address!');
        }
        if ($this->getOriginAddress()->getType() != 'origin') {
            throw new AloPeykApiException('Type Of Origin Address is not correct! please change it to `origin`.');
        }

        // CHECK DESTINATIONS
        if (count($this->getDestinationsAddress()) < 1) {
            throw new AloPeykApiException('Each Order Requires At Least One Destination!');
        }
        foreach ($this->getDestinationsAddress() as $destination) {
            if ($destination->getType() != 'destination') {
                throw new AloPeykApiException('Type of Destination Address is not correct! please change it to `destination`.');
            }
        }

        return true;
    }

}