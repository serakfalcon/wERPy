<?php

define ('LIKE','LIKE'); //what is this used for?
require_once ($PathPrefix .'includes/MiscFunctions.php'); // should this be in ConnectDB.inc?


/*load Doctrine DBAL classes */
use Doctrine\Common\ClassLoader;
require_once ('includes/DoctrineDBAL/lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php');
$commonLoader = new ClassLoader('Doctrine\Common','includes/DoctrineDBAL/lib/vendor/doctrine-common/lib');
$DBALLoader = new ClassLoader('Doctrine\DBAL','includes\DoctrineDBAL\lib');
$commonLoader->register();
$DBALLoader->register();

if (!isset($sqlport)){
	$sqlport = 3306;
}

if (!isset($DBALDriver)) {
    $DBALDriver = 'pdo_mysql';
}

global $db;

if (isset($DatabaseName)) {
	$_SESSION['DatabaseName'] = $DatabaseName;
}

        //note that sqlite does not have a host,port or dbname, instead set those values to null
$connectionParams = array(  'driver' => $DBALDriver,
                            'user' => $DBUser,
                            'password' => $DBPassword,
                            'host' => $host,
                            'port' => $sqlport,
                            'dbname' => $_SESSION['DatabaseName']);

//db specific settings (optional)
switch ($DBALDriver) {
    case 'pdo_sqlite':
        if ($SQLitePath) {
            $connectionParams['path'] = $SQLitePath;
        } elseif ($SQLiteMemory) {
            $connectionParams['memory'] = $SQLiteMemory;
        }
        break;
    case 'sqlanywhere':
        $connectionParams['server'] = $SQLAnywhereServer;
        $connectionParams['persistent'] = $SQLAnywherePersistent;
        break;
    case 'pdo_oci':
    case 'oci8':
        if (isset($ociServiceName)) {
            $connectionParams['service'] = true;
            $connectionParams['servicename'] = $ociServiceName;
        }
        $connectionParams['pooled'] = (isset($ociPooled)) ? $ociPooled : false;
        $connectionParams['charset'] = 'utf8';
        break;
    case 'pdo_pgsql':
        $connectionParams['sslmode'] = $pgSQLSSLmode;
        $connectionParams['charset'] = 'utf8';
        break;
    case 'pdo_mysql':
    case 'mysqli':
        $connectionParams['charset'] = 'utf8';
        //fallthrough
    case 'drizzle_pdo_mysql':
        if ($DBUnixSocket) {
            $connectionParams['unix_socket'] = $DBUnixSocket;
        }
        break;
}

try {
    $db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
} catch (\Exception $error) {
    echo _('Connect failed') . ': ' . $error->getMessage();
	session_unset();
	session_destroy();
	echo '<p>' . _('Click') . ' ' . '<a href="index.php">' . _('here') . '</a>' . ' '  ._('to try logging in again') . '</p>';
    
    /* find specific errors for these:
        echo '<br />' . _('The company name entered does not correspond to a database on the database server specified in the config.php configuration file. Try logging in with a different company name');
		echo '<br /><a href="index.php">' . _('Back to login page') . '</A>';
    */
	exit();
}

/* Update to allow RecurringSalesOrdersProcess.php to run via cron */


function DB_query($query,&$Conn,$ErrorMessage = null,$DebugMessage = null,$Transaction = null,$TrapErrors = null,$bindvars = null) {
    //for legacy code compatibility, remove & rename when ready
    return DB_querynew($query,
                $bindvars,
                $ErrorMessage,
                $DebugMessage,
                $Transaction,
                $TrapErrors);
}

