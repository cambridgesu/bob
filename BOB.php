<!--
- $Id: BOB.php 131 2007-02-20 17:34:40Z dme26 $
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2007
-
- Significant contributions: [*1]
- David Turner, Simon Hopkins, Robert Whittaker and
- Martin Lucas-Smith.
-
- See genElection.pl for installation/configuration details.
-
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
- [*1] but almost certainly not responsible for any nasty code.
-->

<?php
class BOB {
  // constructor
  function BOB($dbdb,$dbhost,$dbuser,$electionInfo,$emailRO,$emailTech,$ename,$endBallot,$htmlNotRegistered,$htmlPostBallot,$htmlPreBallot,$htmlRO,$htmlTech,$startBallot,$title,$adminDuringElectionOK,$positionInfo,$ro,$eOfficials,$viewBallot) {
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
    $this->adminDuringElectionOK = $adminDuringElectionOK;
    $this->positionInfo = $positionInfo;
    $this->ro = trim ($ro);
    $this->eOfficials = $eOfficials;
	$this->viewBallot = $viewBallot;

    // set a dummy crsid value if the authentication read is unsuccessful
    ($this->crsid = $_SERVER['REMOTE_USER']) or ($this->crsid = "testVoter");
    $this->loadtime = time();
  }

  // open the database
  function openDB() {
    if(!$dbpass = rtrim(file_get_contents('./dbpass'))) return($this->err('Could not read database password!'));
    if(!$db = mysql_connect($this->dbhost, $this->dbuser, $dbpass)) return($this->err('Error opening database connection!'));
    if(!mysql_select_db($this->dbdb,$db)) return($this->err("Error selecting database!"));
    return true;
  }

  // check whether a given voter (or the current user) is permitted to vote and has not already
  function checkVotingPermission($c = false){
	if ((!$c) && $this->userIsElectionOfficial()) {return true;}
    if(!$c) $c=$this->crsid;
    echo "<p> Checking voting permission for $c ...";
    // Read information about this CRSID's voting from database.
    if(!($result = mysql_query("SELECT crsid,voted FROM {$this->ename}voter WHERE crsid='$c'"))) return($this->err("Database read failure."));
    if(!($result and $row = mysql_fetch_array($result))) return($this->fail("failed.</p> $this->htmlNotRegistered"));
    if($row['voted']!=0) return($this->fail("our records indicate that you have already voted. Contact the $this->htmlRO if you disagree.</p>"));
    echo "you are allowed to vote.</p>\n"; // this voter is listed and hasn't voted
    return true;
  }
  
  // check whether the current user is an election official in the list
  function userIsElectionOfficial(){
  	$c=$this->crsid;
	$officials = explode(' ', trim($this->eOfficials));
	$userIsElectionOfficial = (in_array ($c,$officials));
	return $userIsElectionOfficial;
  }

  function err($e) {
    return $this->fail("<b>ERROR:</b> $e</p>");
  }
  function fail($e) { 
    echo $e;
    return false;
  }
  
  // check whether doing admin actions is appropriate
  function adminOK(){
    return $this->adminDuringElectionOK || $this->beforeElection() || $this->afterElection();
  }

  function afterBallotView(){ return $this->viewBallot < $this->loadtime; }
  function afterElection(){ return $this->endBallot < $this->loadtime; }
  function beforeElection(){ return $this->loadtime < $this->startBallot; }
  function duringElection(){ return $this->loadtime >= $this->startBallot && $this->endBallot >= $this->loadtime; }

