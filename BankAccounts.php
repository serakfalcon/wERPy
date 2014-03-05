<?php

/* $Id: BankAccounts.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

$Title = _('Bank Accounts Maintenance');
$ViewTopic= 'GeneralLedger';
$BookMark = 'BankAccounts';
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Bank') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<div class="page_help_text">' . _('Update Bank Account details.  Account Code is for SWIFT or BSB type Bank Codes.  Set Default for Invoices to Currency Default  or Fallback Default to print Account details on Invoices (only one account should be set to Fall Back Default).') . '.</div><br />';

if (isset($_GET['SelectedBankAccount'])) {
	$SelectedBankAccount=$_GET['SelectedBankAccount'];
} elseif (isset($_POST['SelectedBankAccount'])) {
	$SelectedBankAccount=$_POST['SelectedBankAccount'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	$sql="SELECT count(accountcode)
			FROM bankaccounts WHERE accountcode='".$_POST['AccountCode']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedBankAccount)) {
		$InputError = 1;
		prnMsg( _('The bank account code already exists in the database'),'error');
		$Errors[$i] = 'AccountCode';
		$i++;
	}
	if (mb_strlen($_POST['BankAccountName']) >50) {
		$InputError = 1;
		prnMsg(_('The bank account name must be fifty characters or less long'),'error');
		$Errors[$i] = 'AccountName';
		$i++;
	}
	if ( trim($_POST['BankAccountName']) == '' ) {
		$InputError = 1;
		prnMsg(_('The bank account name may not be empty.'),'error');
		$Errors[$i] = 'AccountName';
		$i++;
	}
	if ( trim($_POST['BankAccountNumber']) == '' ) {
		$InputError = 1;
		prnMsg(_('The bank account number may not be empty.'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;
	}
	if (mb_strlen($_POST['BankAccountNumber']) >50) {
		$InputError = 1;
		prnMsg(_('The bank account number must be fifty characters or less long'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;
	}
	if (mb_strlen($_POST['BankAddress']) >50) {
		$InputError = 1;
		prnMsg(_('The bank address must be fifty characters or less long'),'error');
		$Errors[$i] = 'BankAddress';
		$i++;
	}

	if (isset($SelectedBankAccount) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/

		$sql = "SELECT banktransid FROM banktrans WHERE bankact='" . $SelectedBankAccount . "'";
		$BankTransResult = DB_query($sql,$db);
		if (DB_num_rows($BankTransResult)>0) {
			$sql = "UPDATE bankaccounts SET bankaccountname='" . $_POST['BankAccountName'] . "',
											bankaccountcode='" . $_POST['BankAccountCode'] . "',
											bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
											bankaddress='" . $_POST['BankAddress'] . "',
											invoice ='" . $_POST['DefAccount'] . "'
										WHERE accountcode = '" . $SelectedBankAccount . "'";
			prnMsg(_('Note that it is not possible to change the currency of the account once there are transactions against it'),'warn');
	echo '<br />';
		} else {
			$sql = "UPDATE bankaccounts SET bankaccountname='" . $_POST['BankAccountName'] . "',
											bankaccountcode='" . $_POST['BankAccountCode'] . "',
											bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
											bankaddress='" . $_POST['BankAddress'] . "',
											currcode ='" . $_POST['CurrCode'] . "',
											invoice ='" . $_POST['DefAccount'] . "'
										WHERE accountcode = '" . $SelectedBankAccount . "'";
		}

		$msg = _('The bank account details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO bankaccounts (accountcode,
										bankaccountname,
										bankaccountcode,
										bankaccountnumber,
										bankaddress,
										currcode,
										invoice
									) VALUES ('" . $_POST['AccountCode'] . "',
										'" . $_POST['BankAccountName'] . "',
										'" . $_POST['BankAccountCode'] . "',
										'" . $_POST['BankAccountNumber'] . "',
										'" . $_POST['BankAddress'] . "',
										'" . $_POST['CurrCode'] . "',
										'" . $_POST['DefAccount'] . "' )";
		$msg = _('The new bank account has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The bank account could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the bank account details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br />';
		unset($_POST['AccountCode']);
		unset($_POST['BankAccountName']);
		unset($_POST['BankAccountCode']);
		unset($_POST['BankAccountNumber']);
		unset($_POST['BankAddress']);
		unset($_POST['CurrCode']);
		unset($_POST['DefAccount']);
		unset($SelectedBankAccount);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(bankact) AS accounts FROM banktrans WHERE banktrans.bankact='" . $SelectedBankAccount . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	if ($myrow['accounts']>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this bank account because transactions have been created using this account'),'warn');
		echo '<br /> ' . _('There are') . ' ' . $myrow['accounts'] . ' ' . _('transactions with this bank account code');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM bankaccounts WHERE accountcode='" . $SelectedBankAccount . "'";
		$result = DB_query($sql,$db);
		prnMsg(_('Bank account deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedBankAccount);
}

/* Always show the list of accounts */
if (!isset($SelectedBankAccount)) {
	$sql = "SELECT bankaccounts.accountcode,
					bankaccounts.bankaccountcode,
					chartmaster.accountname,
					bankaccountname,
					bankaccountnumber,
					bankaddress,
					currcode,
					invoice
			FROM bankaccounts INNER JOIN chartmaster
			ON bankaccounts.accountcode = chartmaster.accountcode";

	$ErrMsg = _('The bank accounts set up could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank account details was') . '<br />' . $sql;
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    
    $BankAccountsTable = $MainView->createTable();
    $BankAccountsTable->sortable = true;
    $BankAccountsTable->id = 'BankAccountsTable';
    $headers[] = _('GL Account Code');
    $headers[] = _('Bank Account Name');
    $headers[] = _('Bank Account Code');
    $headers[] = _('Bank Account Number');
    $headers[] = _('Bank Address');
    $headers[] =  _('Currency');
    $headers[] = _('Default for Invoices');
    
    $BankAccountsTable->setHeaders($headers);

	while ($myrow = DB_fetch_array($result)) {
        
        $newrow = array();
        $newrow[] = $myrow['accountcode'] . '<br />' . $myrow['accountname'];
        $newrow[] = $myrow['bankaccountname'];
        $newrow[] = $myrow['bankaccountcode'];
        $newrow[] = $myrow['bankaccountnumber'];
        $newrow[] = $myrow['bankaddress'];
        $newrow[] = $myrow['currcode'];
        
        if ($myrow['invoice']==0) {
			$DefaultBankAccount=_('No');
		} elseif ($myrow['invoice']==1) {
			$DefaultBankAccount=_('Fall Back Default');
		} elseif ($myrow['invoice']==2) {
			$DefaultBankAccount=_('Currency Default');
		}
        
        $newrow[] = $DefaultBankAccount;
        $editrow['content'] = _('Edit');
        $editrow['link'] = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedBankAccount=' . $myrow['accountcode'];
        $newrow[] = $editrow;
        $delrow['content'] = _('Delete');
        $delrow['link'] = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedBankAccount=' . $myrow['accountcode'] . '&delete=1';
        $delrow['attributes'] = 'onclick="return confirm(\'' . _('Are you sure you wish to delete this bank account?') . '\');"';
        $newrow[] = $delrow;
        //addRow($columns,$id = null,$class = null,$attributes = null)
        $BankAccountsTable->addRow($newrow);

	} //END WHILE LIST LOOP
    $BankAccountsTable->display();
}

