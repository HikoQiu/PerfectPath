<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

/**
 * 自动加载未导入的类
 * @param $className
 */
function loadClass($className)
{
    $fileName    = '';
    $includePath = dirname(__FILE__).'/../';
    $includePath = realpath($includePath);

    if (false !== ($lastNsPos = strripos($className, '\\'))) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= $className . '.php';
    $filePath = rtrim($includePath, '/') . DIRECTORY_SEPARATOR . $fileName;

    if (file_exists($filePath)) {
        include_once $filePath;
    } else {
        throw new Exception('Class "' . $className . '" 不存在!');
    }
}

spl_autoload_register('loadClass');
