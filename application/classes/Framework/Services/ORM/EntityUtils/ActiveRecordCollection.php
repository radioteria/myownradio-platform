<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 21.12.14
 * Time: 17:40
 */

namespace Framework\Services\ORM\EntityUtils;


use Framework\Services\ORM\Core\MicroORM;

class ActiveRecordCollection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    private $collection = [];
    private $object = null;

    function __construct($objectName) {
        $this->object = $objectName;
    }

    public function addMany(array $objects) {
        foreach ($objects as $object) {
            $this->offsetSet(null, $object);
        }
    }

    public function offsetExists($offset) {
        return isset($collection[$offset]);
    }

    public function offsetGet($offset) {
        return MicroORM::getInstance()
            ->getObjectByData($this->object, $this->collection[$offset]);
    }

    public function offsetSet($offset, $value) {
        $this->collection[is_null($offset) ? count($this->collection) : $offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->collection[$offset]);
    }

    public function count() {
        return count($this->collection);
    }

    public function getIterator() {
        $orm = MicroORM::getInstance();
        foreach ($this->collection as $item) {
            yield $orm->getObjectByData($this->object, $item);
        }
    }

    public function jsonSerialize() {
        $data = [];
        foreach ($this as $item) {
            $data[] = $item->jsonSerialize();
        }
        return $data;
    }

    /**
     * @param $className
     * @return \Generator
     */
    public function cast($className) {
        foreach ($this->collection as $item) {
            yield new $className($item);
        }
    }

    /**
     * @return \Generator
     */
    public function getKeys() {
        $orm = MicroORM::getInstance();
        foreach ($this->collection as $item) {
            yield $orm->getObjectByData($this->object, $item)->getKey();
        }
    }

}