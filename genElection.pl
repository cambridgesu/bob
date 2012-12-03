#!/usr/bin/perl -w

# This file is part of the Basic On-line Ballot-box (BOB).
# $Id: genElection.pl 127 2007-02-20 15:39:15Z dme26 $
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

$title=$ename=$emailRO=$emailTech=$ro=$eofficials=$ballotStart =$ballotEnd =$htmlPreBallot=$htmlPostBallot=$htmlNotRegistered=$dbhost=$dbdb=$dbuser=$dbpass=$election=$adminDuringElectionOK='';

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
DirectoryIndex index.html index.htm index.php ballot.php
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
${title}
EOV;
\$ename=<<<EOV
${ename}
EOV;
\$ro=<<<EOV
${ro}
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

doSymlinkSource("ballot");
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
(8) Run "make rinse" to remove the unnecessary files from the election system.
(9) The database permissions now need only be SELECT, INSERT, UPDATE.

EOF
