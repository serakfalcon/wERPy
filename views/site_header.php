<?php
/* default header includes */
    //theme css, to be replaced by theme css when possible
    global $ViewTopic;
    global $BookMark;
    global $strictXHTML;
	$ViewTopic = isset($ViewTopic)?'?ViewTopic=' . $ViewTopic : '';
	$BookMark = isset($BookMark)? '#' . $BookMark : '';
	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	if($Title == _('Copy a BOM to New Item Code')){//solve the cannot modify heaer information in CopyBOM.php scritps
		ob_start();
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><title><?php echo $Title; ?></title>
<link rel="shortcut icon" href="<?php echo $RootPath; ?>/favicon.ico" />
<link rel="icon" href="<?php echo $RootPath; ?>/favicon.ico" />
<?php
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
	echo '<link href="' . $this->getStyleLink() .'/default.css" rel="stylesheet" type="text/css" />';
?>
<script type="text/javascript" src = "<?php echo $RootPath; ?>/javascripts/MiscFunctions.js"></script>
<script type="text/javascript" src = "<?php echo $RootPath; ?>/javascripts/filterchildselect.js"></script>