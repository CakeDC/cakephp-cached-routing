<?php
declare(strict_types=1);

/**
 * Copyright 2013 - 2022, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2013 - 2022, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$findRoot = function () {
    $root = dirname(__DIR__);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(__DIR__, 2);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(__DIR__, 3);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    return null;
};

function def($name, $value)
{
    if (!defined($name)) {
        define($name, $value);
    }
}

def('DS', DIRECTORY_SEPARATOR);
def('ROOT', $findRoot());
def('APP_DIR', 'App');
def('WEBROOT_DIR', 'webroot');
def('APP', ROOT . '/tests/App/');
def('CONFIG', ROOT . '/tests/Config/');
def('WWW_ROOT', ROOT . '/webroot/');
def('TESTS', ROOT . DS . 'tests' . DS);
def('TMP', ROOT . DS . 'tmp' . DS);
def('LOGS', TMP . 'logs' . DS);
def('CACHE', TMP . 'cache' . DS);
def('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
def('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
def('CAKE', CORE_PATH . 'src' . DS);

require ROOT . '/vendor/cakephp/cakephp/src/basics.php';
require ROOT . '/vendor/autoload.php';
