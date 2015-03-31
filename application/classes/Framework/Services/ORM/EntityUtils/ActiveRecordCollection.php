<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 21.12.14
 * Time: 17:40
 */

namespace Framework\Services\ORM\EntityUtils;


use Framework\Services\ORM\Core\MicroORM;

class ActiveRecordCollection implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable {

    private $collection = [];
    private $objectName = null;
    private $position = 0;

    function __construct($objectName) {
        $this->objectName = $objectName;
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
            ->getObjectByData($this->objectName, $this->collection[$offset]);
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

    public function current() {
        return $this->offsetGet($this->position);
    }

    public function next() {
        $this->position++;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->collection[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }

    public function jsonSerialize() {
        $data = [];
        foreach ($this as $item) {
            $data[] = $item->jsonSerialize();
        }
        return $data;
    }

}