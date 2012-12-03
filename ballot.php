<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
  $bob = false; // we expect BOB.php to reassign this as a class instance
  require_once("./BOB.php");
?>

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $bob->title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
      table.vote {border: 1px; border-collapse: collapse; border-spacing: 0px;}
      table.vote td, table.vote th {border: 2px #ddd solid; padding: 3px;}
    </style>
	
	<script type="text/javascript">
		// Prevent mouse wheel scrolling when focus is on a select widget
		function PreventScroll(e)
		{
			var ie = document.all;
			if(ie)
			{
				e = window.event;
				e.returnValue = false;
				e.cancelBubble = true;
			}
		}
	</script>
  </head>

  <body>
    <h1><?php echo $bob->title; ?></h1>

<p>This system provides a means to forward your anonymised votes to
the returning officer, who for this election is <?php echo $bob->ro; ?>. <a
href="#disclaimer">Please read the disclaimer</a>.  The Returning
Officer will be able to see who has voted but will not be able to tell
who has cast which votes.</p>

<p>When you have successfully placed your vote, you will be emailed a
sequence of random-looking short words - your "voting token". This system does
not store the connection between your voting token and your
identity, however it does email your voting token alongside your vote
to the returning officer (and store it in a database to protect
against email fraud).  When polls have closed, the Returning Officer
will print out a list of all the votes cast - because only you will
know your voting token, you will be able to check that
your vote was correctly included.</p>

<hr />

<h3>How to vote</h3>

<p>Voting is by the Single Transferable Vote system described in Chapter 1
of the Ordinances of the University of Cambridge.</p>

<ul>

<li>Next to number 1 (in the preference column for a given post),
select the name of the candidate to whom you give your first
preference (using the pull-down selection menu controls).</li>

<li>You may also enter, against preference ranks 2, 3 and so on, the
names of other candidates in the order you wish to vote for them.</li>

<li>Continue until you have voted for those candidates you wish to vote
for, and leave any remaining boxes blank. You are under no obligation to
vote for all candidates.</li>

<li>Repeat this process for each post listed.</li>

<li>Some elections may list a candidate named "RON". This acronym
expands to "Re-Open Nominations". You may vote for RON as you would
any other candidate. Should RON be 'elected', the position will be
re-opened, and will be decided at a subsequent election.</li>

<li>The order of your preferences is crucial. Later preferences will
only be considered if an earlier preference has qualified for election
or has been eliminated from the election due to gaining an
insufficient number of votes. </li>

<li>When you have completed this form CHECK IT, since once you submit
the form errors cannot be corrected and may invalidate your vote.  It
is possible to cast invalid votes with this electronic system. This
intentionally mirrors the choice available to you to cast invalid
votes in a paper-based balloting process.</li>

</ul>

<?php $bob->ballotPage(); /* Voting workflow */ ?>

      <hr />

      <p><font color="red"><b>Please double-check your choices before
      submitting your vote!</b></font> Due to the anonymity built into this
      voting system, it is not possible to correlate your response after you
      vote.</p>

      <input type="checkbox" name="confirmvote" id="confirmvote" /><label for="confirmvote">I have checked my vote.</label>

      <p>After you click "Cast my vote", your vote will be passed
      anonymously to the Returning Officer. You will receive a blind
      copy by email. This will allow you to check we have recorded
      your vote correctly by confirming to yourself that the printed
      sheets that will be posted after the votes have been counted.
      Any queries should be directed to the Returning Officer.</p>

      <p><input value="Cast my vote" type="submit" /></p>

    </form>

    <hr />

<a name="disclaimer"></a>

<p><b>Disclaimer:</b> The (extremely minimal) software behind this
voting system has been checked independently, and has been agreed to
be a system which should avoid, but will at least detect voting
irregularities. The service is hosted on a computer that is not under
the direct administrative control of the organisation running the
election. Evidence can be acquired from external system administrators
that the software is not modified during the election.  If you do not
trust this system, you are advised to contact the Returning
Officer. As stated in the GPL license, this software comes with no
guarantees.  Feel free to examine the PHP code that drives the various
pages:

<ul>
<?php 
  $files = array('vote','ballot','BOB','config');

  foreach($files as $f){
    $md5 = md5 (file_get_contents ("./$f.php"));
    echo <<<EOF
  <li><a href="$f.txt" target="_blank" title="[Link opens in a new window]">$f.php</a> has MD5 sum <kbd>$md5</kbd></li>
EOF;
  }
?>
</ul>
    <hr />
<?php echo "<address>Contacts: {$bob->htmlTech} or {$bob->htmlRO}</address>\n"; ?>
  </body>
</html>
