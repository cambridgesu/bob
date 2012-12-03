#!/usr/bin/perl -w

# This file is part of the Basic On-line Ballot-box (BOB).
# $Id: genElection.pl 120 2007-02-11 16:42:14Z dme26 $
# http://www.cl.cam.ac.uk/~dme26/proj/BOB/
#
# BOB is free software; you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the
# Free Software Foundation; either version 2 of the License, or (at your
# option) any later version.
#
# BOB is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
# FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
# for more details.
#
# You should have received a copy of the GNU General Public License
# along with BOB; if not, write to the Free Software Foundation,
# Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

$title=$ename=$emailRO=$emailTech=$htmlTech=$ro=$eofficials=$htmlRO=$ballotStart =$ballotEnd =$htmlPreBallot=$htmlPostBallot=$htmlNotRegistered=$dbhost=$dbdb=$dbuser=$dbpass=$election=$adminDuringElectionOK='';

if(-f "electionConfig.pm"){
    print "+ Loading election configuration.\n";
    require electionConfig;
}else{
    print "! Could not find election configuration.\n";
    exit
}

    # parse election variable
    my @eData=();

my @vData=();
my @pData=();
my $blank=1;

foreach $el (split(/\n/,$election)){
    if($el =~ /^\s*$/) {
	if(!$blank){
	    $blank=1;
	    push(@eData,[@vData]) if $#vData>=0;
	    @vData=();
	}
    }else{
	if($blank){ # first new row
	    if($el =~ /^\s*([0-9]+)\s*$/ ){ # number of positions to elect
		push(@pData,$el);
		$blank=0;
		next;
	    }else{
		push(@pData,1); # default is one to be elected per position
	    }
	}
	push(@vData,$el);
	$blank=0;
    }
}
push(@eData,[@vData]) if $#vData>=0;

sub openIfNoFile {
    my $f;
    if(-f $_[0])  {
	print "! File $_[0] already exists. Not overwriting in case your changes would be clobbered.\n";
	return 0;
    }    else{
	print "+ Creating file $_[0].\n";
	open($f,">$_[0]") || print "! Error opening file $_[0] for writing.\n";
	if($_[1]){
	    chmod $_[1],$_[0];
	}
	return $f;
    }
}

sub doMD5 {
    my $f = $_[0];
    if(-f $f && ($md5sumA = `md5sum $f`)){
	$md5sumA =~ s/\s.*$//;
	$md5sumA =~ s/(....)/$1 /g;
	$md5sumA = " The file $f has the following MD5 sum: <kbd>$md5sumA</kbd>. ";
	return $md5sumA;
    }else{
	print "* Unable to calculate md5sum of $f.\n";
	return 0;
    }
}

sub doSymlinkSource {
    my $f = $_[0];
    if(-f "$f.php"){
        print ": $f.php is present\n";
        if(-f "$f.txt"){
            print ": $f.txt is already present\n";
        }else{
            if((system "ln -s $f.php $f.txt")==0){
                print "+ Created $f.txt symlink to $f.php for source viewing.\n";
            }else{
                print "! Failed to create $f.txt symlink to $f.php.\n";
    	}
        }
    }else{
        print "! $f.php does not appear to be present, but is necessary for the functioning of this system\n";
    }
}

sub rand_str {
    my $len=shift;
    my $s ='';
    foreach (1..$len){
	$s.=chr(65+rand(26));
    }
    return $s;
}

if($f=openIfNoFile(".htaccess")){
    $ckey = rand_str(10).$ename;

    if($eofficials){
	$eoff = <<EOF;
<Files "admin*">
  Require user $eofficials
</Files>
EOF
    }else{
	$eoff = <<EOF;
<Files "admin*">
  deny from all
</Files>
EOF
    }

    print $f <<EOF;
AACookieKey "$ckey"
AADescription "$title"
AuthType Ucam-WebAuth
Require valid-user
DirectoryIndex index.html index.htm index.php ballot.html
<Files ".ht*">
  deny from all
</Files>
<Files "dbpass">
  deny from all
</Files>
$eoff
EOF
    close $f;
}

if($f=openIfNoFile("dbpass",0640)){
    print $f $dbpass;
    close $f;
}

# generate PHP array of election info
$electionPHP = "\$electionInfo = array(";
for $i ( 0 .. $#eData ) {
    $ta = $eData[$i];
    $n = @$ta - 1;
    $electionPHP .= "," if $i != 0;
    $electionPHP .= "\narray(";
    for $j ( 0 .. $n ) {
	$tmp = $eData[$i][$j];
	$tmp =~ s/'/'."'".'/go; # '
        $electionPHP .= ", " if $j != 0;
	$electionPHP .= "'$tmp'";
    }
    $electionPHP .= ")";
}
$electionPHP .= ");\n";

# generate PHP array of number of positions per slot
$positionsPHP = "\$positionInfo = array(".join(',',@pData).");\n";

