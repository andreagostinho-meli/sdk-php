<?php

/** API version: 5d077b6f-61b2-4b3a-8333-7a64ee547448 */

namespace MercadoPago\Resources\Order;

use MercadoPago\Resources\Common\Identification;
use MercadoPago\Serialization\Mapper;

/** Payer class. */
class Payer
{
    /** Class mapper. */
    use Mapper;

    /** Email. */
    public ?string $email;

    /** First name. */
    public ?string $first_name;

    /** Last name. */
    public ?string $last_name;

    /** Identification. */
    public ?Identification $identification;

    /** Phone. */
    public ?Phone $phone;

    /** Address. */
    public ?Address $address;

    private $map = [
        "identification" => "MercadoPago\Resources\Common\Identification",
        "phone" => "MercadoPago\Resources\Order\Phone",
        "address" => "MercadoPago\Resources\Order\Address",
    ];

    /**
     * Method responsible for getting map of entities.
     */
    public function getMap(): array
    {
        return $this->map;
    }

    public function jsonDeserialize(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value; // Atribui diretamente os valores
            }
        }
    }
}
