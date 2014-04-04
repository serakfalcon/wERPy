<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?php echo $this->title; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" href="<?php echo $this->cssLink;?>/login.css" type="text/css" />
    </head>
    <body>
        <div id="container">
            <?php echo $this->ErrorMessage; ?>
        </div>
    </body>
</html>