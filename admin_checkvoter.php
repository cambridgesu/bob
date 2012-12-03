<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: rollcheck.php 77 2006-10-31 00:54:13Z dme26 $
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2007
-->

<?php
  // initialise all variables (i.e. thwart register_globals attacks)
  $bob = false;
  require("./BOB.php");
  $title .= ' - voter checking page';
?>

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
    .votemsg {border:1px solid #bbbbbb; background: #eeeeee; padding: 4px;}
    </style>
  </head>
  <body>
    <h1><?php echo $title; ?></h1>
<?php
    if($bob->adminOK()){
      $crsid = $_REQUEST['crsid'];
      if($crsid) { 
	$bob->rollcheckWF($crsid);
	echo "<hr />\n";
      }
?><hr />
<form name="checkvoter" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<p>
<label for="crsid">Voter's CRSID to check: </label>
<input type="text" size="10" id="crsid" name="crsid" />
<input type="submit" />
</p>
</form>
<?php
    }else{
?><p>The election is currently in progress, so this service is not available.</p><?php
    } ?>
    <hr />
    <address><?php echo 'Contact: ',$htmlTech,' or the ',$htmlRO; ?></address>
  </body>
</html>
