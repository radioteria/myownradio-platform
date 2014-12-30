<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 9:31
 */

namespace Framework\Services\ORM\Core;


use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\Injectable;
use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordCollection;
use Framework\Services\ORM\Exceptions\ORMException;
use Tools\Optional;
use Tools\Singleton;

/**
 * Class MicroORM
 * @package MVC
 */
class MicroORM extends FilterORM implements Injectable {

    use Singleton;

    private $ORMCache = [];

    /**
     * @param ActiveRecord $object
     * @internal param null $reflection
     * @return ActiveRecord
     */
    public function cloneObject(ActiveRecord $object) {

        $reflection = new \ReflectionClass($object);
        $beanConfig = $this->getBeanConfig($reflection);

        /** @var \ReflectionClass $copy */
        $copy = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getProperties() as $prop) {
            /** Don't clone primary key value */
            if ($prop->getName() == $beanConfig["@key"]) {
                continue;
            }
            $prop->setAccessible(true);
            $value = $prop->getValue($object);
            $prop->setValue($copy, $value);
        }

        return $copy;

    }

    /**
     * @param string $bean
     * @param array $data
     * @return object
     */
    public function getObjectByData($bean, array $data) {

        $reflection = new \ReflectionClass($bean);

        $instance = $reflection->newInstance();

        /** @var \ReflectionProperty $prop */
        foreach ($reflection->getProperties() as $prop) {
            $prop->setAccessible(true);
            $prop->setValue($instance, @$data[$prop->getName()]);
        }

        return $instance;

    }

    /**
     * @param string $bean
     * @param int $id
     * @return Optional
     */
    public function getObjectByID($bean, $id) {

        $reflection = new \ReflectionClass($bean);

        $beanConfig = $this->getBeanConfig($reflection);

        return $this->_loadObject($reflection, $beanConfig, $id);

    }

    /**
     * @param string $bean
     * @param string $filter
     * @param array $args
     * @return Optional
     */
    public function getObjectByFilter($bean, $filter, array $args = null) {

        $reflection = new \ReflectionClass($bean);
        $beanConfig = $this->getBeanConfig($reflection);
        return $this->_getObjectByFilter($reflection, $beanConfig, $filter, $args);

    }

    /**
     * @param ActiveRecord $object
     * @throws ORMException
     */
    public function deleteObject(ActiveRecord $object) {

        $reflection = new \ReflectionClass($object);

        $beanConfig = $this->getBeanConfig($reflection);

        if (isset($beanConfig["@view"])) {
            throw new ORMException("Object has read only access");
        }

        $param = $reflection->getProperty($beanConfig["@key"]);
        $param->setAccessible(true);

        $id = $param->getValue($object);

        if (!is_null($id)) {
            $this->_deleteObject($beanConfig, $id);
        }

        $param->setValue($object, null);

    }

    /**
     * @param string $bean
     * @param int|null $limit
     * @param int|null $offset
     * @return Object[]
     */
    public function getListOfObjects($bean, $limit = null, $offset = null) {

        $reflection = new \ReflectionClass($bean);

        $config = $this->getBeanConfig($reflection);

        return $this->_loadObjects($reflection, $config, null, null, $limit, $offset);

    }

    /**
     * @param string $bean
     * @param string $filter
     * @param array|null $filterArgs
     * @param int|null $limit
     * @param int|null $offset
     * @return object
     * @throws ORMException
     */
    public function getFilteredListOfObjects($bean, $filter, array $filterArgs = null, $limit = null, $offset = null) {

        $reflection = new \ReflectionClass($bean);

        $beanConfig = $this->getBeanConfig($reflection);

        return $this->_loadObjects($reflection, $beanConfig, $filter, $filterArgs, $limit, $offset);

    }

    /**
     * @param ActiveRecord $bean
     * @return mixed
     * @throws ORMException
     */
    public function saveObject(ActiveRecord $bean) {

        $reflection = new \ReflectionClass($bean);

        $beanConfig = $this->getBeanConfig($reflection);
        $dbq = DBQuery::getInstance();

        if (isset($beanConfig["@view"])) {
            throw new ORMException("Object has read only access");
        }


        $keyProp = $reflection->getProperty($beanConfig["@key"]);
        $keyProp->setAccessible(true);

        $key = $keyProp->getValue($bean);

        if (is_null($key)) {

            $query = $dbq->into($beanConfig["@table"]);

            foreach ($reflection->getProperties() as $prop) {
                if ($prop->getName() == $beanConfig["@key"])
                    continue;
                $prop->setAccessible(true);
                $query->values($prop->getName(), $prop->getValue($bean));
            }

            $result = $query->executeInsert();

            $keyProp->setValue($bean, $result);

        } else {

            $query = $dbq->updateTable($beanConfig["@table"]);

            foreach ($reflection->getProperties() as $prop) {

                $prop->setAccessible(true);

                if ($prop->getName() == $beanConfig["@key"]) {

                    $query->where($prop->getName(), $prop->getValue($bean));

                } else {

                    $query->set($prop->getName(), $prop->getValue($bean));

                }

            }

            $query->executeUpdate();

        }

    }

    /**
     * @param \ReflectionClass $reflection
     * @param array $config
     * @param int $id
     * @return Optional
     */
    private function _loadObject($reflection, array $config, $id) {

        $query = $this->createBaseSelectRequest($config);

        $this->applyKey($query, $config, $id);
        $this->applyInnerJoin($query, $config);

        return $this->_getSingleObject($query, $reflection);

    }

    /**
     * @param \ReflectionClass $reflection
     * @param array $config
     * @param $filter
     * @param array $args
     * @return Optional
     */
    private function _getObjectByFilter($reflection, array $config, $filter, array $args = null) {

        $query = $this->createBaseSelectRequest($config);
        $this->applyInnerJoin($query, $config);
        $this->applyFilter($query, $filter, $config, $args);

        return $this->_getSingleObject($query, $reflection);

    }

    /**
     * @param $config
     * @param $id
     * @internal param \ReflectionClass $reflection
     */
    private function _deleteObject($config, $id) {

        $dbq = DBQuery::getInstance();

        $query = $dbq->deleteFrom($config["@table"]);
        $query->where($config["@key"], $id);

        $query->executeUpdate();

    }

    /**
     * @param \ReflectionClass $reflection
     * @param $config
     * @param string|null $filter
     * @param array|null $filterArgs
     * @param int|null $limit
     * @param int|null $offset
     * @return mixed
     */
    private function _loadObjects($reflection, $config, $filter = null, $filterArgs = null, $limit = null,
                                  $offset = null) {

        $query = $this->createBaseSelectRequest($config);

        //$this->applyInnerJoin($query, $config);

        if (is_string(($filter))) {
            $this->applyFilter($query, $filter, $config, $filterArgs);
        }

        return $this->_getListOfObjects($query, $reflection, $config, $limit, $offset);

    }

    /**
     * @param SelectQuery $query
     * @param \ReflectionClass $reflection
     * @return Optional
     */
    protected function _getSingleObject(SelectQuery $query, \ReflectionClass $reflection) {

        $query->limit(1);

        $row = $query->fetchOneRow($query)
            ->getOrElseNull();

        if ($row === null) {
            return Optional::noValue();
        }

        $instance = $reflection->newInstance();

        foreach ($reflection->getProperties() as $prop) {
            $prop->setAccessible(true);
            $prop->setValue($instance, @$row[$prop->getName()]);
        }

        return Optional::hasValue($instance);

    }

    /**
     * @param SelectQuery $query
     * @param \ReflectionClass $reflection
     * @param array $config
     * @param null|int $limit
     * @param null|int $offset
     * @return ActiveRecord[]
     */
    protected function _getListOfObjects(SelectQuery $query, \ReflectionClass $reflection, array $config, $limit = null, $offset = null) {

        $array = new ActiveRecordCollection($reflection->getName());

        if (is_numeric($limit)) {
            $query->limit($limit);
        }

        if (is_numeric($offset)) {
            $query->offset($offset);
        }

        $query->eachRow(function ($row) use (&$array, &$config) {

            $array[] = $row;

        });

        return $array;

    }

    /**
     * @param \ReflectionClass $reflection
     * @return array
     */
    private function parseDocComments(\ReflectionClass $reflection) {
        $parameters = [];
        preg_match_all("~(\\@\\w+)\\s+(.+)~m", $reflection, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $parameters[$match[1]] = trim($match[2]);
        }
        return $parameters;
    }

    /**
     * @param \ReflectionClass $beanComment
     * @throws ORMException
     * @return array
     */
    private function getBeanConfig($beanComment) {

        $beanConfig = $this->parseDocComments($beanComment);

        if (empty($beanConfig["@table"])) {
            throw new ORMException("No comment '@table' present");
        }

        if (empty($beanConfig["@key"])) {
            throw new ORMException("No comment '@key' present");
        }

        return $beanConfig;

    }

    private function saveCache(ActiveRecord $object, $key, array $data = []) {
        $className = (new \ReflectionClass($object))->getName();
        $this->ORMCache[$className][$key] = $data;
    }
} 