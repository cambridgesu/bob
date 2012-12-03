# $Id: electionConfig.pm,v 1.4 2005/11/01 18:25:01 dme26 Exp $
# This file is part of BOB: the Basic On-line Ballot-box
#####################################################################
# User variables you'll definitely need to change or at least check.
#

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
$dbdb="DBname";

# Database user
$dbuser="DBuser";

# Database password
$dbpass="DBPassword";

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
#####################################################################

