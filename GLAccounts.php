<?php
/* $Id: GLAccounts.php 6338 2013-09-28 05:10:46Z daintree $*/

include('includes/session.inc');
$Title = _('Chart of Accounts Maintenance');

$ViewTopic= 'GeneralLedger';
$BookMark = 'GLAccounts';

include('includes/header.inc');

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		prnMsg( _('The account name must be fifty characters or less long'),'warn');
	}

	if (isset($SelectedAccount) AND $InputError !=1) {

		$sql = "UPDATE chartmaster SET accountname='" . $_POST['AccountName'] . "',
						group_='" . $_POST['Group'] . "'
				WHERE accountcode ='" . $SelectedAccount . "'";

		$ErrMsg = _('Could not update the account because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg (_('The general ledger account has been updated'),'success');
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$ErrMsg = _('Could not add the new account code');
		$sql = "INSERT INTO chartmaster (accountcode,
						accountname,
						group_)
					VALUES ('" . $_POST['AccountCode'] . "',
							'" . $_POST['AccountName'] . "',
							'" . $_POST['Group'] . "')";
		$result = DB_query($sql,$db,$ErrMsg);

		prnMsg(_('The new general ledger account has been added'),'success');
	}

	unset ($_POST['Group']);
	unset ($_POST['AccountCode']);
	unset ($_POST['AccountName']);
	unset($SelectedAccount);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'

	$sql= "SELECT COUNT(*)
			FROM chartdetails
			WHERE chartdetails.accountcode ='" . $SelectedAccount . "'
			AND chartdetails.actual <>0";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this account because chart details have been created using this account and at least one period has postings to it'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('chart details that require this account code');

	} else {
// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$sql= "SELECT COUNT(*)
				FROM gltrans
				WHERE gltrans.account ='" . $SelectedAccount . "'";

		$ErrMsg = _('Could not test for existing transactions because');

		$result = DB_query($sql,$db,$ErrMsg);

		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this account because transactions have been created using this account'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions that require this account code');

		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$sql= "SELECT COUNT(*) FROM companies
					WHERE debtorsact='" . $SelectedAccount ."'
					OR pytdiscountact='" . $SelectedAccount ."'
					OR creditorsact='" . $SelectedAccount ."'
					OR payrollact='" . $SelectedAccount ."'
					OR grnact='" . $SelectedAccount ."'
					OR exchangediffact='" . $SelectedAccount ."'
					OR purchasesexchangediffact='" . $SelectedAccount ."'
					OR retainedearnings='" . $SelectedAccount ."'";


			$ErrMsg = _('Could not test for default company GL codes because');

			$result = DB_query($sql,$db,$ErrMsg);

			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this account because it is used as one of the company default accounts'),'warn');

			} else  {
				//PREVENT DELETES IF Company default accounts set up to this account
				$sql= "SELECT COUNT(*) FROM taxauthorities
					WHERE taxglcode='" . $SelectedAccount ."'
					OR purchtaxglaccount ='" . $SelectedAccount ."'";

				$ErrMsg = _('Could not test for tax authority GL codes because');
				$result = DB_query($sql,$db,$ErrMsg);

				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg( _('Cannot delete this account because it is used as one of the tax authority accounts'),'warn');
				} else {
//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$sql= "SELECT COUNT(*) FROM salesglpostings
						WHERE salesglcode='" . $SelectedAccount ."'
						OR discountglcode='" . $SelectedAccount ."'";

					$ErrMsg = _('Could not test for existing sales interface GL codes because');

					$result = DB_query($sql,$db,$ErrMsg);

					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg( _('Cannot delete this account because it is used by one of the sales GL posting interface records'),'warn');
					} else {
//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$sql= "SELECT COUNT(*)
								FROM cogsglpostings
								WHERE glcode='" . $SelectedAccount ."'";

						$ErrMsg = _('Could not test for existing cost of sales interface codes because');

						$result = DB_query($sql,$db,$ErrMsg);

						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this account because it is used by one of the cost of sales GL posting interface records'),'warn');

						} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$sql= "SELECT COUNT(*) FROM stockcategory
									WHERE stockact='" . $SelectedAccount ."'
									OR adjglact='" . $SelectedAccount ."'
									OR purchpricevaract='" . $SelectedAccount ."'
									OR materialuseagevarac='" . $SelectedAccount ."'
									OR wipact='" . $SelectedAccount ."'";

							$Errmsg = _('Could not test for existing stock GL codes because');

							$result = DB_query($sql,$db,$ErrMsg);

							$myrow = DB_fetch_row($result);
							if ($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg( _('Cannot delete this account because it is used by one of the stock GL posting interface records'),'warn');
							} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
								$sql= "SELECT COUNT(*) FROM bankaccounts
								WHERE accountcode='" . $SelectedAccount ."'";
								$ErrMsg = _('Could not test for existing bank account GL codes because');

								$result = DB_query($sql,$db,$ErrMsg);

								$myrow = DB_fetch_row($result);
								if ($myrow[0]>0) {
									$CancelDelete = 1;
									prnMsg( _('Cannot delete this account because it is used by one the defined bank accounts'),'warn');
								} else {

									$sql = "DELETE FROM chartdetails WHERE accountcode='" . $SelectedAccount ."'";
									$result = DB_query($sql,$db);
									$sql="DELETE FROM chartmaster WHERE accountcode= '" . $SelectedAccount ."'";
									$result = DB_query($sql,$db);
									prnMsg( _('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'),'succes');
								}
							}
						}
					}
				}
			}
		}
	}
}