if (isset($SelectedBankAccount)) {
	echo '<br />';
	echo '<div class="centre"><p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Bank Accounts Defined') . '</a></p></div>';
	echo '<br />';
}


$BankAccountsForm = $MainView->createForm();
$BankAccountsForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
$BankAccountsForm->FormID = $_SESSION['FormID'];

if (isset($SelectedBankAccount) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT accountcode,
					bankaccountname,
					bankaccountcode,
					bankaccountnumber,
					bankaddress,
					currcode,
					invoice
			FROM bankaccounts
			WHERE bankaccounts.accountcode='" . $SelectedBankAccount . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['AccountCode'] = $myrow['accountcode'];
	$_POST['BankAccountName']  = $myrow['bankaccountname'];
	$_POST['BankAccountCode']  = $myrow['bankaccountcode'];
	$_POST['BankAccountNumber'] = $myrow['bankaccountnumber'];
	$_POST['BankAddress'] = $myrow['bankaddress'];
	$_POST['CurrCode'] = $myrow['currcode'];
	$_POST['DefAccount'] = $myrow['invoice'];

    $BankAccountsForm->addHiddenControl('SelectedBankAccount', $SelectedBankAccount);
    $BankAccountsForm->addHiddenControl('AccountCode',$_POST['AccountCode']);
	
    //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
    $controlsettings['text'] = $_POST['AccountCode'];
    $BankAccountsForm->addControl(1,0,'static',_('Bank Account GL Code') . ':',$controlsettings);
	
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	
    //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
    $controlsettings['name'] = 'AccountCode';
    $controlsettings['autofocus'] = true;
    $BankAccountsForm->addControl(1,1,'select', _('Bank Account GL Code') . ':',$controlsettings,(in_array('AccountCode',$Errors) ?   'selecterror' : null ));

	$sql = "SELECT accountcode,
					accountname
			FROM chartmaster LEFT JOIN accountgroups
			ON chartmaster.group_ = accountgroups.groupname
			WHERE accountgroups.pandl = 0
			ORDER BY accountcode";

	$result = DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['AccountCode']) and $myrow['accountcode']==$_POST['AccountCode']) {
            //addControlOption($key,$text,$value,$isSelected = null,$id = null)
            $BankAccountsForm->addControlOption(1,htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false),$myrow['accountcode'],true);
		} else {
            $BankAccountsForm->addControlOption(1,htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false),$myrow['accountcode']);
		}

	} //end while loop
}

