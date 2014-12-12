<?php

class radioVerifier extends Model {

    public function checkUserEmailExists($email) {
        return (bool) $this->database->fetchOneColumn("SELECT COUNT(*) FROM r_users WHERE mail = ?", array($email))
            ->getOrElseThrow(new morException("Unknown database exception (checkUserEmailExists)"));
    }

    /**
     * @param string $login
     * @param string $password
     * @return Optional
     */
    public function checkUserLogin($login, $password) {

        $query = $this->database->getFluentPDO()->from("r_users");
        $query->select(null)->select("uid");
        $query->where("login", $login);
        $query->where("password", md5($login . $password));
        $query->limit(1);

        return $this->database->fetchOneColumn($query->getQuery(false), $query->getParameters());

    }

}
