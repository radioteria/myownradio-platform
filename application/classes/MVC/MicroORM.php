<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 9:31
 */

namespace MVC;


use Model\Beans\BeanObject;
use MVC\Exceptions\ORMException;
use MVC\Services\Database;
use Tools\Singleton;

/**
 * Class MicroORM
 * @package MVC
 */
class MicroORM {

    use Singleton;

    /**
     * @param string $bean
     * @param int $id
     * @return object
     */
    public function fetchObject($bean, $id) {

        $reflection = new \ReflectionClass($bean);

        $beanComment = $reflection->getDocComment();
        $beanConfig = $this->getBeanConfig($beanComment);

        return $this->_loadObject($reflection, $bean, $beanConfig, $id);

    }

    /**
     * @param BeanObject $bean
     * @return mixed
     */
    public function saveObject(BeanObject $bean) {

        $reflection = new \ReflectionClass($bean);

        $beanComment = $reflection->getDocComment();
        $beanConfig = $this->getBeanConfig($beanComment);

        return Database::doInConnection(function (Database $db) use ($reflection, $beanConfig, $bean) {

            $keyProp = $reflection->getProperty($beanConfig["@key"]);
            $keyProp->setAccessible(true);

            $key = $keyProp->getValue($bean);

            if (is_null($key)) {

                $query = $db->getDBQuery()->insertInto($beanConfig["@table"]);

                foreach ($reflection->getProperties() as $prop) {
                    if ($prop->getName() == $beanConfig["@key"])
                        continue;
                    $prop->setAccessible(true);
                    $query->values($prop->getName(), $prop->getValue($bean));
                }

                $result = $db->executeInsert($query);

            } else {

                $query = $db->getDBQuery()->updateTable($beanConfig["@table"]);

                foreach ($reflection->getProperties() as $prop) {

                    $prop->setAccessible(true);

                    if ($prop->getName() == $beanConfig["@key"]) {

                        $query->where($prop->getName(), $prop->getValue($bean));

                    } else {

                        $query->set($prop->getName(), $prop->getValue($bean));

                    }

                }

                $result = $db->executeUpdate($query);

            }

            $db->commit();

            return $result;

        });

    }

    /**
     * @param \ReflectionClass $reflection
     * @param string $bean
     * @param array $config
     * @param int $id
     * @return object $bean
     */
    private function _loadObject($reflection, $bean, $config, $id) {

        $object = Database::doInConnection(function (Database $db) use ($reflection, $bean, $config, $id) {
            $query = $db->getDBQuery()->selectFrom($config["@table"])
                ->select("*")->where($config["@key"], $id);

            $row = $db->fetchOneRow($query)
                ->getOrElseThrow(
                    new ORMException(sprintf("No object '%s' with key '%s' exists", $config["@table"], $id))
                );

            $instance = $reflection->newInstance();

            foreach ($reflection->getProperties() as $prop) {
                $prop->setAccessible(true);
                $prop->setValue($instance, $row[$prop->getName()]);
            }

            return $instance;

        });

        return $object;

    }

    /**
     * @param string $comments
     * @return array
     */
    private function parseDocComments($comments) {
        $parameters = [];
        preg_match_all("~(\\@\\w+)\\s+(\\w+)~m", $comments, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $parameters[$match[1]] = $match[2];
        }
        return $parameters;
    }

    /**
     * @param string $beanComment
     * @return array
     * @throws Exceptions\ORMException
     */
    private function getBeanConfig($beanComment) {

        $beanConfig = $this->parseDocComments($beanComment);

        if (empty($beanConfig["@table"])) {
            throw new ORMException("No 'table' comment present");
        }

        if (empty($beanConfig["@key"])) {
            throw new ORMException("No 'key' comment present");
        }

        return $beanConfig;

    }
} 