// Check if details exist, if not set some defaults
if (!isset($_POST['BankAccountName'])) {
	$_POST['BankAccountName']='';
}
if (!isset($_POST['BankAccountNumber'])) {
	$_POST['BankAccountNumber']='';
}
if (!isset($_POST['BankAccountCode'])) {
        $_POST['BankAccountCode']='';
}
if (!isset($_POST['BankAddress'])) {
	$_POST['BankAddress']='';
}
//clear controlsettings
unset($controlsettings);
$controlsettings['name'] = 'BankAccountName';
$controlsettings['value'] = $_POST['BankAccountName'];
$controlsettings['required'] = true;
$controlsettings['size'] = 40;
$controlsettings['maxlength'] = 50;
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$BankAccountsForm->addControl(2,2,'text',_('Bank Account Name') . ':',$controlsettings,(in_array('AccountName',$Errors) ?  'inputerror' : null ));

//keep size and maxlength from previous control, so only reset required setting
unset($controlsettings['required']);
$controlsettings['name'] = 'BankAccountCode';
$controlsettings['value'] = $_POST['BankAccountCode'];
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$BankAccountsForm->addControl(3,3,'text', _('Bank Account Code') . ':',$controlsettings,(in_array('AccountCode',$Errors) ?  'inputerror' : null ));

$controlsettings['name'] = 'BankAccountNumber';
$controlsettings['value'] = $_POST['BankAccountNumber'];
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$BankAccountsForm->addControl(4,4,'text',_('Bank Account Number') . ':',$controlsettings,(in_array('AccountNumber',$Errors) ? 'inputerror' : null ));

$controlsettings['name'] = 'BankAddress';
$controlsettings['value'] = $_POST['BankAddress'];
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$BankAccountsForm->addControl(5,5,'text',_('Bank Address') . ':',$controlsettings,(in_array('BankAddress',$Errors) ? 'inputerror' : null));

$selectsettings['name'] = 'CurrCode';
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$BankAccountsForm->addControl(6,6,'select',_('Currency Of Account') . ':',$selectsettings);

if (!isset($_POST['CurrCode']) or $_POST['CurrCode']==''){
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
}
$result = DB_query("SELECT currabrev,
							currency
					FROM currencies",$db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['currabrev']==$_POST['CurrCode']) {
        //addControlOption($key,$text,$value,$isSelected = null,$id = null)
        $BankAccountsForm->addControlOption(6,$myrow['currabrev'],$myrow['currabrev'],true);
	} else {
        $BankAccountsForm->addControlOption(6,$myrow['currabrev'],$myrow['currabrev']);
	}
} //end while loop

//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$selectsettings['name'] = 'DefAccount';
$BankAccountsForm->addControl(7,7,'select',_('Default for Invoices') . ':',$selectsettings);


if (!isset($_POST['DefAccount']) OR $_POST['DefAccount']==''){
	$_POST['DefAccount'] = $_SESSION['CompanyRecord']['currencydefault'];
}


if (isset($SelectedBankAccount)) {
	$result = DB_query("SELECT invoice FROM bankaccounts where accountcode =" . $SelectedBankAccount . ' LIMIT 1',$db);
	$myrow = DB_fetch_array($result);
    if ($myrow['invoice']== 1) {
        //addControlOption($key,$text,$value,$isSelected = null,$id = null)
        $BankAccountsForm->addControlOption(7,_('Fall Back Default'),1,true);
        $BankAccountsForm->addControlOption(7,_('Currency Default'),2);
        $BankAccountsForm->addControlOption(7,_('No'),0);
        
    } else {
        //addControlOption($key,$text,$value,$isSelected = null,$id = null)
        $BankAccountsForm->addControlOption(7,_('No'),0,null,1);
        $BankAccountsForm->addControlOption(7,_('Currency Default'),2,null,2);
        $BankAccountsForm->addControlOption(7,_('Fall Back Default'),1,null,3);
        
        if ($myrow['invoice']== 2) {
            //cause _('Currency Default') to be selected, (null $text and $value means do not change those)
            //setControlOption($key,$id,$text = null,$value = null,$isSelected = null)
            $BankAccountsForm->setControlOption(7,2,null,null,true);
        } else {
            //cause _('No') to be selected, (null $text and $value means do not change those)
            $BankAccountsForm->setControlOption(7,1,null,null,true);
        }
    }
} else {
    $BankAccountsForm->addControlOption(7,_('Fall Back Default'),1);
    $BankAccountsForm->addControlOption(7,_('Currency Default'),2);
    $BankAccountsForm->addControlOption(7,_('No'),0);
}

//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
$submitsettings['name'] = 'submit';
$BankAccountsForm->addControl(8,8,'submit',_('Enter Information'),$submitsettings);

$BankAccountsForm->display();

include('includes/footer.inc');
?>