if (!isset($_GET['delete'])) {

    $GLaccountsForm = $MainView->createForm();
    $GLaccountsForm->id = 'GLAccounts';
    $GLaccountsForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
    
	if (isset($SelectedAccount)) {
		//editing an existing account

		$sql = "SELECT accountcode, accountname, group_ FROM chartmaster WHERE accountcode='" . $SelectedAccount ."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['AccountCode'] = $myrow['accountcode'];
		$_POST['AccountName']	= $myrow['accountname'];
		$_POST['Group'] = $myrow['group_'];
        
        $GLaccountsForm->addHiddenControl('SelectedAccount',$SelectedAccount);
        $GLaccountsForm->addHiddenControl('AccountCode',$_POST['AccountCode']);
        
        $controlsettings['text'] = $_POST['AccountCode'];
        //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
        $GLaccountsForm->addControl(1,0,'static',_('Account Code'),$controlsettings);
	} else {
        
        $controlsettings['name'] = 'AccountCode';
        $controlsettings['title'] = _('Enter up to 20 alpha-numeric characters for the general ledger account code');
        $controlsettings['required'] = true;
        $controlsettings['autofocus'] = true;
        $controlsettings['data-type'] = 'no-illegal-chars';
        $controlsettings['size'] = 20;
        $controlsettings['maxlength'] = 20;
        
        //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
        $GLaccountsForm->addControl(1,1,'text',_('Account Code'),$controlsettings);
	}

	if (!isset($_POST['AccountName'])) {
		$_POST['AccountName']='';
	}
    
    unset($controlsettings);
    $controlsettings['name'] = 'AccountName';
    $controlsettings['value'] = $_POST['AccountName'];
    $controlsettings['title'] = _('Enter up to 50 alpha-numeric characters for the general ledger account name');
    $controlsettings['autofocus'] = isset($_POST['AccountCode']);
    $controlsettings['required'] = true;
    $controlsettings['size'] = 51;
    $controlsettings['maxlength'] = 50;
    //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
    $GLaccountsForm->addControl(2,2,'text',_('Account Name') . ':',$controlsettings);

	$sql = "SELECT groupname FROM accountgroups ORDER BY sequenceintb";
	$result = DB_query($sql, $db);
    
    unset($controlsettings);
    $controlsettings['name'] = 'Group';
    $controlsettings['required'] = true;
    //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
    $GLaccountsForm->addControl(3,3,'select',_('Account Group') . ':',$controlsettings);
    
	while ($myrow = DB_fetch_array($result)){
        //addControlOption($key,$text,$value,$isSelected = null,$parentID = null,$id = null)
        $GLaccountsForm->addControlOption(3,$myrow[0],$myrow[0],(isset($_POST['Group']) and $myrow[0]==$_POST['Group']));
	}
    unset($controlsettings['required']);
    $controlsettings['name'] = 'submit';
    $GLaccountsForm->addControl(4,4,'submit',_('Enter Information'),$controlsettings);
    $GLaccountsForm->display();

} //end if record deleted no point displaying form to add record


if (!isset($SelectedAccount)) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of ChartMaster will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . _('Balance Sheet') . "' ELSE '" . _('Profit/Loss') . "' END AS acttype
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		ORDER BY chartmaster.accountcode";

	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$db,$ErrMsg);
    
    
    $GLaccountsTable = $MainView->createTable();
    $GLaccountsTable->sortable = true;
    $GLaccountsTable->id = "GLAccountsTable";
    $header[] =  _('Account Code');
    $header[] = _('Account Name');
    $header[] = _('Account Group');
    $header[] = _('P/L or B/S');
    $GLaccountsTable->setHeaders($header);

	while ($myrow = DB_fetch_row($result)) {
        $tablerow = array();
        $tablerow[] = $myrow[0];
        $tablerow[] = htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8');
        $tablerow[] = $myrow[2];
        $tablerow[] = $myrow[3];
        $editrow['content'] = _('Edit');
        $editrow['link'] = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&SelectedAccount=' . $myrow[0];
        $tablerow[] = $editrow;
        $delrow['content'] = _('Delete');
        $delrow['link'] = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&SelectedAccount=' . $myrow[0] . '&delete=1';
        $delrow['attributes'] = 'onclick="return confirm(\'' . _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . '\');"';
        $tablerow[] = $delrow;
        $GLaccountsTable->addRow($tablerow);
	}
	//END WHILE LIST LOOP
	$GLaccountsTable->display();
} //END IF selected ACCOUNT

//end of ifs and buts!

echo '<br />';

if (isset($SelectedAccount)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' .  _('Show All Accounts') . '</a></div>';
}

include('includes/footer.inc');
?>
