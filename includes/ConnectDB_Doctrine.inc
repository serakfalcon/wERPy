<?php

/*load Doctrine DBAL classes */
use Doctrine\Common\ClassLoader;
require_once ('includes/DoctrineDBAL/lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php');
$commonLoader = new ClassLoader('Doctrine\Common','includes/DoctrineDBAL/lib/vendor/doctrine-common/lib/Doctrine/Common');
$DBALLoader = new ClassLoader('Doctrine\DBAL','includes/DoctrineDBAL/lib/Doctrine/DBAL');
$commonLoader->register();
$DBALLoader->register();

if (!isset($mysqlport)){
	$mysqlport = 3306;
}

global $dbWrapper;

$dbWrapper = \Doctrine\DBAL\DriverManager::getConnection(array( 'host' => $host,
                                                                'port' => $mysqlport,
                                                                'user' => $DBUser,
                                                                'password' => $DBPassword,
                                                                'dbname' => $_SESSION['DatabaseName'],
                                                                'driver' => 'pdo_mysql'));



?>