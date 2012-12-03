#!/usr/bin/perl -w

# This file is part of the Basic On-line Ballot-box (BOB).
# $Id: genElection.pl,v 1.13 2005/11/01 18:25:01 dme26 Exp $
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


# These variables' values should be set in the electionConfig.pm file rather than here.
# (they're left here for interest's sake, and to unnecessarily slow things down a bit)

# Title embedded into Raven and the web forms.
# e.g. title='Graduate Union electronic ballot'
$title='electronic ballot';

# Prefix used on database tables, etc.
$ename='electionName';

# Returning Officer's email address
$emailRO='returningOfficer@localhost';

# Election administrator's email address
$emailTech='adminperson@localhost';

# Election administrator as referred to via HTML
$htmlTech="<a href=\"mailto:$emailTech\"> technical administrator ($emailTech)</a>";

# Returning Officer's title 
# (i.e. in the context of "The returning officer is ... ")
$ro='someone trustworthy';

# Returning officer as referred to via HTML
$htmlRO="<a href=\"mailto:$emailRO\">returning officer ($emailRO)</a>";

# Start time (YYYY-MM-DD HH:MM)
$ballotStart = "2005-11-02 00:00";

# End time (YYYY-MM-DD HH:MM)
$ballotEnd = "2005-11-02 20:00";

# Pre ballot open message
$htmlPreBallot = "<p>This ballot is not yet open. It will run from $ballotStart to $ballotEnd.</p>";

# Post ballot close message
$htmlPostBallot = "<p>This ballot has closed. It ran from $ballotStart to $ballotEnd.</p>";

# Not registerd voter message
$htmlNotRegistered = <<EOF;
<p>Unfortunately you do not appear to be recorded in our list of registered voters. 
Please contact the $htmlRO, or vote using alternative means (if applicable).</p>
EOF

# Database configuration variables

# Database host
$dbhost="localhost";

# Database name
$dbdb="yourDBname";

# Database user
$dbuser="yourDBuser";

# Database password
$dbpass="yourDBPassword";

# Election info (must put a backslash before $'s and "'s i.e. \$ and \")
$election=<<ENDOFDATA;

Position 1 - perhaps "President"
Some Candidate
Another Candidate

Another Position
A candidate for this position
Hopefully you get the idea

Each separate block
Represents another vote
First line of a block is the position title
All other lines are Candidate names

ENDOFDATA

# End of the user variables

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
my $blank=1;