//note that $Conn is ignored, delete if willing to update legacy code, also $bindvars should be after $query
function DB_querynew(   $query,
                        $bindvars = null,
                        $ErrorMessage='',
                        $DebugMessage= null,
                        $Transaction=false,
                        $TrapErrors=true) {

    global $db;
    global $debug;
    global $PathPrefix;
    
    if (!isset($DebugMessage)) {
        $DebugMessage = _('The SQL that failed was');
    }
    
    if ($Transaction) {
        //let connection know it is not to commit until told
        $db->beginTransaction();
    }
    
    if (isset($bindvars) && !is_array($bindvars)) {
        /*executeQuery expects an array of parameters, otherwise it treats the statement as a non-parametrized query
        * so if $bindvars is passed but is not an array, make it an array with a single value
        */
        $bindvars = array($bindvars);
    } elseif (!isset($bindvars)) {
        $nobind = true;
    }
    
    if (is_array($bindvars)) {
        //sanitize the string
        $query = filter_var($query, FILTER_SANITIZE_STRING);
    }
    
    if ($Transaction) {
        try {
            //attempt to run the query on the DB
            if ($nobind) {
                $stmt = $db->query($query);
            } else {
                $stmt = $db->executeQuery($query,$bindvars);
            }
            $db->commit();
            $_SESSION['LastInsertId'] = $db->insert_id;
        } catch (\Exception $e) {
            $db->rollback();
            if ($TrapErrors) {
                require_once($PathPrefix . 'includes/header.inc');
                prnMsg($ErrorMessage . '<br />' . $e->getMessage(),'error', _('Database Error'). ' ' .$e->getCode());
                if ($debug==1){
                    prnMsg($DebugMessage. '<br />' . $query . '<br />','error',_('Database SQL Failure'));
                }
                if ($db->errno !=0){
                    prnMsg(_('Error Rolling Back Transaction'), 'error', _('Database Rollback Error'). ' ' .$db->errno );
                }else{
                    prnMsg(_('Rolling Back Transaction OK'), 'error', _('Database Rollback Due to Error Above'));
                }
                include($PathPrefix . 'includes/footer.inc');
                exit;
            }
        }
    } else {
        try {
            if ($nobind) {
                $stmt = $db->query($query);
            } else {
                $stmt = $db->executeQuery($query,$bindvars); 
            }
            $_SESSION['LastInsertId'] = $db->insert_id;
        } catch (\Exception $e) {
             if ($TrapErrors) {
                require_once($PathPrefix . 'includes/header.inc');
                prnMsg($ErrorMessage . '<br />' . $e->getMessage(),'error', _('Database Error'). ' ' .$e->getCode());
                if ($debug==1){
                    prnMsg($DebugMessage. '<br />' . $query . '<br />','error',_('Database SQL Failure'));
                }
                include($PathPrefix . 'includes/footer.inc');
                exit;
            }
        }
    }
    
    if (isset($_SESSION['MonthsAuditTrail']) && ($db->errno==0 && $_SESSION['MonthsAuditTrail']>0) && ($db->affected_rows)>0){
            $SQLArray = explode($query,' ');
            /*db info works on insert, update, alter table or load data infile, otherwise look for a delete
            if the third element is audittrail, don't log this as it is either DELETE FROM audittrail or INSERT INTO audittrail.  */
            if (($db->info != '' || (strpos($query,'DELETE ') !== false)) && $SQLArray[2] != 'audittrail') {
                $db->executeQuery(  'INSERT INTO audittrail (userid,querystring,transactiondate)
                                    VALUES(?,?,?);',array(trim($_SESSION['UserID']),$query . '/n' . json_encode($params),Date('Y-m-d H:i:s')));
 
        }
    }
    

    //if no problems, returning statement back to user:
    //note, statement must be closed!
    return $stmt;
    
    /* returning results set if applicable 
    $return = $stmt->fetchAll();
    $stmt->close();
    return $result;
    */
}                                


function DB_fetch_row (&$Result) {
	if ($Result) {
        return $Result->fetch(PDO::FETCH_BOTH);
    } else {
        return false;
    }
}

function DB_fetch_assoc (&$Result) {
    if ($Result) {
        return $Result->fetchAssoc();
    } else {
        return false;
    }
}

function DB_fetch_array (&$Result) {
    if ($Result) {
        return $Result->fetch(PDO::FETCH_NUM);
    } else {
        return false;
    }
}

function DB_fetch_all_close (&$Result) {
    $return = $Result->fetchAll();
    $Result->closeCursor();
    return $Result;
}

function DB_data_seek (&$Result,$Record) {
	//not properly supported, emulate
    $return = $Result->fetchAll();
    return $return[$Record];
}

function DB_free_result (&$Result){
    $Result->closeCursor();
}

function DB_num_rows (&$Result){
    global $DBALdriver;
    if ($Result) {
        //emulate a true count
        switch ($DBALdriver) {
            case 'mysqli':
            case 'pdo_mysql':
                return $Result->rowCount();
                break;
            default:
                return 1;
        }
    } else {
        return 0;
    }
    

}

function DB_affected_rows(&$Result){
    if ($Result) {
        return  $Result->rowCount();
    } else {
        return 0;
    }
    
}

function DB_error_no (&$Conn = null){
	global $db;
    return $db->errorCode();
}

function DB_error_msg(&$Conn = null){
    global $db;
    return $db->errorInfo();
}

function DB_Last_Insert_ID(){
	return (isset($_SESSION['LastInsertId'])) ? $_SESSION['LastInsertId'] : 0;
}

function DB_escape_string($String){
    //note, will add single quotes: different behavior from mysqli, but doesn't matter if we use parameters
	global $db;
	return $db->quote($String);
}

function DB_show_tables(&$Conn = null){
    global $db;
	$Result = DB_querynew('SHOW TABLES');
	return $Result;
}

function DB_show_fields($TableName){
	$Result = DB_querynew('DESCRIBE ' . $db->quoteIdentifier($TableName));
	return $Result;
}

function interval( $val, $Inter ){
		global $dbtype;
		return "\n".'interval ' . $val . ' '. $Inter."\n";
}

function DB_Maintenance(){
	prnMsg(_('The system has just run the regular database administration and optimisation routine.'),'info');

	$TablesResult = DB_querynew('SHOW TABLES');
	while ($myrow = DB_fetch_row($TablesResult)){
		$Result = DB_querynew('OPTIMIZE TABLE ' . $myrow[0]);
	}

	$Result = DB_querynew("UPDATE config
				SET confvalue='" . Date('Y-m-d') . "'
				WHERE confname='DB_Maintenance_LastRun'");
    return $Result;
}

function DB_Txn_Begin(){
    global $db;
    $db->beginTransaction();
}

function DB_Txn_Commit(){
    global $db;
    $db->commit();
}

function DB_Txn_Rollback(){
    global $db;
    $db->rollback();
}
function DB_IgnoreForeignKeys(){
	global $db;
    $db->query('SET FOREIGN_KEY_CHECKS=0'); //may not work for all database types
}
function DB_ReinstateForeignKeys(){
    global $db;
	$db->query('SET FOREIGN_KEY_CHECKS=1'); //may not work for all database types
}


?>