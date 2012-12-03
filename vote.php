<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: vote.php 109 2007-02-10 22:45:50Z dme26 $
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2007
-
- Significant contributions: 
- David Turner, Simon Hopkins, Robert Whittaker and
- Martin Lucas-Smith.
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
  $bob = new BOB($dbdb,$dbhost,$dbuser,$electionInfo,$emailRO,$emailTech,$ename,$endBallot,$htmlNotRegistered,$htmlPostBallot,$htmlPreBallot,$htmlRO,$htmlTech,$startBallot,$title,$rfc2289words);
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
<?php $bob->voteWF(); /* Start ballot workflow */ ?>
    <hr />
    <address><?php echo 'Contact: ',$htmlTech,' or the ',$htmlRO; ?></address>
  </body>
</html>

<?php
class BOB {
  // constructor
  function BOB($dbdb,$dbhost,$dbuser,$electionInfo,$emailRO,$emailTech,$ename,$endBallot,$htmlNotRegistered,$htmlPostBallot,$htmlPreBallot,$htmlRO,$htmlTech,$startBallot,$title,$rfc2289words) {
    $this->dbdb = $dbdb;
    $this->dbhost = $dbhost;
    $this->dbuser = $dbuser;
    $this->electionInfo = $electionInfo;
    $this->emailRO = $emailRO;
    $this->emailTech = $emailTech;
    $this->ename = $ename;
    $this->endBallot = $endBallot;
    $this->htmlNotRegistered = $htmlNotRegistered;
    $this->htmlPostBallot = $htmlPostBallot;
    $this->htmlPreBallot = $htmlPreBallot;
    $this->htmlRO = $htmlRO;
    $this->htmlTech = $htmlTech;
    $this->startBallot = $startBallot;
    $this->title = $title;
    $this->rfc2289words = $rfc2289words;
  }

  function err($e) {
    return fail("<b>ERROR:</b> $e</p>");
  }
  function fail($e) { 
    echo $e;
    return false;
  }
  
  // public entry point for voting workflow
  function voteWF(){
    $openTrans = false;
    $retval = voteWFinternal($openTrans);
    $openTrans and (mysql_query("ROLLBACK") or err("Unable to roll back the database transaction."));
    return $retval;
  }

  // internal voting workflow
  function voteWFinternal(&$openTrans){
    // set a dummy crsid value if the authentication read is unsuccessful
    ($crsid = $_SERVER['REMOTE_USER']) or ($crsid = "testVoter");
    mt_srand();

    // check that the ballot is open.
    $time = time();
    if($this->startBallot and ($this->startBallot >= $time)) return(fail($this->htmlPreBallot));
    if($this->endBallot and ($this->endBallot <= $time)) return(fail($this->htmlPostBallot));
    
    if($_REQUEST['confirmvote']!='on') return(fail("<p>You have not confirmed your vote. Please click your web browser's back button <a href=\"ballot.html\">to return to the ballot page</a>.</p>"));
    
    if(!$dbpass = rtrim(file_get_contents('dbpass'))) return(err('Could not read database password!'));
    if(!$db = mysql_connect($this->dbhost, $this->dbuser, $dbpass)) return(err('Error opening database connection!'));
    if(!mysql_select_db($this->dbdb,$db)) return(err("Error selecting database!"));
    
    echo "<p> Checking voting permission for $crsid ...";
    // Read information about this CRSID's voting from database.
    if(!($result = mysql_query("SELECT crsid,voted FROM {$this->ename}voter WHERE crsid='$crsid'"))) return(err("Database read failure."));
    if(!($result and $row = mysql_fetch_array($result))) return(fail("failed.</p> $this->htmlNotRegistered"));
    if($row['voted']!=0) return(fail("our records indicate that you have already voted. Contact the $this->htmlRO if you disagree.</p>"));
    
    // this voter is listed and hasn't voted
    echo "you are allowed to vote.</p>\n";
    // generate token
    $token="";
    for($i=0; $i<4; $i++){
      $token.=(($i==0)?'':' ').$this->rfc2289words[mt_rand(0,2047)];
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
    if(!($this->openTrans = mysql_query("BEGIN WORK"))) return(err("Failed to start database transaction."));
    
    // record data from the ballot HTML form along with random token.
    if(!(mysql_query("INSERT INTO {$this->ename}vote ($coln) VALUES ($colv)"))
       or mysql_affected_rows() != 1) return(err("Database vote insert failure."));
    
    // write of ballot to database was OK.
    echo "done.</p><p>Updating your status as having voted ...";
    
    // modify the voter table to indicate this vote has been cast
    if(!(mysql_query("UPDATE {$this->ename}voter SET voted='1' WHERE crsid='$crsid' AND voted='0'"))
       or mysql_affected_rows() != 1) return(err("Recording voter as having voted failed."));
    if(!(mysql_query("COMMIT"))) return(err("Transaction failed to commit."));
    $this->openTrans = false;
    
    // update of voter having voted was successful
    echo <<<EOF
done.</p>
<p>Our database now indicates that it has successfully recorded your vote and, separately, that you have voted.</p>

<p>We will now attempt to read back your vote from our database, and email it to the returning officer, blind-carbon-copied (BCC) to your Hermes address.
In the highly unusual case that there is a failure somewhere in the remainder of this voting process, you should keep a record of your proof-of-voting token "<b>$token</b>" and use it to check your vote really was recorded correctly when the count sheet is posted up after voting has closed.</p>

<p>Reading back your vote ...
EOF;

    // create email body containing ballot information
    if(!($result = mysql_query("SELECT * FROM {$this->ename}vote WHERE token='$token'"))) return(err("Vote read-back failed (1)."));
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
	  $thisPosition = $this->electionInfo[$matches[1]-1][0];
	  $thisPreference = $matches[2];
	  $thisCandidate = $v ? $this->electionInfo[$matches[1]-1][$v] : "(no candidate)";
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

<p>Emailing your vote to the $this->htmlRO and blind-carbon-copying $crsid@cam.ac.uk ...
EOF;

    if(!(mail($this->emailRO,$this->title,$message,"From: $this->emailTech\r\nBCC: $crsid@cam.ac.uk\r\n"))) return(err("Enqueue email to voter failed."));
    echo "voting confirmation email successfully enqueued.</p><p><b>Voting process has successfully completed.</b></p>";
    return true;
  }
}

?>
