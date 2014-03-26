<?php

define('UL_OK',  0);		/* User verified, session initialised */
define('UL_NOTVALID', 1);	/* User/password do not agree */
define('UL_BLOCKED', 2);	/* Account locked, too many failed logins */
define('UL_CONFIGERR', 3);	/* Configuration error in webERP or server */
define('UL_SHOWLOGIN', 4);
define('UL_MAINTENANCE', 5);

// the sessionController manages session functions, session initialization, brokering the login
// user configuration settings, & general maintenance functions.
class sessionController {
    
    public function initSession() {
        global $SessionLifeTime;
        global $MaximumExecutionTime;
        ini_set('session.gc_maxlifetime',$SessionLifeTime);

        if( !ini_get('safe_mode') ){
            set_time_limit($MaximumExecutionTime);
            ini_set('max_execution_time',$MaximumExecutionTime);
        }
        session_write_close(); //in case a previous session is not closed
        session_start();
        if (!isset($_SESSION['AttemptsCounter']) OR $AllowDemoMode==true){
            $_SESSION['AttemptsCounter'] = 0;
        }

    }
    
    public function userLogin($Name = null, $Password = null, $SysAdminEmail = '') {
        global $debug;
        global $db;
        
        if (isset($Name) && isset($Password)) {
        
            if (!isset($_SESSION['AccessLevel']) OR $_SESSION['AccessLevel'] == '' OR
                (isset($Name) AND $Name != '')) {
            /* if not logged in */
                $_SESSION['AccessLevel'] = '';
                $_SESSION['CustomerID'] = '';
                $_SESSION['UserBranch'] = '';
                $_SESSION['SalesmanLogin'] = '';
                $_SESSION['Module'] = '';
                $_SESSION['PageSize'] = '';
                $_SESSION['UserStockLocation'] = '';
                $_SESSION['AttemptsCounter']++;

                // Show login screen
                if (!isset($Name) or $Name == '') {
                    $_SESSION['DatabaseName'] = '';
                    $_SESSION['CompanyName'] = '';
                    return  UL_SHOWLOGIN;
                }
                /* The SQL to get the user info must use the * syntax because the field name could change between versions if the fields are specifed directly then the sql fails and the db upgrade will fail */
                $sql = "SELECT *
                        FROM www_users
                        WHERE www_users.userid='" . $Name . "'
                        AND (www_users.password='" . CryptPass($Password) . "'
                        OR  www_users.password='" . $Password . "')";
                $ErrMsg = _('Could not retrieve user details on login because');
                $debug =1;
                $Auth_Result = DB_query($sql, $db,$ErrMsg);
                // Populate session variables with data base results
                if (DB_num_rows($Auth_Result) > 0) {
                    $myrow = DB_fetch_array($Auth_Result);
                    if ($myrow['blocked']==1){
                    //the account is blocked
                        return  UL_BLOCKED;
                    }
                    /*reset the attempts counter on successful login */
                    $_SESSION['UserID'] = $myrow['userid'];
                    $_SESSION['AttemptsCounter'] = 0;
                    $_SESSION['AccessLevel'] = $myrow['fullaccess'];
                    $_SESSION['CustomerID'] = $myrow['customerid'];
                    $_SESSION['UserBranch'] = $myrow['branchcode'];
                    $_SESSION['DefaultPageSize'] = $myrow['pagesize'];
                    $_SESSION['UserStockLocation'] = $myrow['defaultlocation'];
                    $_SESSION['UserEmail'] = $myrow['email'];
                    $_SESSION['ModulesEnabled'] = explode(",", $myrow['modulesallowed']);
                    $_SESSION['UsersRealName'] = $myrow['realname'];
                    $_SESSION['Theme'] = $myrow['theme'];
                    $_SESSION['Language'] = $myrow['language'];
                    $_SESSION['SalesmanLogin'] = $myrow['salesman'];
                    $_SESSION['CanCreateTender'] = $myrow['cancreatetender'];
                    $_SESSION['AllowedDepartment'] = $myrow['department'];

                    if (isset($myrow['pdflanguage'])) {
                        $_SESSION['PDFLanguage'] = $myrow['pdflanguage'];
                    } else {
                        $_SESSION['PDFLanguage'] = '0'; //default to latin western languages
                    }

                    if ($myrow['displayrecordsmax'] > 0) {
                        $_SESSION['DisplayRecordsMax'] = $myrow['displayrecordsmax'];
                    } else {
                        $_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
                    }

                    $sql = "UPDATE www_users SET lastvisitdate='". date('Y-m-d H:i:s') ."'
                                    WHERE www_users.userid='" . $Name . "'";
                    $Auth_Result = DB_query($sql, $db);
                    /*get the security tokens that the user has access to */
                    $sql = "SELECT tokenid
                            FROM securitygroups
                            WHERE secroleid =  '" . $_SESSION['AccessLevel'] . "'";
                    $Sec_Result = DB_query($sql, $db);
                    $_SESSION['AllowedPageSecurityTokens'] = array();
                    if (DB_num_rows($Sec_Result)==0){
                        return  UL_CONFIGERR;
                    } else {
                        $i=0;
                        $UserIsSysAdmin = FALSE;
                        while ($myrow = DB_fetch_row($Sec_Result)){
                            if ($myrow[0] == 15){
                                $UserIsSysAdmin = TRUE;
                            }
                            $_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
                            $i++;
                        }
                    }
                    // check if only maintenance users can access webERP
                    $sql = "SELECT confvalue FROM config WHERE confname = 'DB_Maintenance'";
                    $Maintenance_Result = DB_query($sql, $db);
                    if (DB_num_rows($Maintenance_Result)==0){
                        return  UL_CONFIGERR;
                    } else {
                        $myMaintenanceRow = DB_fetch_row($Maintenance_Result);
                        if (($myMaintenanceRow[0] == -1) AND ($UserIsSysAdmin == FALSE)){
                            // the configuration setting has been set to -1 ==> Allow SysAdmin Access Only
                            // the user is NOT a SysAdmin
                            return  UL_MAINTENANCE;
                        }
                    }
                } else {     // Incorrect password
                    // 5 login attempts, show failed login screen
                    if (!isset($_SESSION['AttemptsCounter'])) {
                        $_SESSION['AttemptsCounter'] = 0;
                    } elseif ($_SESSION['AttemptsCounter'] >= 5 AND isset($Name)) {
                        /*User blocked from future accesses until sysadmin releases */
                        $sql = "UPDATE www_users
                                    SET blocked=1
                                    WHERE www_users.userid='" . $Name . "'";
                        $Auth_Result = DB_query($sql, $db);

                        if ($SysAdminEmail != ''){
                            $EmailSubject = _('User access blocked'). ' ' . $Name ;
                            $EmailText =  _('User ID') . ' ' . $Name . ' - ' . $Password . ' - ' . _('has been blocked access at') . ' ' .
                                        Date('Y-m-d H:i:s') . ' ' . _('from IP') . ' ' . $_SERVER["REMOTE_ADDR"] . ' ' . _('due to too many failed attempts.');
                            if($_SESSION['SmtpSetting']==0){
                                    mail($SysAdminEmail,$EmailSubject,$EmailText);

                            }else{
                                    include('includes/htmlMimeMail.php');
                                    $mail = new htmlMimeMail();
                                    $mail->setSubject($EmailSubject);
                                    $mail->setText($EmailText);
                                    $result = SendmailBySmtp($mail,array($SysAdminEmail));
                            }

                        }

                        return  UL_BLOCKED;
                    }
                    return  UL_NOTVALID;
                }
            }		// End of userid/password check
            // Run with debugging messages for the system administrator(s) but not anyone else

            return   UL_OK;		    /* All is well */
        } elseif (empty($_SESSION['DatabaseName'])) {
            return UL_SHOWLOGIN;
        } else {
            return UL_OK;
        }
    }