  // public entry point for voting workflow
  function voteWF(){
	
    // check that the ballot is open.
    if($this->beforeElection()) return($this->fail($this->htmlPreBallot));
    if($this->afterElection()) return($this->fail($this->htmlPostBallot));
    
	// open the database or end
    if(!$this->openDB()) return false;
	
	// Check whether the user is in the list and whether they've already voted
    if(!$this->checkVotingPermission()) return false;
	
	// Remind election officials that they cannot vote
	if ($this->userIsElectionOfficial ()) {echo '<p style="color: red;">Note: you are accessing this as an election official. As such, you cannot vote, only see the voting screen.</p>';}
	
	// Check for any problems if the form has been posted
	$problems = array ();
    if (!empty ($_POST)) {
		
		// Checks done to prevent invalid votes from crafted forms being cast
		
		// Define what a referendum looks like in terms of the available candidates
		$referendumCandidates = array ('0' => '(blank)', '1' => 'Yes', '2' => 'No');
		
		// Confirm that what is posted matches the list of candidates in the config file
		// Loop through each vote set specified in the config file
		foreach ($this->electionInfo as $voteSet => $candidates) {
			$voteSet = $voteSet + 1;	// Adjust the array indexing - the generated <select> boxes start at [1] not [0]
			
			// Loop through each candidate specified in the config file
			foreach ($candidates as $candidateNumber => $candidate) {
				
				// Skip the first 'candidate' as that is actually a heading
				if ($candidateNumber == 0) {continue;}
				
				// Determine whether electionInfo->voteSet->candidateNumber exists, i.e. that _POST['v'][candidateNumber] exists, as it should
				$structureOk = (isSet ($_POST['v']) && is_array ($_POST['v']) && isSet ($_POST['v'][$voteSet]) && is_array ($_POST['v'][$voteSet]) && isSet ($_POST['v'][$voteSet][$candidateNumber]));
				
				// If the structure does not match, then the user has probably posted a user-crafted form; set the error message and break out of the inner and outer loop
				if (!$structureOk) {
					$problems[] = "Your browser does not appear to be submitting the entire page. Please try again.";
					break 2;
				}
				
				// Ensure that the selection given is in the list in the config file, e.g. 0 for no vote or 1/2/3 for candidates 1/2/3
				$availableCandidates = array_keys (($candidate == 'referendum') ? $referendumCandidates : $candidates);
				if (!in_array ($_POST['v'][$voteSet][$candidateNumber], $availableCandidates)) {
					$problems[] = "Your browser appeared to post a non-existent option. Please try again.";
					break 2;
				}
				
				// Ensure that no additional preference have been added in, to ensure that eventually submitted data matches the database structure
				$expectedPreferencesTotal = (($candidate == 'referendum') ? 1 : count ($candidates) - 1);
				if (count ($_POST['v'][$voteSet]) != $expectedPreferencesTotal) {	// _POST['v'][$voteSet] is already confirmed as an array if this code is reached
					$problems[] = "Your browser appeared to post more preferences than exist. Please try again.";
					break 2;
				}
			}
			
			// Ensure that no additional votesets have been added in, to ensure that eventually submitted data matches the database structure
			if (count ($_POST['v']) != count ($this->electionInfo)) {	// _POST['v'] is already confirmed as an array if this code is reached
				$problems[] = "Your browser appeared to post more votes than exist. Please try again.";
				break;
			}
			
			// By this point, we know we are using a non-crafted form (or a crafted form which contains the same submittable values, pointlessly), else we would be out of the loop
			// Do the following checks as recommended by an ERS member:
			
			
			// 1. Prevent a voter putting the same candidate twice
			
			// Check for duplicate non-0 values - using the function at http://www.php.net/manual/en/function.array-unique.php#28892
			$checkKeysUniqueComparison = create_function('$value','if ($value > 1) return true;');
			// Take the 0's out of the cast votes for checking purposes
			$votesCast = array ();
			foreach ($_POST['v'][$voteSet] as $key => $value) {
				if ($value == '0') {continue;}	// Next item in loop
				$votesCast[$key] = $value;
			}
			// Check for duplicated values
			$duplicateValues = array_keys (array_filter (array_count_values ($votesCast), $checkKeysUniqueComparison)); 
			if ($duplicateValues) {
				$problems[] = "You set a candidate twice, in the '{$candidates[0]}' vote.";
			}
			
			
			// 2. Prevent a voter leaving out a preference in sequence
			$allZeroSoFar = true;
			foreach ($_POST['v'][$voteSet] as $key => $value) {
				if ($value == '0') {
					$allZeroSoFar = false;
					continue;	// Next item in loop
				}
				if ((!$allZeroSoFar) && ($value != '0')) {
					$problems[] = "You left out a preference in sequence, in the '{$candidates[0]}' vote.";
					break;	// Don't bother checking any more
				}
			}
		}
		
		// Ensure that the voter has confirmed their vote
		if (!isSet ($_POST['confirmvote']) || $_POST['confirmvote']!='on') {
	    	$problems[] = "You have not confirmed your vote.";
		}
    	
		// Show any problems if found
		if ($problems) {
			echo '<div class="problem">';
			echo "<p>The following " . (count ($problems) == 1 ? 'problem was' : 'problems were') . " found:</p>";
			echo '<ul><li>' . implode ('</li><li>', $problems) . '</li></ul>';
			echo "<p>The vote has therefore not been cast yet. <strong>Please correct the " . (count ($problems) == 1 ? 'problem' : 'problems') . " in the form below and try again.</strong></p>";
			echo '</div>';
		}
	}
	
	// Show the ballot page if not posted or problems found, then end
	if (empty ($_POST) || $problems) {
		$this->ballotPage();
		return;
	}
	
	// Election officials cannot vote
	if ($this->userIsElectionOfficial ()) {return false;}
	
	// add the vote
    $openTrans = false;
    $retval = $this->voteWFinternal($openTrans);
    $openTrans and (mysql_query("ROLLBACK") or $this->err("Unable to roll back the database transaction."));
    return $retval;
  }

