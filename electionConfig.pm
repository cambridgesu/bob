# $Id: electionConfig.pm,v 1.6 2006/10/22 16:54:47 dme26 Exp $
# This file is part of BOB: the Basic On-line Ballot-box
# http://www.cl.cam.ac.uk/~dme26/proj/BOB/
#####################################################################
# User variables you'll definitely need to change or at least check.
#

# Title embedded into Raven and the web forms.
# e.g. title='Graduate Union electronic ballot'
$title=<<EOF;
Some electronic ballot
EOF

# Prefix used on database tables, etc. Best use alphanumeric chars only.
$ename='electionName';

# Returning Officer's email address
$emailRO='returningOfficer@localhost';

# Election administrator's email address
$emailTech='adminperson@localhost';

# Election administrator as referred to via HTML (needs $emailTech defined first)
$htmlTech=<<EOF;
<a href="mailto:$emailTech"> technical administrator ($emailTech)</a>
EOF

# Returning Officer's title
# (i.e. in the context of "The returning officer is ... ")
$ro=<<EOF;
someone trustworthy
EOF

# Returning officer as referred to via HTML (needs $emailRO defined first)
$htmlRO=<<EOF;
<a href="mailto:$emailRO">returning officer ($emailRO)</a>
EOF

# Start time (YYYY-MM-DD HH:MM)
$ballotStart = '2005-11-02 00:00';

# End time (YYYY-MM-DD HH:MM)
$ballotEnd = '2005-11-02 20:00';

# Pre ballot open message
$htmlPreBallot=<<EOF;
<p>This ballot is not yet open. It will run from $ballotStart to $ballotEnd.</p>
EOF

# Post ballot close message
$htmlPostBallot=<<EOF;
<p>This ballot has closed. It ran from $ballotStart to $ballotEnd.</p>
EOF

# Not registerd voter message
$htmlNotRegistered=<<EOF;
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

# Database password (will go into "dbpasswd" file)
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