$i=0;
foreach $el (split(/\n/,$election)){
    if($el =~ /^\s*$/) {
#	print "wline\n";
	if(!$blank){
	    $blank=1;
	    push(@eData,[@vData]) if $#vData>=0;
	    @vData=();
	}
    }else{
	$blank=0;
	push(@vData,$el);
    }
#    print "$i $el\n";
    $i++
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

if($f=openIfNoFile(".htaccess")){
    print $f <<EOF;
AACookieKey "${ename}$$"
AADescription "${title}"
AuthType Ucam-WebAuth
Require valid-user
DirectoryIndex index.html index.htm index.php ballot.html				
EOF
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

if($f=openIfNoFile("secret.php",0660)){

    # fix quoting for htmlNotRegistered
    $htmlNotRegistered =~ s/'/'."'".'/go; # '
    print $f <<EOF;
<?php 
\$title = "${title} response";
\$ename = "${ename}";
\$emailRO = "${emailRO}";
\$emailTech = "${emailTech}";
\$htmlRO = "<a href=\\"mailto:$emailRO\\">returning officer ($emailRO)</a>";
\$htmlTech = "<a href=\\"mailto:$emailTech\\">technical administrator ($emailTech)</a>";
\$dbhost = "${dbhost}";
\$dbuser = "${dbuser}";
\$dbpass = "${dbpass}";
\$dbdb = "${dbdb}";
${electionPHP}
\$htmlNotRegistered = '${htmlNotRegistered}';
\$htmlPreBallot = "${htmlPreBallot}";
\$htmlPostBallot = "${htmlPostBallot}";
EOF
    if($ballotStart =~ /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/){
	print $f "\$startBallot = mktime($4,$5,0,$2,$3,$1);\n"
    }else{
	print "! Unable to parse \$ballotStart date.\n";
    }
    if($ballotEnd =~ /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/){
	print $f "\$endBallot = mktime($4,$5,0,$2,$3,$1);\n"
    }else{
	print "! Unable to parse \$ballotEnd date.\n";
    }
    print $f <<EOF;
?>
EOF
    close $f;
}


if($f=openIfNoFile("voters.sql",0660)){
    print $f <<EOF;
USE ${dbdb};
INSERT INTO ${ename}voter VALUES ('dme26',0);
EOF
    close $f;
}

if($f=openIfNoFile("createtable.sql",0660)){
    print $f "USE ${dbdb};
DROP TABLE IF EXISTS ${ename}voter;
DROP TABLE IF EXISTS ${ename}vote;
CREATE TABLE ${ename}voter (
crsid VARCHAR(8) NOT NULL PRIMARY KEY,
voted TINYINT
) TYPE=InnoDB;
CREATE TABLE ${ename}vote (
token VARCHAR(32) NOT NULL PRIMARY KEY";
    for $i ( 0 .. $#eData ) {
        $ta = $eData[$i];
        $v=$i+1;
        for $j ( 1 .. @$ta-1 ) {
	    print $f ",\n  v${v}c${j} TINYINT";
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
random sequence of letters. This system does not store the connection
between this letter sequence and your identity, however it does email
the random letters alongside your vote to the returning officer (and
store it in a database to protect against email fraud).  When polls
have closed, the Returning Officer will print out a list of all the
votes cast - because only you will know your sequence of random
letters, you will be able to check that your vote was correctly
included.</p>

<hr />

<h3>How to vote</h3>

<p>Voting is by the Single Transferable Vote system as specified in
the Statutes of the University of Cambridge.</p>

<ul>

<li>Place a "1" next to the candidate you would like to see elected to
a given post, a "2" next to your second favourite, and so on.</li>

<li>Repeat this process for each post listed.</li>

<li>You do not have to express a preference for every candidate. It is
acceptable to leave boxes blank.</li>

<li>"RON" stands for "Re-Open Nominations". Vote for RON if you feel
that none of the candidates are adequate, or if you feel that no other
candidates apart from the ones you have already recorded a preference
for are adequate.</li>

<li>When you have completed this form CHECK IT, since once you submit
the form errors cannot be corrected and may invalidate your vote.</li>

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
	    $selectOpts .= "<option value=\"$j\">$j</option>";
	}
    }

    print $f <<EOF;
<h2>$eData[$i][0]</h2>
<table class="vote v$i">
  <tr><th>Candidate</th><th>Preference</th></tr>
EOF
$v=$i+1;
    for $j ( 1 .. $n ) {
	print $f "<tr class=\"c$j ".(($j%2)?"codd":"ceven")."\"><td>$eData[$i][$j]</td><td align=\"center\"><select name=\"v[$v][$j]\">$selectOpts</select></td></tr>\n";
    }

    print $f "</table>\n";
}

    if($md5sum = `md5sum vote.php`){
	$md5sum =~ s/\s.*$//;
	$md5sum =~ s/(....)/$1 /g;
	$md5sum = "The PHP source code has the following MD5 sum: <kbd>$md5sum</kbd>";
    }else{
	print "* Unable to calculate md5sum of vote.php.\n";
    }

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
guarantees.  Feel free to <a href="vote.txt">examine the simple PHP
code that makes it work</a> directly. $md5sum</p>

    <hr />
    <address>Contacts: ${htmlTech} or ${htmlRO}</address>
  </body>
</html>
EOF
    close $f;
}

# check that vote.php is present.
if(-f "vote.php"){
    print ": vote.php is present\n";
    if(-f "vote.txt"){
        print ": vote.txt is already present\n";
    }else{
        if((system "ln -s vote.php vote.txt")==0){
            print "+ Created vote.txt symlink to vote.php source viewing.\n";
        }else{
            print "! Failed to created vote.txt symlink to vote.php.\n";
	}
    }
}else{
    print "! vote.php does not appear to be present, but is necessary for the functioning of this system\n";
}

print <<EOF;

To complete installation you need to:
(1) Ensure that .htaccess will be protecting this directory
(2) Run createtable.sql to make the database tables, e.g.

    mysql -u ${dbuser} -p ${dbpass} ${dbdb} <createtable.sql

(3) Edit voters.sql to include items for all your users (the 0 means "hasn't voted")
(4) Run voters.sql to seed the database tables, e.g.

    mysql -u ${dbuser} -p ${dbpass} ${dbdb} <voters.sql

EOF
