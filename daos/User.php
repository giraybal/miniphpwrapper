<?php

/**
 * Created by IntelliJ IDEA.
 * User: Giray BAL (giraybal@gmail.com)
 * Date: 02.12.2016
 * Time: 14:49
 *
 * Last edit: Date: 02.12.2016
 */
class User extends \MPW\DaoProto {

    public $id, $dateRegister, $name, $surname, $email, $password, $country;

    public function __construct($id, $dateRegister, $name, $surname, $email, $password, $country) {
        $this->id = $id;
        $this->dateRegister = $dateRegister;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->password = $password;
        $this->country = $country;
    }

    public static function toObject($dataArr, $class = null) {
        return parent::toObject(static::class, $dataArr);
    }

    /**
     * @param $result
     * @param null $class
     * @return bool|null|User
     */
    public static function toObjectFromPDO($result, $class = null) {
        return parent::toObjectFromPDO(static::class, $result);
    }

    /**
     * @param $result
     * @param null $class
     * @return User[]|null
     */
    public static function toObjectArrFromPDO($result, $class = null) {
        return parent::toObjectArrFromPDO(static::class, $result);
    }

    //SELECT
    public static function getCount(\MPW\DB $DB) {
        $result = $DB->query("SELECT COUNT(*) AS 'count' FROM `user`");
        if ($result === null) return null;
        return $result[1][0]['count'];
    }

    public static function getAll(\MPW\DB $DB) {
        return self::toObjectArrFromPDO($DB->query('SELECT * FROM `user` ORDER BY `id` DESC'));
    }

    public static function getAllByLimit(\MPW\DB $db, $offset, $limit) {
        return self::toObjectArrFromPDO($db->query('SELECT * FROM `user` ORDER BY `id` DESC LIMIT ' . $offset . ', ' . $limit));
    }

    public static function getById(\MPW\DB $DB, $id) {
        return self::toObjectFromPDO($DB->query('SELECT * FROM `user` WHERE `id` = :id LIMIT 1', array(
            ':id' => $id
        )));
    }

    public static function getByEmail(\MPW\DB $DB, $email) {
        return self::toObjectFromPDO($DB->query('SELECT * FROM `user` WHERE `email` = :email LIMIT 1', array(
            ':email' => $email
        )));
    }

    //INSERT
    public static function insert(\MPW\DB $DB, $dateRegister, $name, $surname, $email, $password, $country) {
        $result = $DB->query('INSERT INTO `user` (`date_register`, `name`, `surname`, `email`, `password`, `country`) VALUES (:dateRegister, :name, :surname, :email, :password, :country)', array(
            ':dateRegister' => $dateRegister,
            ':name' => $name,
            ':surname' => $surname,
            ':email' => $email,
            ':password' => $password,
            ':country' => $country
        ));
        if ($result === null) return null;
        return $result[0] > 0;
    }

    //Util
    public function checkPassword($password) {
        return $this->password == md5(Data::PASSWORD_HASH . $password);
    }
}