  // Ballot page
  function ballotPage(){
    
	echo <<<EOF
	<p>This system provides a means to forward your anonymised votes to the returning officer, who for this election is {$this->ro}. <a href="#disclaimer">Please read the disclaimer below</a>.  The Returning Officer will be able to see who has voted but will not be able to tell who has cast which votes.</p>
	<p>When you have successfully placed your vote, you will be emailed a sequence of random-looking short words - your "voting token". This system does not store the connection between your voting token and your identity, however it does email your voting token alongside your vote to the returning officer (and store it in a database to protect against email fraud).  When polls have closed, the list of all the votes cast will be made available - because only you will know your voting token, you will be able to check that your vote was correctly included.</p>	<hr />
	
	<h3>How to vote</h3>
	<p>Voting is by the Single Transferable Vote system described in Chapter 1 of the Ordinances of the University of Cambridge.</p>
	<ul>
		<li>Next to number 1 (in the preference column for a given post), select the name of the candidate to whom you give your first preference (using the pull-down selection menu controls).</li>
		<li>You may also enter, against preference ranks 2, 3 and so on, the names of other candidates in the order you wish to vote for them.</li>
		<li>Continue until you have voted for those candidates you wish to vote for, and leave any remaining boxes blank. You are under no obligation to vote for all candidates.</li>
		<li>Repeat this process for each post listed.</li>
		<li>Some elections may list a candidate named "RON". This acronym expands to "Re-Open Nominations". You may vote for RON as you would any other candidate. Should RON be 'elected', the position will be re-opened, and will be decided at a subsequent election.</li>
		<li>The order of your preferences is crucial. Later preferences will only be considered if an earlier preference has qualified for election or has been eliminated from the election due to gaining an insufficient number of votes. </li>
		<li>When you have completed this form CHECK IT.</li>
		<li>When you have checked the form, click on the 'Cast my vote' button.</li>
	</ul>
EOF;
	
	echo '<form action="vote.php" method="post">',"\n\n";
	
	$i = 0;	// Start a count of vote groups
	foreach ($this->electionInfo as $options) {	// Loop through each vote group
		
		$i++;	// Advance the vote group counter
		if (!$options) {continue;}	// If the array is empty, move on
		
		# Set the heading
		echo "<h2>{$options[0]}</h2>\n";
		
		// Replace the heading as the blank option
		$options[0] = '(blank)';
		
		// Define the number of boxes
		$boxes = count ($options) - 1;	// Number of boxes should match the number of candidates, minus the blank option
	  	
		// Deal with the special case of a referendum, and define what a referendum looks like in terms of the available candidates
		$isReferendum = ($options[1] == 'referendum');
		if ($isReferendum) {
			$options = array (0 => '(blank)', 1 => 'Yes', 2 => 'No');
			$boxes = 1;
		}
		
		// Create the HTML, creating as many boxes as requested
		echo "<table class=\"vote v{$i}\">\n";
		if (count ($options) > 11) {echo "<p class=\"comment\">Note: there are " . (count ($options) - 1) . " candidates standing in this election. Your browser may require you to scroll to see all.</p>";}	// IE6/Win in Classic Theme, i.e. not XP standard, only displays 11 options at once
		if (!$isReferendum) {echo "\t\t<tr>\n\t\t\t<th>Preference</th>\n\t\t\t<th>Candidate</th>\n\t\t</tr>\n";}
		for ($box = 1; $box <= $boxes; $box++) {
			
			// Determine what option has been selected for this box, if any
			$itemChosen = ((isSet ($_POST['v']) && is_array ($_POST['v']) && isSet ($_POST['v'][$i]) && is_array ($_POST['v'][$i]) && isSet ($_POST['v'][$i][$box])) ? $_POST['v'][$i][$box] : '');
			
			// Create the option boxes
			$selectOpts = '';
			foreach ($options as $index => $option) {
			    $selectOpts .= "\t\t\t\t\t<option value=\"{$index}\"" . ($index == $itemChosen ? ' selected="selected"' : '') . ">" . htmlentities ($option) . "</option>\n";
			}
		    echo "\t\t<tr class=\"c{$box} " . (($box % 2) ? 'codd' : 'ceven' ) . "\">\n\t\t\t" . ($isReferendum ? '<th>Referendum decision:</th>' : "<td class=\"preference\">{$box}</td>") . "\n\t\t\t<td class=\"candidate\">\n\t\t\t\t<select name=\"v[{$i}][{$box}]\" onmousewheel=\"PreventScroll(event);\">\n{$selectOpts}\t\t\t\t</select>\n\t\t\t</td>\n\t\t</tr>\n";
		}
		echo "</table>\n\n";
	}
	
	echo <<<EOF
		<hr />
		<p><font color="red"><b>Please double-check your choices before submitting your vote!</b></font> Due to the anonymity built into this voting system, it is not possible to correlate your response after you vote.</p>
		<input type="checkbox" name="confirmvote" id="confirmvote" /><label for="confirmvote">I have checked my vote.</label>
		<p>After you click "Cast my vote", your vote will be passed anonymously to the Returning Officer. You will receive a blind copy by email. This will allow you to check we have recorded your vote correctly by confirming to yourself that the printed sheets that will be posted after the votes have been counted. Any queries should be directed to the Returning Officer.</p>
		<p><input value="Cast my vote" type="submit" /></p>
	</form>
	
	<hr />
	
	<a name="disclaimer"></a>
	<p><b>Disclaimer:</b> The (extremely minimal) software behind this voting system has been checked independently, and has been agreed to be a system which should avoid, but will at least detect voting irregularities. The service is hosted on a computer that is not under the direct administrative control of the organisation running the election. Evidence can be acquired from external system administrators that the software is not modified during the election.  If you do not trust this system, you are advised to contact the Returning Officer. As stated in the GPL license, this software comes with no guarantees.  Feel free to examine the PHP code that drives the various pages:</p>
EOF;
	
	echo '<ul>';
	$files = array('vote','BOB','config');
	foreach($files as $f){
	  $md5 = md5 (file_get_contents ("./$f.php"));
	  echo "<li><a href=\"{$f}.txt\" target=\"_blank\" title=\"[Link opens in a new window]\">{$f}.php</a> has MD5 sum <kbd>{$md5}</kbd></li>";
	}
	echo '</ul>';
  }
  
  
  // Function to generate a unique token
  function generateUniqueToken ()
  {
	// Check that the token isn't already in use
	$tokenChosen = false;
	while (!$tokenChosen) {
		$token = $this->generateToken();
	    if(!($result = mysql_query("SELECT COUNT(token) AS total FROM {$this->ename}vote WHERE token='$token'"))) return($this->err("Token checking failed. The vote submission could not proceed."));
	    if(!($row = mysql_fetch_array($result))) return($this->err("Token checking failed (2). The vote submission could not proceed."));
		if ($row['total'] == '0') {$tokenChosen = true;}	// If there are no matching tokens, then accept this one
	}
	
	return $token;
  }
  
