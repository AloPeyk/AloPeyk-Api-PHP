<?php

namespace AloPeyk\Model;

use AloPeyk\Config\Configs;
use AloPeyk\Exception\AloPeykApiException;
use AloPeyk\Validator\AloPeykValidator;

class Address
{
    // Attributes ------------------------------------------------------------------------------------------------------

    private $type;
    private $latitude;
    private $longitude;
    private $description;       //some description for origin'
    private $unit;              //unit of origin address
    private $number;            //number of origin address
    private $person_fullname;   //sender s name
    private $person_phone;      //sender s phone

    /**
     * Address constructor.
     * @param $type
     * @param $latitude
     * @param $longitude
     */
    public function __construct($type, $latitude, $longitude)
    {
        $this->setType($type);
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
    }

    // Setters ---------------------------------------------------------------------------------------------------------

    /**
     * @param $type
     * @throws AloPeykApiException
     */
    public function setType($type)
    {
        $type = AloPeykValidator::sanitize($type);
        if (!in_array($type, array_values(Configs::ADDRESS_TYPES))) {
            throw new AloPeykApiException('Type Of Address is not correct');
        }

        $this->type = $type;
    }

    /**
     * @param $latitude
     * @throws AloPeykApiException
     */
    public function setLatitude($latitude)
    {
        if (!AloPeykValidator::validateLatitude($latitude)) {
            throw new AloPeykApiException('Latitude is not correct!');
        }

        $this->latitude = $latitude;
    }

    /**
     * @param $longitude
     * @throws AloPeykApiException
     */
    public function setLongitude($longitude)
    {
        if (!AloPeykValidator::validateLongitude($longitude)) {
            throw new AloPeykApiException('Longitude is not correct!');
        }

        $this->longitude = $longitude;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = AloPeykValidator::sanitize($description);
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = AloPeykValidator::sanitize($unit);
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = AloPeykValidator::sanitize($number);
    }

    /**
     * @param mixed $person_fullname
     */
    public function setPersonFullname($person_fullname)
    {
        $this->person_fullname = AloPeykValidator::sanitize($person_fullname);
    }

    /**
     * @param mixed $person_phone
     */
    public function setPersonPhone($person_phone)
    {
        $this->person_phone = AloPeykValidator::sanitize($person_phone);
    }

    // Getters ---------------------------------------------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return mixed
     */
    public function getPersonFullname()
    {
        return $this->person_fullname;
    }

    /**
     * @return mixed
     */
    public function getPersonPhone()
    {
        return $this->person_phone;
    }


    // Actions ---------------------------------------------------------------------------------------------------------



    // Utilities -------------------------------------------------------------------------------------------------------

    /**
     * @param $endPoint
     * @return array
     */
    public function toArray($endPoint)
    {
        $this->isValid($endPoint);

        return [
            'type' => $this->getType(),
            'lat' => $this->getLatitude(),
            'lng' => $this->getLongitude(),
            'description' => $this->getDescription(),
            'unit' => $this->getUnit(),
            'number' => $this->getNumber(),
            'person_fullname' => $this->getPersonFullname(),
            'person_phone' => $this->getPersonPhone(),
        ];
    }

    /**
     * @param $endPoint
     * @return bool
     * @throws AloPeykApiException
     */
    private function isValid($endPoint)
    {
        if ($endPoint == "getPrice") {
            if (!$this->getType() || !$this->getLongitude() || !$this->getLatitude()) {
                throw new AloPeykApiException('Fill Out This Attributes in all destinations: type , latitude , longitude');
            }
        }

        return true;
    }


}
