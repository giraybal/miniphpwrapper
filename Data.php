<?php

/**
 * Created by IntelliJ IDEA.
 * User: Giray BAL (giraybal@gmail.com)
 * Date: 02.12.2016
 * Time: 14:49
 *
 * Last edit: Date: 02.12.2016
 */
class Data {
    public static $DEBUG_MODE = false;

    const DISPLAY_ERRORS = true;
    const LOG_ERRORS = true;
//    const DEFAULT_LANGUAGE_ID = 0;
    const PASSWORD_HASH = '123456789';

    // Daos
    static $DAOS = array(
        'Test.php',
    );

    // informations
    const DATABASE_HOST = '';
    const DATABASE_NAME = '';
    const DATABASE_USERNAME = '';
    const DATABASE_PASSWORD = '';
    const URL_ROOT = 'https://www.google.com/';

    // informations_debug
    const DATABASE_HOST_DEBUG = 'localhost';
    const DATABASE_NAME_DEBUG = '';
    const DATABASE_USERNAME_DEBUG = 'root';
    const DATABASE_PASSWORD_DEBUG = '';
    const URL_ROOT_DEBUG = 'http://localhost/';

    const ROW_LIMIT_DEFAULT = 20;
    const ROW_LIMIT_MAX = 250;
}