    public function getConfig($ForceConfigReload = null) {
        if($ForceConfigReload === true || !isset($_SESSION['CompanyDefaultsLoaded'])) {
            global  $db;		// It is global, we may not be.
            $sql = "SELECT confname, confvalue FROM config";
            $ErrMsg = _('Could not get the configuration parameters from the database because');
            $ConfigResult = DB_query($sql,$db,$ErrMsg);
            while( $myrow = DB_fetch_array($ConfigResult) ) {
                if (is_numeric($myrow['confvalue']) AND $myrow['confname']!='DefaultPriceList' AND $myrow['confname']!='VersionNumber'){
                    //the variable name is given by $myrow[0]
                    $_SESSION[$myrow['confname']] = (double) $myrow['confvalue'];
                } else {
                    $_SESSION[$myrow['confname']] =  $myrow['confvalue'];
                }
            } //end loop through all config variables
            $_SESSION['CompanyDefaultsLoaded'] = true;

            DB_free_result($ConfigResult); // no longer needed
            /*Maybe we should check config directories exist and try to create if not */

            if (!isset($_SESSION['VersionNumber'])){ // the config record for VersionNumber is not yet added
                header('Location: UpgradeDatabase.php'); //divert to the db upgrade if the VersionNumber is not in the config table
            }

            /*Load the pagesecurity settings from the database */
            $sql="SELECT script, pagesecurity FROM scripts";
            $result=DB_query($sql, $db,'','',false,false);
            if (DB_error_no($db)!=0){
                /* the table may not exist with the pagesecurity field in it if it is an older webERP database
                 * divert to the db upgrade if the VersionNumber is not in the config table
                 * */
                header('Location: UpgradeDatabase.php');
            }
            //Populate the PageSecurityArray array for each script's  PageSecurity value
            while ($myrow=DB_fetch_array($result)) {
                $_SESSION['PageSecurityArray'][$myrow['script']]=$myrow['pagesecurity'];
            }

            /*
             check the decimalplaces field exists in currencies - this was added in 4.0 but is required in 4.04 as it is used everywhere as the default decimal places to show on all home currency amounts
            */
            $result = DB_query("SELECT decimalplaces FROM currencies",$db,'','',false,false);
            if (DB_error_no($db)!=0) { //then decimalplaces not already a field in currencies
                $result = DB_query("ALTER TABLE `currencies`
                                    ADD COLUMN `decimalplaces` tinyint(3) NOT NULL DEFAULT 2 AFTER `hundredsname`",$db);
            }
        /* Also reads all the company data set up in the company record and returns an array */

            $sql=	"SELECT	coyname,
                            gstno,
                            regoffice1,
                            regoffice2,
                            regoffice3,
                            regoffice4,
                            regoffice5,
                            regoffice6,
                            telephone,
                            fax,
                            email,
                            currencydefault,
                            debtorsact,
                            pytdiscountact,
                            creditorsact,
                            payrollact,
                            grnact,
                            exchangediffact,
                            purchasesexchangediffact,
                            retainedearnings,
                            freightact,
                            gllink_debtors,
                            gllink_creditors,
                            gllink_stock,
                            decimalplaces
                        FROM companies
                        INNER JOIN currencies ON companies.currencydefault=currencies.currabrev
                        WHERE coycode=1";

            $ErrMsg = _('An error occurred accessing the database to retrieve the company information');
            $ReadCoyResult = DB_query($sql,$db,$ErrMsg);

            if (DB_num_rows($ReadCoyResult)==0) {
                    echo '<br /><b>';
                prnMsg( _('The company record has not yet been set up') . '</b><br />' . _('From the system setup tab select company maintenance to enter the company information and system preferences'),'error',_('CRITICAL PROBLEM'));
                exit;
            } else {
                $_SESSION['CompanyRecord'] = DB_fetch_array($ReadCoyResult);
            }

            /*Now read in smtp email settings - not needed in a properly set up server environment - but helps for those who can't control their server .. I think! */

            $sql="SELECT id,
                        host,
                        port,
                        heloaddress,
                        username,
                        password,
                        timeout,
                        auth
                    FROM emailsettings";
            $result=DB_query($sql, $db,'','',false,false);
            if (DB_error_no($db)==0) {
                /*test to ensure that the emailsettings table exists!!
                 * if it doesn't exist then we are into an UpgradeDatabase scenario anyway
                */
                $myrow=DB_fetch_array($result);

                $_SESSION['SMTPSettings']['host']=$myrow['host'];
                $_SESSION['SMTPSettings']['port']=$myrow['port'];
                $_SESSION['SMTPSettings']['heloaddress']=$myrow['heloaddress'];
                $_SESSION['SMTPSettings']['username']=$myrow['username'];
                $_SESSION['SMTPSettings']['password']=$myrow['password'];
                $_SESSION['SMTPSettings']['timeout']=$myrow['timeout'];
                $_SESSION['SMTPSettings']['auth']=$myrow['auth'];
            }
        } //end if force reload or not set already
    
    }
    
    public function getUpgrades() {
        global $db;
        /*If the Code $Version - held in ConnectDB.inc is > than the Database VersionNumber held in config table then do upgrades */
        if (strcmp($Version,$_SESSION['VersionNumber'])>0 AND (basename($_SERVER['SCRIPT_NAME'])!='UpgradeDatabase.php')) {
            header('Location: UpgradeDatabase.php');
        }
        if(isset($_SESSION['DB_Maintenance'])){ 
            if ($_SESSION['DB_Maintenance']>0)  { //run the DB maintenance script
                if (DateDiff(Date($_SESSION['DefaultDateFormat']),
                        ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
                        ,'d')	>= 	$_SESSION['DB_Maintenance']){

                    /*Do the DB maintenance routing for the DB_type selected */
                    DB_Maintenance($db);
                    $_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');
                }
            }
        }
    }
    
    public function clearExpiredAuditHistory() {       
        global $db;
        //purge the audit trail if necessary
        if (isset($_SESSION['MonthsAuditTrail'])){ 
             $sql = "DELETE FROM audittrail
                    WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
            $ErrMsg = _('There was a problem deleting expired audit-trail history');
            $result = DB_query($sql,$db);
        }
    }
    
    public function updateCurrencyRates() {
        global $db;
        /*Check to see if currency rates need to be updated */
        if (isset($_SESSION['UpdateCurrencyRatesDaily'])){
            if ($_SESSION['UpdateCurrencyRatesDaily']!=0)  {
                if (DateDiff(Date($_SESSION['DefaultDateFormat']),
                    ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']),'d')> 0){

                    if ($_SESSION['ExchangeRateFeed']=='ECB') {
                        $CurrencyRates = GetECBCurrencyRates(); // gets rates from ECB see includes/MiscFunctions.php
                        /*Loop around the defined currencies and get the rate from ECB */
                        if ($CurrencyRates!=false) {
                            $CurrenciesResult = DB_query("SELECT currabrev FROM currencies",$db);
                            while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
                                if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){

                                    $UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . GetCurrencyRate($CurrencyRow[0],$CurrencyRates) . "'
                                                                        WHERE currabrev='" . $CurrencyRow[0] . "'",$db);
                                }
                            }
                        }
                    } else {
                        $CurrenciesResult = DB_query("SELECT currabrev FROM currencies",$db);
                        while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
                            if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){
                                $UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . google_currency_rate($CurrencyRow[0]) . "'
                                                                    WHERE currabrev='" . $CurrencyRow[0] . "'",$db);
                            }
                        }
                    }
                    $_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
                    $UpdateConfigResult = DB_query("UPDATE config SET confvalue = '" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'",$db);
                }
            }
        }
        
    }
    
    public function doMaintenance() {
        global $db;
        $this->getUpgrades();
        $this->clearExpiredAuditHistory();
        $this->updateCurrencyRates();
    }
  
}




?>