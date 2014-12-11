<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.12.14
 * Time: 22:06
 */

class Container {
    private $values;

    public function __construct() {
        $this->values = func_get_args();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get() {
        foreach ($this->values as $value) {
            if ($value instanceof Exception) {
                throw $value;
            }
            if (!empty($value)) {
                return $value;
            }
        }
        throw new Exception("No values in container");
    }

    /**
     * @return self
     */
    public static function of() {
        $reflector = new ReflectionClass(get_called_class());
        $arguments = func_get_args();
        return call_user_func_array(array($reflector, "newInstance"), $arguments);
    }

} 