<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:03
 */

namespace Model\Traits;


use Model\User;
use MVC\Exceptions\ControllerException;
use ReflectionClass;

trait Bean {

    public function reload() {

        $userId = User::getInstance()->getId();

        $object = $this->db->fetchOneRow("SELECT * FROM r_tracks WHERE tid = ?", [$this->key])
            ->getOrElseThrow(ControllerException::noEntity($this->key));

        if (intval($object["uid"]) !== $userId) {
            throw ControllerException::noPermission();
        }

        try {
            $reflection = new ReflectionClass($this);
            foreach ($this->bean_fields as $field) {
                $prop = $reflection->getProperty($field);
                $prop->setAccessible(true);
                $prop->setValue($this, $object[$field]);
            }
        } catch (\ReflectionException $exception) {
            throw new ControllerException($exception->getMessage());
        }

        return $this;

    }

    public function save() {

        $fluent = $this->db->getFluentPDO();
        $query = $fluent->update("r_tracks");

        try {

            $reflection = new ReflectionClass($this);

            $keyProperty = $reflection->getProperty($this->bean_key);
            $keyProperty->setAccessible(true);
            $query->where($this->bean_key, $keyProperty->getValue($this));

            foreach ($this->bean_update as $field) {
                $property = $reflection->getProperty($field);
                $property->setAccessible(true);
                $query->set($property->getName(), $property->getValue($this));
            }

            $this->db->executeUpdate($query->getQuery(), $query->getParameters());

        } catch (\ReflectionException $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

} 