  // Function to generate a (potentially non-unique) token
  function generateToken ($words = 4){
    $token='';
    for($i=0; $i<$words; $i++){
      $token.=(($i==0)?'':' ').$this->rfc2289words[mt_rand(0,2047)];
    }
    return $token;
  }
  
  
  // internal voting workflow
  function voteWFinternal(&$openTrans){
    mt_srand();

    // generate token
    if (!$token = $this->generateUniqueToken()) {return false;}
	echo '<p>Recording your vote ...';
    // find _POST fields of the form v[voteNumber][preferenceValue]=candidateNumber
    $coln="token";
    $colv="'$token'";
    
	// Loop through the (now-validated) POST array
    foreach($_POST['v'] as $k1=>$v1) {
      if(is_array($v1)){
		foreach($v1 as $k2=>$v2){
		  if(!is_array($v2)){
		    $v2 = is_numeric($v2)?((int)$v2):0;
		    $k1 = (int)$k1;
		    $k2 = (int)$k2;
		    $coln.=",v${k1}p${k2}";
		    $colv.=",$v2";
		  }
		}
      }
    }
    
    // Start transaction.
    if(!($openTrans = mysql_query("BEGIN WORK"))) return($this->err("Failed to start database transaction."));
    
    // record data from the ballot HTML form along with random token.
    if(!(mysql_query("INSERT INTO {$this->ename}vote ($coln) VALUES ($colv)"))
       or mysql_affected_rows() != 1) return($this->err("Database vote insert failure."));
    
    // modify the voter table to indicate this vote has been cast
    if(!(mysql_query("UPDATE {$this->ename}voter SET voted='1' WHERE crsid='$this->crsid' AND voted='0'"))
       or mysql_affected_rows() != 1) return($this->err("Recording voter as having voted failed. As such, the vote itself has not been stored either."));
	if(!(mysql_query("COMMIT"))) return($this->err("Transaction failed to commit."));
    $openTrans = false;
    
    // write of ballot to database was OK.
    echo "done.</p><p>Updating your status as having voted ...";
    
    // update of voter having voted was successful
    echo <<<EOF
done.</p>
<p>Our database now indicates that it has successfully recorded your vote and, separately, that you have voted.</p>

<p>We will now attempt to read back your vote from our database, and email it to the returning officer, blind-carbon-copied (BCC) to your @cam address.
In the highly unusual case that there is a failure somewhere in the remainder of this voting process, you should keep a record of your proof-of-voting token "<b>$token</b>" and use it to check your vote really was recorded correctly when the count sheet is posted up after voting has closed.</p>

<p>Reading back your vote ...
EOF;

    // create email body containing ballot information
    if(!($result = mysql_query("SELECT * FROM {$this->ename}vote WHERE token='$token'"))) return($this->err("Vote read-back failed (1)."));
    if(!($row = mysql_fetch_array($result))) return($this->err("Vote read-back failed (2)."));
    
    $message=<<<EOF
Below you will find a record of each of the selections you made on the
ballot web-page in order. Each ballot choice is represented in a
computer-parsable representation, with an equivalent verbal description
to the right of each equals sign. Your voting token is "$token".

You should not disclose this e-mail or your voting token to others.


EOF;

    foreach ($row as $k => $v){
      if(!is_numeric($k) && is_numeric($v)){
	if(preg_match('/\Av(\d+)p(\d+)\z/',$k,$matches)){
	  $thisPosition = $this->electionInfo[$matches[1]-1][0];
	  $thisPreference = $matches[2];
	  $message.="$k: $v";
	  if($this->electionInfo[$matches[1]-1][1] == 'referendum'){
	    switch($v){
	    case 1:
	      $message.=" = Vote in favour of referendum ";
	      break;
	    case 2:
	      $message.=" = Vote against referendum ";
	      break;
	    default:
	      $message.=" = Abstain from voting in referendum ";
	    }
	    $message.="$thisPosition.";
	  }else{
	    $thisCandidate = $v ? $this->electionInfo[$matches[1]-1][$v] : "(no candidate)";
	    if($thisPosition and $thisCandidate){
	      $message.=" = Give preference $thisPreference to $thisCandidate for $thisPosition.";
	    }
	  }
	  $message.="\n";
	}
      }
    }
    
    echo <<<EOF
done.</p>

<p>If you do not receive a confirmation email containing the text in the box below within a minute or two, we recommend that you save or print this webpage as an alternative personal record of your vote.</p>
<p>You should not disclose this e-mail or your voting token to others.</p>
<p><strong>When you have finished reading this page, including text below, you should ideally <a href="logout.html">logout</a> then close your browser.</strong></p>

<div class="votemsg">
<pre>
$message
</pre>
</div>

<p>Emailing your vote to the $this->htmlRO and blind-carbon-copying $this->crsid@cam.ac.uk ...
EOF;

    if(!(mail($this->emailRO,$this->title,$message,"From: $this->emailTech\r\nBCC: $this->crsid@cam.ac.uk\r\n"))) return($this->err("Enqueue email to voter failed."));
    echo "voting confirmation email successfully enqueued.</p><p><b>Voting process has successfully completed.</b></p>";
    return true;
  }

  // public entry point for checking the electoral roll
  function rollcheckWF($c = ''){
    if(!$this->openDB()) { return false; }
	$c = addslashes ($c);
	return $this->checkVotingPermission($c);
  }

  // admin entry to return current vote count
  function votecount(){
    if(!$this->openDB()) { return false; }
    if(!($result = mysql_query("SELECT count(*) FROM {$this->ename}vote"))) return($this->err("Vote count failed (1)."));
    if(!($row = mysql_fetch_row($result))) return($this->err("Vote count failed (2)."));
    return $row[0];
  }

  // explain the candidate <-> numerical identifier relationship
  function voteDataKey(){
    echo "<pre>\n";
    foreach( $this->electionInfo as $c => $pos){
      echo "v".(1+$c)." is the election for position: ".array_shift($pos)."\n";
      foreach( $pos as $p => $name){
	echo "   candidate number ".(1+$p)." is $name\n";	
      }
    }
    echo "</pre>\n";
    //$this->electionInfo[$matches[1]-1][0];
  }

  // return an array containing the election data
  function getVoteData(){
    if(!$this->openDB()) { return false; }
    if(!($result = mysql_query("SELECT * FROM {$this->ename}vote ORDER BY token"))) return($this->err("Vote list read failed."));
    $electionData=array();
    $prefsizes = array();
    foreach( $this->electionInfo as $c => $pos){
      array_push($prefsizes,count($pos)-1);
    }
    while($row = mysql_fetch_assoc($result)){
      $a2 = array(); // array($row['token']);
      foreach( $prefsizes as $v => $num ){
		$a = array();
		for( $i=1; $i<=$num; $i++){
		  array_push($a,$row["v".(1+$v)."p{$i}"]);
		}
		array_push($a2,$a);
      }
      $electionData[$row['token']] = $a2;
    }
    return $electionData;
  }

  // print out the votes that have been cast
  function listvotes(){
    if(!$this->openDB()) { return false; }
    if(!($result = mysql_query("SELECT * FROM {$this->ename}vote ORDER BY token"))) return($this->err("Vote list read failed."));

    echo "<pre>\n";
    printf("%22s",mysql_field_name($result,0));    
    for($count=1; $count<mysql_num_fields($result); $count++){
      printf(",%5s",mysql_field_name($result,$count));
    }
    echo "\n";
    $count=0;
    while($row = mysql_fetch_row($result)){
      printf("%22s",array_shift($row));
      foreach ($row as $k => $v){
	printf(",%5d",$v);
      }
      $count++;
      echo "\n";
    }
    echo "</pre><p>Total number of votes cast was $count</p>";
	
    if(!($result = mysql_query("SELECT count(*) as total FROM {$this->ename}voter"))) return($this->err("Vote total read failed."));
	if(!($row = mysql_fetch_array($result))) return($this->err("Vote total read failed (2)."));
	echo "<p>Total number of voters on the roll is: {$row['total']}</p>";
	
    return true;
  }

  // print out the voters that have voted
  function listvoters(){
    if(!$this->openDB()) { return false; }
    if(!($result = mysql_query("SELECT crsid FROM {$this->ename}voter WHERE voted='1' ORDER BY crsid"))) return($this->err("Vote list read failed."));

    echo "<pre>";
    $count=0;
    while($row = mysql_fetch_row($result)){
      if($count != 0)
	echo ",";
      echo $row[0];
      if(($count++) % 10 ==0){
	echo "\n";
      }
    }
    echo "</pre><p>Total number of voters was $count</p>";
    return true;
  }

