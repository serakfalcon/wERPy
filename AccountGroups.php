<?php

/* $Id: AccountGroups.php 6338 2013-09-28 05:10:46Z daintree $*/

include('includes/session.inc');

$Title = _('Account Groups');
$ViewTopic= 'GeneralLedger';
$BookMark = 'AccountGroups';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


function CheckForRecursiveGroup ($ParentGroupName, $GroupName, $db) {

/* returns true ie 1 if the group contains the parent group as a child group
ie the parent group results in a recursive group structure otherwise false ie 0 */

	$ErrMsg = _('An error occurred in retrieving the account groups of the parent account group during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the account groups of the parent account group and that failed in the process was');

	do {
		$sql = "SELECT parentgroupname
				FROM accountgroups
				WHERE groupname='" . $GroupName ."'";

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_row($result);
		if ($ParentGroupName == $myrow[0]){
			return true;
		}
		$GroupName = $myrow[0];
	} while ($myrow[0]!='');
	return false;
} //end of function CheckForRecursiveGroupName




// If $Errors is set, then unset it.
if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['MoveGroup'])) {
	$sql="UPDATE chartmaster SET group_='" . $_POST['DestinyAccountGroup'] . "' WHERE group_='" . $_POST['OriginalAccountGroup'] . "'";
	$ErrMsg = _('An error occurred in moving the account group');
	$DbgMsg = _('The SQL that was used to move the account group was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Account Groups') . '</a></div>';
	prnMsg( _('All accounts in the account group:') . ' ' . $_POST['OriginalAccountGroup'] . ' ' . _('have been changed to the account group:') . ' ' . $_POST['DestinyAccountGroup'],'success');
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	$sql="SELECT count(groupname)
			FROM accountgroups
			WHERE groupname='" . $_POST['GroupName'] . "'";

	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$ErrMsg = _('Could not check whether the group exists because');

	$result=DB_query($sql, $db,$ErrMsg,$DbgMsg);
	$myrow=DB_fetch_row($result);

	if ($myrow[0] != 0 AND $_POST['SelectedAccountGroup'] == '') {
		$InputError = 1;
		prnMsg( _('The account group name already exists in the database'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (ContainsIllegalCharacters($_POST['GroupName'])) {
		$InputError = 1;
		prnMsg( _('The account group name cannot contain the character') . " '&' " . _('or the character') ."' '",'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (mb_strlen($_POST['GroupName'])==0){
		$InputError = 1;
		prnMsg( _('The account group name must be at least one character long'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if ($_POST['ParentGroupName'] !=''){
		if (CheckForRecursiveGroup($_POST['GroupName'],$_POST['ParentGroupName'],$db)) {
			$InputError =1;
			prnMsg(_('The parent account group selected appears to result in a recursive account structure - select an alternative parent account group or make this group a top level account group'),'error');
			$Errors[$i] = 'ParentGroupName';
			$i++;
		} else {
			$sql = "SELECT pandl,
						sequenceintb,
						sectioninaccounts
					FROM accountgroups
					WHERE groupname='" . $_POST['ParentGroupName'] . "'";

			$DbgMsg = _('The SQL that was used to retrieve the information was');
			$ErrMsg = _('Could not check whether the group is recursive because');

			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

			$ParentGroupRow = DB_fetch_array($result);
			$_POST['SequenceInTB'] = $ParentGroupRow['sequenceintb'];
			$_POST['PandL'] = $ParentGroupRow['pandl'];
			$_POST['SectionInAccounts']= $ParentGroupRow['sectioninaccounts'];
			prnMsg(_('Since this account group is a child group, the sequence in the trial balance, the section in the accounts and whether or not the account group appears in the balance sheet or profit and loss account are all properties inherited from the parent account group. Any changes made to these fields will have no effect.'),'warn');
		}
	}
	if (!ctype_digit($_POST['SectionInAccounts'])) {
		$InputError = 1;
		prnMsg( _('The section in accounts must be an integer'),'error');
		$Errors[$i] = 'SectionInAccounts';
		$i++;
	}
	if (!ctype_digit($_POST['SequenceInTB'])) {
		$InputError = 1;
		prnMsg( _('The sequence in the trial balance must be an integer'),'error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	}
	if (!ctype_digit($_POST['SequenceInTB']) OR $_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		prnMsg( _('The sequence in the TB must be numeric and less than') . ' 10,000','error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	}


	if ($_POST['SelectedAccountGroup']!='' AND $InputError !=1) {

		/*SelectedAccountGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		if ($_POST['SelectedAccountGroup']!==$_POST['GroupName']) {

			DB_IgnoreForeignKeys($db);

			$sql = "UPDATE chartmaster
					SET group_='" . $_POST['GroupName'] . "'
					WHERE group_='" . $_POST['SelectedAccountGroup'] . "'";
			$ErrMsg = _('An error occurred in renaming the account group');
			$DbgMsg = _('The SQL that was used to rename the account group was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			$sql = "UPDATE accountgroups
					SET parentgroupname='" . $_POST['GroupName'] . "'
					WHERE parentgroupname='" . $_POST['SelectedAccountGroup'] . "'";

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			DB_ReinstateForeignKeys($db);
		}

		$sql = "UPDATE accountgroups SET groupname='" . $_POST['GroupName'] . "',
										sectioninaccounts='" . $_POST['SectionInAccounts'] . "',
										pandl='" . $_POST['PandL'] . "',
										sequenceintb='" . $_POST['SequenceInTB'] . "',
										parentgroupname='" . $_POST['ParentGroupName'] . "'
									WHERE groupname = '" . $_POST['SelectedAccountGroup'] . "'";
		$ErrMsg = _('An error occurred in updating the account group');
		$DbgMsg = _('The SQL that was used to update the account group was');

		$msg = _('Record Updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account group form */

		$sql = "INSERT INTO accountgroups ( groupname,
											sectioninaccounts,
											sequenceintb,
											pandl,
											parentgroupname
										) VALUES (
											'" . $_POST['GroupName'] . "',
											'" . $_POST['SectionInAccounts'] . "',
											'" . $_POST['SequenceInTB'] . "',
											'" . $_POST['PandL'] . "',
											'" . $_POST['ParentGroupName'] . "')";
		$ErrMsg = _('An error occurred in inserting the account group');
		$DbgMsg = _('The SQL that was used to insert the account group was');
		$msg = _('Record inserted');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		prnMsg($msg,'success');
		unset ($_POST['SelectedAccountGroup']);
		unset ($_POST['GroupName']);
		unset ($_POST['SequenceInTB']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

	$sql= "SELECT COUNT(group_) AS groups FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
	$ErrMsg = _('An error occurred in retrieving the group information from chartmaster');
	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_array($result);
	if ($myrow['groups']>0) {
		prnMsg( _('Cannot delete this account group because general ledger accounts have been created using this group'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow['groups'] . ' ' . _('general ledger accounts that refer to this account group');
		echo '<br />';
        
        $accountGroupsForm = $MainView->createForm();
        $accountGroupsForm->id = 'AccountGroups';
        $accountGroupsForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
        $accountGroupsForm->FormID = $_SESSION['FormID'];
        $accountGroupsForm->addHiddenControl('OriginalAccountGroup', $_GET['SelectedAccountGroup']);
        //Destiny? spelling error? Should be DestinationAccountGroup or NewAccountGroup
        $controlsettings['name'] = 'DestinyAccountGroup';
        //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null
        $accountGroupsForm->addControl(1,2,'select', _('Parent Group') . ':',$controlsettings, (in_array('ParentGroupName',$Errors) ?  'selecterror' : null));
        
        $sql = "SELECT groupname FROM accountgroups";
		$GroupResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
        
        while($GroupRow = DB_fetch_array($GroupResult)) {
            // $RroupRow spelling error?
            
            if (isset($_POST['ParentGroupName']) AND $_POST['ParentGroupName']==$GroupRow['groupname']) {
                                                    //$key,$text,$value,$isSelected,$id = null
                $accountGroupsForm->addControlOption(1,htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8'),
                                                       htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8'),
                                                       true);
            } else {
                                                    //$key,$text,$value,$isSelected,$id = null
                $accountGroupsForm->addControlOption(1,htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8'),
                                                       htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8'));
            }
        }
        //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null
        $controlsettings['name'] = 'MoveGroup';
        $controlsettings['value'] = _('Move Group');
        $accountGroupsForm->addControl(2,6,'submit',null,$controlsettings);
        
		$accountGroupsForm->display();

	} else {
		$sql = "SELECT COUNT(groupname) groupnames FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
		$ErrMsg = _('An error occurred in retrieving the parent group information');
		$DbgMsg = _('The SQL that was used to retrieve the information was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_array($result);
		if ($myrow['groupnames']>0) {
			prnMsg( _('Cannot delete this account group because it is a parent account group of other account group(s)'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow['groupnames'] . ' ' . _('account groups that have this group as its/there parent account group');

		} else {
			$sql="DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
			$ErrMsg = _('An error occurred in deleting the account group');
			$DbgMsg = _('The SQL that was used to delete the account group was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			prnMsg( $_GET['SelectedAccountGroup'] . ' ' . _('group has been deleted') . '!','success');
		}

	} //end if account group used in GL accounts

}

if (!isset($_GET['SelectedAccountGroup']) AND !isset($_POST['SelectedAccountGroup'])) {

/* An account group could be posted when one has been edited and is being updated or GOT when selected for modification
 SelectedAccountGroup will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of account groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT groupname,
					sectionname,
					sequenceintb,
					pandl,
					parentgroupname
			FROM accountgroups
			LEFT JOIN accountsection ON sectionid = sectioninaccounts
			ORDER BY sequenceintb";

	$DbgMsg = _('The sql that was used to retrieve the account group information was ');
	$ErrMsg = _('Could not get account groups because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';

    $accountGroupTable = $MainView->createTable();
    $accountGroupTable->id = 'GroupTable';
    $accountGroupTable->sortable = true;
    $accountGroupTable->sortSettings = '"aaSorting": [[ 2, "asc" ]]';
    //set up headers
    //$accountGroupTable->setClass('headers','header');
    $tableHeaders[] = _('Group Name');
    $tableHeaders[] = _('Section');
    $tableHeaders[] = _('Sequence In TB');
    $tableHeaders[] = _('Profit and Loss');
    $tableHeaders[] = _('Parent Group');
    
    $accountGroupTable->setHeaders($tableHeaders);
    
	while ($myrow = DB_fetch_array($result)) {
        $tablerow = array();
        $tablerow[] = htmlspecialchars($myrow['groupname'], ENT_QUOTES,'UTF-8');
        $tablerow[] = $myrow['sectionname'];
        $tablerow[] =  $myrow['sequenceintb'];
        switch ($myrow['pandl']) {
		case -1:
			$PandLText=_('Yes');
			break;
		case 1:
			$PandLText=_('Yes');
			break;
		case 0:
			$PandLText=_('No');
			break;
		} //end of switch statement
        $tablerow[] = $PandLText;
        $tablerow[] = $myrow['parentgroupname'];
        $editrow['content'] = _('Edit');
        $editrow['link'] = htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($myrow['groupname']), ENT_QUOTES,'UTF-8');
        $tablerow[] = $editrow;
        $delrow['content'] = _('Delete');
        $delrow['link'] = htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($myrow['groupname']), ENT_QUOTES,'UTF-8') . '&amp;delete=1';
        $delrow['attributes'] = 'onclick="return confirm(\'' . _('Are you sure you wish to delete this account group?') . '\');"';
        $tablerow[] = $delrow;
        $accountGroupTable->addRow($tablerow);
	} //END WHILE LIST LOOP
	if(!$accountGroupTable->display()) {
        echo "error displaying table";
    }
} //end of ifs and buts!


if (isset($_POST['SelectedAccountGroup']) or isset($_GET['SelectedAccountGroup'])) {
	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Account Groups') . '</a></div>';
}

if (!isset($_GET['delete'])) {

        $accountGroupsForm = $MainView->createForm();
        $accountGroupsForm->id = 'AccountGroups';
        $accountGroupsForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
        $accountGroupsForm->FormID = $_SESSION['FormID'];

	if (isset($_GET['SelectedAccountGroup'])) {
		//editing an existing account group

		$sql = "SELECT groupname,
						sectioninaccounts,
						sequenceintb,
						pandl,
						parentgroupname
				FROM accountgroups
				WHERE groupname='" . $_GET['SelectedAccountGroup'] ."'";

		$ErrMsg = _('An error occurred in retrieving the account group information');
		$DbgMsg = _('The SQL that was used to retrieve the account group and that failed in the process was');
		$result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result) == 0) {
			prnMsg( _('The account group name does not exist in the database'),'error');
			include('includes/footer.inc');
			exit;
		}
		$myrow = DB_fetch_array($result);

		$_POST['GroupName'] = $myrow['groupname'];
		$_POST['SectionInAccounts']  = $myrow['sectioninaccounts'];
		$_POST['SequenceInTB']  = $myrow['sequenceintb'];
		$_POST['PandL']  = $myrow['pandl'];
		$_POST['ParentGroupName'] = $myrow['parentgroupname'];

        $accountGroupsForm->formTitle = _('Edit Account Group Details');
        
	} elseif (!isset($_POST['MoveGroup'])) { //end of if $_POST['SelectedAccountGroup'] only do the else when a new record is being entered

		if (!isset($_POST['SelectedAccountGroup'])){
			$_POST['SelectedAccountGroup']='';
		}
		if (!isset($_POST['GroupName'])){
			$_POST['GroupName']='';
		}
		if (!isset($_POST['SectionInAccounts'])){
			$_POST['SectionInAccounts']='';
		}
		if (!isset($_POST['SequenceInTB'])){
			$_POST['SequenceInTB']='';
		}
		if (!isset($_POST['PandL'])){
			$_POST['PandL']='';
		}
        
        $accountGroupsForm->formTitle = _('New Account Group Details');
	}
    $accountGroupsForm->addHiddenControl('SelectedAccountGroup',$_GET['SelectedAccountGroup']);
    
    $controlsettings['name'] = 'GroupName';
    $controlsettings['value'] = $_POST['GroupName'];
    $controlsettings['placeholder']  = _('Enter the account group name');
    $controlsettings['title'] = _('A unique name for the account group must be entered - at least 3 characters long and less than 30 characters long. Only alpha numeric characters can be used.');
    $controlsettings['autofocus'] = true;
    $controlsettings['required'] = true;
    $controlsettings['data-type'] = 'no-illegal-chars';
    $controlsettings['size'] = 30;
    $controlsettings['minlength'] =3;
    $controlsettings['maxlength'] =30;
    //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null
    $accountGroupsForm->addControl(1,1,'text',_('Account Group Name') . ':',$controlsettings,in_array('GroupName',$Errors) ?  'inputerror' : null);
    unset($controlsettings);
    $controlsettings['name'] = 'ParentGroupName';
    $accountGroupsForm->addControl(2,2,'select',_('Parent Group') . ':',$controlsettings,in_array('ParentGroupName',$Errors) ?  'class="selecterror"' : null );

	$sql = "SELECT groupname FROM accountgroups";
	$groupresult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
    
	if (!isset($_POST['ParentGroupName'])){
        $accountGroupsForm->addControlOption(2,_('Top Level Group'),'',true);
	} else {
        $accountGroupsForm->addControlOption(2,_('Top Level Group'),'');
	}

	while ( $grouprow = DB_fetch_array($groupresult) ) {
        
		if (isset($_POST['ParentGroupName']) AND $_POST['ParentGroupName']==$grouprow['groupname']) {
			$accountGroupsForm->addControlOption(2,htmlspecialchars($grouprow['groupname'], ENT_QUOTES,'UTF-8'),htmlspecialchars($grouprow['groupname'], ENT_QUOTES,'UTF-8'),true);
		} else {
            $accountGroupsForm->addControlOption(2,htmlspecialchars($grouprow['groupname'], ENT_QUOTES,'UTF-8'),htmlspecialchars($grouprow['groupname'], ENT_QUOTES,'UTF-8'));
		}
	}
	
    $controlsettings['name'] = 'SectionInAccounts';
    //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null
    $dimensions['width'] = 8;
    $accountGroupsForm->addControl(3,3,'select',_('Section In Accounts') . ':',$controlsettings,in_array('SectionInAccounts',$Errors) ?  'class="selecterror"' : null,3,null,$dimensions);

	$sql = "SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid";
	$secresult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
	while( $secrow = DB_fetch_array($secresult) ) {
		if ($_POST['SectionInAccounts']==$secrow['sectionid']) {
            //$text,$value,$isSelected = false,$id = null
            $accountGroupsForm->addControlOption(3,$secrow['sectionname'].' ('.$secrow['sectionid'].')',$secrow['sectionid'],true);
		} else {
            //$text,$value,$isSelected = false,$id = null
            $accountGroupsForm->addControlOption(3,$secrow['sectionname'].' ('.$secrow['sectionid'].')',$secrow['sectionid']);
		}
	}
	 //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null
    $controlsettings['name'] = 'PandL';
    $controlsettings['title'] = _('Select YES if this account group will contain accounts that will consist of only profit and loss accounts or NO if the group will contain balance sheet account');
    if ($_POST['PandL']==0) {
        $controlsettings['selectNo'] = true;
    } else {
        $controlsettings['selectYes'] = true;
    }
    //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null 
    $dimensions = 4;
    $accountGroupsForm->addControl(4,4,'yesno',_('Profit and Loss') . ':',$controlsettings,null,3,null,$dimensions);
    unset($controlsettings);
    $controlsettings['name'] = 'SequenceInTB';
    $controlsettings['title'] = _('Enter the sequence number that this account group and its child general ledger accounts should display in the trial balance');
    $controlsettings['value'] = $_POST['SequenceInTB'];
    $controlsettings['required'] = true;
    $controlsettings['maxlength'] = 4;
    //$key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null
    $accountGroupsForm->addControl(5,5,'text', _('Sequence In TB') . ':',$controlsettings,'number');
    unset($controlsettings);
    $controlsettings['name'] = 'submit';
    $accountGroupsForm->addControl(6,6,'submit',_('Enter Information'),$controlsettings);
	
    $accountGroupsForm->display();

} //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>
