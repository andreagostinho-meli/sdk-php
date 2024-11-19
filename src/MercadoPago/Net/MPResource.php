<?php

namespace MercadoPago\Net;

/** MPResource class. */
class MPResource
{
    private MPResponse $response;

    public function setResponse(MPResponse $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): MPResponse
    {
        return $this->response;
    }

    /**
     * Method responsible for deserializing JSON data into the object properties.
     * @param array $data Data to be deserialized.
     * @return void
     */
    public function jsonDeserialize(array $data)
    {
        if (method_exists($this, 'getMap')) {
            // O GETMAP  ainda não está funcionando, será esse o problema????
            foreach ($this->getMap() as $property => $class) {
                if (isset($data[$property])) {
                    if (class_exists($class)) {
                        $object = new $class();
                        if (method_exists($object, 'jsonDeserialize')) {
                            $object->jsonDeserialize($data[$property]);
                        }
                        $this->{$property} = $object;
                    } else {
                        $this->{$property} = $data[$property];
                    }
                }
            }
        }
    }
}
