<?php
/* $Id: session.inc 6338 2013-09-28 05:10:46Z daintree $*/

if (!isset($PathPrefix)) {
	$PathPrefix='';
}


if (!file_exists($PathPrefix . 'config.php')){
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
	if ($RootPath == '/' OR $RootPath == "\\") {
		$RootPath = '';
	}
	header('Location:' . $RootPath . '/install/index.php');
	exit;
}
include($PathPrefix . 'config.php');

if (isset($dbuser)) {
	$DBUser=$dbuser;
	$DBPassword=$dbpassword;
	$DBType=$dbType;
}

if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

if (!isset($SysAdminEmail)) {
	$SysAdminEmail='';
}
include_once('presentation/session/core.php');
$SessionControl = new sessionController;
$SessionControl->initSession();

include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/DateFunctions.inc');

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
DEPRECATED TO REMOVE ONCE SQL FUNCTIONS ARE DONE BY PARAMETERS
*/

if (!isset($_SESSION['DatabaseName'])) {
    //new session
    $_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}


/* get configuration parameters  - save in session*/
/* set up default styling */
$Theme = 'aguapop';
$_SESSION['Theme'] = 'twig';
$_SESSION['Style'] = 'aguapop';
include_once('views/viewcontroller.php');

if (!isset($AllowAnyone)){ /* only do security checks if AllowAnyone is not true */

    $rc = $SessionControl->userLogin($_POST['UserNameEntryField'], $_POST['Password'], $SysAdminEmail);
    include($PathPrefix . 'includes/LanguageSetup.php');
	switch ($rc) {
	case  UL_OK; //user logged in successfully
		break;

	case UL_SHOWLOGIN:
        $MainView->displayLogin();
        exit; //mainview will already exit
	case UL_BLOCKED:
		die(include($PathPrefix . 'includes/FailedLogin.php'));

	case  UL_CONFIGERR:
		$Title = _('Account Error Report');
		include($PathPrefix . 'includes/header.inc');
		echo '<br /><br /><br />';
		prnMsg(_('Your user role does not have any access defined for webERP. There is an error in the security setup for this user account'),'error');
		include($PathPrefix . 'includes/footer.inc');
			exit;

	case  UL_NOTVALID:
		$demo_text = '<font size="3" color="red"><b>' .  _('incorrect password') . '</b></font><br /><b>' . _('The user/password combination') . '<br />' . _('is not a valid user of the system') . '</b>';
		die(include($PathPrefix . 'includes/Login.php'));

	case  UL_MAINTENANCE:
		$demo_text = '<font size="3" color="red"><b>' .  _('system maintenance') . '</b></font><br /><b>' . _('webERP is not available right now') . '<br />' . _('during maintenance of the system') . '</b>';
		die(include($PathPrefix . 'includes/Login.php'));

	}
} /* only do security checks if AllowAnyone is not true */
 elseif(basename($_SERVER['SCRIPT_NAME'])=='Logout.php'){
    include($PathPrefix . 'includes/LanguageSetup.php');
	header('Location: index.php');
}
$SessionControl->getConfig($ForceConfigReload);
$SessionControl->doMaintenance();
/* override default styling with user specific styling, change styling if it has recently been changed */
if (isset($_POST['Style']) AND ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
	$_SESSION['Style'] = $_POST['Style'];
	$Theme = ($_POST['Style']) ? $_POST['Style'] : 'aguapop';
    $MainView->setStyle($_SESSION['Style']);
} else {
	$Theme = ($_SESSION['Style']) ? $_SESSION['Style'] : 'aguapop';
    $MainView->setStyle($Theme);
}

if (isset($_POST['Theme']) && ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
    $_SESSION['Theme'] = $_POST['Theme'];
    $MainView->setTheme($_SESSION['Theme']);
}

        /* Set the logo if not yet set.
         * will be done only once per session and each time
         * we are not in session (i.e. before login)
         */
if (!isset($_SESSION['LogoFile'])) {
    /*for backwards compatibility, sets $_SESSION['LogoFile'], however this function also returns the logo file
     so you can call this function instead of referencing the logofile, replace $_SESSION['LogoFile'] with $MainView->getLogo();
     everywhere and this code can be erased */
   $MainView->getLogo();

}

        


if ($_SESSION['HTTPS_Only']==1){
	if ($_SERVER['HTTPS']!='on'){
		prnMsg(_('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....','error');
		exit;
	}
}



// Now check that the user as logged in has access to the page being called. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


if (!is_array($_SESSION['AllowedPageSecurityTokens']) AND !isset($AllowAnyone)) {
	$Title = _('Account Error Report');
	include($PathPrefix . 'includes/header.inc');
	echo '<br /><br /><br />';
	prnMsg(_('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'),'error');
	include($PathPrefix . 'includes/footer.inc');
	exit;
}

/*The page security variable is now retrieved from the database in GetConfig.php and stored in the $SESSION['PageSecurityArray'] array
 * the key for the array is the script name - the script name is retrieved from the basename ($_SERVER['SCRIPT_NAME'])
 */
if (!isset($PageSecurity)){
//only hardcoded in the UpgradeDatabase script - so old versions that don't have the scripts.pagesecurity field do not choke
	$PageSecurity = $_SESSION['PageSecurityArray'][basename($_SERVER['SCRIPT_NAME'])];
}


if (!isset($AllowAnyone)){
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
		$Title = _('Security Permissions Problem');
		include($PathPrefix . 'includes/header.inc');
		echo '<tr>
			<td class="menu_group_items">
				<table width="100%" class="table_index">
					<tr><td class="menu_group_item">';
		echo '<b><font style="size:+1; text-align:center;">' . _('The security settings on your account do not permit you to access this function') . '</font></b>';

		echo '</td>
			</tr>
			</table>
			</td>
			</tr>';

		include($PathPrefix . 'includes/footer.inc');
		exit;
	}
}

//$PageSecurity = 9 hard coded for supplier access Supplier access must have just 9 and 0 tokens
if (in_array(9,$_SESSION['AllowedPageSecurityTokens']) AND count($_SESSION['AllowedPageSecurityTokens'])==2){
	$SupplierLogin=1;
}
if (in_array(1,$_SESSION['AllowedPageSecurityTokens']) AND count($_SESSION['AllowedPageSecurityTokens'])==2){
	$CustomerLogin = 1;
}

if (in_array($_SESSION['PageSecurityArray']['WWW_Users.php'], $_SESSION['AllowedPageSecurityTokens'])) { /*System administrator login */
	$debug = 1; //allow debug messages
} else {
	$debug = 0; //don't allow debug messages
}
function CryptPass( $Password ) {
		global $CryptFunction;
		if ( $CryptFunction == 'sha1' ) {
			return sha1($Password);
		} elseif ( $CryptFunction == 'md5' ) {
			return md5($Password);
	} else {
			return $Password;
		}
 }


if (sizeof($_POST) > 0 AND !isset($AllowAnyone)) {
	/*Security check to ensure that the form submitted is originally sourced from webERP with the FormID = $_SESSION['FormID'] - which is set before the first login*/
	if (!isset($_POST['FormID']) OR ($_POST['FormID'] != $_SESSION['FormID'])) {
		$Title = _('Error in form verification');
		include('includes/header.inc');
		prnMsg(_('This form was not submitted with a correct ID') , 'error');
		include('includes/footer.inc');
		exit;
	}
}

?>