if($f=openIfNoFile("config.php")){
    print $f <<EOF;
<?php 
\$title=<<<EOV
${title} response
EOV;
\$ename=<<<EOV
${ename}
EOV;
\$emailRO="${emailRO}";
\$emailTech="${emailTech}";
\$htmlRO=<<<EOV
<a href="mailto:$emailRO">returning officer ($emailRO)</a>
EOV;
\$htmlTech=<<<EOV
<a href="mailto:$emailTech">technical administrator ($emailTech)</a>
EOV;
\$dbhost="${dbhost}";
\$dbuser="${dbuser}";
\$dbdb="${dbdb}";
\$adminDuringElectionOK=${adminDuringElectionOK};
${electionPHP}
${positionsPHP}
\$htmlNotRegistered=<<<EOV
${htmlNotRegistered}
EOV;
\$htmlPreBallot=<<<EOV
${htmlPreBallot}
EOV;
\$htmlPostBallot=<<<EOV
${htmlPostBallot}
EOV;
EOF
# the 0's in the regexps below combat a PHP bug in mktime. Thanks Simon!
    if($ballotStart =~ /(\d{4})-0*(\d{1,2})-0*(\d{1,2}) 0*(\d{1,2}):0*(\d{1,2})/){
	print $f "\$startBallot = mktime($4,$5,0,$2,$3,$1);\n"
    }else{
	print "! Unable to parse \$ballotStart date.\n";
    }
    if($ballotEnd =~ /(\d{4})-0*(\d{1,2})-0*(\d{1,2}) 0*(\d{1,2}):0*(\d{1,2})/){
	print $f "\$endBallot = mktime($4,$5,0,$2,$3,$1);\n"
    }else{
	print "! Unable to parse \$ballotEnd date.\n";
    }
    close $f;
}

if($f=openIfNoFile("voters.sql",0660)){
    print $f <<EOF;
USE ${dbdb};
INSERT INTO ${ename}voter VALUES ('dme26',0);
EOF
    close $f;
}

if($f=openIfNoFile("printelection.sql",0660)){
    print $f <<EOF;
USE ${dbdb};
SELECT * FROM ${ename}vote;
SELECT * FROM ${ename}voter;
EOF
    close $f;
}

if($f=openIfNoFile("createtable.sql",0660)){
    print $f "USE ${dbdb};
DROP TABLE IF EXISTS ${ename}voter;
DROP TABLE IF EXISTS ${ename}vote;
CREATE TABLE ${ename}voter (
crsid VARCHAR(16) NOT NULL PRIMARY KEY,
voted TINYINT
) TYPE=InnoDB;
CREATE TABLE ${ename}vote (
token VARCHAR(32) NOT NULL PRIMARY KEY";
    for $i ( 0 .. $#eData ) {
        $ta = $eData[$i];
        $v=$i+1;
        for $j ( 1 .. @$ta-1 ) {
	    print $f ",\n  v${v}p${j} TINYINT";
	} 
    }
    print $f ") TYPE=InnoDB;\n";
    close $f;
}

if($f=openIfNoFile("ballot.html")){
    print $f <<EOF;
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>${title}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
      table.vote {border-collapse: separate; border:1px outset lightgrey;}
      table.vote td, table.vote th {border:1px inset lightgrey; padding: 4px;}
    </style>
  </head>

  <body>
    <h1>${title}</h1>

<p>This system provides a means to forward your anonymised votes to
the returning officer, who for this election is ${ro}. <a
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

    <form action="vote.php" method="post">
EOF

# prints HTML form
my $selectOpts;
for $i ( 0 .. $#eData ) {
    $ta = $eData[$i];
    $n = @$ta - 1;

    if($n >= 0){
	$selectOpts="<option value=\"0\">(blank)</option>";
	for $j ( 1 .. $n ) {
	    $selectOpts .= "<option value=\"$j\">$eData[$i][$j]</option>";
	}
    }

    print $f <<EOF;
<h2>$eData[$i][0]</h2>
<table class="vote v$i">
  <tr><th>Preference</th><th>Candidate</th></tr>
EOF
$v=$i+1;
    for $j ( 1 .. $n ) {
	print $f "<tr class=\"c$j ".(($j%2)?"codd":"ceven")."\"><td>$j</td><td align=\"center\"><select name=\"v[$v][$j]\">$selectOpts</select></td></tr>\n";
    }

    print $f "</table>\n";
}

    my $md5sumVote = doMD5('vote.php');
    my $md5sumConfig = doMD5('config.php');
    my $md5sumBOB = doMD5('BOB.php');
			
    print $f <<EOF;

      <hr />

      <p><font color="red"><b>Please double-check your choices before
      submitting your vote!</b></font> Due to the anonymity built into this
      voting system, it is not possible to correlate your response after you
      vote.</p>

      <input type="checkbox" name="confirmvote" />I have checked my vote.

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
guarantees.  Feel free to <a href="vote.txt">examine the PHP code that
drives the voting page</a> directly. $md5sumVote The voting page calls on
<a href="BOB.txt">functions within a library</a>. $md5sumBOB The
specific <a href="config.txt">election configuration file</a> is also
viewable. $md5sumConfig </p>

    <hr />
    <address>Contacts: ${htmlTech} or ${htmlRO}</address>
  </body>
</html>
EOF
    close $f;
}

doSymlinkSource("vote");
doSymlinkSource("BOB");
doSymlinkSource("config");

print <<EOF;

To complete installation you need to:
(1) Ensure that the .htaccess file created will be protecting this directory
    (i.e. Raven authentication is required to access the ballot form)
(2) Note: the database user you have chosen will need the following database
    permissions: SELECT, INSERT, UPDATE, CREATE, DROP.
    
    Run createtable.sql to make the database tables, e.g.
    (your database password is in the dbpass file)

    mysql -u ${dbuser} -p ${dbdb} <createtable.sql
    (this will drop previous tables if present)

(3) For testing, ensure that you are in the voters.sql file
    (the 0 means "hasn't voted")
(4) Run voters.sql to seed the database tables, e.g.

    mysql -u ${dbuser} -p ${dbdb} <voters.sql

(5) Check the on-line system works as expected.
(6) Check your vote is recorded, and you are listed as having voted, e.g.

    mysql -u ${dbuser} -p ${dbdb} <printelection.sql

(7) Repeat steps (2) to (4), but using the real electoral roll.
(8) The database permissions now need only be SELECT, INSERT, UPDATE.

EOF
