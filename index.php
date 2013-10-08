<?php

## Config file for BOB ##
## All settings must be specified, except for these (which will revert to internal defaults if omitted): dbHostname,countingInstallation,countingMethod,urlMoreInfo,adminDuringElectionOK,randomisationInfo,frontPageMessageHtml,afterVoteMessageHtml,organisationName,organisationUrl,organisationLogoUrl,headerLocation,footerLocation

# Unique identifier for this ballot
$config['id'] = 'testelection';

# Database connection details
$config['dbHostname'] = 'localhost';
$config['dbDatabase'] = 'votes';
$config['dbPassword'] = 'your_password_goes_here';
$config['dbDatabaseStaging'] = false;	// or a different database name if the configuration is shifted from a staging database before the vote opens to the main append-only database
$config['dbUsername'] = 'testvote';
$config['dbSetupUsername'] = 'testvotesetup';

# Counting installation config; must end /openstv/ (slash-terminated)
$config['countingInstallation'] = '%documentroot/openstv/';
$config['countingMethod'] = 'ERS97STV';

# Title and info about the ballot
$config['title'] = "Some electronic ballot";	// Text, no HTML
$config['urlMoreInfo'] = 'http://www.example.com/';	// Or false if none

# Details of Returning Officer, Sysadmins, and usernames of the election officials
$config['emailReturningOfficer'] = 'returningOfficer@localhost';	// In a managed hosting scenario, this might be a master mailbox rather than the officials' e-mail account(s)
$config['emailTech'] = 'adminperson@localhost';
$config['officialsUsernames'] = 'abc12 xyz98';	// Space-separated

# Start and end of the ballot and when the votes can be viewed
$config['ballotStart'] = '2009-02-13 00:00:00';
$config['ballotEnd'] = '2009-02-18 00:01:00';
$config['ballotViewable'] = '2009-02-19 00:01:00';

# Textual information about any randomisation which may have been made
$config['randomisationInfo'] = false;	// Will have htmlspecialchars applied to it

# Percentage of voters who must cast a vote in a referendum for the referendum to be countable
$config['referendumThresholdPercent'] = 10;

# Extra messages (as HTML), if any, which people will see on the front page before voting, and when they have voted
$config['frontPageMessageHtml'] = false;
$config['afterVoteMessageHtml'] = false;

# Whether the administrator can access admin pages during the election
$config['adminDuringElectionOK'] = false;

# Organisation name, logo and link (all optional; set to false if not wanted)
$config['organisationName'] = "Some organisation";		// Will have htmlspecialchars applied to it
$config['organisationUrl'] = 'http://www.example.com/';
$config['organisationLogoUrl'] = 'https://www.example.com/somelogo.png';	// Will be resized to height=100; Also, you are advised to put this on an https host to avoid security warnings

# Location in the URL space of optional header and footer file; must start with /
$config['headerLocation'] = '/style/header.html';
$config['footerLocation'] = '/style/footer.html';

# Number of posts being elected; each position and the candidate names; each block separated by one line break
# If any contain accented/etc. characters, ensure this file is saved as UTF-8 without a Byte Order Mark (BOM)

$config['electionInfo'] = <<<ENDOFDATA

1
Position 1 - perhaps "President"
Some Candidate
Another Candidate
Re-Open Nominations ('RON')

1
Another Position
A candidate for this position
Hopefully you get the idea

2
Each separate block
Represents another vote
First line of a block is the position title
All other lines are Candidate names

1
Referendum: Do you agree with the proposed changes to the Constitution?
referendum

ENDOFDATA;


## End of config; now run the system ##

# Load and run the BOB class
require_once ('BOB.php');
new BOB ($config);

?>
