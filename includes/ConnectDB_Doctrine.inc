<?php

/*load Doctrine DBAL classes */
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/Connection.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Platforms/AbstractPlatform.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/Statement.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/DriverManager.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Configuration.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/Common/EventManager.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Connection.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Query/Expression/ExpressionBuilder.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/PDOConnection.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/PDOStatement.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Events.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Statement.php');

/* MySQL with PDO */
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/PDOMySql/Driver.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Platforms/MySqlPlatform.php');

/*
    // Mysqli 
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/Mysqli/Driver.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Platforms/MySqlPlatform.php');

    //PostgreSQL
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/PDOPgSql/Driver.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Platforms/PostgreSqlPlatform.php'); 

    //SQLite
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Driver/PDOSqlite/Driver.php');
require_once('includes/DoctrineDBAL/lib/Doctrine/DBAL/Platforms/SqlitePlatform.php'); 
    
*/

if (!isset($mysqlport)){
	$mysqlport = 3306;
}

global $db;

$db = \Doctrine\DBAL\DriverManager::getConnection(array( 'host' => $host,
                                                                'port' => $mysqlport,
                                                                'user' => $DBUser,
                                                                'password' => $DBPassword,
                                                                'dbname' => $_SESSION['DatabaseName'],
                                                                'driver' => 'pdo_mysql'));



?>