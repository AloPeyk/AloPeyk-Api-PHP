<?php

namespace AloPeyk\Model;

use AloPeyk\Config\Configs;
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Validator\AloPeykValidator;

class Order
{
    // Attributes ------------------------------------------------------------------------------------------------------

    private $transportType;
    private $city;
    private $originAddress;
    private $destinationsAddress;
    private $hasReturn;
    private $cashed;

    public function __construct($transportType, $originAddress, $destinationsAddress)
    {
        $this->setTransportType($transportType);
        $this->addOriginAddress($originAddress);
        $this->setHasReturn(false);
        $this->setCashed(false);

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
        if (!in_array($transportType, array_values(Configs::TRANSPORT_TYPES))) {
            throw new AloPeykApiException('Transport Type is not correct');
        }

        $this->transportType = $transportType;
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
        $this->city = $originAddress->getCity();
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

    // Utilities -------------------------------------------------------------------------------------------------------
    public function toArray($endPoint)
    {
        $this->isValid();

        $orderArray = [
            'city' => $this->city,
            'transport_type' => $this->getTransportType(),
            'addresses' => [$this->getOriginAddress()->toArray($endPoint)],
            'has_return' => $this->getHasReturn(),
            'cashed' => $this->getCashed(),
        ];

        // add destinations
        foreach ($this->getDestinationsAddress() as $address) {
            array_push($orderArray['addresses'], $address->toArray($endPoint));
        }

        return $orderArray;
    }

    /**
     * @return bool
     * @throws AloPeykApiException
     */
    private function isValid()
    {
        // CHECK CITY
        if (AloPeykValidator::sanitize($this->city) != $this->getOriginAddress()->getCity()) {
            throw new AloPeykApiException('Origin Address is not valid!');
        }

        // CHECK TRANSPORT_TYPE
        if (!in_array($this->getTransportType(), array_values(Configs::TRANSPORT_TYPES))) {
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