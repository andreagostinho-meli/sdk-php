<?php

namespace MercadoPago\Serialization;

/**
 * Mapper trait.
 */
trait Mapper
{
    /**
     * Method responsible for returning the mapped class for entity filled.
     * @param string $field field to be mapped.
     * @return mixed mapped class.
     */
    public function map(string $field)
    {
        $map = $this->getMap();
        return isset($map[$field]) ? $map[$field] : null;
    }

    /**
     * Method responsible for deserializing JSON data into the object properties.
     * @param array $data Data to be deserialized.
     * @return void
     */
    public function jsonDeserialize(array $data)
    {
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

    /**
     * Method responsible for getting map of entities.
     * @return array map of entities.
     */
    abstract public function getMap(): array;
}
