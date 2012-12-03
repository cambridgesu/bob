<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: vote.php,v 1.17 2006/11/09 03:03:34 dme26 Exp $
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
- Token word list Copyright The Internet Society (1998).
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
<?php // Start ballot workflow
  $openTrans = false;
  voteWF();
  $openTrans and (mysql_query("ROLLBACK") or err("Unable to roll back the database transaction."));
?>
    <hr />
    <address><?php echo 'Contact: ',$htmlTech,' or the ',$htmlRO; ?></address>
  </body>
</html>

<?php
function err($e){ echo "<b>ERROR:</b> $e</p>"; }

function voteWF(){
  global $htmlRO,$ename,$crsid,$emailTech,$emailRO,$title,$electionInfo,$dbdb,$dbhost,$dbuser,$dbuser,$startBallot,$endBallot,$htmlPreBallot,$htmlPostBallot,$htmlNotRegistered,$openTrans,$rfc2289words;

  // set a dummy crsid value if the authentication read is unsuccessful
  ($crsid = $_SERVER['REMOTE_USER']) or ($crsid = "testVoter");
  mt_srand();

  // check that the ballot is open.
  $time = time();
  if($startBallot and ($startBallot >= $time)) return(print($htmlPreBallot));
  if($endBallot and ($endBallot <= $time)) return(print($htmlPostBallot));

  if($_REQUEST['confirmvote']!='on') return(print("<p>You have not confirmed your vote. Please click your web browser's back button <a href=\"ballot.html\">to return to the ballot page</a>.</p>"));

  if(!$dbpass = rtrim(file_get_contents('dbpass'))) return(err('Could not read database password!'));
  if(!$db = mysql_connect($dbhost, $dbuser, $dbpass)) return(err('Error opening database connection!'));
  if(!mysql_select_db($dbdb,$db)) return(err("Error selecting database!"));
  
  echo "<p> Checking voting permission for $crsid ...";
  // Read information about this CRSID's voting from database.
  if(!($result = mysql_query("SELECT crsid,voted FROM ${ename}voter WHERE crsid='$crsid'"))) return(err("Database read failure."));
  if(!($result and $row = mysql_fetch_array($result))) return(print("failed.</p> $htmlNotRegistered"));
  if($row['voted']!=0) return(print("our records indicate that you have already voted. Contact the $htmlRO if you disagree.</p>"));

  // this voter is listed and hasn't voted
  echo "you are allowed to vote.</p>\n";
  // generate token
  $token="";
  for($i=0; $i<4; $i++){
    $token.=(($i==0)?'':' ').$rfc2289words[mt_rand(0,2047)];
  }
  echo '<p>Recording your vote ...';
  // find _REQUEST fields of the form v[voteNumber][preferenceValue]=candidateNumber
  $coln="token";
  $colv="'$token'";
    
  foreach($_REQUEST['v'] as $k1=>$v1) {
    if(is_array($v1)){
      foreach($v1 as $k2=>$v2){
	if(!is_array($v2)){
	  $v2 = is_numeric($v2)?((int)$v2):0;
          $k1 = (int)$k1;
          $k2 = (int)$k2;
	  $coln.=",v${k1}c${k2}";
	  $colv.=",$v2";
	}
      }
    }
  }
    
  // Start transaction.
  if(!($openTrans = mysql_query("BEGIN WORK"))) return(err("Failed to start database transaction."));

  // record data from the ballot HTML form along with random token.
  if(!(mysql_query("INSERT INTO ${ename}vote ($coln) VALUES ($colv)"))
     or mysql_affected_rows() != 1) return(err("Database vote insert failure."));

  // write of ballot to database was OK.
  echo "done.</p><p>Updating your status as having voted ...";

  // modify the voter table to indicate this vote has been cast
  if(!(mysql_query("UPDATE ${ename}voter SET voted='1' WHERE crsid='$crsid' AND voted='0'"))
     or mysql_affected_rows() != 1) return(err("Recording voter as having voted failed."));
  if(!(mysql_query("COMMIT"))) return(err("Transaction failed to commit."));
  $openTrans = false;

  // update of voter having voted was successful
  echo <<<EOF
done.</p>
<p>Our database now indicates that it has successfully recorded your vote and, separately, that you have voted.</p>

<p>We will now attempt to read back your vote from our database, and email it to the returning officer, blind-carbon-copied (BCC) to your Hermes address.
In the highly unusual case that there is a failure somewhere in the remainder of this voting process, you should keep a record of your proof-of-voting token "<b>$token</b>" and use it to check your vote really was recorded correctly when the count sheet is posted up after voting has closed.</p>

<p>Reading back your vote ...
EOF;

  // create email body containing ballot information
  if(!($result = mysql_query("SELECT * FROM ${ename}vote WHERE token='$token'"))) return(err("Vote read-back failed (1)."));
  if(!($row = mysql_fetch_array($result))) return(err("Vote read-back failed (2)."));

  $message=<<<EOF
Below you will find a record of each of the selections you made on the
ballot web-page in order. Each ballot choice is represented in a
computer-parsable representation, with an equivalent verbal description
to the right of each equals sign. Your voting token is "$token".
 

EOF;

  foreach ($row as $k => $v){
    if(!is_numeric($k) && is_numeric($v)){
      if(preg_match('/\Av(\d+)c(\d+)\z/',$k,$matches)){
	$thisPosition = $electionInfo[$matches[1]-1][0];
	$thisPreference = $matches[2];
	$thisCandidate = $v ? $electionInfo[$matches[1]-1][$v] : "(no candidate)";
	$message.="$k: $v";
	if($thisPosition and $thisCandidate){
	  $message.=" = Give preference $thisPreference to $thisCandidate for $thisPosition.";
	}
	$message.="\n";
      }
    }
  }

  echo <<<EOF
done.</p>

<p>If you do not receive a confirmation email containing the text in the box below within a minute or two, we recommend that you save or print this webpage as an alternative personal record of your vote.</p>

<div class="votemsg">
<pre>
$message
</pre>
</div>

<p>Emailing your vote to the $htmlRO and blind-carbon-copying $crsid@cam.ac.uk ...
EOF;

  if(!(mail($emailRO,$title,$message,"From: $emailTech\r\nBCC: $crsid@cam.ac.uk\r\n"))) return(err("Enqueue email to voter failed."));
  echo "voting confirmation email successfully enqueued.</p><p><b>Voting process has successfully completed.</b></p>";
}

?>

