<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: rollcheck.php,v 1.1 2006/10/31 00:54:13 dme26 Exp $
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2006
-
- Significant contributions: 
- David Turner, Simon Hopkins and Robert Whittaker.
-
- See genElection.pl for installation/configuration details.
-
- Called by: ballot.html
- Requires : config.php
- Uses     : MySQL
-
- This program is distributed in the hope that it will be useful,
- but WITHOUT ANY WARRANTY; without even the implied warranty of
- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
- GNU General Public License for more details.
-
-->

<?php
  // initialise all variables (i.e. thwart register_globals attacks)
  $dbdb=$dbhost=$dbuser=$electionInfo=$emailRO=$emailTech=$ename=$endBallot=$htmlNotRegistered=$htmlPostBallot=$htmlPreBallot=$htmlRO=$htmlTech=$startBallot=$title=$rfc2289words='';
  require("config.php");
  $title .= ' - roll check page';
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
<?php rollcheckWF(); ?>
    <hr />
    <address><?php echo 'Contact: ',$htmlTech,' or the ',$htmlRO; ?></address>
  </body>
</html>

<?php
function err($e){ echo "<b>ERROR:</b> $e</p>"; }

function rollcheckWF(){
  global $htmlRO,$ename,$crsid,$dbdb,$dbhost,$dbuser,$htmlNotRegistered;

  // set a dummy crsid value if the authentication read is unsuccessful
  ($crsid = $_SERVER['REMOTE_USER']) or ($crsid = "testVoter");

  if(!$dbpass = rtrim(file_get_contents('dbpass'))) return(err('Could not read database password!'));
  if(!$db = mysql_connect($dbhost, $dbuser, $dbpass)) return(err('Error opening database connection!'));
  if(!mysql_select_db($dbdb,$db)) return(err("Error selecting database!"));
  
  echo "<p> Checking voting permission for $crsid ...";
  // Read information about this CRSID's voting from database.
  if(!($result = mysql_query("SELECT crsid,voted FROM ${ename}voter WHERE crsid='$crsid'"))) return(err("Database read failure."));
  if(!($result and $row = mysql_fetch_array($result))) return(print("failed.</p> $htmlNotRegistered"));

  // this voter is listed
  echo "done. You are listed on the electoral roll for this ballot.</p>\n";
}

?>