  // return an array of the voters that have voted
  function getvoters(){
    $retval = array();
    if(!$this->openDB()) { return false; }
    if(!($result = mysql_query("SELECT crsid FROM {$this->ename}voter WHERE voted='1' ORDER BY crsid"))) return($this->err("Vote list read failed."));

    while($row = mysql_fetch_row($result)) array_push($retval,$row[0]);
    return $retval;
  }

  // RFC2289 defines a list of words for human-friendly one-time key exchange.
  // Copyright (C) The Internet Society (1998).  All Rights Reserved.
  var $rfc2289words=array(
"A","ABE","ACE","ACT","AD","ADA","ADD",
"AGO","AID","AIM","AIR","ALL","ALP","AM","AMY",
"AN","ANA","AND","ANN","ANT","ANY","APE","APS",
"APT","ARC","ARE","ARK","ARM","ART","AS","ASH",
"ASK","AT","ATE","AUG","AUK","AVE","AWE","AWK",
"AWL","AWN","AX","AYE","BAD","BAG","BAH","BAM",
"BAN","BAR","BAT","BAY","BE","BED","BEE","BEG",
"BEN","BET","BEY","BIB","BID","BIG","BIN","BIT",
"BOB","BOG","BON","BOO","BOP","BOW","BOY","BUB",
"BUD","BUG","BUM","BUN","BUS","BUT","BUY","BY",
"BYE","CAB","CAL","CAM","CAN","CAP","CAR","CAT",
"CAW","COD","COG","COL","CON","COO","COP","COT",
"COW","COY","CRY","CUB","CUE","CUP","CUR","CUT",
"DAB","DAD","DAM","DAN","DAR","DAY","DEE","DEL",
"DEN","DES","DEW","DID","DIE","DIG","DIN","DIP",
"DO","DOE","DOG","DON","DOT","DOW","DRY","DUB",
"DUD","DUE","DUG","DUN","EAR","EAT","ED","EEL",
"EGG","EGO","ELI","ELK","ELM","ELY","EM","END",
"EST","ETC","EVA","EVE","EWE","EYE","FAD","FAN",
"FAR","FAT","FAY","FED","FEE","FEW","FIB","FIG",
"FIN","FIR","FIT","FLO","FLY","FOE","FOG","FOR",
"FRY","FUM","FUN","FUR","GAB","GAD","GAG","GAL",
"GAM","GAP","GAS","GAY","GEE","GEL","GEM","GET",
"GIG","GIL","GIN","GO","GOT","GUM","GUN","GUS",
"GUT","GUY","GYM","GYP","HA","HAD","HAL","HAM",
"HAN","HAP","HAS","HAT","HAW","HAY","HE","HEM",
"HEN","HER","HEW","HEY","HI","HID","HIM","HIP",
"HIS","HIT","HO","HOB","HOC","HOE","HOG","HOP",
"HOT","HOW","HUB","HUE","HUG","HUH","HUM","HUT",
"I","ICY","IDA","IF","IKE","ILL","INK","INN",
"IO","ION","IQ","IRA","IRE","IRK","IS","IT",
"ITS","IVY","JAB","JAG","JAM","JAN","JAR","JAW",
"JAY","JET","JIG","JIM","JO","JOB","JOE","JOG",
"JOT","JOY","JUG","JUT","KAY","KEG","KEN","KEY",
"KID","KIM","KIN","KIT","LA","LAB","LAC","LAD",
"LAG","LAM","LAP","LAW","LAY","LEA","LED","LEE",
"LEG","LEN","LEO","LET","LEW","LID","LIE","LIN",
"LIP","LIT","LO","LOB","LOG","LOP","LOS","LOT",
"LOU","LOW","LOY","LUG","LYE","MA","MAC","MAD",
"MAE","MAN","MAO","MAP","MAT","MAW","MAY","ME",
"MEG","MEL","MEN","MET","MEW","MID","MIN","MIT",
"MOB","MOD","MOE","MOO","MOP","MOS","MOT","MOW",
"MUD","MUG","MUM","MY","NAB","NAG","NAN","NAP",

"NAT","NAY","NE","NED","NEE","NET","NEW","NIB",
"NIL","NIP","NIT","NO","NOB","NOD","NON","NOR",
"NOT","NOV","NOW","NU","NUN","NUT","O","OAF",
"OAK","OAR","OAT","ODD","ODE","OF","OFF","OFT",
"OH","OIL","OK","OLD","ON","ONE","OR","ORB",
"ORE","ORR","OS","OTT","OUR","OUT","OVA","OW",
"OWE","OWL","OWN","OX","PA","PAD","PAL","PAM",
"PAN","PAP","PAR","PAT","PAW","PAY","PEA","PEG",
"PEN","PEP","PER","PET","PEW","PHI","PI","PIE",
"PIN","PIT","PLY","PO","POD","POE","POP","POT",
"POW","PRO","PRY","PUB","PUG","PUN","PUP","PUT",
"QUO","RAG","RAM","RAN","RAP","RAT","RAW","RAY",
"REB","RED","REP","RET","RIB","RID","RIG","RIM",
"RIO","RIP","ROB","ROD","ROE","RON","ROT","ROW",
"ROY","RUB","RUE","RUG","RUM","RUN","RYE","SAC",
"SAD","SAG","SAL","SAM","SAN","SAP","SAT","SAW",
"SAY","SEA","SEC","SEE","SEN","SET","SEW","SHE",
"SHY","SIN","SIP","SIR","SIS","SIT","SKI","SKY",
"SLY","SO","SOB","SOD","SON","SOP","SOW","SOY",
"SPA","SPY","SUB","SUD","SUE","SUM","SUN","SUP",
"TAB","TAD","TAG","TAN","TAP","TAR","TEA","TED",
"TEE","TEN","THE","THY","TIC","TIE","TIM","TIN",
"TIP","TO","TOE","TOG","TOM","TON","TOO","TOP",
"TOW","TOY","TRY","TUB","TUG","TUM","TUN","TWO",
"UN","UP","US","USE","VAN","VAT","VET","VIE",
"WAD","WAG","WAR","WAS","WAY","WE","WEB","WED",
"WEE","WET","WHO","WHY","WIN","WIT","WOK","WON",
"WOO","WOW","WRY","WU","YAM","YAP","YAW","YE",
"YEA","YES","YET","YOU","ABED","ABEL","ABET","ABLE",
"ABUT","ACHE","ACID","ACME","ACRE","ACTA","ACTS","ADAM",
"ADDS","ADEN","AFAR","AFRO","AGEE","AHEM","AHOY","AIDA",
"AIDE","AIDS","AIRY","AJAR","AKIN","ALAN","ALEC","ALGA",
"ALIA","ALLY","ALMA","ALOE","ALSO","ALTO","ALUM","ALVA",
"AMEN","AMES","AMID","AMMO","AMOK","AMOS","AMRA","ANDY",
"ANEW","ANNA","ANNE","ANTE","ANTI","AQUA","ARAB","ARCH",
"AREA","ARGO","ARID","ARMY","ARTS","ARTY","ASIA","ASKS",
"ATOM","AUNT","AURA","AUTO","AVER","AVID","AVIS","AVON",
"AVOW","AWAY","AWRY","BABE","BABY","BACH","BACK","BADE",
"BAIL","BAIT","BAKE","BALD","BALE","BALI","BALK","BALL",
"BALM","BAND","BANE","BANG","BANK","BARB","BARD","BARE",
"BARK","BARN","BARR","BASE","BASH","BASK","BASS","BATE",
"BATH","BAWD","BAWL","BEAD","BEAK","BEAM","BEAN","BEAR",
"BEAT","BEAU","BECK","BEEF","BEEN","BEER","BEET","BELA",
"BELL","BELT","BEND","BENT","BERG","BERN","BERT","BESS",
"BEST","BETA","BETH","BHOY","BIAS","BIDE","BIEN","BILE",
"BILK","BILL","BIND","BING","BIRD","BITE","BITS","BLAB",
"BLAT","BLED","BLEW","BLOB","BLOC","BLOT","BLOW","BLUE",
"BLUM","BLUR","BOAR","BOAT","BOCA","BOCK","BODE","BODY",

"BOGY","BOHR","BOIL","BOLD","BOLO","BOLT","BOMB","BONA",
"BOND","BONE","BONG","BONN","BONY","BOOK","BOOM","BOON",
"BOOT","BORE","BORG","BORN","BOSE","BOSS","BOTH","BOUT",
"BOWL","BOYD","BRAD","BRAE","BRAG","BRAN","BRAY","BRED",
"BREW","BRIG","BRIM","BROW","BUCK","BUDD","BUFF","BULB",
"BULK","BULL","BUNK","BUNT","BUOY","BURG","BURL","BURN",
"BURR","BURT","BURY","BUSH","BUSS","BUST","BUSY","BYTE",
"CADY","CAFE","CAGE","CAIN","CAKE","CALF","CALL","CALM",
"CAME","CANE","CANT","CARD","CARE","CARL","CARR","CART",
"CASE","CASH","CASK","CAST","CAVE","CEIL","CELL","CENT",
"CERN","CHAD","CHAR","CHAT","CHAW","CHEF","CHEN","CHEW",
"CHIC","CHIN","CHOU","CHOW","CHUB","CHUG","CHUM","CITE",
"CITY","CLAD","CLAM","CLAN","CLAW","CLAY","CLOD","CLOG",
"CLOT","CLUB","CLUE","COAL","COAT","COCA","COCK","COCO",
"CODA","CODE","CODY","COED","COIL","COIN","COKE","COLA",
"COLD","COLT","COMA","COMB","COME","COOK","COOL","COON",
"COOT","CORD","CORE","CORK","CORN","COST","COVE","COWL",
"CRAB","CRAG","CRAM","CRAY","CREW","CRIB","CROW","CRUD",
"CUBA","CUBE","CUFF","CULL","CULT","CUNY","CURB","CURD",
"CURE","CURL","CURT","CUTS","DADE","DALE","DAME","DANA",
"DANE","DANG","DANK","DARE","DARK","DARN","DART","DASH",
"DATA","DATE","DAVE","DAVY","DAWN","DAYS","DEAD","DEAF",
"DEAL","DEAN","DEAR","DEBT","DECK","DEED","DEEM","DEER",
"DEFT","DEFY","DELL","DENT","DENY","DESK","DIAL","DICE",
"DIED","DIET","DIME","DINE","DING","DINT","DIRE","DIRT",
"DISC","DISH","DISK","DIVE","DOCK","DOES","DOLE","DOLL",
"DOLT","DOME","DONE","DOOM","DOOR","DORA","DOSE","DOTE",
"DOUG","DOUR","DOVE","DOWN","DRAB","DRAG","DRAM","DRAW",
"DREW","DRUB","DRUG","DRUM","DUAL","DUCK","DUCT","DUEL",
"DUET","DUKE","DULL","DUMB","DUNE","DUNK","DUSK","DUST",
"DUTY","EACH","EARL","EARN","EASE","EAST","EASY","EBEN",
"ECHO","EDDY","EDEN","EDGE","EDGY","EDIT","EDNA","EGAN",
"ELAN","ELBA","ELLA","ELSE","EMIL","EMIT","EMMA","ENDS",
"ERIC","EROS","EVEN","EVER","EVIL","EYED","FACE","FACT",
"FADE","FAIL","FAIN","FAIR","FAKE","FALL","FAME","FANG",
"FARM","FAST","FATE","FAWN","FEAR","FEAT","FEED","FEEL",
"FEET","FELL","FELT","FEND","FERN","FEST","FEUD","FIEF",
"FIGS","FILE","FILL","FILM","FIND","FINE","FINK","FIRE",
"FIRM","FISH","FISK","FIST","FITS","FIVE","FLAG","FLAK",
"FLAM","FLAT","FLAW","FLEA","FLED","FLEW","FLIT","FLOC",
"FLOG","FLOW","FLUB","FLUE","FOAL","FOAM","FOGY","FOIL",
"FOLD","FOLK","FOND","FONT","FOOD","FOOL","FOOT","FORD",
"FORE","FORK","FORM","FORT","FOSS","FOUL","FOUR","FOWL",
"FRAU","FRAY","FRED","FREE","FRET","FREY","FROG","FROM",
"FUEL","FULL","FUME","FUND","FUNK","FURY","FUSE","FUSS",
"GAFF","GAGE","GAIL","GAIN","GAIT","GALA","GALE","GALL",
"GALT","GAME","GANG","GARB","GARY","GASH","GATE","GAUL",
"GAUR","GAVE","GAWK","GEAR","GELD","GENE","GENT","GERM",

"GETS","GIBE","GIFT","GILD","GILL","GILT","GINA","GIRD",
"GIRL","GIST","GIVE","GLAD","GLEE","GLEN","GLIB","GLOB",
"GLOM","GLOW","GLUE","GLUM","GLUT","GOAD","GOAL","GOAT",
"GOER","GOES","GOLD","GOLF","GONE","GONG","GOOD","GOOF",
"GORE","GORY","GOSH","GOUT","GOWN","GRAB","GRAD","GRAY",
"GREG","GREW","GREY","GRID","GRIM","GRIN","GRIT","GROW",
"GRUB","GULF","GULL","GUNK","GURU","GUSH","GUST","GWEN",
"GWYN","HAAG","HAAS","HACK","HAIL","HAIR","HALE","HALF",
"HALL","HALO","HALT","HAND","HANG","HANK","HANS","HARD",
"HARK","HARM","HART","HASH","HAST","HATE","HATH","HAUL",
"HAVE","HAWK","HAYS","HEAD","HEAL","HEAR","HEAT","HEBE",
"HECK","HEED","HEEL","HEFT","HELD","HELL","HELM","HERB",
"HERD","HERE","HERO","HERS","HESS","HEWN","HICK","HIDE",
"HIGH","HIKE","HILL","HILT","HIND","HINT","HIRE","HISS",
"HIVE","HOBO","HOCK","HOFF","HOLD","HOLE","HOLM","HOLT",
"HOME","HONE","HONK","HOOD","HOOF","HOOK","HOOT","HORN",
"HOSE","HOST","HOUR","HOVE","HOWE","HOWL","HOYT","HUCK",
"HUED","HUFF","HUGE","HUGH","HUGO","HULK","HULL","HUNK",
"HUNT","HURD","HURL","HURT","HUSH","HYDE","HYMN","IBIS",
"ICON","IDEA","IDLE","IFFY","INCA","INCH","INTO","IONS",
"IOTA","IOWA","IRIS","IRMA","IRON","ISLE","ITCH","ITEM",
"IVAN","JACK","JADE","JAIL","JAKE","JANE","JAVA","JEAN",
"JEFF","JERK","JESS","JEST","JIBE","JILL","JILT","JIVE",
"JOAN","JOBS","JOCK","JOEL","JOEY","JOHN","JOIN","JOKE",
"JOLT","JOVE","JUDD","JUDE","JUDO","JUDY","JUJU","JUKE",
"JULY","JUNE","JUNK","JUNO","JURY","JUST","JUTE","KAHN",
"KALE","KANE","KANT","KARL","KATE","KEEL","KEEN","KENO",
"KENT","KERN","KERR","KEYS","KICK","KILL","KIND","KING",
"KIRK","KISS","KITE","KLAN","KNEE","KNEW","KNIT","KNOB",
"KNOT","KNOW","KOCH","KONG","KUDO","KURD","KURT","KYLE",
"LACE","LACK","LACY","LADY","LAID","LAIN","LAIR","LAKE",
"LAMB","LAME","LAND","LANE","LANG","LARD","LARK","LASS",
"LAST","LATE","LAUD","LAVA","LAWN","LAWS","LAYS","LEAD",
"LEAF","LEAK","LEAN","LEAR","LEEK","LEER","LEFT","LEND",
"LENS","LENT","LEON","LESK","LESS","LEST","LETS","LIAR",
"LICE","LICK","LIED","LIEN","LIES","LIEU","LIFE","LIFT",
"LIKE","LILA","LILT","LILY","LIMA","LIMB","LIME","LIND",
"LINE","LINK","LINT","LION","LISA","LIST","LIVE","LOAD",
"LOAF","LOAM","LOAN","LOCK","LOFT","LOGE","LOIS","LOLA",
"LONE","LONG","LOOK","LOON","LOOT","LORD","LORE","LOSE",
"LOSS","LOST","LOUD","LOVE","LOWE","LUCK","LUCY","LUGE",
"LUKE","LULU","LUND","LUNG","LURA","LURE","LURK","LUSH",
"LUST","LYLE","LYNN","LYON","LYRA","MACE","MADE","MAGI",
"MAID","MAIL","MAIN","MAKE","MALE","MALI","MALL","MALT",
"MANA","MANN","MANY","MARC","MARE","MARK","MARS","MART",
"MARY","MASH","MASK","MASS","MAST","MATE","MATH","MAUL",
"MAYO","MEAD","MEAL","MEAN","MEAT","MEEK","MEET","MELD",
"MELT","MEMO","MEND","MENU","MERT","MESH","MESS","MICE",

"MIKE","MILD","MILE","MILK","MILL","MILT","MIMI","MIND",
"MINE","MINI","MINK","MINT","MIRE","MISS","MIST","MITE",
"MITT","MOAN","MOAT","MOCK","MODE","MOLD","MOLE","MOLL",
"MOLT","MONA","MONK","MONT","MOOD","MOON","MOOR","MOOT",
"MORE","MORN","MORT","MOSS","MOST","MOTH","MOVE","MUCH",
"MUCK","MUDD","MUFF","MULE","MULL","MURK","MUSH","MUST",
"MUTE","MUTT","MYRA","MYTH","NAGY","NAIL","NAIR","NAME",
"NARY","NASH","NAVE","NAVY","NEAL","NEAR","NEAT","NECK",
"NEED","NEIL","NELL","NEON","NERO","NESS","NEST","NEWS",
"NEWT","NIBS","NICE","NICK","NILE","NINA","NINE","NOAH",
"NODE","NOEL","NOLL","NONE","NOOK","NOON","NORM","NOSE",
"NOTE","NOUN","NOVA","NUDE","NULL","NUMB","OATH","OBEY",
"OBOE","ODIN","OHIO","OILY","OINT","OKAY","OLAF","OLDY",
"OLGA","OLIN","OMAN","OMEN","OMIT","ONCE","ONES","ONLY",
"ONTO","ONUS","ORAL","ORGY","OSLO","OTIS","OTTO","OUCH",
"OUST","OUTS","OVAL","OVEN","OVER","OWLY","OWNS","QUAD",
"QUIT","QUOD","RACE","RACK","RACY","RAFT","RAGE","RAID",
"RAIL","RAIN","RAKE","RANK","RANT","RARE","RASH","RATE",
"RAVE","RAYS","READ","REAL","REAM","REAR","RECK","REED",
"REEF","REEK","REEL","REID","REIN","RENA","REND","RENT",
"REST","RICE","RICH","RICK","RIDE","RIFT","RILL","RIME",
"RING","RINK","RISE","RISK","RITE","ROAD","ROAM","ROAR",
"ROBE","ROCK","RODE","ROIL","ROLL","ROME","ROOD","ROOF",
"ROOK","ROOM","ROOT","ROSA","ROSE","ROSS","ROSY","ROTH",
"ROUT","ROVE","ROWE","ROWS","RUBE","RUBY","RUDE","RUDY",
"RUIN","RULE","RUNG","RUNS","RUNT","RUSE","RUSH","RUSK",
"RUSS","RUST","RUTH","SACK","SAFE","SAGE","SAID","SAIL",
"SALE","SALK","SALT","SAME","SAND","SANE","SANG","SANK",
"SARA","SAUL","SAVE","SAYS","SCAN","SCAR","SCAT","SCOT",
"SEAL","SEAM","SEAR","SEAT","SEED","SEEK","SEEM","SEEN",
"SEES","SELF","SELL","SEND","SENT","SETS","SEWN","SHAG",
"SHAM","SHAW","SHAY","SHED","SHIM","SHIN","SHOD","SHOE",
"SHOT","SHOW","SHUN","SHUT","SICK","SIDE","SIFT","SIGH",
"SIGN","SILK","SILL","SILO","SILT","SINE","SING","SINK",
"SIRE","SITE","SITS","SITU","SKAT","SKEW","SKID","SKIM",
"SKIN","SKIT","SLAB","SLAM","SLAT","SLAY","SLED","SLEW",
"SLID","SLIM","SLIT","SLOB","SLOG","SLOT","SLOW","SLUG",
"SLUM","SLUR","SMOG","SMUG","SNAG","SNOB","SNOW","SNUB",
"SNUG","SOAK","SOAR","SOCK","SODA","SOFA","SOFT","SOIL",
"SOLD","SOME","SONG","SOON","SOOT","SORE","SORT","SOUL",
"SOUR","SOWN","STAB","STAG","STAN","STAR","STAY","STEM",
"STEW","STIR","STOW","STUB","STUN","SUCH","SUDS","SUIT",
"SULK","SUMS","SUNG","SUNK","SURE","SURF","SWAB","SWAG",
"SWAM","SWAN","SWAT","SWAY","SWIM","SWUM","TACK","TACT",
"TAIL","TAKE","TALE","TALK","TALL","TANK","TASK","TATE",
"TAUT","TEAL","TEAM","TEAR","TECH","TEEM","TEEN","TEET",
"TELL","TEND","TENT","TERM","TERN","TESS","TEST","THAN",
"THAT","THEE","THEM","THEN","THEY","THIN","THIS","THUD",

"THUG","TICK","TIDE","TIDY","TIED","TIER","TILE","TILL",
"TILT","TIME","TINA","TINE","TINT","TINY","TIRE","TOAD",
"TOGO","TOIL","TOLD","TOLL","TONE","TONG","TONY","TOOK",
"TOOL","TOOT","TORE","TORN","TOTE","TOUR","TOUT","TOWN",
"TRAG","TRAM","TRAY","TREE","TREK","TRIG","TRIM","TRIO",
"TROD","TROT","TROY","TRUE","TUBA","TUBE","TUCK","TUFT",
"TUNA","TUNE","TUNG","TURF","TURN","TUSK","TWIG","TWIN",
"TWIT","ULAN","UNIT","URGE","USED","USER","USES","UTAH",
"VAIL","VAIN","VALE","VARY","VASE","VAST","VEAL","VEDA",
"VEIL","VEIN","VEND","VENT","VERB","VERY","VETO","VICE",
"VIEW","VINE","VISE","VOID","VOLT","VOTE","WACK","WADE",
"WAGE","WAIL","WAIT","WAKE","WALE","WALK","WALL","WALT",
"WAND","WANE","WANG","WANT","WARD","WARM","WARN","WART",
"WASH","WAST","WATS","WATT","WAVE","WAVY","WAYS","WEAK",
"WEAL","WEAN","WEAR","WEED","WEEK","WEIR","WELD","WELL",
"WELT","WENT","WERE","WERT","WEST","WHAM","WHAT","WHEE",
"WHEN","WHET","WHOA","WHOM","WICK","WIFE","WILD","WILL",
"WIND","WINE","WING","WINK","WINO","WIRE","WISE","WISH",
"WITH","WOLF","WONT","WOOD","WOOL","WORD","WORE","WORK",
"WORM","WORN","WOVE","WRIT","WYNN","YALE","YANG","YANK",
"YARD","YARN","YAWL","YAWN","YEAH","YEAR","YELL","YOGA",
"YOKE");

}

  // initialise all variables (i.e. thwart register_globals attacks)
  $dbdb=$dbhost=$dbuser=$electionInfo=$emailRO=$emailTech=$ename=$endBallot=$htmlNotRegistered=$htmlPostBallot=$htmlPreBallot=$htmlRO=$htmlTech=$startBallot=$title=$adminDuringElectionOK=$positionInfo=$ro=$eOfficials=$viewBallot='';
  require_once('./config.php');
  $bob = new BOB($dbdb,$dbhost,$dbuser,$electionInfo,$emailRO,$emailTech,$ename,$endBallot,$htmlNotRegistered,$htmlPostBallot,$htmlPreBallot,$htmlRO,$htmlTech,$startBallot,$title,$adminDuringElectionOK,$positionInfo,$ro,$eOfficials,$viewBallot);

?>
