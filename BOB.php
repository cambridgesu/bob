<?php

/*
 * This file is part of the Basic Online Ballot-box (BOB).
 * https://github.com/cusu/bob
 * License: GPL; see below
 * Copyright David Eyers, Martin Lucas-Smith and contributors 2005-2013
 *
 * Significant contributions (but almost certainly not responsible for any nasty code) :
 * David Turner, Simon Hopkins, Robert Whittaker
 *
 * Requires : index.php as a bootstrap file; see installation notes below
 * Requires : Container-managed authentication
 * Uses     : MySQL
 *
 * Token word list Copyright The Internet Society (1998).
 *
 * Version 1.1.11
 *
 * Copyright (C) authors as above
 * 
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


/*
 *	INSTALLATION
 *	
 *	Create a file index.html or index.php (whichever is allowed in a DirectoryIndex) like this,
 *	which defines the settings then instantiates the BOB class (which must be in your include_path) with those settings.
 *	


<?php

## Config file for BOB ##
## All settings must be specified, except for these (which will revert to internal defaults if omitted): dbHostname,countingInstallation,countingMethod,urlMoreInfo,adminDuringElectionOK,randomisationInfo,referendumThresholdPercent,referendumThresholdIsYesVoters,frontPageMessageHtml,afterVoteMessageHtml,voterReceiptDisableable,disableListWhoVoted,organisationName,organisationUrl,organisationLogoUrl,headerLocation,footerLocation,additionalVotesCsvDirectory

# Unique identifier for this ballot
$config['id'] = 'testelection';

# Database connection details
$config['dbHostname'] = 'localhost';
$config['dbDatabase'] = 'testvote';
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
$config['paperVotingEnd'] = false;
$config['ballotViewableDelayed'] = false;

# Textual information about any randomisation which may have been made
$config['randomisationInfo'] = false;	// Will have htmlspecialchars applied to it

# Percentage of voters who must cast a vote in a referendum for the referendum to be countable
$config['referendumThresholdPercent'] = 10;

# Referenda pass if simple majority, but also requires either: (false, the default) x% to turn up to vote, or (true) yes-vote count not less than x% of eligible voters
$config['referendumThresholdIsYesVoters'] = false;

# Extra messages (as HTML), if any, which people will see on the front page before voting, and when they have voted
$config['frontPageMessageHtml'] = false;
$config['afterVoteMessageHtml'] = false;

# Whether users can choose to disable the e-mail vote receipt
$config['voterReceiptDisableable'] = false;

# Whether to disable the list of usernames who voted that appears on the show votes page afterwards (the default increases voter assurance but at expense of some privacy)
$config['disableListWhoVoted'] = false;

# Whether the administrator can access certain admin pages during the election
$config['adminDuringElectionOK'] = false;

# Organisation name, logo and link (all optional; set to false if not wanted)
$config['organisationName'] = "Some organisation";		// Will have htmlspecialchars applied to it
$config['organisationUrl'] = 'http://www.example.com/';
$config['organisationLogoUrl'] = 'https://www.example.com/somelogo.png';	// Will be resized to height=60; Also, you are advised to put this on an https host to avoid security warnings

# Location in the URL space of optional header and footer file; must start with /
$config['headerLocation'] = '/style/header.html';
$config['footerLocation'] = '/style/footer.html';

# Directory where additional votes cast on paper transcribed into a CSV file are stored; must have filename <id>.draft.csv (for testing of the result) and <id>.final.csv (which is made public)
$config['additionalVotesCsvDirectory'] = false;

# Number of posts being elected; each position and the candidate names; each block separated by one line break
# If any contain accented/etc. characters, ensure this file is saved as UTF-8 without a Byte Order Mark (BOM)

$config['electionInfo'] = <<<ENDOFDATA

1
Position 1 - perhaps "President"
Some Candidate
Another Candidate

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


 *	
 *	Alternatively create a file index.html or index.php (whichever is allowed in a DirectoryIndex) like this,
 *	but this time with most of the config being in a database table, and just the following defined in the settings file:
 *	


<?php

## Config file for BOB ##
## All settings must be specified, except for these (which will revert to internal defaults if omitted): dbHostname,countingInstallation,countingMethod

# Unique name for this ballot
$config['id'] = 'testelection';

# Database connection details
$config['dbHostname'] = 'localhost';
$config['dbDatabase'] = 'testvote';
$config['dbPassword'] = 'your_password_goes_here';
$config['dbDatabaseStaging'] = false;
$config['dbUsername'] = 'testvote';
$config['dbSetupUsername'] = 'testvotesetup';

# Optional database table containing the config which the dbSetupUsername has SELECT rights on
$config['dbConfigTable'] = 'instances';

# Counting installation config; must end /openstv/ (slash-terminated)
$config['countingInstallation'] = '%documentroot/openstv/';
$config['countingMethod'] = 'ERS97STV';


# The database table should contain these fields, in addition to id as above:
# title,urlMoreInfo,emailReturningOfficer,emailTech,officialsUsernames,ballotStart,ballotEnd,paperVotingEnd,ballotViewableDelayed,randomisationInfo,referendumThresholdPercent,frontPageMessageHtml,afterVoteMessageHtml,voterReceiptDisableable,disableListWhoVoted,adminDuringElectionOK,organisationName,organisationUrl,organisationLogoUrl,headerLocation,footerLocation,additionalVotesCsvDirectory,electionInfo
# However, urlMoreInfo,referendumThresholdPercent,referendumThresholdIsYesVoters,frontPageMessageHtml,afterVoteMessageHtml,voterReceiptDisableable,disableListWhoVoted,adminDuringElectionOK,headerLocation,footerLocation,additionalVotesCsvDirectory are optional fields which need not be created


## End of config; now run the system ##

# Load and run the BOB class
require_once ('BOB.php');
new BOB ($config);


 *	
	E.g. to create the instances table, use the following.
	Note that, in a managed GUI voting scenario, the items commented out with -- may be wanted. They are not needed by BOB itself.
	There are other fields, e.g. adminDuringElectionOK,headerLocation,additionalVotesCsvDirectory,footerLocation,voterReceiptDisableable,disableListWhoVoted are best set as a global config file option.

CREATE TABLE IF NOT EXISTS `instances` (
   `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Generated globally-unique ID',
-- `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Computed URL location of this ballot',
-- `academicYear` varchar(5) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Computed academic year string',
-- `urlSlug` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Unique identifier for this ballot',
-- `provider` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Provider name',
-- `organisation` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Organisation name',
   `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Title of this ballot',
   `urlMoreInfo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL for more info about the ballot',
   `frontPageMessageHtml` text COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional front-page message',
   `afterVoteMessageHtml` text COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'An extra message, if any, which people will see when they have voted',
   `emailReturningOfficer` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail address of Returning Officer / mailbox',
   `emailTech` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail address of Technical Administrator',
   `officialsUsernames` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Usernames of Returning Officer + Sysadmins',
   `randomisationInfo` enum('','Candidate order has been automatically randomised','Candidate order has been automatically alphabetised','Candidates have been entered by the Returning Officer in the order shown') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Candidate ordering/randomisation',
   `organisationName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Organisation name',
   `organisationUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Organisation URL',
   `organisationLogoUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL of organisation''s logo',
-- `addRon` enum('','Yes','No') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Should Re-Open Nominations (RON) be automatically added as an additional candidate in each election?',
   `electionInfo` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Election info: Number of positions being elected; Position title; Names of candidates; each block separated by one line break',
-- `electionInfoAsEntered` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Election info',
   `referendumThresholdPercent` int(2) DEFAULT '10' COMMENT 'Percentage of voters who must cast a vote in a referendum for the referendum to be countable',
   `referendumThresholdIsYesVoters` int(1) DEFAULT NULL COMMENT 'Whether the threshold refers to yes-vote level (rather than all voter turnout)',
   `ballotStart` datetime NOT NULL COMMENT 'Start date/time of the ballot',
   `ballotEnd` datetime NOT NULL COMMENT 'End date/time of the ballot',
   `paperVotingEnd` datetime DEFAULT NULL COMMENT 'End time of paper voting, if paper voting is also taking place',
   `ballotViewableDelayed` datetime NOT NULL COMMENT 'End date/time for delayed viewing of results by voters',
-- `instanceCompleteTimestamp` datetime DEFAULT NULL COMMENT 'Timestamp for when the instance (configuration and voters list) is complete',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


 *	
 */


/*

BOB: Threats and risks
----------------------

We focus on the relevance to BOB of two of the primary risks inherent in 
common balloting systems, both paper-based and electronic:

(a) Manipulation of the results of an election

(b) Compromised anonymity of voters

Although strenuous efforts have been made to avoid the BOB software 
containing flaws, it has always been a design goal to shun approaches that 
cannot have their results verified independently of the software.

The catch-all for handling (a) is the post-election verification process 
(PEV). During the PEV all recorded votes and corresponding pseudononymous 
tokens are displayed to the electorate. Voters can choose to reveal the 
link between their recorded preferences and their otherwise secret 
pseudononymous token, in order to demonstrate a flawed ballot record. For 
the PEV to be effective, it is key that a sufficient number of voters 
check that their votes really have been recorded correctly. In contentious 
cases, it may sometimes make sense to go further and have a trusted third 
party (e.g. the returning officer) keep a list of people signing that they 
have verified their votes.

The PEV itself must be carried out in an appropriate manner - e.g. both 
electronic and hard-copy output produced, with the latter placed in an 
accessible but secure location, in which manipulation is likely to be 
detected.

In terms of (b), voters can choose to compromise their own anonymity, e.g. 
to sell votes. This is an unfortunate but unavoidable side-effect of the 
PEV. BOB was designed in the view that the value of the PEV outweighed the 
risks of vote-selling.


== Participants ==

Types of people in the BOB process:

(*) Voters.

(*) Returning officer: receives electronic votes by email.

(*) Sysadmins: those who have super-user privileges on the computer 
running the voting system code. In cases where the sysadmins are directly 
involved in the election - as opposed to operating a shared 
general-purpose computing infrastructure - the sysadmins perform the key 
security-related aspects of the traditional role of a Returning Officer; 
they are effectively the transporters and guards of votes.

(*) Election admins: those who install and/or configure the voting system. 
They may be overlap with the sysadmin group.


== Some technological risk areas ==

All of the technologies on which BOB relies need to be considered for 
risks to voting process and voter anonymity. Consider possible privileges 
over ---and the side-channels available from---at least the following:

* The BOB configuration and code. * The webserver. Access logs, webserver 
config, PHP's "helpfulness", etc. * The database server. Logs, etc. * The 
MTA for email. Logs, etc. * Server OS, file system, physical equipment, 
etc. * The network in which the server is placed: also issues such as DNS.

At a technical level, anonymity can be compromised by having access to the 
webserver, database server, MTA or OS (e.g. log file monitoring, network 
monitoring).

The BOB software contains a large number of checks and balances, and will 
attempt to highlight accidental misconfiguration of an election. Assuming 
trustworthy sysadmins and an uncompromised base system, it is easy for the 
BOB software to provide an indication to voters, e.g. through the 
presentation of MD5 checksums, as to whether its configuration or code has 
changed, for example under the control of election admins.

In terms of malicious damage, if the sysadmins are party to this 
deception, or intruders gain sysadmin privileges, there is no current 
mechanism by which BOB can inform voters of election admins of this 
reliably. However, the election outcome will be protected by the PEV.


== Recommended deployment checks ==

Some checks recommended for BOB deployment are:

* That access control to the DNS entries for the voting server be
   audited.

* That the MTA correctly strips BCC headers from messages. (The
   decision was made to send one message from BOB to both the voter and
   the returning officer. Sending two emails carries the risk that the
   system fails between the two message sends (of course some
   unavoidable, pathological failure conditions remain).)

* The set of users for MySQL be examined to ensure that only
   appropriate users have access to the necessary BOB tables. (Two
   database users are employed per ballot to ensure that the privileges
   for setting up a ballot database are isolated from the privileges
   required to run a ballot. Consider that MySQL replication could make
   it easier for attackers to compromise voter anonymity.)

* The webserver access and error logs need to be protected.

* The cookie-based authentication scheme, and all web interactions,
   run over HTTPS. (BOB itself should enforce this also.) Authentication
   cookies are password-equivalent to an active attacker over HTTP links.

* The Returning Officer is encouraged to enlist people to perform PEV.


== Common BOB deployment scenarios ==

BOB can run - and has been run - in many different system configurations. 
Some configurations carry more risk than others. For example:

(1) An externally-managed service. Election officials, the returning 
officer and voters have no access to logs or configuration settings of the 
webserver, database server, MTA information or machine OS, etc. In this 
situation both election officials and voters need to trust the sysadmins, 
and that the managed setup is safe in terms of vulnerabilities and leaks 
in all areas. Since one system of this type will probably host multiple 
elections, the data of each election must be isolated (particularly 
regarding votes being configured in parallel to ballots being open, and 
parallel ballots). Printed diagnostics from the code (e.g. MD5 checksums, 
configuration variables, etc) can be selectively faked if the hosting is 
compromised. On a non-compromised system, these diagnostics can give 
voters confidence that the configuration of the election is valid and 
unmodified during the course of the election.

(2) Local installation (e.g. on equipment managed by the organisation 
carrying out the vote). Sysadmins on that equipment can trivially and 
undetectably violate anonymity if they are able to observe voters voting 
at the same time as monitoring the voting server.  Software can be 
maliciously modified to return fake MD5 sums, etc.  If the software 
operates as specified, then the voters can trust that the election 
officials (assuming that they are not sysadmins) cannot undetectably 
(until PEV) manipulate the configuration or results of an election.

(3) Restricted hosting (e.g. on multi-user systems). The election admins 
in these contexts are assumed to have very limited administrative rights. 
They may not be able to set up entities to run with less privilege than 
themselves. This breaks the potential to enforce desirable isolation 
properties in terms of BOB configuration and BOB databases. Anonymity is 
probably easily breakable by other users running on a multi-user system 
who are able to observe voters in real life simultaneously. As in all 
cases, though, the PEV can determine whether manipulation of the balloting 
process occurred. This kind of scenario is generally not recommended.

While the above considerations may appear quite onerous, most paper-based 
balloting systems also carry serious risks of manipulation: we note that 
they will usually preserve voter anonymity more effectively than being 
able to guarantee the election outcome.


- The BOB team, February 2009.

 */


/*

Explanation of BLT format
=========================

4 2          # four candidates are competing for two seats
-2           # Bob has withdrawn (optional)
1 4 1 3 2 0  # first ballot
1 2 4 1 3 0
1 1 4 2 3 0  # The first number is the ballot weight (>= 1).
1 1 2 4 3 0  # The last 0 is an end of ballot marker.
1 1 4 3 0    # Numbers in between correspond to the candidates
1 3 2 4 1 0  # on the ballot.
1 3 4 1 2 0
1 3 4 1 2 0  # Chuck, Diane, Amy, Bob
1 4 3 2 0
1 2 3 4 1 0  # last ballot
0            # end of ballots marker
"Amy"        # candidate 1
"Bob"        # candidate 2
"Chuck"      # candidate 3
"Diane"      # candidate 4
"Gardening Club Election"  # title


 */


/*
 *	TODO
 *	
 *	- Reverse the looping design in vote()
 *	- Replace the mysql_ API calls with PDO calls, using prepared statements
 *	- Convert all pages to return $html rather than echo it directly
 *	- Add the ability to disable the verifyRuntimeDatabasePrivileges() check, as two users may not be possible in some hosting environments; that will reduce security to some extent
 *	- Consider changing the duringElection, afterElection, afterBallotView flags to a state model and set this using NOW() in the database call
 *	- In the registeredVoters() routine, when there are no users, as well as the SQL, list the same thing but in a format directly invocable at shell, with a --extra-defaults-file=mysql.ini flag; then change the openDatabaseConnection()'s call of file_get_contents to an ini read
 *	- Enable the string 'Raven' and the .htaccess example to be non-Raven specific (or show the example as one of a number of examples)
 *	- Other items marked with #!# below
 */




# PHP5 class to implement an online election system
class BOB
{
	# Config defaults (setting both structure and default values; NULL means that the instantiator must supply a value); see the above under 'INSTALLATION' for what these each mean
	private $defaults = array (
		'id'					=> NULL,
		'dbHostname'			=> 'localhost',
		'dbPassword'			=> NULL,
		'dbDatabase'			=> NULL,
		'dbDatabaseStaging'		=> false,
		'dbUsername'			=> NULL,
		'dbSetupUsername'		=> NULL,
		'dbConfigTable'			=> false,
		'countingInstallation'		=> '%documentroot/openstv/',		// Absolute path to counting installation; must end /openstv/ (slash-terminated) ; %documentroot can be used as a starting placeholder which will be substituted if present
		'countingMethod'		=> 'ERS97STV',				// Method as per the list at https://github.com/cusu/openstv/tree/master/openstv/MethodPlugins
	);
	
	# Config defaults (setting both structure and default values; NULL means that the instantiator must supply a value) that can come from a database table; if not these will get merged with the above main defaults
	private $defaultsDatabaseable = array (
		'title'							=> NULL,
		'urlMoreInfo'					=> false,
		'emailReturningOfficer'			=> NULL,
		'emailTech'						=> NULL,
		'officialsUsernames'			=> NULL,
		'randomisationInfo'				=> false,
		'referendumThresholdPercent'	=> 10,
		'referendumThresholdIsYesVoters'	=> false,
		'adminDuringElectionOK'			=> false,
		'ballotStart'					=> NULL,
		'ballotEnd'						=> NULL,
		'paperVotingEnd'				=> false,
		'ballotViewableDelayed'				=> false,
		'frontPageMessageHtml'			=> false,
		'afterVoteMessageHtml'			=> false,
		'voterReceiptDisableable'			=> false,
		'disableListWhoVoted'			=> false,
		'organisationName'				=> false,
		'organisationUrl'				=> false,
		'organisationLogoUrl'			=> false,
		'headerLocation'				=> false,
		'footerLocation'				=> false,
		'additionalVotesCsvDirectory'			=> false,
		'electionInfo'					=> NULL,
	);
	
	
	# Registry of available actions
	private $actions = array (
		
		/* Functions available to normal users */
		'home' => array (
			'description' => 'Summary',
			'administrator' => false,
		),
		/* Functions available to normal users */
		'vote' => array (
			#!# The description should really change to "Your vote has been cast" once cast
			'description' => 'Cast your vote',
			'administrator' => false,
		),
		'showvotes' => array (
			'description' => 'View votes',
			'administrator' => false,
		),
		'results' => array (
			'description' => 'View results',
			'administrator' => false,
		),
		'viewsource' => array (
			'description' => 'Show the source code of this program',
			'administrator' => false,
			'disableGui' => true,
		),
		
		/* Admin functions */
		'admin' => array (
			'description' => 'Admin menu',
			'administrator' => true,
		),
		'admin_rollcheck' => array (
			'description' => 'Roll check for a user',
			'administrator' => true,
		),
		'admin_viewform' => array (
			'description' => 'View the ballot form',
			'administrator' => true,
		),
		'admin_paperroll' => array (
			'description' => 'Electoral roll for paper voting (following online voting)',
			'administrator' => true,
			'require' => 'paperVotingFollowsOnlineVoting',
		),
		'admin_ballotpapers' => array (
			'description' => 'Printable ballot papers for paper voting (following online voting)',
			'administrator' => true,
			'require' => 'paperVotingFollowsOnlineVoting',
		),
		'admin_additionalvotes' => array (
			'description' => 'Enter additional votes (from paper voting)',
			'administrator' => true,
			'require' => 'splitElection',
		),
	);
	
	
	# State variables
	private $databaseConnection = NULL;		// Database connection
	private $errors = array ();				// Array of setup errors
	private $config = array ();				// Global config (i.e. $this->config; not $config which comes into the constructor, which is local)
	private $username = NULL;				// Username of voter
	private $beforeElection = false;		// Whether we are before the election has opened
	private $duringElection = false;		// Whether we are during the election (online voting)
	private $afterElection = false;			// Whether we are after the election has closed
	private $afterBallotView = false;		// Whether we are after the point when the ballot is viewable
	private $registeredVoters = 0;			// Number of registered voters
	private $totalVoted = 0;				// Number of people that have voted
	private $userIsRegisteredVoter = false;	// Whether the user is on the electoral roll
	private $userHasVoted = false;			// Whether the user has voted
	
	
	# Other class variables
	#!# Some of these could be made into per-instance settings, e.g. logoutLocation and convertTo_CandidateToNumber
	private $logoutLocation = 'logout.html';		// Logout location which will get inserted (unprocessed) into strings mentioning this
	private $documentRoot = false;			// Document root, which will be derived from DOCUMENT_ROOT
	private $headerHtml = '';				// HTML for the header
	private $footerHtml = '';				// HTML for the footer
	private $pageTitle = false;				// Title of the page
	private $voterTable = false;			// Name of the table of voters
	private $votesTable = false;			// Name of the table of votes
	private $ballotStartFormatted;			// Formatted date for ballot start
	private $ballotEndFormatted;			// Formatted date for ballot end
	private $ballotViewableFormatted;		// Formatted date for ballot being viewable
	private $positionInfo;					// Positions available per election; derived from electionInfo
	private $bobMd5;						// MD5 of the BOB program (this file)
	private $configMd5;						// MD5 of the config being used
	private $convertTo_CandidateToNumber = true;	// In the admin ballot printing mode, whether to convert to candidate=>number format
	private $additionalVotePrefix = 'additionalvote';	// Prefix for additional votes
	private $additionalVotesFile = false;		// Additional votes file - filename in use (if any)
	private $additionalVotesFileFinal = false;	// Additional votes file - whether the data is finalised
	
	# Define what a referendum looks like in terms of the available candidates
	private $referendumCandidates = array ('0' => '(blank)', '1' => 'Yes', '2' => 'No');
	
	# Contants
	const MINIMUM_PHP_VERSION = '5';
	
	
	
	/* START OF SETUP, SANITY-CHECKING AND INSTANTIATION SECTION */
	
	# Constructor (front controller)
	function __construct ($config = array ())	// $config is an array coming from a launching file such as index.php or index.html which instantiates the class
	{
		# Assign the load time
		$this->loadtime = time ();
		
		# Create an HTML representation of the config structure so it can be echoed to screen below
		$configHtml  = $this->configHtml ($config, 'coming into the system');
		
		# Assign the configuration
		if (!$this->assignConfiguration ($config)) {
			$this->showErrors ();
			return false;
		}
		
		# Process parts of the configuration
		if (!$this->processConfiguration ()) {
			$this->showErrors ();
			return false;
		}
		
		# Create an HTML representation of the config structure so it can be echoed to screen below
		$configHtml .= $this->configHtml ($this->config, 'after it has been processed and sanitised, as used by the runtime voting workflow');
		
		# Ensure a clean server environment (e.g. register_globals off, etc.)
		if (!$this->environmentIsOk ()) {
			$this->showErrors ();
			return false;
		}
		
		# Ensure that the DOCUMENT_ROOT is not slash-terminated
		$this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
		if (substr ($_SERVER['DOCUMENT_ROOT'], -1) == '/') {
			$this->documentRoot = substr ($_SERVER['DOCUMENT_ROOT'], 0, -1);
		}
		
		# Ensure there is a username and assign it
		if (!$this->username = $this->getUsername ()) {
			$this->showErrors ();
			return false;
		}
		
		# Set the table names for this vote
		$this->voterTable = $this->config['id'] . '_voter';		// Username + voted flag
		$this->votesTable = $this->config['id'] . '_votes';		// Storage of votes + vote token
		
		# Set up the tables if they do not exist, complaining if they exist but are incorrect
		if (!$this->setupTables ()) {
			$this->showErrors ();
			return false;
		}
		
		# Connect to the database at the runtime user privilege level
		if (!$this->openDatabaseConnection ($this->config['dbUsername'])) {
			$this->errors[] = "... therefore the runtime database connection could not be established.";
			$this->showErrors ();
			return false;
		}
		
		# Ensure the correct privileges for the runtime database user are in place
		if (!$this->verifyRuntimeDatabasePrivileges ()) {
			$this->showErrors ();
			return false;
		}
		
		# Ensure that there are users in the voter table
		#!# registeredVoters should probably be renamed votersOnOnlineRoll, or similar, for clarity
		if (!$this->registeredVoters = $this->registeredVoters ()) {
			$this->showErrors ();
			return false;
		}
		
		# Ensure the server environment provides sufficient memory
		if (!$this->environmentProvidesSufficientMemory ()) {
			$this->showErrors ();
			return false;
		}
		
		# Ensure the counting program is correctly installed, if specified
		if (!$this->countingProgramConfigValid ()) {
			$this->showErrors ();
			return false;
		}
		
		# Get the total number of people that have voted; this also performs a database consistency check
		$this->totalVoted = $this->totalVoted ();
		if ($this->totalVoted === false) {
			$this->showErrors ();
			return false;
		}
		
		# Set whether the user is on the electoral roll and whether they have voted
		list ($this->userIsRegisteredVoter, $this->userHasVoted) = $this->userRegisteredVoted ();
		
		# Set whether the user is an election official
		$this->userIsElectionOfficial = $this->userIsElectionOfficial ();
		
		# Set time-based states
		$this->beforeElection = $this->beforeElection ();
		$this->duringElection = $this->duringElection ();
		$this->afterElection = $this->afterElection ();
		$this->afterBallotView = $this->afterBallotView ();
		$this->afterBallotViewDelayed = $this->afterBallotViewDelayed ();
		
		# Ensure there are no votes before the start of the election
		if ($this->beforeElection && $this->totalVoted) {
			$this->errors[] = "There are people marked as having voted, but the election has not yet started.";
			$this->showErrors ();
			return false;
		}
		
		# Set whether this is a split election (online and paper)
		$this->splitElection = $this->splitElection ();
		
		# Set whether, for a split election, whether paper voting follows online voting (rather than being concurrent)
		$this->paperVotingFollowsOnlineVoting = ($this->splitElection && ($this->config['paperVotingEnd'] > $this->config['ballotEnd']));
		
		# Ensure correct setup of any CSV file(s) for additional votes transcribed from paper
		if (!$this->additionalVotesSetupOk ()) {
			$this->showErrors ();
			return false;
		}
		
		# Unset actions failing to meet a required property
		foreach ($this->actions as $action => $attributes) {
			if (array_key_exists ('require', $attributes)) {
				$requirementProperty = $attributes['require'];
				if (!$this->{$requirementProperty}) {
					unset ($this->actions[$action]);
				}
			}
		}
		
		# Validate and set the action
		$defaultAction = 'home';
		$requestedAction = (strlen ($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : $defaultAction);
		$action = (isSet ($this->actions[$requestedAction]) ? $requestedAction : false);
		
		# Set the title
		if ($action) {
			$this->pageTitle = htmlspecialchars ($this->config['title']) . ($this->actions[$action]['description'] ? ':<br />' . htmlspecialchars ($this->actions[$action]['description']) : '');
		} else {
			$this->pageTitle = 'Error 404: Page not found';
		}
		
		# Assign the header and footer
		if (!$this->assignHeaderAndFooter ()) {
			$this->showErrors ();
			return false;
		}
		
		# Determine whether to disable the GUI
		$disableGui = ($action && isSet ($this->actions[$action]) && isSet ($this->actions[$action]['disableGui']) && $this->actions[$action]['disableGui']);
		
		# Show the header, and echo out the config in the HTML comments
		if (!$disableGui) {
			echo $this->headerHtml;
			echo $configHtml;		// View source to see this
		}
		
		# Show the title
		if (!$disableGui) {
			echo "\n<p class=\"loggedinas\">You are logged in as: <strong>{$this->username}</strong> [<a href=\"{$this->logoutLocation}\" class=\"logout\">log out</a>]</p>";
			echo "\n" . '<p class="navigationmenu"><a href="./">Ballot home</a>' . ($this->userIsElectionOfficial ? ' | <a href="./?admin">Admin section</a>' : '') . '</p>';
			echo "\n\n\n<h1>{$this->pageTitle}</h1>\n\n";
		}
		
		# Take the action
		if ($action) {
			
			# Ensure the user has rights
			$actionRequiresElectionOfficial = $this->actions[$action]['administrator'];
			$userAuthorised = ($this->userIsElectionOfficial || !$actionRequiresElectionOfficial);
			if ($userAuthorised) {
				
				# Reassure admins about admin-only pages
				if ($this->userIsElectionOfficial && $actionRequiresElectionOfficial) {
					echo "\n<p><em>Note: You can only access this page because you are an election official. It is not available to ordinary voters.</em></p>";
				}
				
				# Run the (now validated and authorised) function
				$this->$action ();
			} else {
				echo "\n<p>Sorry, you do not have rights to access this section.</p>";
			}
		} else {
			echo "\n<p>There is no such page. Please check the URL and try again.</p>";
			#!# Ideally this would throw a 404 but that cannot be done until each action returns HTML rather than echos it directly (because then the code surrounding the current block can just build then echo $html;
			// header ('HTTP/1.0 404 Not Found');
		}
		
		# Show the footer
		if (!$disableGui) {
			echo $this->footerHtml;
		}
		
		# Explicitly the database connection
		$this->closeDatabaseConnection ();
	}
	
	
	# Function to convert the config to an HTML comment so a user can verify what is coming into the system
	private function configHtml ($config, $sourceDescription)
	{
		# Blank out the password field; and fields containing machine paths
		$config['dbPassword'] = '[...]';
		$config['countingInstallation'] = '[...]';
		$config['additionalVotesCsvDirectory'] = '[...]';
		
		# Build the HTML
		$html  = "\n\n<!-- This is the config {$sourceDescription}, with entity conversion added:\n\n";
		$html .= htmlspecialchars (print_r ($config, true));	// HTML specialchars is essential to avoid a --> string closing the comment prematurely
		$html .= "\n-->\n\n";
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to merge the config
	private function assignConfiguration ($suppliedArguments)	// Supplied arguments comes from the config stub launching file
	{
		# If database config is used, retrieve that additional config
		$defaults = $this->defaults;
		if (isSet ($suppliedArguments['dbConfigTable']) && $suppliedArguments['dbConfigTable'] && is_string ($suppliedArguments['dbConfigTable'])) {
			
			# Merge the core defaults (id and database connectivity), so that there is enough to connect to the database and select the config
			if (!$this->config = $this->mergeConfiguration ($defaults, $suppliedArguments)) {
				return false;
			}
			
			# Connect to the database at the setup user privilege level
			if (!$this->openDatabaseConnection ($this->config['dbSetupUsername'])) {
				$this->errors[] = "... therefore the setup database connection could not be established.";
				return false;
			}
			
			# Obtain the current fields; no error handling needed as we know that the table exists; the escaping is used just in case the admin has specified a stupid table name in the config, though this is not a security issue
			# Note there is no problem if this table has additional fields - these will be ignored in the mergeConfiguration() routine and will never get past that into the rest of the system
			$query = "SELECT * FROM `{$this->config['dbDatabase']}`.`{$this->config['dbConfigTable']}` WHERE id = '" . mysql_real_escape_string ($this->config['id']) . "' LIMIT 1;";
			if (!$data = $this->getData ($query)) {
				
				# If there is no staging database specified, throw an error
				if (!$this->config['dbDatabaseStaging']) {
					$this->errors[] = "A database-stored configuration in the '<strong>" . htmlspecialchars ("{$this->config['dbDatabase']}.{$this->config['dbConfigTable']}") . "</strong>' table for an election with id '<strong>" . htmlspecialchars ($this->config['id']) . "</strong>' was specified but it could not be retrieved.";
					return false;
				} else {
					
					# Now try to fallback to the staging database, ensuring that it is for a ballot that has not yet opened (which therefore prevents the use of the staging database for live votes)
					$query = "SELECT * FROM `{$this->config['dbDatabaseStaging']}`.`{$this->config['dbConfigTable']}` WHERE id = '" . mysql_real_escape_string ($this->config['id']) . "' AND NOW() < ballotStart LIMIT 1;";
					if (!$data = $this->getData ($query)) {
						$this->errors[] = "A database-stored configuration in the '<strong>" . htmlspecialchars ("{$this->config['dbDatabaseStaging']}.{$this->config['dbConfigTable']}") . "</strong>' staging table for an election with id '<strong>" . htmlspecialchars ($this->config['id']) . "</strong>' was specified but it could not be retrieved.";
						return false;
					}
					
					# Set the staging database as the database to be used by the runtime
					$suppliedArguments['dbDatabase'] = $this->config['dbDatabaseStaging'];
					
					# Force reconnection later with the new database name
					$this->closeDatabaseConnection ();
				}
			}
			
			# Ensure there is only one config; if not, the instances table doesn't have a unique key for id
			if (count ($data) != 1) {
				$this->errors[] = "More than one database-stored configuration in the '<strong>" . htmlspecialchars ("{$this->config['dbDatabase']}.{$this->config['dbConfigTable']}") . "</strong>' table was retrieved for a configuration with id '<strong>" . htmlspecialchars ($this->config['id']) . "</strong>'. Please ensure the id field has the UNIQUE KEY specifier.";
				return false;
			}
			
			# Merge the data into the supplied arguments, with any specified in the physical config file taking precedence for security
			$suppliedArguments = array_merge ($data[0], $suppliedArguments);
		}
		
		# Merge in the non-core ('databaseable') defaults; file (non-databaseable) defaults take priority
		$defaults += $this->defaultsDatabaseable;
		
		# Merge the full configuration (or end if failure) and make the full config available as a class variable
		if (!$this->config = $this->mergeConfiguration ($defaults, $suppliedArguments)) {
			return false;
		}
		
		# Unset specification of the staging database as it is no longer required in the configuration
		unset ($this->config['dbDatabaseStaging']);
		
		# Signal success
		return true;
	}
	
	
	# Function used by assignConfiguration to merge defaults with supplied config
	private function mergeConfiguration ($defaults, $suppliedArguments)
	{
		# Start a list of errors (so that all setup errors are shown at once)
		$errors = array ();
		
		# Merge the defaults
		$arguments = array ();
		foreach ($defaults as $argument => $defaultValue) {
			
			# Sanity check: fields marked NULL or array() in the defaults MUST be supplied in the config and must not be an empty string
			if ((is_null ($defaultValue) || $defaultValue === array ()) && (!isSet ($suppliedArguments[$argument]) || !strlen ($suppliedArguments[$argument]))) {
				$errors[] = "No '<strong>{$argument}</strong>' has been set in the configuration.";
				
			# Having passed the check, reverting to the default value if no value is specified in the supplied config
			} else {
				$arguments[$argument] = (isSet ($suppliedArguments[$argument]) ? $suppliedArguments[$argument] : $defaultValue);
			}
		}
		
		# Assign and return the errors if there are any
		if ($errors) {
			$this->errors += $errors;
			return false;
		}
		
		# Return the arguments
		return $arguments;
	}
	
	
	# Function to process parts of the config
	private function processConfiguration ()
	{
		# Force the unique ID is lowercased (to ensure compatibility with MySQL tables when running on Windows)
		if (!preg_match ('/^([-a-z0-9]+)$/D', $this->config['id'])) {
			$this->errors[] = "The '<strong>id</strong>' setting in the configuration contains characters other than lower-case a-z, numbers or hyphens.";
			return false;
		}
		
		# Convert the string containing the list of places/position-name/candidates into an array of places and an array of (position-name, candidate1, candidate2, etc.)
		$string = trim ($this->config['electionInfo']);						// Trim whitespace at edges
		$this->config['electionInfo'] = array ();
		$this->positionInfo = array ();
		$string = str_replace ("\r\n", "\n", $string);	// Standardise to Unix newlines
		if (substr_count ($string, "\n\n\n")) {
			$this->errors[] = "The '<strong>ename</strong>' setting in the configuration contains triple-line breaks. There must only be a single extra line between each election.";
			return false;
		}
		$elections = explode ("\n\n", $string);			// Split into each election, by finding the double line-breaks (if any - if not, there is only a single election)
		foreach ($elections as $index => $election) {
			$election = trim ($election);				// Just in case but should never arise
			$this->config['electionInfo'][$index] = explode ("\n", $election);
			
			# Extract the first item in the block, and make that the number of positions available
			$this->positionInfo[] = array_shift ($this->config['electionInfo'][$index]);
		}
		
		# Convert the times to unixtime
		$timeSettings = array ('ballotStart', 'ballotEnd', 'paperVotingEnd', 'ballotViewableDelayed');
		foreach ($timeSettings as $timeSetting) {
			
			# The paperVotingEnd setting, is not required; only perform the check and conversion to UNIX time below if not false/empty
			if (($timeSetting == 'paperVotingEnd') || ($timeSetting == 'ballotViewableDelayed')) {
				if ($this->config[$timeSetting] == '0000-00-00 00:00:00') {$this->config[$timeSetting] = false;}	// Deal with 32/64 bit compatibility; see: http://stackoverflow.com/questions/141315
				if (!$this->config[$timeSetting]) {
					$this->config[$timeSetting] = false;	// Explicitly cast false/NULL/0/''/'0'/etc. (see http://php.net/types.comparisons ) as false, to avoid persistence as some other equivalent of false
					continue;
				}
			}
			
			# Check formatting as SQLtime string, e.g. '2013-11-06 17:00:00'
			if (!preg_match ('/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/D', $this->config[$timeSetting], $matches)) {
				$this->errors[] = "The '<strong>{$timeSetting}</strong>' setting in the configuration is not formatted correctly; it should be similar to this: " . date ('Y') . '-01-01 00:00:00';	// date('Y') just used to make pretty documentation but Jan 1st so it's an obviously "example" date
				return false;
			}
			
			# Convert to UNIX timestamp
			list ($wholeString, $year, $month, $day, $hour, $minute, $second) = $matches;
			$this->config[$timeSetting] = mktime ($hour, $minute, $second, $month, $day, $year);
		}
		
		# Validate that the ballot ends after it opens (and is not the same)
		if ($this->config['ballotEnd'] <= $this->config['ballotStart']) {
			$this->errors[] = "The time settings for this ballot in the configuration are wrong; the end time must be after the start time.";
			return false;
		}
		
		# Validate that any paper voting time is after the ballot start point
		if ($this->config['paperVotingEnd']) {
			if ($this->config['paperVotingEnd'] <= $this->config['ballotStart']) {
				$this->errors[] = "The time settings for this ballot in the configuration are wrong; the close of paper voting time must be after the start time.";
				return false;
			}
		}
		
		# Determine the ballotViewable time (is the later of online and paper voting)
		#!# Rename this variable to ballotsViewable for clarity perhaps
		$this->ballotViewable = max ($this->config['ballotEnd'], $this->config['paperVotingEnd']);
		
		# Set the ballotViewableDelayed to be the same as ballotViewable if not specified
		$this->ballotViewableDelayed = ($this->config['ballotViewableDelayed'] ? $this->config['ballotViewableDelayed'] : $this->ballotViewable);
		
		# Validate that any delayed ballot viewable time is not before the ballot viewable time
		if ($this->ballotViewableDelayed < $this->ballotViewable) {
			$this->errors[] = "The time settings for this ballot in the configuration are wrong; the delayed ballot viewable time must be the same or after the ballot viewable time.";
			return false;
		}
		
		# Create formatted versions of each of the times
		$this->ballotStartFormatted = date ('H:ia, l, jS F Y', $this->config['ballotStart']);
		$this->ballotEndFormatted = date ('H:ia, l, jS F Y', $this->config['ballotEnd']);
		$this->paperVotingEndFormatted = date ('H:ia, l, jS F Y', $this->config['paperVotingEnd']);
		$this->ballotViewableFormatted = date ('H:ia, l, jS F Y', $this->ballotViewable);
		$this->ballotViewableDelayedFormatted = date ('H:ia, l, jS F Y', $this->ballotViewableDelayed);
		
		# Create an MD5 hash of BOB itself and a serialised version of the config
		$this->bobMd5 = md5_file (__FILE__);
		$this->configMd5 = md5 (serialize ($this->config));
		
		# Signal success
		return true;
	}
	
	
	# Generalised support function to display errors
	private function showErrors ($heading = '')
	{
		# Build up a list of errors if there are any
		$html = '';
		if ($this->errors) {
			$html .= "\n" . '<h1>Online voting setup errors</h1>';
			$html .= "\n" . '<div class="error">';
			$html .= "\n" . '<p>The following setup errors were found, so this system cannot run. The administrator needs to correct these errors.';
			$html .= "\n\t<ul>";
			foreach ($this->errors as $error) {
				$html .= "\n\t\t<li>" . nl2br ($error) . '</li>';
			}
			$html .= "\n\t</ul>";
			$html .= "\n</div>";
		}
		
		# Explicitly close the database connection to prevent further execution (this is otherwise done implicitly by PHP anyway at script end)
		$this->closeDatabaseConnection ();
		
		# Show the result
		echo $html;
	}
	
	
	# Function to show the header
	private function assignHeaderAndFooter ()
	{
		# Define the default header HTML
		$this->headerHtml = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Online voting</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<style type="text/css">
			body {font-family: sans-serif;}
			h2 {font-size: 1.3em; margin-top: 2.2em; margin-bottom: 0.6em;}
		</style>
	</head>
	<body>
  		' . "\n\n\n";
		
		# Define the default footer HTML
		$this->footerHtml = "\n\n\n" . '
		<hr />
	</body>
</html>
  		';
		
		# Sanity-check the header/footer file(s)
		$types = array ('header', 'footer');
		foreach ($types as $type) {
			$configType = $type . 'Location';
			
			# Skip if nothing defined
			if (!$this->config[$configType]) {continue;}
			
			# Ensure a starting slash
			if (substr ($this->config[$configType], 0, 1) != '/') {
				$this->errors[] = "A {$type} setting has been supplied does it not start with a /";
				return false;
			}
			
			# Construct the filename
			$file = $this->documentRoot . $this->config[$configType];
			
			# Ensure the file exists
			if (!file_exists ($file)) {
				$this->errors[] = "The specified <strong>{$configType}</strong> file does not exist.";
				return false;
			}
			
			# Ensure the file is readable
			if (!is_readable ($file)) {
				$this->errors[] = "The specified <strong>{$configType}</strong> file is not readable.";
				return false;
			}
			
			# Overwrite the default HTML; note that this is done as a file_get_contents (rather than an include) so that it not executed; therefore there is a guarantee that no external PHP is being injected
			$htmlType = $type . 'Html';
			$this->$htmlType = file_get_contents ($file);
		}
		
		# Inject additional HTML into the header to disable mouse wheel scrolling
		$scrollWheelHtml = '
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
		';
		if (!substr_count ($this->headerHtml, 'function PreventScroll(e)')) {	// Don't add it if it's already there!
			$this->headerHtml = str_replace ('</head>', $scrollWheelHtml . '</head>', $this->headerHtml);
		}
		
		# Inject additional styles into the header
		$stylesHtml = '
		<style type="text/css">
			table.vote {border: 1px; border-collapse: collapse; border-spacing: 0px;}
			table.vote td, table.vote th {border: 2px #ddd solid; padding: 3px;}
			.votemsg {border:1px solid #bbbbbb; background: #eeeeee; padding: 4px;}
			div.problem {color: red; border: 2px solid red; padding: 2px 10px;}
			option {color: #603;}
			.comment {color: #444;}
			.heavilyfaded {color: #eee;}
			p.loggedinas {text-align: right;}
			p.navigationmenu {float: right; margin-left: 20px; color: gray;}
			h2.unit, h2.paperballots {page-break-before: always;}
			table.paperroll th {min-width: 150px;}
			table.ballotpapers {width: 85%;}
			table.referendum {width: 200px;}
			table.referendum th {text-align: center;}
			table.referendum td {width: 50%; height: 60px;}
			table.ballotpapers td.candidate {width: 75%;}
			table.ballotpapers td.position {width: 25%;}
			#stampbox {float: right; width: 200px; height: 150px; display: block; border: 4px solid #aaa; margin: 20px 0 20px 20px; padding: 10px;}
			ul.explanation {margin: 0; padding: 0;}
			ul.explanation li {list-style: none; margin-left: 10px; padding-left: 10px;}
			p.winner {color: #603; font-weight: bold; font-size: 1.2em; background-image: url(/images/icons/bullet_go.png); background-position: 5px 1px; background-repeat: no-repeat; padding-left: 28px;}
			p.warning {color: red;}
			p.success {background-image: url(/images/icons/tick.png); background-repeat: no-repeat; padding-left: 22px;}
			h2.electionresult {position: relative;}
			h2.electionresult a.resultpermalink {position: absolute; left: -1em; font-size: 0.83em; color: #aaa;}

			div.graybox {border: 1px solid #ddd; padding: 10px 15px; margin: 0 10px 10px 0; background-color: #fcfcfc; overflow: hidden; /* overflow prevents floats not being enclosed - see http://gtwebdev.com/workshop/floats/enclosing-floats.php */}
			div.graybox:hover {background-color: #fafafa; border-color: #aaa;}
			div.graybox p {text-align: left; margin-top: 10px;}

			ul.actions {margin: 0; padding: 0; float: right; display: block; margin-left: 10px;}
			ul.actions li {list-style: none; margin-bottom: 1px;}
			ul.actions li a {border-bottom: 0; border: 1px solid #ddd; padding: 4px 8px 2px; -moz-border-radius: 4px; -webkit-border-radius: 4px; width: 185px;}
			ul.actions li a {display: block;}
			ul.actions li a:hover {border-color: #aaa;}
			ul.actions li a img {vertical-align: top; width: 16px; height: 16px; border: 0;}
			ul.actions li a {border-bottom: 0; border: 1px solid #ddd; padding: 4px 8px 2px; -moz-border-radius: 4px; width: 185px;}
			ul.actions li a {display: block;}
			ul.actions.left {float: none;}
			
			ul.actions {margin-bottom: 1.2em;}
			ul.actions li {margin-bottom: 7px;}
			p.electoralroll, ul.actions li a, p.winner {background-repeat: no-repeat; background-position: 6px 5px; padding-left: 28px;}
			ul.actions li a {font-weight: bold;}
			p.electoralroll {background-image: url(/images/icons/script.png);}
			ul.actions li a {width: 15em;}
			ul.actions li.vote a {background-image: url(/images/icons/pencil.png);}
			ul.actions li.showvotes a {background-image: url(/images/icons/application_view_list.png);}
			ul.actions li a:hover {background-color: #e8c8c8;}
			ul.actions li a img {margin-right: 2px;}
			
			table.lines {border-collapse: collapse; /* width: 95%; */}
			.lines td, .lines th {border-bottom: 1px solid #e9e9e9; padding: 6px 4px 2px; vertical-align: top; text-align: left;}
			.lines tr:first-child {border-top: 1px solid #e9e9e9;}
			table.compressed td {padding: 0 4px;}

			table.lines td.comment {padding-bottom: 1.25em;}
			table.regulated td.key p {width: 150px;}
			@media print {
				body {background-color: #fff; color: #000;}
			}
		</style>
		';
		$this->headerHtml = str_replace ('</head>', $stylesHtml . '</head>', $this->headerHtml);
		
		# Inject the page title into the header if there is one
		if ($this->pageTitle) {
			$this->headerHtml = str_replace ('</title>', ' - ' . str_replace ('<br />', ' ', $this->pageTitle) . '</title>', $this->headerHtml);	// pageTitle already has had htmlspecialchars applied globally
		}
		
		# Signal success
		return true;
	}
	
	
	# Function to check that the environment is clean
	private function environmentIsOk ()
	{
		# Explicitly set the time zone using date_default_timezone_set () to avoid errors in error_reporting = 2048
		// This cannot be done until PHP 5.2 is more widespread
		//
		
		# Ensure that we are running HTTPS and not HTTP
		$isHttps = (isSet ($_SERVER['HTTPS']) && (strtolower ($_SERVER['HTTPS']) == 'on'));
		if (!$isHttps) {
			$this->errors[] = 'The server is not running HTTPS.';
			return false;
		}
		
		# Ideally here there would be a check that the webserver process (e.g. Apache) is configured to emit UTF8; however Apache does not provide this so this is impossible
		// 
		
		# Ensure that the minimum PHP version is met
		if (!version_compare (PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=')) {
			$this->errors[] = "The server does not meet the minimum PHP version for the voting system, which is '<strong>" . self::MINIMUM_PHP_VERSION . "</strong>'.";
			return false;
		}
		
		# Start a list of errors (so that all settings setup errors are shown at once)
		$errors = array ();
		
		# Define settings that must be off to ensure the highest level of security
		$optionsOff = array (
			'register_globals',
			'auto_prepend_file',
			'auto_append_file',
			'magic_quotes_gpc',
			'magic_quotes_runtime',
			'magic_quotes_sybase',
			'output_buffering',
			'safe_mode',
			'expose_php',
			'file_uploads',
			'display_errors',
		);
		
		# Ensure each option is off
		foreach ($optionsOff as $option) {
			if ((bool) ini_get ($option)) {
				$errors[] = "The server environment currently has the PHP '<strong>{$option}</strong>' setting switched on so this system will not run. The administrator needs to switch it off first.";
			}
		}
		
		# Define settings that must be on to ensure the highest level of security
		$optionsOn = array (
			'log_errors',
			'error_reporting',
		);
		
		# Ensure each option is off
		foreach ($optionsOn as $option) {
			if (!(bool) ini_get ($option)) {
				$errors[] = "The server environment currently has the PHP '<strong>{$option}</strong>' setting switched off so this system will not run. The administrator needs to switch it on first.";
			}
		}
		
		# Check that error_reporting is set to log offset errors in the logs
		$errorReportingLevel = ini_get ('error_reporting');
		$minimumAcceptableLevel = 2047;
		if ($errorReportingLevel < $minimumAcceptableLevel) {
			$errors[] = "The server environment currently has the PHP '<strong>error_reporting</strong>' setting too low. Please ensure it is at least <strong>{$minimumAcceptableLevel}</strong>.";
		}
		
		
		# Ensure that post_max_size is not too large to prevent massive form submissions
		$postMaxSize = ini_get ('post_max_size');
		$postMaxSizeBytes = $this->return_bytes ($postMaxSize);
		$reasonableMaximum = '8M';	// This is the default in php.ini
		$reasonableMaximumBytes = $this->return_bytes ($reasonableMaximum);
		if ($postMaxSizeBytes > $reasonableMaximumBytes) {
			$errors[] = "The server environment currently has the PHP option post_max_size set to '<strong>{$postMaxSize}</strong>'. This is too high. Please set it to <strong>{$reasonableMaximum}</strong> or slightly lower.";
		}
		
		# Assign the errors
		if ($errors) {
			$this->errors += $errors;
			return false;
		}
		
		# Signal that this test has been passed
		return true;
	}
	
	
	# Ensure the server environment provides sufficient memory
	private function environmentProvidesSufficientMemory ()
	{
		# Arrays in PHP are very inefficient; see: http://bugs.php.net/41053 ; an int in an array requires 68 bytes of storage
		# The most memory-intensive function is admin_paperroll - which loads data from all voters in an array
		# A typical person in the voter list would be 6+1+10+10+20 bytes coming from the database = 47 bytes.
		# For safety, let's go to over double that: 100 bytes
		# Times by 100 (liberal allocation) gives 10,000 bytes (10K) per voter.
		#   (In practice, actually only about 1.5-2K per voter actually seems to be required)
		# Example: times by 20,000 voters would be 200MB
		# So, in summary, as a formula with safety built in, we will use:   $bytesRequired = (voters * 10,000)
		$bytesRequired = $this->registeredVoters * 10000;
		
		# Get the current memory setting
		$memoryLimit = ini_get ('memory_limit');
		$memoryLimitBytes = $this->return_bytes ($memoryLimit);
		
		# Check that the requirement is below or equal to the current limit, and complain, giving a slightly higher limit
		if ($memoryLimitBytes < $bytesRequired) {
			$megaBytesRequired = ceil ($bytesRequired / (1000 * 1000)) . 'M';	// 1,000 rather than 1,024 for simplicity in the UI
			$this->errors[] = "The server environment currently has the PHP option memory_limit set to '<strong>{$memoryLimit}</strong>'. This is too low to ensure that all modules available in this system will run correctly. Please set it to at least <strong>{$megaBytesRequired}</strong> (which equates to a very liberal c. 10K per voter).";
			return false;
		}
		
		# Signal that this test has been passed
		return true;
	}
	
	
	# Function to convert an ini_get setting to bytes; from http://www.php.net/ini_get
	private function return_bytes ($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$number = substr($val, 0, -1);
		switch($last) {
			case 'g':
				$number *= 1024;
			case 'm':
				$number *= 1024;
			case 'k':
				$number *= 1024;
		}
		return $number;
	}
	
	
	# Function to check that the counting program is correctly installed, if specified
	private function countingProgramConfigValid ()
	{
		# Return no problems if not required
		if (!$this->config['countingInstallation']) {return true;}
		
		# Substitute placeholder %documentroot if present
		$this->config['countingInstallation'] = preg_replace ('/^%documentroot/', $this->documentRoot, $this->config['countingInstallation']);
		
		# Ensure the path ends with '/openstv/' (slash-terminated)
		if (!preg_match ('|/openstv/$|D', $this->config['countingInstallation'])) {
                        $this->errors[] = "The countingInstallation option has been specified but does not end with /openstv/ - please ensure the program is installed as such, and the config option corrected.";
			return false;
		}
		
		# Ensure the directory is present and readable
		if (!is_dir ($this->config['countingInstallation']) || !is_readable ($this->config['countingInstallation'])) {
			$this->errors[] = "The countingInstallation option has been specified but does not appear to be present and readable.";
			return false;
		}
		
		# Signal that this test has been passed
		return true;
	}
	
	
	# Function to check and obtain the username
	private function getUsername ()
	{
		# If there is no REMOTE_USER from the webserver, explain that an .htaccess file is needed, and end
		if (!isSet ($_SERVER['REMOTE_USER']) || empty ($_SERVER['REMOTE_USER'])) {
			
			# Generate a random string of length 10 characters
			for ($randomString = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 10; $x = rand(0,$z), $randomString .= $a{$x}, $i++);
			$this->errors[] = 'The server is not supplying a username, so this system will not run. You probably need to create an .htaccess file containing something like:
			
			# Authentication
			AADescription "Online voting"
			AACookieKey "' . $randomString . 'electionName"
			AuthType Ucam-WebAuth
			Require valid-user
			AAForceInteract On
			&lt;Files ' . $this->logoutLocation . '&gt;
				SetHandler AALogout
			&lt;/Files&gt;
			
			# Deny technical files being retrieved via a browser
			&lt;Files ".ht*"&gt;
				deny from all
			&lt;/Files&gt;
			
			# Directory index to prevent file listings
			DirectoryIndex index.html index.php
			';
			return false;
		}
		
		# Return the username
		return $_SERVER['REMOTE_USER'];
	}
	
	
	# Function to establish a database connection
	private function openDatabaseConnection ($dbUsername)
	{
		# Connect to the database as the specified user
		if (!$this->databaseConnection = @mysql_connect ($this->config['dbHostname'], $dbUsername, $this->config['dbPassword'])) {
			$this->errors[] = "Error opening database connection with the database username '<strong>" . htmlspecialchars ($dbUsername) . "</strong>'. The database server said: '<em>" . htmlspecialchars (mysql_error ()) . "</em>'";
			return false;
		}
		
		# Ensure we are talking in Unicode
		if (!mysql_query ("SET NAMES 'utf8';")) {
			$this->errors[] = "Error setting the database connection to UTF-8";
			return false;
		}
		
		# Connect to the database
		if (!@mysql_select_db ($this->config['dbDatabase'], $this->databaseConnection)) {
			$this->databaseConnection = NULL;
			$this->errors[] = "Error selecting the database '<strong>" . htmlspecialchars ($this->config['dbDatabase']) . "</strong>'. Check it exists and that the user {$dbUsername} has rights to it. The database server said: '<em>" . htmlspecialchars (mysql_error ()) . "</em>'";
			return false;
		}
		
		# Signal success
		return true;
	}
	
	
	# Function to close the database explicitly
	private function closeDatabaseConnection ()
	{
		# Explicitly close the administrative database connection so that it cannot be reused
		if ($this->databaseConnection) {
			mysql_close ($this->databaseConnection);
			$this->databaseConnection = NULL;
		}
	}
	
	
	# Set up the tables if they do not exist, complaining if they exist but are incorrect
	private function setupTables ()
	{
		# Connect to the database at the setup user privilege level; do not reconnect if the db has already been established, i.e. if the dbSetupUsername config setting has been used, as that will have triggered a connection to retrieve the config
		if (!$this->databaseConnection) {
			if (!$this->openDatabaseConnection ($this->config['dbSetupUsername'])) {
				$this->errors[] = "... therefore the database connection for the setup check could not be established.";
				return false;
			}
		}
		
		# Ideally here there would be a check, like the example query below, that other (non-root) users cannot access the database; however that doesn't work because otherwise the BOB user(s) would need read-access to the 'mysql' database
		# The Technical Admin should be encouraged to do something like this anyway.
		/*
		SELECT
			User, Db, Select_priv, Insert_priv, Update_priv, Delete_priv, Create_priv, Drop_priv, Grant_priv, References_priv
		FROM `mysql`.`db`
		WHERE
			'testvote' LIKE Db
			AND User != 'root'
			AND NOT Select_priv = 'N'
			AND Insert_priv = 'N' AND Update_priv = 'N' AND Delete_priv = 'N' AND Create_priv = 'N' AND Drop_priv = 'N' AND References_priv = 'N' AND References_priv = 'N'
		UNION (
			SELECT
				User, '*' as Db, Select_priv, Insert_priv, Update_priv, Delete_priv, Create_priv, Drop_priv, Grant_priv, References_priv
				FROM `mysql`.`user`
			WHERE NOT (
				Select_priv = 'N' AND Insert_priv = 'N' AND Update_priv = 'N' AND Delete_priv = 'N' AND Create_priv = 'N' AND Drop_priv = 'N' AND Grant_priv = 'N' AND References_priv = 'N'
			)
			AND User != 'root'
		) ORDER BY
			`User` ASC, `Db` ASC;
		*/
		
		# Define the fields for the voter table, used below either for checking or table creation
		$voterTableFields = array (
			'username'	=> 'VARCHAR(16) collate utf8_unicode_ci NOT NULL PRIMARY KEY',	// Voter usernames
			'voted'		=> 'TINYINT(4) DEFAULT 0',										// The flag for whether a voter has voted, defaulting to 0
			'forename'	=> 'VARCHAR(255) collate utf8_unicode_ci',						// Forename (optional)
			'surname'	=> 'VARCHAR(255) collate utf8_unicode_ci',						// Surname (optional)
			'unit'		=> 'VARCHAR(255) collate utf8_unicode_ci',						// Organisational unit (optional), e.g. college
		);
		
		# Define the fields for the vote table, used below either for checking or table creation
		$votesTableFields = $this->votesTableFields ();
		
		# Check if the tables exist
		$tables = $this->getTables ($this->config['dbDatabase']);
		$voterTablePresent = ($tables && in_array ($this->voterTable, $tables));
		$votesTablePresent = ($tables && in_array ($this->votesTable, $tables));
		
		# Create whichever/both of the two tables that do not currently exist, or end if there is a failure
		if (!$voterTablePresent) {
			if (!$this->createTable ($this->voterTable, $voterTableFields)) {return false;}
		}
		if (!$votesTablePresent) {
			if (!$this->createTable ($this->votesTable, $votesTableFields)) {return false;}
		}
		
		# Validate the fields for each (possibly newly-created) table, or end if there is a failure
		if (!$this->validateTableFields ($this->voterTable, $voterTableFields)) {return false;}
		if (!$this->validateTableFields ($this->votesTable, $votesTableFields)) {return false;}
		
		# Close the database connection
		$this->closeDatabaseConnection ();
		
		# Signal success
		return true;
	}
	
	
	# Define the fields for the vote table, used below either for checking or table creation
	private function votesTableFields ()
	{
		# Assemble the fields
		$votesTableFields = array ();	// Explicit creation of an array; not necessary in the PHP language
		$votesTableFields['token'] = 'VARCHAR(32) collate utf8_unicode_ci NOT NULL PRIMARY KEY';		// The token that the voter receives
		foreach ($this->config['electionInfo'] as $index => $election) {
			$vote = $index + 1;	// Field names start at 1, not 0
			$positionsAvailable = count ($election) - 1;					// Number of candidates, i.e. number of lines in the array minus the title
			for ($position = 1; $position <= $positionsAvailable; $position++) {
				$votesTableFields["v{$vote}p{$position}"] = 'TINYINT(4)';	// The list of cast ballots
			}
		}
		
		# Return the fields
		return $votesTableFields;
	}
	
	
	# Function to create a table from a list of fields
	private function validateTableFields ($name, $expectedFields)
	{
		# Check that the table type is InnoDB; the features used in BOB are the use of transactions and automatic key ordering
		$query = "SHOW TABLE STATUS LIKE '{$name}';";	// LIKE does do an exact match here; using only a substring fails to return any results
		if (!$data = $this->getData ($query)) {
			$this->errors[] = "The table status for the table name {$name} could not be retrieved.";
			return false;
		}
		$engine = $data[0]['Engine'];
		if ($engine != 'InnoDB') {
			$this->errors[] = "The table {$name} is not using the InnoDB storage engine.";
			return false;
		}
		
		# Obtain the current fields; error handling not really needed as we know that the table exists
		$query = "SHOW FULL FIELDS FROM `{$this->config['dbDatabase']}`.`{$name}`;";
		if (!$data = $this->getData ($query)) {
			$this->errors[] = "The field status for the table name {$name} could not be retrieved.";
			return false;
		}
		
		# Create a list of fields, building up a string for each equivalent to the per-field specification in a CREATE TABLE query
		$fieldsInDatabase = array ();
		foreach ($data as $index => $field) {
			$key = $field['Field'];
			$specification  = strtoupper ($field['Type']);
			if (strlen ($field['Collation'])) {$specification .= ' collate ' . $field['Collation'];}
			if (strtoupper ($field['Null']) == 'NO') {$specification .= ' NOT NULL';}
			if (strtoupper ($field['Key']) == 'PRI') {$specification .= ' PRIMARY KEY';}
			if (strlen ($field['Default'])) {$specification .= ' DEFAULT ' . $field['Default'];}
			$fieldsInDatabase[$key] = $specification;
		}
		
		# Compare with strict comparison; the ordering is also considered important to match, as otherwise the key may be in the wrong place
		#!# Is this check TOO harsh in terms of capitalisation (e.g. 'DEFAULT' rather than 'default') ?
		if ($fieldsInDatabase !== $expectedFields) {
			$this->errors[] = "The fields for the {$name} table do not match; expected was: " . print_r ($expectedFields, true) . " but what was found was: " . print_r ($fieldsInDatabase, true);
			return false;
		}
		
		# Signal success
		return true;
	}
	
	
	# Function to create a table from a list of fields
	private function createTable ($name, $fields)
	{
		# Construct the list of fields
		$fieldsSql = array ();
		foreach ($fields as $fieldname => $specification) {
			$fieldsSql[] = "{$fieldname} {$specification}";
		}
		
		# Compile the overall SQL; type is deliberately set to InnoDB so that rows are physically stored in the unique key order
		$query = "CREATE TABLE `{$name}` (" . implode (', ', $fieldsSql) . ") ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
		
		# Create the table
		if (!mysql_query ($query)) {
			$this->errors[] = "There was a problem setting up the {$name} table.";
			return false;
		}
		
		# Signal success
		return true;
	}
	
	
	# Generalised function to get data from an SQL query and return it as an array
	#!# Add failures as an explicit return false; this is not insecure at present though as array() will be retured (equating to boolean false), with the calling code then stopping execution in each case
	private function getData ($query)
	{
		# Create an empty array to hold the data
		$data = array ();
		
		# Execute the query or return false on failure
		if ($result = mysql_query ($query)) {
			
			# Check that the table contains data
			if (mysql_num_rows ($result) > 0) {
				
				# Loop through each row and add the data to it
				while ($row = mysql_fetch_assoc ($result)) {
					$data[] = $row;
				}
			}
		}
		
		# Return the array
		return $data;
	}
	
	
	# Function to obtain a list of tables in a database
	#!# Add failures as an explicit return false; this is not insecure at present though as array() will be retured (equating to boolean false), with the calling code then stopping execution in each case
	private function getTables ($database)
	{
		# Create a list of tables, alphabetically ordered, and put the result into an array
		$query = "SHOW TABLES FROM `{$database}`;";
		
		# Start a list of tables
		$tables = array ();
		
		# Get the tables
		if (!$tablesList = mysql_query ($query)) {
			return $tables;
		}
		
		# Loop through the table resource to get the list of tables
		while ($tableDetails = mysql_fetch_row ($tablesList)) {
			$tables[] = $tableDetails[0];
		}
		
		# Return the list of tables as an array
		return $tables;
	}
	
	
	# Function to verify the runtime user privileges
	private function verifyRuntimeDatabasePrivileges ()
	{
		# Define the correct privileges for the runtime user; they should be exactly as stated: no more and no less
		$correctPrivileges = 'select,insert,update';
		
		# Perform these operations for each of the two tables
		$tables = array ($this->voterTable, $this->votesTable);
		foreach ($tables as $table) {
			
			# Obtain the current fields; error handling not really needed as we know that the table exists; note that we cannot reuse the same call in validateTableFields as that was running under the setup user
			$query = "SHOW FULL FIELDS FROM `{$this->config['dbDatabase']}`.`{$table}`;";
			if (!$fields = $this->getData ($query)) {
				$this->errors[] = "The full fields status for the table name {$table} could not be retrieved.";
				return false;
			}
			
			# Loop through each field and ensure that the privileges are correct
			$wrongFields = array ();
			foreach ($fields as $index => $field) {
				if (strtolower ($field['Privileges']) != $correctPrivileges) {
					$wrongFields[] = $field['Field'];
				}
			}
			
			# Report an error if it is has occured
			if ($wrongFields) {
				$this->errors[] = "The privileges for field(s) <strong>" . implode (',', $wrongFields) . "</strong> in the {$table} table are incorrect - they should all be set to exactly <strong>{$correctPrivileges}</strong>";
				return false;
			}
		}
		
		# Signal success
		return true;
	}
	
	
	# Function to get the number of registered voters (and thus check that there are any at all)
	private function registeredVoters ()
	{
		# Get the total; there is not really any need for error handling as readability will have been checked in verifyRuntimeDatabasePrivileges()
		$query = "SELECT COUNT(*) AS total FROM `{$this->voterTable}`;";
		if (!$data = $this->getData ($query)) {
			$this->errors[] = "The count for the voter table could not be retrieved.";
			return false;
		}
		$total = $data[0]['total'];
		
		# If there are any, return the total (which will evaluate to true)
		if ($total) {return $total;}
		
		# Otherwise register an error and return false;
		$this->errors[] = "There are no voters registered. You need to insert them into the <strong>{$this->config['dbDatabase']}.{$this->voterTable}</strong> table via a GUI or using some SQL like:
		
		/* Add the users; only the username field is essential; the voted field will already have been set to zero as the default, and the other fields are for split-ballot listings only */
		USE {$this->config['dbDatabase']};
		SET NAMES 'utf8';
		INSERT INTO `{$this->voterTable}` VALUES ('someusername', 0, 'forename1', 'surname1', 'some college');
		INSERT INTO `{$this->voterTable}` VALUES ('anotherusername', 0, 'forename2', 'surname2', 'another college');
		INSERT INTO `{$this->voterTable}` VALUES ('nextusername', 0, 'forename3', 'surname3', 'next college');
		";
		return false;
	}
	
	
	# Function to get the number of people that have voted
	private function totalVoted ()
	{
		# Get the counts from both tables (as a single query to avoid a race condition)
		$query = "SELECT
			COUNT(voted) AS total FROM `{$this->voterTable}` WHERE voted = '1'
			UNION ALL
			SELECT COUNT(*) AS total FROM `{$this->votesTable}`
		;";
		if (!$data = $this->getData ($query)) {
			$this->errors[] = 'There was a problem checking the number of votes.';
			return false;
		}
		$votedVoter = $data[0]['total'];
		$votedVotes = $data[1]['total'];
		
		# Sanity-check that the two match; if not, this represents a serious error in the database state
		if ($votedVoter != $votedVotes) {
			$this->errors[] = "The vote count data is in an inconsistent state, so the ballot has been shut down. The administrator needs to check what has caused the problem.";
			return false;
		}
		
		# Return the total
		return $votedVoter;
	}
	
	
	# Function to check whether the current user is an election official in the list
	private function userIsElectionOfficial ()
	{
		$officials = explode (' ', trim ($this->config['officialsUsernames']));
		$userIsElectionOfficial = (in_array ($this->username, $officials));
		return $userIsElectionOfficial;
	}
	
	
	# Function to determine whether we are before the election has opened
	private function beforeElection ()
	{
		return ($this->loadtime < $this->config['ballotStart']);
	}
	
	
	# Function to determine whether we are during the election
	private function duringElection ()
	{
		return (($this->loadtime >= $this->config['ballotStart']) && ($this->config['ballotEnd'] >= $this->loadtime));
	}
	
	
	# Function to determine whether whether we are after the election has closed electronically
	#!# This should be renamed to "afterElectronicVoting" for clarity
	private function afterElection ()
	{
		return ($this->config['ballotEnd'] < $this->loadtime);
	}
	
	
	# Function to determine whether have reached the point when the ballot is viewable
	private function afterBallotView ()
	{
		return ($this->ballotViewable < $this->loadtime);
	}
	
	
	# Function to determine whether have reached the point when the ballot is viewable by all voters
	private function afterBallotViewDelayed ()
	{
		return ($this->ballotViewableDelayed < $this->loadtime);
	}
	
	
	# Function to determine whether we this is a split election (online and paper)
	private function splitElection ()
	{
		return (bool) $this->config['paperVotingEnd'];
	}
	
	
	# Function to check whether the user is on the electoral roll
	private function userRegisteredVoted ($username = false)
	{
		# Default to the logged-in user, or use the supplied username if requested
		if ($username === false) {$username = $this->username;}
		
		# Get the user's details; there is no need for any error handling as readability will have been checked in verifyRuntimeDatabasePrivileges()
		$query = "SELECT username,voted FROM `{$this->voterTable}` WHERE username='" . mysql_real_escape_string ($username) . "'";
		$result = mysql_query ($query);
		
		# Determine if the user is registered
		$row = mysql_fetch_assoc ($result);
		$userIsRegisteredVoter = ($row ? true : false);	// ($row) is a boolean cast in PHP but this ternary form is more explicit
		
		# Determine if the user has voted; clearly this will be false if they are not registered
		$userHasVoted = false;
		if ($userIsRegisteredVoter) {
			$userHasVoted = ($row['voted'] != 0);
		}
		
		# Return the result
		return array ($userIsRegisteredVoter, $userHasVoted);
	}
	
	
	/* END OF SETUP, SANITY-CHECKING AND INSTANTIATION SECTION */
	
	
	/* START OF PAGES AND VOTING FLOW SECTION */
	/* (The voting flow code is from BOB v. 0.7d plus a few minor refactorings) */
	
	
	# Home page
	private function home ()
	{
		# Build the HTML
		$html  = "\n<p>Welcome to the online voting system for this ballot.</p>";
		if ($this->config['frontPageMessageHtml'] && ($this->beforeElection || $this->duringElection)) {
			$html .= "\n" . "<div class=\"warningbox\">";
			$html .= "\n" . $this->config['frontPageMessageHtml'];	// It is assumed that the process which generates this setting has already sanitised it for inappropriate HTML
			$html .= "\n" . "</div>";
		}
		
		# Voting status and links
		$html .= "\n<div class=\"graybox\">";
		$html .= "\n\t<ul class=\"actions left\">";
		if ($this->beforeElection) {
			$html .= "\n\t\t" . '<li><strong>Current status:</strong> The ballot has not yet opened.</li>';
			if ($this->userIsRegisteredVoter) {
				$html .= "\n\t\t" . "<p class=\"electoralroll\">You (<strong>{$this->username}</strong>) <strong>are</strong> on the electoral roll.</p>";
			} else {
				$html .= "\n\t\t" . "<p class=\"electoralroll\">No, you (<strong>{$this->username}</strong>) are <strong>not</strong> on the electoral roll.<br />If you think you should be, please contact the election official(s) listed below.</p>";
			}
		}
		if ($this->duringElection) {
			$html .= "\n\t\t" . '<li><strong>Current status:</strong> Voting is now open for the above ballot.</li>';
			if ($this->userIsRegisteredVoter) {
				if ($this->userHasVoted) {
					$html .= "\n\t\t" . '<li>You have voted already.</li>';
				} else {
					$html .= "\n\t\t" . '<li class="vote"><a href="./?vote">Cast your vote</a></li>';
				}
			} else {
				if ($this->splitElection) {
					$html .= "\n\t\t" . '<li>You are not on the online electoral roll so cannot vote online. If you think you should be able to vote online, please contact the election official(s) listed below.</li>';
				} else {
					$html .= "\n\t\t" . '<li>You are not on the electoral roll so cannot vote. If you think you should be, please contact the election official(s) listed below.</li>';
				}
			}
		}
		if ($this->afterElection) {
			$html .= "\n\t\t" . '<li><strong>Current status:</strong> The ballot has now closed.</li>';
		}
		if ($this->afterBallotView && !$this->afterBallotViewDelayed) {
			$html .= "\n\t\t" . '<li>Results and vote data will be visible' . ($this->userIsElectionOfficial ? ' <strong>to ordinary voters</strong>' : '') . ' from ' . $this->ballotViewableDelayedFormatted . '.</li>';
			if ($this->userIsElectionOfficial) {
				$html .= "\n\t\t" . '<p>As an election official, you are able to view the results and votes data already (but can in no way amend these):</p>';
			}
		}
		if ($this->afterBallotViewDelayed || ($this->userIsElectionOfficial && $this->afterBallotView)) {
			$html .= "\n\t\t" . "<li class=\"showvotes\"><a href=\"./?results\">View results of election</a></li>";
			$html .= "\n\t\t" . "<li class=\"spaced showvotes\"><a href=\"./?showvotes\">View list of votes cast" . ($this->splitElection ? ' electronically' : '') . ' (total ' . number_format ($this->totalVoted) . ')</a></li>';
		}
		if ($this->duringElection || ($this->splitElection && $this->afterElection && !$this->afterBallotViewDelayed)) {
			$html .= "\n\t\t" . '<li>You will be able to view the list of votes cast (which are used to calculate the results) here at<br />' . $this->ballotViewableDelayedFormatted . '.</li>';
		}
		$html .= "\n\t</ul>";
		$html .= "\n</div>";
		
		# Link to admin page for election officials
		if ($this->userIsElectionOfficial) {
			$html .= "\n<h2>Admin section</h2>";
			$html .= "\n" . '<p>As an election official, you can access the <a href="./?admin">Admin section</a> to monitor the ballot.</p>';
		}
		
		# Details of this ballot
		$html .= "\n<h2>Details of this ballot:</h2>";
		$html .= "\n<table class=\"lines\">";
		$html .= "\n\t<tr>\n\t\t<td>Name of election:</td>\n\t\t<td><strong>" . htmlspecialchars ($this->config['title']) . "</strong></td>\n\t</tr>";
		if ($this->afterBallotView) {$html .= "\n\t<tr>\n\t\t<td>Votes cast" . ($this->splitElection ? ' electronically' : '') . ":</td>\n\t\t<td><strong>" . number_format ($this->totalVoted) . "</strong></td>\n\t</tr>";}
		if ($this->config['urlMoreInfo']) {$html .= "\n\t<tr>\n\t\t<td>More information about this ballot:</td>\n\t\t<td><a href=\"{$this->config['urlMoreInfo']}\"><strong>Information about this ballot</strong></a></td>\n\t</tr>";}
		if ($this->config['organisationName'] || $this->config['organisationLogoUrl']) {
			$html .= "\n\t<tr>\n\t\t<td>Organisation:</td>\n\t\t<td>";
			if ($this->config['organisationName']) {$html .= ($this->config['organisationUrl'] ? "<a href=\"{$this->config['organisationUrl']}\">" . htmlspecialchars ($this->config['organisationName']) . '</a>' : htmlspecialchars ($this->config['organisationName']));}
			if ($this->config['organisationName'] && $this->config['organisationLogoUrl']) {$html .= "<br />";}
			if ($this->config['organisationLogoUrl']) {$html .= "<img src=\"{$this->config['organisationLogoUrl']}\" alt=\"Logo\" height=\"60\" />";}
			$html .= "</td>\n\t</tr>";
		}
		$html .= "\n\t<tr>\n\t\t<td>Username(s) of election official(s):</td>\n\t\t<td><strong>" . htmlspecialchars (str_replace (' ', ', ', $this->config['officialsUsernames'])) . "</strong></td>\n\t</tr>";
		$html .= "\n\t<tr>\n\t\t<td>" . ($this->splitElection ? 'Online voting' : 'Vote') . " opening time:</td>\n\t\t<td>" . $this->ballotStartFormatted . "</td>\n\t</tr>";
		$html .= "\n\t<tr>\n\t\t<td>" . ($this->splitElection ? 'Online voting' : 'Vote') . " closing time:</td>\n\t\t<td>" . $this->ballotEndFormatted . "</td>\n\t</tr>";
		if ($this->splitElection) {
			$html .= "\n\t<tr>\n\t\t<td>Paper voting closing time:</td>\n\t\t<td>" . $this->paperVotingEndFormatted . "</td>\n\t</tr>";
		}
		$html .= "\n\t<tr>\n\t\t<td>List of votes cast " . ($this->splitElection ? 'online ' : '') . "viewable at:</td>\n\t\t<td>" . $this->ballotViewableFormatted . "</td>\n\t</tr>";
		$html .= "\n\t<tr>\n\t\t<td>Total eligible registered " . ($this->splitElection ? 'online ' : '') . "voters:</td>\n\t\t<td>" . number_format ($this->registeredVoters) . ($this->beforeElection ? ' (This may change before the voting opens)' : '') . "</td>\n\t</tr>";
		if ($this->config['randomisationInfo']) {$html .= "\n\t<tr>\n\t\t<td>Randomisation:</td>\n\t\t<td>" . htmlspecialchars ($this->config['randomisationInfo']) . "</td>\n\t</tr>";}
		$html .= "\n\t<tr>\n\t\t<td>E-mail of Technical Administrator:</td>\n\t\t<td>" . htmlspecialchars ($this->config['emailTech']) . "</td>\n\t</tr>";
		if (!$this->beforeElection) {	// The security hashes only need to be constant from the opening of the ballot onwards, e.g. the configuration would change before opening if a candidate pulled out
			$html .= "\n\t<tr>\n\t\t<td>BOB program security hash:</td>\n\t\t<td>{$this->bobMd5}</td>\n\t</tr>";
			$html .= "\n\t<tr>\n\t\t<td>Configuration security hash:</td>\n\t\t<td>{$this->configMd5}</td>\n\t</tr>";
		}
		
		$html .= "\n</table>";
		$html .= "\n<p>If you have any questions about this ballot, please contact the election official(s) listed above in the first instance.</p>";
		
		# Information about the voting system and link to view source
		$html .= "\n<h2>About the voting system</h2>";
		$html .= "\n<p>This system enables votes for a ballot to be cast online and securely and anonymously communicated to the Returning Officer. The Returning Officer will be able to see who has voted but will not be able to tell who has cast which votes.</p>";
		$html .= "\n<p>When you have successfully placed your vote, you will be e-mailed a sequence of random-looking short words - your \"voting token\". This system does not store the connection between your voting token and your identity, however it does store your voting token in a database, and e-mails it to a mailbox for audit purposes. When polls have closed, the list of all the votes cast will be made available - because only you will know your voting token, you will be able to check that your vote was correctly included.</p>";
		$html .= "\n<p>Disclaimer: The software behind this voting system has been checked independently, and acknowledged as a system that should detect and avoid voting irregularities. However, as stated in the GPL license, it comes with no guarantees.
			You can examine the code of the PHP class <a href=\"./?viewsource\" target=\"_blank\" title=\"[Link opens in a new window]\">BOB.php</a> [link opens in a new window], which has MD5 sum <tt>" . $this->bobMd5 . "</tt>, that drives the various pages.
			If you have any questions or concerns about the system, please contact the external System Administrators &lt;{$this->config['emailTech']}&gt; or the Returning Officer.</p>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to show the source code of this file so that voters can verify the code
	private function viewsource ()
	{
		# Send the contents as plain text; the GUI will have been switched off in the front controller
		header ('Content-Type: text/plain');
		$code = file_get_contents (__FILE__);
		echo $code;
	}
	
	
	# Function to provide a front page for admins
	private function admin ()
	{
		# Create the menu
		$list = array ();
		
		# Create a list of actions
		foreach ($this->actions as $action => $attributes) {
			if ($action == __FUNCTION__) {continue;}	// Skip the current page
			$section = ($attributes['administrator'] ? 'Pages for election officials only' : 'Public pages viewable by voters');
			$list[$section][$action] = "<li class=\"{$action}\"><a href=\"./?{$action}\">" . htmlspecialchars ($attributes['description']) . '</a></li>';
		}
		
		# Assemble the list of actions into HTML
		$html  = "\n<p>Welcome to the admin section.</p>\n<p>This page is viewable only by election officials ({$this->config['officialsUsernames']}).</p>";
		foreach ($list as $section => $actions) {
			$html .= "\n<h2>{$section}:</h2>";
			$html .= "\n<ul>";
			foreach ($actions as $actionListItem) {
				$html .= "\n{$actionListItem}";
			}
			$html .= "\n</ul>";
		}
		
		# Status information
		$html .= "\n<h2>Status</h2>";
		$html .= "\n<table class=\"lines\">";
		$html .= "\n\t<tr>\n\t\t<td>Total registered voters:</td>\n\t\t<td>" . number_format ($this->registeredVoters) . ($this->beforeElection ? ' (This may change before the voting opens)' : '') . "</td>\n\t</tr>";
		if (!$this->beforeElection) {$html .= "\n\t<tr>\n\t\t<td>Votes cast:</td>\n\t\t<td>" . number_format ($this->totalVoted) . "</td>\n\t</tr>";}
		$html .= "\n</table>";
		
		# Timestamp
		$html .= "\n<p class=\"signature small\"><em>Page generated at: " . date ('r') . '</em></p>';
		
		# Show the HTML
		echo $html;
	}
	
	
	# Check whether a specified user is on the roll
	private function admin_rollcheck ()
	{
		# Obtain the username if posted
		$username = (isSet ($_POST['username']) ? trim ($_POST['username']) : false);
		$usernameEntitySafe = htmlspecialchars ($username);
		
		# Show the form
		$html  = "\n<p>Enter a username to check whether for this election the user is in the list of " . ($this->splitElection ? 'online ' : '') . "voters and whether they have voted:</p>";
		$html .= "\n<form method=\"post\" action=\"./?" . __FUNCTION__ . '">';
		$html .= "\n<input type=\"text\" name=\"username\" value=\"{$usernameEntitySafe}\" autofocus=\"autofocus\">";
		$html .= "\n<input type=\"submit\">";
		$html .= "\n</form>";
		
		# Process the form if there is a username
		if (strlen ($username)) {
			
			# Obtain the data
			list ($userIsRegisteredVoter, $userHasVoted) = $this->userRegisteredVoted ($username);
			
			# Show the result
			$html .= "\n\n<h2>Information for username: {$usernameEntitySafe}</h2>";
			if ($userIsRegisteredVoter) {
				$html .= "\n<p>The user {$usernameEntitySafe} <strong>can</strong> vote.</p>";
				if (!$this->beforeElection) {
					$html .= ($userHasVoted ? "\n<p>The user {$usernameEntitySafe} <strong>has</strong> voted.</p>" : "\n<p>The user has <strong>not</strong> voted.</p>");
				}
			} else {
				$html .= "\n<p>There is <strong>not</strong> a user {$usernameEntitySafe} on the electoral roll.</p>";
			}
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function enabling the election officials to view the ballot form even if they have a vote and have voted
	function admin_viewform ()
	{
		# Hand off to the vote routine, in view-only mode
		return $this->vote ($viewOnly = true);
	}
	
	
	# Error-reporting function
	private function error ($errorString) {
		$message = "\n<p><strong>ERROR:</strong> {$errorString}</p>";
		echo $message;
		return false;
	}
	
	
	// public entry point for voting workflow
	private function vote ($viewOnly = false)
	{
		# Check that the ballot is open; note that not even admins can see the ballot paper before the election opens; this is so that, in a managed hosting GUI scenario, where the vote is being randomised, an election official cannot randomise until they find an order they like
		if ($this->beforeElection) {
			echo "<p>This ballot is not yet open.</p>\n<p>It will run from<br />{$this->ballotStartFormatted} &nbsp; to<br />{$this->ballotEndFormatted}.</p>";
			return false;
		}
		
		# In the default (voting) mode, perform checks
		if (!$viewOnly) {
			
			# Close the ballot when election is over
			if ($this->afterElection) {
				echo "<p>Online voting for this ballot has closed.</p>\n<p>It ran from<br />{$this->ballotStartFormatted} &nbsp; to<br />{$this->ballotEndFormatted}.</p>";
				return false;
			}
			
			# Check whether the user is in the list
			if (!$this->userIsRegisteredVoter) {
				echo "\n<p>Sorry, you do not appear to be recorded in our list of registered voters.</p>\n<p>Please contact the election official(s) listed on the front page, or vote using alternative means (if applicable).</p>";
				return false;
			}
			
			# Check if the user has already voted
			if ($this->userHasVoted) {
				echo "\n<p>Our records indicate that you have already voted.</p>\n<p>Please contact the election official(s) listed on the front page if you disagree.</p>";
				return false;
			}
		}
		
		// In view-only (admin) mode, remind the user
		if ($viewOnly) {echo "\n<p style=\"color: red;\">Note: you are accessing this as an election official. As such, the form below does not submit any vote or change any data.</p>";}
		
		// Check for any problems if the form has been posted
		$problems = array ();
		if (!empty ($_POST)) {
			
			// Checks done to prevent invalid votes from crafted forms being cast
			
			// Confirm that what is posted matches the list of candidates in the config file
			// Loop through each vote set specified in the config file
			foreach ($this->config['electionInfo'] as $voteSet => $candidates) {
				$voteSet = $voteSet + 1;	// Adjust the array indexing - the generated <select> boxes start at [1] not [0]
				
				// Loop through each candidate specified in the config file
				foreach ($candidates as $candidateNumber => $candidate) {	// number of candidates == number of preferences, so although $candidateNumber is used, this actually means a preference value in the checking context below
					
					// Skip the first 'candidate' as that is actually a heading
					if ($candidateNumber == 0) {continue;}
					
					// Set a flag for whether electionInfo->voteSet->candidateNumber exists, i.e. that _POST['v'][candidateNumber] exists, as it should
					$structureOk = (
						   isSet    ($_POST['v'])
						&& is_array ($_POST['v'])
						&& isSet    ($_POST['v'][$voteSet])
						&& is_array ($_POST['v'][$voteSet])
						&& isSet    ($_POST['v'][$voteSet][$candidateNumber])
					);
					
					// If the structure does not match, then the user has probably posted a user-crafted form; set the error message and break out of the inner and outer loop
					if (!$structureOk) {
						$problems[] = "Your browser does not appear to be submitting the entire page. Please try again.";
						break 2;
					}
					
					// Ensure that the selection given is in the list in the config file, e.g. 0 for no vote or 1/2/3 for candidates 1/2/3
					$availableCandidates = array_keys (($candidate == 'referendum') ? $this->referendumCandidates : $candidates);
					foreach ($availableCandidates as $index => $availableCandidate) {
						$availableCandidates[$index] = (string) $availableCandidate;	// Cast the numbers 0,1,2, etc explicitly as strings so that the (string) POST data can have strict comparison with in_array(); see http://www.php.net/in_array#61491
					}
					$isExistentCandidateOption = (
						   is_string   ($_POST['v'][$voteSet][$candidateNumber])	// Is a string (e.g. not an array)
						&& strlen      ($_POST['v'][$voteSet][$candidateNumber])	// Has length, i.e. not an empty string
						&& in_array    ($_POST['v'][$voteSet][$candidateNumber], $availableCandidates, true)	// Is in the list of candidates; strict mode is used for in_array
						&& ctype_digit ($_POST['v'][$voteSet][$candidateNumber])	// Is all numbers (basically a redundant check)
					);
					if (!$isExistentCandidateOption) {
						$problems[] = "Your browser appeared to post a non-existent option. Please try again.";
						break 2;
					}
					
					// At this point, we now know that, for every selection of every preference of this vote set, there is a valid value in the $_POST['v'] structure
					
					// Now we check for crafted forms that contain additional (invalid) data, even though this will not actually be looped-through later in the vote-recording process
					$expectedPreferencesTotal = (($candidate == 'referendum') ? 1 : count ($candidates) - 1);
					if (count ($_POST['v'][$voteSet]) != $expectedPreferencesTotal) {	// _POST['v'][$voteSet] is already confirmed as an array if this code is reached
						$problems[] = "Your browser appeared to post more preferences than exist. Please try again.";
						break 2;
					}
				}
				
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
					$problems[] = "You set a candidate twice, in the '". htmlspecialchars ($candidates[0]) . "' vote.";
				}
				
				
				// 2. Prevent a voter leaving out a preference in sequence
				$allNonZeroSoFar = true;
				foreach ($_POST['v'][$voteSet] as $key => $value) {
					if ($value == '0') {
						$allNonZeroSoFar = false;
						continue;	// Go to next in loop
					}
					if ((!$allNonZeroSoFar) && ($value != '0')) {
						$problems[] = "You left out a preference in sequence, in the '" . htmlspecialchars ($candidates[0]) . "' vote.";
						break;	// Don't bother checking any more
					}
				}
			}
			
			// At this point, we now know that, for every vote set defined in the configuration, there is a valid structure in the $_POST['v'] structure
			
			// Ensure that no additional votesets have been added in, to ensure that eventually submitted data matches the database structure
			if (count ($_POST['v']) != count ($this->config['electionInfo'])) {	// _POST['v'] is already confirmed as an array if this code is reached
				$problems[] = "Your browser appeared to post more votes than exist. Please try again.";
			}
			
			// We now know that the $_POST['v'] structure is exactly as we expect
			
			// A crafted form could contain other $_POST keys, but we never use these elsewhere in the program so do not bother to check for these
			
			// By this point, we know we are using a sufficiently non-crafted form (or a crafted form which contains the same submittable values, pointlessly), else we would be out of the loop
			
			// Ensure that the voter has confirmed their vote
			if (!isSet ($_POST['confirmvote']) || $_POST['confirmvote']!='on') {
				$problems[] = "You have not confirmed your vote.";
			}
			
			// Show any problems if found
			if ($problems) {
				echo "\n<br />";
				echo '<div class="problem">';
				echo "<p>The following " . (count ($problems) == 1 ? 'problem was' : 'problems were') . " found:</p>";
				echo '<ul><li>' . implode ('</li><li>', $problems) . '</li></ul>';
				echo "<p>The vote has therefore not been cast yet. <strong>Please correct the " . (count ($problems) == 1 ? 'problem' : 'problems') . " in the form below and try again.</strong></p>";
				echo '</div>';
			}
		}
		
		// Show the ballot page if not posted or problems found, then end
		if (empty ($_POST) || $problems) {
			#!# This might need to called statically under PHP 5.5; not yet tested
			$this->ballotPage($this->config, $viewOnly);
			return;
		}
		
		// Election officials cannot vote
		if ($viewOnly) {
			echo "\n<p>(No vote has been cast, and no data has been changed.)</p>";
			return false;
		}
		
		// Add the vote
		$retval = $this->voteWFinternal ();
		return $retval;
	}
  
  
  // Statically-callable method of showing the ballot page, for use by the separate bobgui program for viewing a ballot setup
  public function viewBallotPageExternal ($config, $submitTo) {
  	self::ballotPage ($config, $viewOnly = true, $submitTo);
  }
	
	
	# Function to determine if this election contains only people (i.e. names in "SURNAME, Forename" format), or whether it contains non-person questions (e.g. referenda)
	private function onlyPeople ($electionInfo, $reOpenNominationsLabels)
	{
		# Loop through the election info
		foreach ($electionInfo as $electionIndex => $candidates) {
			array_shift ($candidates);      // Skip label
			
			# Check each candidate
			foreach ($candidates as $candidate) {
				
				# Skip checks if 'RON'
				if (in_array ($candidate, $reOpenNominationsLabels, true)) {continue;}
				
				# Check for "string, string" format
				if (!preg_match ('/^([^,]+), (.+)$/i', $candidate, $matches)) {
					return false;
				}
				
				# Check first string is upper-case
				list ($complete, $surname, $forename) = $matches;
				$surnameUppercased = utf8_encode (strtoupper (utf8_decode ($surname)));        // i.e. mb_strtoupper
				if ($surname != $surnameUppercased) {
					return false;
				}
			}
		}
		
		# All positions confirmed as "SURNAME, Forename" format
		return true;
	}
  
  
  // Ballot page; can be run in an embeddable (external) static context also
  private function ballotPage($config, $viewOnly = false, $submitTo = false){
    
	# Define labels (case-sensitive) used to match RON
	$reOpenNominationsLabels = array ('Re-Open Nominations (RON)', 'Re-Open Nominations', 'RON');
	
	# Determine if RON is present
	$ronPresent = false;
	foreach ($config['electionInfo'] as $electionIndex => $candidates) {
		array_shift ($candidates);	// Skip label
		foreach ($candidates as $candidate) {
			if (in_array ($candidate, $reOpenNominationsLabels, true)) {
				$ronPresent = true;
				break;
			}
		}
	}
	
	# Determine if this election contains only people (i.e. names in "SURNAME, Forename" format), or whether it contains non-person questions (e.g. referenda)
	$onlyPeople = self::onlyPeople ($config['electionInfo'], $reOpenNominationsLabels);	// Has to be called statically, as ballotPage may be run statically
	$choiceDescription = ($onlyPeople ? 'candidate' : 'candidate/option');
	$choiceDescriptionPlural = ($onlyPeople ? 'candidates' : 'candidates/options');
	
	# Voting instructions
	echo "
	<h2>How to vote</h2>
	<p>The voting in this ballot uses the Single Transferable Vote system. Please see the published rules governing the voting system for " . htmlspecialchars ($config['organisationName']) . " ballots.</p>
	<ul>
		<li>Next to number 1 (in the preference column for a given post), select the name of the {$choiceDescription} to whom you give your first preference (using the pull-down selection menu controls).</li>
		<li>You may also enter, against preference ranks 2, 3 and so on, the names of other {$choiceDescriptionPlural} in the order you wish to vote for them.</li>
		<li>Continue until you have voted for those {$choiceDescriptionPlural} you wish to vote for, and leave any remaining boxes blank. You are under no obligation to vote for all {$choiceDescriptionPlural}.</li>"
		. (count ($config['electionInfo']) > 1 ? "<li>Repeat this process for each post listed.</li>" : '')
		. ($ronPresent ? "<li>Some elections may list a candidate named 'RON'. This acronym expands to 'Re-Open Nominations'. You may vote for RON as you would any other candidate. Should RON be 'elected', the position will be re-opened, and will be decided at a subsequent election.</li>" : '') . "
		<li>The order of your preferences is crucial. A later preference can be considered only if an earlier preference has received sufficient votes to qualify for election or has been excluded because of insufficient support. Under no circumstances can a later preference count against an earlier preference.</li>
		<li>When you have completed this form CHECK IT.</li>
		<li>When you have checked the form, click on the 'Cast my vote' button.</li>
	</ul>
	<!--<h2>Ballot form - cast your vote here:</h2>-->
	";
	
	echo "\n" . '<div class="graybox">';
	echo '<form action="' . ($submitTo ? $submitTo : ($viewOnly ? './?admin_viewform' : './?vote')) . '" method="post">',"\n\n";
	
	$i = 0;	// Start a count of vote groups
	foreach ($config['electionInfo'] as $options) {	// Loop through each vote group
		
		$i++;	// Advance the vote group counter
		if (!$options) {continue;}	// If the array is empty, move on
		
		# Set the heading
		echo "\n\n\n<h3>{$options[0]}</h3>";
		
		// Replace the heading as the blank option
		$options[0] = '(blank)';
		
		// Define the number of boxes
		$boxes = count ($options) - 1;	// Number of boxes should match the number of candidates, minus the blank option
	  	
		// Deal with the special case of a referendum, and define what a referendum looks like in terms of the available candidates
		$isReferendum = ($options[1] == 'referendum');
		if ($isReferendum) {
			$options = array (0 => '(blank)', 1 => 'Yes', 2 => 'No');	// Options for a referendum
			$boxes = 1;
		}
		
		// Create the HTML, creating as many boxes as requested
		echo "\n\n<table class=\"vote v{$i}\">\n";
		if (count ($options) > 11) {echo "<p class=\"comment\">Note: there are " . (count ($options) - 1) . " {$choiceDescriptionPlural} standing in this election. Your browser may require you to scroll to see all.</p>";}	// IE6/Win in Classic Theme, i.e. not XP standard, only displays 11 options at once
		if (!$isReferendum) {echo "\t\t<tr>\n\t\t\t<th>Preference</th>\n\t\t\t<th>" . ucfirst ($choiceDescription) . "</th>\n\t\t</tr>\n";}
		for ($box = 1; $box <= $boxes; $box++) {
			
			// Determine what option has been selected for this box, if any
			$itemChosen = ((isSet ($_POST['v']) && is_array ($_POST['v']) && isSet ($_POST['v'][$i]) && is_array ($_POST['v'][$i]) && isSet ($_POST['v'][$i][$box])) ? $_POST['v'][$i][$box] : '');
			
			// Create the option boxes
			$selectOpts = '';
			foreach ($options as $index => $option) {
			    $selectOpts .= "\t\t\t\t\t<option value=\"{$index}\"" . ($index == $itemChosen ? ' selected="selected"' : '') . ">" . htmlspecialchars ($option) . "</option>\n";
			}
		    echo "\t\t<tr class=\"c{$box} " . (($box % 2) ? 'codd' : 'ceven' ) . "\">\n\t\t\t" . ($isReferendum ? '<th>Referendum decision:</th>' : "<td class=\"preference\">{$box}</td>") . "\n\t\t\t<td class=\"candidate\">\n\t\t\t\t<select name=\"v[{$i}][{$box}]\" onmousewheel=\"PreventScroll(event);\">\n{$selectOpts}\t\t\t\t</select>\n\t\t\t</td>\n\t\t</tr>\n";
		}
		echo "</table>";
	}
	
	echo '
		<p><font color="red"><strong>Please double-check your choices before submitting your vote!</strong></font> Due to the anonymity built into this voting system, it is not possible to correlate your response after you vote.</p>
		<p><input type="checkbox" name="confirmvote" id="confirmvote" /><label for="confirmvote"> I have checked my vote.</label></p>
		<p>After you click "Cast my vote", your vote will be passed anonymously to the Returning Officer. ' . ($config['voterReceiptDisableable'] ? 'If specified below, you' : 'You') . ' will receive a blind copy by e-mail. On the next page your vote will be displayed, by way of confirmation. These will allow you to check that we have recorded your vote correctly by confirming to yourself the presence of your vote on the listing that will be visible when voting has closed (and which is used to calculate the results). Any queries should be directed to the Returning Officer.</p>
		' . ($config['voterReceiptDisableable'] ? '<p><input type="checkbox" name="voterreceipt" id="voterreceipt"' . (self::voterReceipt ($config['voterReceiptDisableable']) ? ' checked="checked"': '') . ' /><label for="voterreceipt"> Send me a receipt to ' . (isSet ($this->username) ? $this->username : '[username]') . '@cam.ac.uk showing my vote token and votes.</label><br />(Untick this option if your mailbox is shared (i.e. so others could see your vote) or this address is not enabled or is over-quota (because bounces could be seen by the Returning Officer).</p>' : '') . '
		<p><input value="Cast my vote" type="submit" /></p>
	</form>
	</div>
	';
  }
	
	
	# Function to determine whether a voter receipt is to be sent
	private function voterReceipt ($voterReceiptDisableable)
	{
		# If the config states that the voter receipt is not disableable, it should be sent
		if (!$voterReceiptDisableable) {return true;}
		
		# If the form is not posted, by default, a receipt is enabled (so the checkbox will be checked)
		if (!$_POST) {return true;}
		
		# Otherwise, check the voter's intention
		return (isSet ($_POST['voterreceipt']) && is_string ($_POST['voterreceipt']) && ($_POST['voterreceipt'] == 'on'));
	}
	
  
  
  // Function to generate a unique token
  private function generateUniqueToken ()
  {
	mt_srand();
	// Check that the token isn't already in use
    $tokenChosen = false;
	while (!$tokenChosen) {
		$token = $this->generateToken();
	    if(!($result = mysql_query("SELECT COUNT(token) AS total FROM `{$this->votesTable}` WHERE token='{$token}'"))) return($this->error ("Token checking failed. The vote submission could not proceed."));
	    if(!($row = mysql_fetch_assoc($result))) return($this->error ("Token checking failed (2). The vote submission could not proceed."));
		if ($row['total'] == '0') {$tokenChosen = true;}	// If there are no matching tokens, then accept this one
	}
	
	return $token;
  }
  
  
  // Function to generate a (potentially non-unique) token
  private function generateToken ($words = 4){
    $token='';
    for($i=0; $i<$words; $i++){
      $token.=(($i==0)?'':' ').$this->rfc2289words[mt_rand(0,2047)];
    }
    return $token;
  }
  
  
	# Internal voting workflow
	private function voteWFinternal ()
	{
		# Determine whether the voter will receive the vote receipt
		$voterReceipt = $this->voterReceipt ($this->config['voterReceiptDisableable']);
		
		// Create a string of column names and corresponding column values by looping through the configuration array and looking up the (now-validated) value in the POST array
		$coln  = '';
		$colv  = '';
		foreach ($this->config['electionInfo'] as $voteSet => $candidates) {
			$voteSet = $voteSet + 1;	// Adjust the array indexing - the generated <select> boxes start at [1] not [0]
			
			// Loop through each candidate specified in the config file
			foreach ($candidates as $preferenceNumber => $candidate) {
				
				// Skip the first 'candidate' as that is actually a heading
				if ($preferenceNumber == 0) {continue;}
				
				$coln .= ",v{$voteSet}p{$preferenceNumber}";
				$colv .= ',' . $_POST['v'][$voteSet][$preferenceNumber];
			}
		}
		
		// Start transaction.
		if (!mysql_query ('BEGIN WORK;')) {
			echo "\n<p>Recording your vote ...</p>";
			return $this->error ('Failed to start database transaction.');
		}
		
		// Generate token; this is done as late as possible to minimise the chances of a race condition before the INSERT
		if (!$token = $this->generateUniqueToken ()) {
			echo "\n<p>Recording your vote ...</p>";
			return $this->error ('Token could not be generated. Please resubmit.');	// Safe to end after a BEGIN WORK: MySQL documentation says "If a client connection drops, the server releases table locks held by the client."
		}
		
		// Add the token field and value to the SQL extracts being built
		$coln = "token" . $coln;
		$colv = "'{$token}'" . $colv;
		
		// Record data from the ballot HTML form along with random token.
		if (!mysql_query ("INSERT INTO `{$this->votesTable}` ({$coln}) VALUES ({$colv});") or mysql_affected_rows() != 1) {
			echo "\n<p>Recording your vote ...</p>";
			$this->doRollback ();
			return $this->error ('Database vote insert failure.');
		}
		
		// Modify the voter table to indicate this vote has been cast
		if (!mysql_query ("UPDATE `{$this->voterTable}` SET voted = '1' WHERE username='{$this->username}' AND voted = '0';") or mysql_affected_rows () != 1) {
			echo "\n<p>Recording your vote ...</p>";
			$this->doRollback ();
			return $this->error ('Recording voter as having voted failed. As such, the vote itself has not been stored either.');
		}
		
		# Commit the transaction
		if (!mysql_query ('COMMIT;')) {
			echo "\n<p>Recording your vote ...</p>";
			$this->doRollback ();
			return $this->error ('Transaction failed to commit.');
		}
		
		# Start the narration HTML
		$html = '';
		
		// Write of ballot to database was OK.
		$html .= "\n<p>Our database indicates that it has successfully recorded your vote and, separately, that you have voted.</p>";
		$html .= "\n<p>We will now attempt to read back your vote from our database, and e-mail it to the returning officer" . ($voterReceipt ? ", blind-carbon-copied (BCC) to your @cam address" : '') . ".</p>";
		$html .= "\n<p>In the highly unusual case that there is a failure somewhere in the remainder of this voting process, you should keep a record of your proof-of-voting token '<strong>{$token}</strong>' and use it to check your vote really was recorded correctly when the count sheet is posted up after voting has closed.</p>";
		
		// Create e-mail body containing ballot information
		if (!$result = mysql_query ("SELECT * FROM `{$this->votesTable}` WHERE token='$token';")) {
			$html .= "\n<p><strong>ERROR:</strong> Vote read-back failed (1).</p>";
			echo $html;
			return false;
		}
		if (!$row = mysql_fetch_assoc ($result)) {
			$html .= "\n<p><strong>ERROR:</strong> Vote read-back failed (2).</p>";
			echo $html;
			return false;
		}
		
		# Compile the message start
		$message  = "Below you will find a record of each of the selections you made on the ballot web-page in order. Each ballot choice is represented in a computer-parsable representation, with an equivalent verbal description to the right of each equals sign.";
		$message .= "\n\nYour voting token is '{$token}'.";
		$message .= "\n\nYou should not disclose this e-mail or your voting token to others.\n\n";
		$message = wordwrap ($message);
		
		foreach ($row as $k => $v){
			if ($k == 'token') {continue;}	// Skip the token line
			if (is_numeric($k) || !is_numeric ($v)){
				$html .= "\n<p><strong>ERROR:</strong> MySQL result is giving incorrect field names/values (1).</p>";
				echo $html;
				return false;
			}
			if (!preg_match ('/\Av(\d+)p(\d+)\z/', $k, $matches)) {
				$html .= "\n<p><strong>ERROR:</strong> MySQL result is giving incorrect field names/values (2).</p>";
				echo $html;
				return false;
			}
			$thisPosition = $this->config['electionInfo'][$matches[1]-1][0];
			$thisPreference = $matches[2];
			$message .= "{$k}: {$v}";
			if ($this->config['electionInfo'][$matches[1]-1][1] == 'referendum') {
				switch ($v){
					case 1:
						$message .= ' = Vote in favour of referendum ';
						break;
					case 2:
						$message .= ' = Vote against referendum ';
						break;
					default:
						$message .= ' = Abstain from voting in referendum ';
				}
				$message .= "{$thisPosition}.";
			} else {
				$thisCandidate = ($v ? $this->config['electionInfo'][$matches[1]-1][$v] : '(no candidate)');
				if ($thisPosition and $thisCandidate){
					$message .= " = Give preference {$thisPreference} to {$thisCandidate} for {$thisPosition}.";
				}
			}
			$message .= "\n";
		}
		
		# Add the ID as a reference, so that people get an explanation of the ID in the subject line (see below)
		$message .= "\n\nReference ID for this election: " . $this->config['id'];
		$message .= "\n\nConfiguration security hash of the program: " . $this->bobMd5;
		$message .= "\nConfiguration security hash for this election: " . $this->configMd5;
		if (!$voterReceipt) {
			$message .= "\n\nThe voter opted not to receive an e-mail receipt.";
		}
		$message .= "\n\n\n--- END OF E-MAIL ---\n";
		
		# Continue the narration
		$html .= "\n<p>" . ($voterReceipt ? 'If you do not receive a confirmation e-mail containing the text in the box below within a minute or two, we' : 'We') . " recommend that you save or print this webpage as an alternative personal record of your vote.</p>";
		$html .= "\n<p>You should not disclose this e-mail or your voting token to others.</p>";
		$html .= "\n<div class=\"votemsg\">";
		$html .= "\n<pre>";
		$html .= "\n" . $message;
		$html .= "\n</pre>";
		$html .= "\n</div>";
		$html .= "\n<p>E-mailing your vote to the mailbox &lt;{$this->config['emailReturningOfficer']}&gt;" . ($voterReceipt ? " and blind-carbon-copying {$this->username}@cam.ac.uk" : '') . ' ...';
		
		# Send the e-mail and confirm
		#!# NB Mail domain of @cam.ac.uk is currently hard-coded
		$subject = 'Online voting: ' . $this->config['title'] . ' [' . $this->config['id'] . ']';
		$extraHeaders  = "From: {$this->config['emailTech']}\r\n";
		if ($voterReceipt) {
			$extraHeaders .= "BCC: {$this->username}@cam.ac.uk\r\n";
		}
		if (!mail ($this->config['emailReturningOfficer'], $subject, $message, $extraHeaders)) {
			$html .= "\n<p><strong>ERROR:</strong> Enqueue voting confirmation e-mail failed.</p>";
			echo $html;
			return false;
		}
		$html .= "\n" . " Voting confirmation e-mail successfully enqueued.</p>";
		$html .= "\n<p><strong>Voting process has successfully completed.</strong></p>";
		
		# Define a confirmation summary box
		$summaryHtml  = "\n<div class=\"graybox\">";
		$summaryHtml .= "\n<p class=\"success\"><strong>Your vote has been succesfully recorded; thank you for voting.</strong></p>";
		$summaryHtml .= "\n\n<p>Your voting token is '{$token}'" . ($voterReceipt ? ', which has been e-mailed to you as below.' : '. We recommend you print this page so you have a note of this.') . '</p>';
		if ($this->config['afterVoteMessageHtml']) {
			$summaryHtml .= "\n" . $this->config['afterVoteMessageHtml'];	// It is assumed that the process which generates this setting has already sanitised it for inappropriate HTML
		}
		$summaryHtml .= "\n<p>Please now <a href=\"{$this->logoutLocation}\">logout</a> then close your browser.</p>";
		$summaryHtml .= "\n</div>";
		$summaryHtml .= "\n<h2>Fuller details</h2>";
		
		# Show the HTML
		echo $summaryHtml;
		echo $html;
		
		# Signal success
		return true;
	}
	
	
	# Function to do a database rollback
	private function doRollback ()
	{
		if (!mysql_query ('ROLLBACK;')) {
			$this->error ('Unable to roll back the database transaction.');
		}
		
		# No return value, as outcome of rollback is not evaluated
	}
	
	
	
	# Results page
	private function results ()
	{
		# Set a flag for whether the results are considered preliminary (rather than checked and final)
		$resultsCheckedFinalised = false;
		
		# End if not viewable
		if (!$this->votesViewable ()) {
			echo "\n<p>Viewing the counted results is not yet possible.</p>";
			return false;
		}
		
		# For a split (electronic and paper) election, check and show status on automatic vote counting
		if ($this->splitElection) {
			
			# If the additional votes functionality is not enabled, then results can never be displayed automatically
			if (!$this->config['additionalVotesCsvDirectory']) {
				echo "\n<p>Results cannot be displayed automatically, as this election involves a paper vote after the online vote has closed.</p>";
				echo "\n<p>Results can be calculated on a desktop computer using a program such as <a href=\"http://www.openstv.org/\" target=\"_blank\">OpenSTV</a> and using the BLT data on the <a href=\"./?showvotes#blt\">raw vote data</a> page.</p>";
				return false;
			}
			
			# Since the additional votes functionality is now confirmed enabled, tell the user if the Returning Officer has not added it yet
			if (!$this->additionalVotesFile) {
				echo "\n<p>This election is being conducted using paper votes in addition to online votes.</p>";
				echo "\n<p>The Returning Officer has the option to add the paper votes into this system, though this has not been done as yet. Therefore, results cannot at present be displayed.</p>";
				echo "\n<p>Please check back here later, or look out for an announcement.</p>";
				return false;
			}
			
			# Since an additional votes file is currently present, if it is only a draft, tell the user
			if (!$this->additionalVotesFileFinal) {
				
				# If they are a Returning Officer, then make clear the results below are private to them only
				if ($this->userIsElectionOfficial) {
					echo "\n<div class=\"graybox\">";
					echo "\n\t<p>The results below are only in <strong>draft</strong> and are <strong>visible only to you as a Returning Officer</strong>.</p>";
					echo "\n\t<p class=\"warning\">Please check below and then <a href=\"./?admin_additionalvotes\">return to the paper votes entry page</a> to set the results as finalised.</p>";
					echo "\n</div>";
				} else {
					echo "\n<p>This election is being conducted using paper votes also.</p>";
					echo "\n<p>The Returning Officer has not yet added finalised paper result data into this system. Therefore, results cannot at present be displayed automatically.</p>";
					echo "\n<p>Please check back here later, or look out for an announcement.</p>";
					return false;
				}
			}
			
			# If final, set the flag to indicate these are actually checked results (rather than purely auto-generated)
			if ($this->additionalVotesFileFinal) {
				$resultsCheckedFinalised = true;
			}
		}
		
		# Disable if no counting installation set
		if (!$this->config['countingInstallation']) {
			echo "\n<p>Results cannot be displayed automatically, as this system does not have a compatible counting program installed.</p>";
			echo "\n<p>Results can be calculated on a desktop computer using a program such as <a href=\"http://www.openstv.org/\" target=\"_blank\">OpenSTV</a> and using the BLT data on the <a href=\"./?showvotes#blt\">raw vote data</a> page.</p>";
			return false;
		}
		
		# Explain this page
		echo "\n<p>This page shows the election results.<br />These have been calculated by taking the <a href=\"./?showvotes#votes\">raw vote data</a> in <a href=\"./?showvotes#blt\">.BLT format</a>, which you can view" . ($resultsCheckedFinalised ? ', and have been authorised for publication by the Returning Officer.' : '') . '</p>';
		echo "\n<p>(You can repeat the result calculations yourself on a desktop computer, if you wish, using a program such as <a href=\"http://www.openstv.org/\" target=\"_blank\">OpenSTV</a> and using the BLT data on the <a href=\"./?showvotes#blt\">raw vote data</a> page.)</p>";
		
		# Get the data as raw ballots and formatted BLTs
		$votesBlt = $this->listVotesBlt ($errorMessageHtml);
		if ($errorMessageHtml) {
			echo $errorMessageHtml;
			return false;
		}
		list ($ballots, $blts, $listing) = $votesBlt;
		
		# Count the data and show the results
		$this->countBlt ($ballots, $blts, $resultsCheckedFinalised);
	}
	
	
	# Function to determine if the votes are viewable by the current user
	private function votesViewable ()
	{
		# Election officials can see votes if admin during election is OK
		if ($this->userIsElectionOfficial && $this->config['adminDuringElectionOK']) {
			return true;
		}
		
		# Election officials can see results after ballot view
		if ($this->userIsElectionOfficial && $this->afterBallotView) {
			return true;
		}
		
		# Users can see results after ballot view delayed (which may be the same as ballot view)
		if ($this->afterBallotViewDelayed) {
			return true;
		}
		
		# No access
		return false;
	}
	
	
	# Page listing the votes cast, so that voters can check their ballot was cast correctly
	private function showvotes ()
	{
		# End if not viewable
		if (!$this->votesViewable ()) {
			echo "\n<p>Viewing the ballot box is not yet possible.</p>";
			return false;
		}
		
		# Add a jump list
		echo "\n<p>This page provides information on the anonymous votes cast.</p>";
		echo "\n<p>Jump below to:</p>";
		echo "\n<ul>";
		echo "\n\t<li><a href=\"#key\">Key to vote data</a></li>";
		echo "\n\t<li><a href=\"#votes\">List of votes</a></li>";
		echo "\n\t<li><a href=\"#voters\">List of voters</a></li>";
		echo "\n\t<li><a href=\"#blt\">List of votes in BLT format</a></li>";
		echo "\n\t<li><a href=\"#counts\">Counted results</a></li>";
		echo "\n</ul>";
		
		# Show the results
		echo "\n<h2 id=\"key\">Key to vote data</h2>";
		echo "\n<p>Vote tokens and the votes they are recorded with are listed here.</p>";
		$this->voteDataKey ();
		echo "\n<h2 id=\"votes\">List of votes cast</h2>";
		$this->listVotes ();
		echo "\n<h2 id=\"voters\">List of voters</h2>";
		$this->listVoters ();
		echo "\n<h2 id=\"blt\">List of votes in BLT format</h2>";
		echo "\n<p>Total number of votes cast" . ($this->splitElection ? ' electronically' : '') . ' was: <strong>' . number_format ($this->totalVoted) . '</strong>.</p>';
		echo "<p>The following output is in BLT format used by the Electoral Reform Society. This can be read by programs such as <a href=\"http://www.openstv.org/\" target=\"_blank\">OpenSTV</a> which provide a counting facility.</p>\n<p>Copy and paste " . (count ($this->config['electionInfo']) == 1 ? 'the block' : 'each of the blocks') . " below into a new text file and save " . (count ($this->config['electionInfo']) == 1 ? 'it' : 'each one') . " as a .blt file,<br />e.g. \"{$this->config['id']}_1.blt\"" . (count ($this->config['electionInfo']) == 1 ? '' : ",  \"{$this->config['id']}_2.blt\" etc.") . ".</p>";
		$votesBlt = $this->listVotesBlt ($errorMessageHtml);
		if ($errorMessageHtml) {
			echo $errorMessageHtml;
		} else {
			list ($ballots, $blts, $listing) = $votesBlt;
			echo $listing;
		}
		echo "\n<h2 id=\"counts\">Counted results</h2>";
		echo "\n<p>The <a href=\"./?results\">results page</a> shows the compiled counts.</p>";
	}
	
	
	// explain the candidate <-> numerical identifier relationship
	private function voteDataKey ()
	{
		echo "\n<pre>\n";
		foreach ($this->config['electionInfo'] as $c => $pos) {
			echo 'v' . (1 + $c) . ' is the election for position: ' . htmlspecialchars (array_shift ($pos)) . "\n";
			foreach ($pos as $p => $name) {
				echo '   candidate number ' . (1 + $p) . ' is ' . htmlspecialchars ($name) . "\n";
			}
		}
		echo "</pre>\n";
	}
	
	
	// print out the votes that have been cast
	private function listvotes ()
	{
		echo "\n(In the listing below, column v<em>X</em>p<em>Y</em> is the Yth preference for election X)</p>";
		echo "\n<p>Total number of votes cast" . ($this->splitElection ? ' electronically' : '') . ' was: <strong>' . number_format ($this->totalVoted) . '</strong>.</p>';
		
		# Get the compiled vote data
		$voteData = $this->getVoteData ($voteDataErrorMessageHtml);
		if ($voteDataErrorMessageHtml) {
			echo $voteDataErrorMessageHtml;
			return false;
		}
		list ($castVotes, $fieldnames, $totalAdditionalVotes) = $voteData;
		
		# Show the list of votes
		if ($totalAdditionalVotes) {
			echo "\n<p>The listing below also includes <strong>" . ($totalAdditionalVotes == 1 ? 'one additional vote' : "{$totalAdditionalVotes} additional votes") . "</strong> supplied by the Returning Officer by loading a CSV file of the additional data. " . ($totalAdditionalVotes == 1 ? 'This is' : 'These are') . " clearly labelled as such at the end of the listing.</p>";
		}
		echo "\n<p>To view this data in a spreadsheet, paste it into a text file and save it as a .csv file,<br />e.g. \"{$this->config['id']}.csv\".</p>";
		echo "\n<pre>\n";
		echo $this->tokenVotesListHtml ($fieldnames, $castVotes);
		echo '</pre>';
	}
	
	
	# Function to get the cast vote data
	private function getVoteData (&$errorMessage = false)
	{
		# Get the votes, and order them by token so that it is easier for voters to find their token in an alphabetical list
		if(!($result = mysql_query ("SELECT * FROM `{$this->votesTable}`;"))) return ($this->error ("Vote list read failed."));
		
		# Create an array of the fieldnames
		$fieldnames = array ();
		$fields = mysql_num_fields ($result);
		for ($count = 0; $count < $fields; $count++) {
			$fieldnames[] = mysql_field_name ($result, $count);
		}
		
		# Create an array of the cast votes
		$castVotes = array ();
		while ($row = mysql_fetch_assoc ($result)) {
			$token = array_shift ($row);	// Skip the token, but store it for use as an index
			foreach ($row as $k => $v) {
				$castVotes[$token][$k] = $v;
			}
		}
		
		# Get the additional votes, if configured
		$additionalVotes = $this->additionalVotes ($fieldnames, $additionalVotesErrorMessage);
		if ($additionalVotesErrorMessage) {
			$errorMessage = "\n<p class=\"warning\">The data cannot yet be shown as the Returning Officer has supplied a CSV of additional data, but the following problem was found:<br /><em>" . htmlspecialchars ($additionalVotesErrorMessage) . "</em></p>";
			return false;
		}
		$totalAdditionalVotes = count ($additionalVotes);
		
		# Combine the votes if additional votes are present
		if ($additionalVotes) {
			$castVotes += $additionalVotes;
		}
		
		# Return the data as a collection
		return array ($castVotes, $fieldnames, $totalAdditionalVotes);
	}
  
  
	# Function to assemble HTML for a list of cast votes
	private function tokenVotesListHtml ($fieldnames, $votes)
	{
		# Start the HTML
		$html  = '';
		
		# Start with the header row
		$html .= sprintf ('%22s', array_shift ($fieldnames));
		foreach ($fieldnames as $fieldname) {
			$html .= sprintf (',%5s', $fieldname);
		}
		$html .= "\n";
		
		# Add the cast votes
		foreach ($votes as $token => $row) {
			$html .= sprintf ('%22s', $token);
			foreach ($row as $k => $v) {
				$html .= sprintf (',%5d', $v);
			}
			$html .= "\n";
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to check setup of any CSV file(s) for additional votes transcribed from paper
	private function additionalVotesSetupOk ()
	{
		# Return no problems if split (online+paper) voting not required
		if (!$this->splitElection) {return true;}
		
		# Return no problems if functionality not enabled
		if (!$this->config['additionalVotesCsvDirectory']) {return true;}
		
		# Return no problems if not relevant to the current phase
		if (!$this->afterBallotView) {return true;}
		
		# Check the directory is present and readable
		if (!is_dir ($this->config['additionalVotesCsvDirectory'])) {
			$this->errors[] = 'The specified directory for additional votes cast on paper does not exist.';
			return false;
		}
		if (!is_readable ($this->config['additionalVotesCsvDirectory'])) {
			$this->errors[] = 'The specified directory for additional votes cast on paper is not readable.';
			return false;
		}
		
		# Check the directory is writable (needed so that the Returning Officer can create the files)
		if (!is_writable ($this->config['additionalVotesCsvDirectory'])) {
			$this->errors[] = 'The specified directory for additional votes cast on paper is not writable.';
			return false;
		}
		
		# Check for each file, i.e. /path/to/<id>.draft.csv and /path/to/<id>.final.csv
		$files = array ('draft' => false, 'final' => true);	// Final takes priority if both are present
		$additionalVotesFile = false;
		$isFinal = false;
		foreach ($files as $type => $finality) {
			$filename = $this->config['additionalVotesCsvDirectory'] . '/' . $this->config['id'] . '.' . $type . '.csv';
			if (is_file ($filename)) {
				if (!is_readable ($filename)) {
					$this->errors[] = 'A file for additional votes cast on paper has been found but is not readable.';
					return false;
				}
				$additionalVotesFile = $filename;
				$isFinal = $finality;
			}
		}
		
		# Register the values as class properties
		$this->additionalVotesFile = $additionalVotesFile;
		$this->additionalVotesFileFinal = $isFinal;
		
		# Confirm success
		return true;
	}
	
	
	# Function to retrieve any additional votes cast on paper transcribed into a CSV file
	private function additionalVotes ($fieldnames, &$errorMessage = false)
	{
		# End, returning no results, if no file is present
		if (!$this->additionalVotesFile) {return array ();}	// Not an error condition - there may not be a file yet
		
		# Read the file
		$contents = file_get_contents ($this->additionalVotesFile);
		
		# Convert CSV to array
		$serialKeyPrefixRequired = $this->additionalVotePrefix;		// Prefix for key names which must exist in serial from 1 without gaps (i.e. additionalvote1,additionalvote2,...); this also provides a namespace to avoid clashes with the standard token list, so that merging is safe
		$data = $this->csvToArray ($contents, $separator = ',', $fieldnames, $serialKeyPrefixRequired, $emptyCellToCharacter = '0', $csvErrorMessage);
		if ($csvErrorMessage) {
			$errorMessage = $csvErrorMessage;
			return array ();	// Return no results
		}
		
		# Return the array
		return $data;
	}
	
	
	# Helper function to process a character-separated values string to an associative array
	private function csvToArray ($string, $separator = ',', $expectedHeaders, $serialKeyPrefixRequired = false, $emptyCellToCharacter = false, &$errorMessage = '')
	{
		# Start an array of data to fill
		$data = array ();
		
		# Split by newline
		$string = str_replace ("\r\n", "\n", trim ($string));	// Normalise to \n
		$lines = explode ("\n", $string);
		
		# For each line, create an array of cells
		foreach ($lines as $rowNumber => $line) {
			
			# Create the cells
			$cells = explode ($separator, $line);
			
			# Create the header row
			if ($rowNumber == 0) {
				$headers = $cells;
				if ($headers !== $expectedHeaders) {
					$errorMessage = 'The header row in the CSV file does not match the expected structure, so the data cannot be used.';
					return false;
				}
				$totalHeaders = count ($headers);
				continue;
			}
			
			# Return false if any row has more/less cells than headers
			if (count ($cells) != $totalHeaders) {
				$errorMessage  = "Row " . ($rowNumber + 1) . " does not match the number of header titles in the first row.";	// Show row number as human number, not zero-indexed
				$errorMessage .= '<br />Please check you are using Excel 2007 or later, as Excel 2003 has a <a href="http://support.microsoft.com/kb/77295" target="_blank">inconsistent columns bug</a>.';	// This seems to affect pasting in from the GUI of a .xls file also. See also http://excel.tips.net/T003068_Inconsistent_Output_for_Empty_Columns_in_a_CSV_File.html
				return false;
			}
			
			# Determine the ID of this row, and ensure it is unique
			$rowId = $cells[0];
			if (array_key_exists ($rowId, $data)) {
				$errorMessage = 'The row IDs are not unique.';
				return false;
			}
			
			# If required, check the key matches a specified pattern
			if ($serialKeyPrefixRequired) {
				$expectedKey = $serialKeyPrefixRequired . $rowNumber;
				if ($rowId != $expectedKey) {
					$errorMessage = 'The row IDs must all match the specified pattern and exist in order without gaps.';
					return false;
				}
			}
			
			# Allocate each row, with the index starting from 0
			foreach ($cells as $index => $cell) {
				
				# Skip first column, since it is the key
				if ($index == 0) {continue;}
				
				# Determine the fieldname
				$fieldname = $headers[$index];
				
				# If required, convert empty string to specified character
				if (is_string ($emptyCellToCharacter)) {
					if (!strlen ($cell)) {
						$cell = $emptyCellToCharacter;
					}
				}
				
				# Allocate the cell into the final data
				$data[$rowId][$fieldname] = $cell;
			}
		}
		
		# Return the data
		return $data;
	}
	
	
	# Show the votes that have been cast, in .blt format
	private function listVotesBlt (&$errorMessageHtml = '')
	{
		/*
			http://www.openstv.org/manual explains the format as being as follows, but without the comments starting at #
			It seems to be implied that an empty ballot (i.e. not voting for anyone) is simply skipped, in the BLT format
			
			4 2 # four candidates are competing for two seats
			-2 # Bob has withdrawn (optional)
			1 4 1 3 2 0 # first ballot
			1 2 4 1 3 0
			1 1 4 2 3 0 # The first number is the ballot weight (>= 1).
			1 1 2 4 3 0 # The last 0 is an end of ballot marker.
			1 1 4 3 0 # Numbers inbetween correspond to the candidates
			1 3 2 4 1 0 # on the ballot.
			1 3 4 1 2 0
			1 3 4 1 2 0 # Chuck, Diane, Amy, Bob
			1 4 3 2 0
			1 2 3 4 1 0 # last ballot
			0 # end of ballots marker
			"Amy" # candidate 1
			"Bob" # candidate 2
			"Chuck" # candidate 3
			"Diane" # candidate 4
			"Gardening Club Election" # title
		 */
		
		# Get the compiled vote data
		$voteData = $this->getVoteData ($voteDataErrorMessageHtml);
		if ($voteDataErrorMessageHtml) {
			$errorMessageHtml = $voteDataErrorMessageHtml;
			return false;
		}
		list ($castVotes, $fieldnames, $totalAdditionalVotes) = $voteData;
		
		# Match the vote and position to create an easily-loopable structure
		$ballots = array ();
		foreach ($castVotes as $token => $row) {
			foreach ($row as $slot => $choice) {
				preg_match ('/v([0-9]+)p([0-9]+)/', $slot, $matches);
				$election = $matches[1];
				$position = $matches[2];
				$ballots[$election][$token][$position] = $choice;
			}
		}
		
		# Create a BLT listing for each election
		$listing  = "\n<table class=\"border lines regulated\">";
		$blts = array ();
		foreach ($this->config['electionInfo'] as $electionIndex => $electionInfo) {
			$listing .= "\n\t<tr>";
			
			# Set the election number, i.e. count from 1
			$electionNumber = $electionIndex + 1;
			
			# Calculate total candidates and seats
			$totalCandidates = count ($electionInfo) - 1;
			$totalSeats = $this->positionInfo[$electionIndex];
			
			# Start an introduction (not part of the file format itself)
			$listing .= "\n\t\t<td class=\"key\">";
			$listing .= "\n<p><strong>Election no. {$electionNumber}.</strong><br />Save this text as e.g. <em>\"{$this->config['id']}_{$electionNumber}.blt\"</em> :</p>";
			$listing .= "\n\t\t</td>";
			$listing .= "\n\t\t<td>";
			$listing .= "\n<pre>\n";
			
			# Number of candidates competing for how many seats
			$blts[$electionNumber]  = "{$totalCandidates} {$totalSeats}";
			
			#!# No support in the GUI yet for adding in withdrawn candidates; that will require a $listing entry at this point
			
			# Add the ballot data in
			foreach ($ballots[$electionNumber] as $token => $row) {
				$rowItems = array ();
				foreach ($row as $selection) {
					if ($selection == '0') {break;}	// If they have not voted in a position, end
					$rowItems[] = $selection;
				}
				if ($rowItems) {
					$blts[$electionNumber] .= "\n1 " . implode (' ', $rowItems) . ' 0';
				}
			}
			
			# End of ballot marker
			$blts[$electionNumber] .= "\n0";
			
			# Show each candidate
			$electionTitle = array_shift ($electionInfo);
			foreach ($electionInfo as $candidate) {
				$candidate = str_replace ('"', '\"', $candidate);	// Escape any " character (which is the terminator character)
				$blts[$electionNumber] .= "\n" . '"' . $candidate . '"';	
			}
			
			# Title
			$electionTitle = str_replace ('"', '\"', $electionTitle);	// Escape any " character (which is the terminator character)
			$blts[$electionNumber] .= "\n" . '"' . $electionTitle . '"';
			
			# Add the blt to the listing
			$listing .= htmlspecialchars ($blts[$electionNumber]);
			
			# End of this ballot
			$listing .= "\n</pre>";
			$listing .= "\n\t\t</td>";
			$listing .= "\n\t</tr>";
		}
		$listing .= "\n</table>";
		
		# Return the BLT listings as a collection
		#!# Need to refactor so that the listing generation is a separate function that uses the arranged data
		return array ($ballots, $blts, $listing);
	}
	
	
	# Function to perform a count based on BLT listings
	private function countBlt ($ballots, $blts, $resultsCheckedFinalised)
	{
		# Chop final openstv/ component off end, as the Python integration assumes the containing directory
		$countingInstallation = realpath ($this->config['countingInstallation'] . '../') . '/';
		
		# Define the Python command used to process a ballot; see http://www.openstv.org/manual and http://groups.google.com/group/openstv/browse_frm/thread/38fcfcdee99ce3ff and http://groups.google.com/group/openstv/browse_thread/thread/c445290557242b9
		// Available output formats are generateTextResults, generateERSCSVResults and generateHTMLResults
		// NOTE: This wrapper code works for OpenSTV 1.7 (and 1.6) only. Use BOB0.11.4 for OpenSTV 1.4, or BOB1.0.3 for OpenSTV 1.5.
		$pythonCommand = "python -c \"
# Convert STDIN to tempfile (STDIN can't be used for seek() in BltBallotLoader.py, and StringIO can't be used as elsewhere the 'file' is opened)
import os
import sys
import tempfile
temp = tempfile.NamedTemporaryFile(delete=False)
temp.write(sys.stdin.read())
temp.close()

# Process the ballot
sys.path.append('" . $countingInstallation . "')
from openstv.ballots import Ballots
from openstv.MethodPlugins.{$this->config['countingMethod']} import {$this->config['countingMethod']}
from openstv.ReportPlugins.HtmlReport import HtmlReport
b = Ballots()
b.loadKnown(temp.name, 'blt')
os.unlink(temp.name)
e = {$this->config['countingMethod']}(b)
e.runElection()
r = HtmlReport(e)
r.generateReport()
\"";
		
		# Create a droplist
		$dropList = array ();
		foreach ($this->config['electionInfo'] as $electionIndex => $electionInfo) {
			$electionNumber = $electionIndex + 1;
			$dropList[$electionNumber] = "<li><a href=\"#election{$electionNumber}\">" . htmlspecialchars ($electionInfo[0]) . '</a></li>';
		}
		if (!$resultsCheckedFinalised) {
			echo "\n<div class=\"warningbox\">";
			echo "\n<p class=\"warning\"><strong>IMPORTANT</strong>:</p>";
			echo "\n<p class=\"warning\">Any results noted here are preliminary/indicative calculations, from an automated counting program (OpenSTV) using the raw data.<br />Only the declaration of the Returning Officer, who is responsible for confirming the accuracy of the count, shall indicate finalised results.</p>";
			echo "\n</div>";
		}
		if (count ($dropList) > 1) {
			echo "\n<p>Jump to results below for:</p>" . "\n<ul>" . implode ("\n\t", $dropList) . "\n</ul>";
		}
		
		# Run the program for each ballot
		$listing  = '';
		foreach ($this->config['electionInfo'] as $electionIndex => $electionInfo) {
			
			# Set the election number, i.e. count from 1
			$electionNumber = $electionIndex + 1;
			
			# Add the heading
			$listing .= "\n<hr />";
			$listing .= "\n<h2 id=\"election{$electionNumber}\" class=\"electionresult\"><a class=\"resultpermalink\" href=\"#election{$electionNumber}\" title=\"Permalink to this result\">#</a> " . htmlspecialchars ($electionInfo[0]) . '</h2>';
			
			# Branch to FPTP counting for a referendum
			if ((count ($electionInfo) == 2) && $electionInfo[1] == 'referendum') {
				$listing .= $this->countFPTP ($ballots[$electionNumber], $this->referendumCandidates, true);	// The data for a referendum isn't actually stored in BLT format, but token => votes-array-of-one-item
				continue;
			}
			
			# For elections where an STV count doesn't apply (e.g. candidates == seats), state the results directly
			/*
			#!# Ideally OpenSTV would handle this stuff directly, but throws a runtime error instead. See the code in NonSTV.py :
			if (self.b.nCand < 2 or
				self.nSeats < 1 or
				self.b.nCand <= self.nSeats or
				self.b.nBallots <= self.nSeats
				):
			raise RuntimeError, "Not enough ballots or candidates to run an election."
			}
			*/
			$totalCandidates = count ($electionInfo) - 1;
			$totalSeats = $this->positionInfo[$electionIndex];
			if ($totalCandidates <= $totalSeats) {
				unset ($electionInfo[0]);	// Remove the title from the start, leaving $electionInfo as just a list of candidates, indexed from 1
				$listing .= "\n<p>There " . ($totalCandidates == 1 ? 'is one candidate' : "are {$totalCandidates} candidates") . ' standing and ' . ($totalSeats == 1 ? 'one seat' : "{$totalSeats} seats") . ' available, so ' . ($totalCandidates == 1 ? 'the candidate is' : 'all the candidates are') . ' automatically elected.</p>';
				$listing .= $this->countFPTP ($ballots[$electionNumber], $electionInfo);
				continue;	// End for this election election
			}
			
			# Attempt to execute the command
			$output = $this->createProcess ($pythonCommand, $blts[$electionNumber]);
			if ($output) {
				# If the vote is tied, the system must not generate an output, because a page refresh will re-run the randomisation
				#!# The full solution here is that the results should be cached once run
				if (substr_count (str_replace (array ("\n", '  '), ' ', $output), 'by breaking the tie randomly')) {
					$listing .= "\n<p>This ballot was <strong>tied</strong>, so the Returning Officer will need to re-calculate the results manually from the <a href=\"./?showvotes#blt\">raw vote data</a> and toss a coin to determine the winner of this ballot.</p>";
				} else {
					$listing .= $this->cleanCountOutput ($output);
				}
			} else {
				$listing .= "\n<p><em>The result could not be calculated. (Perhaps OpenSTV is not installed on the webserver?)<br />Please obtain a copy of OpenSTV yourself and save BLT files as above, to create the results yourself instead.</em></p>";
			}
		}
		
		# Show the listing
		echo $listing;
	}
	
	
	# Function to handle running a python process securely without writing out any files
	private function createProcess ($command, $string)
	{
		# Set the descriptors
		$descriptorspec = array (
			0 => array ('pipe', 'r'),  // stdin is a pipe that the child will read from
			1 => array ('pipe', 'w'),  // stdout is a pipe that the child will write to
			// 2 => array ('file', '/tmp/error-output.txt', 'a') // stderr is a file to write to
		);
		
		# Assume failure unless the command works
		$returnStatus = 1;
		
		# Create the process
		$command = str_replace ("\r\n", "\n", $command);	// Standardise to Unix newlines
		$process = proc_open ($command, $descriptorspec, $pipes);
		if (is_resource ($process)) {
			fwrite ($pipes[0], $string);
			fclose ($pipes[0]);
			$output = stream_get_contents ($pipes[1]);
			fclose ($pipes[1]);
			$returnStatus = proc_close ($process);
		}
		
		# Return false as the output if the return status is a failure
		if ($returnStatus) {return false;}	// Unix return status >0 is failure
		
		# Return the output
		return $output;
	}
	
	
	# Function to count, and display results for, a First Past The Post election, i.e. referendum or where candidates <= posts
	private function countFPTP ($data, $candidates, $isReferendum = false)
	{
		# Flatten the data
		$castVotes = array ();
		foreach ($data as $token => $voteData) {
			$castVotes[$token] = $voteData[1];	// i.e. referendum results, or first preference only
		}
		
		# Count the values
		$results = array_count_values ($castVotes);
		
		# Ensure that a count exists for all values (e.g. even if no 'blank' has been registered, blank => 0 still needs to show in the printed results
		$counts = array ();
		foreach ($candidates as $value => $label) {
			$counts[$label] = (array_key_exists ($value, $results) ? $results[$value] : 0);
		}
		
		# Start the HTML
		$html  = '';
		
		# Warn about first preference counts only (NB this is only being required because OpenSTV does not seem to handle cases of candidates <= seats, possibly for good reason)
		$firstPreferenceOnlyShown = (!$isReferendum && count ($candidates) != 1);
		if ($firstPreferenceOnlyShown) {
			$html .= "\n<p>The following shows the <strong>first preference counts only</strong>. For more details, you will need to do a manual STV count from the <a href=\"./?showvotes#blt\">raw vote data</a> for this election.</p>";
		}
		
		# Generate the HTML
		$totalVotes = count ($castVotes);
		$html .= "\n\n<table class=\"lines\">";
		foreach ($counts as $label => $total) {
			$html .= "\n\t<tr>";
			$html .= "\n\t\t<td>" . htmlspecialchars ($label) . '</td>';
			$html .= "\n\t\t<td>" . $total . ($firstPreferenceOnlyShown ? ' first preference' : '') . ($total == 1 ? ' vote' : ' votes') . '</td>';
			if ($isReferendum) {
				$html .= "\n\t\t<td>" . round ((100 * ($total / $totalVotes)), 2) . '%</td>';
			}
			$html .= "\n\t</tr>";
		}
		$html .= "\n</table>\n";
		
		# Show the result
		if ($isReferendum) {
			
			# Determine the numeric threshold
			$threshold = $this->registeredVoters * ($this->config['referendumThresholdPercent'] / 100);
			
			# Determine whether the threshold has been reached
			$yesLabel = $this->referendumCandidates[1];
			$noLabel = $this->referendumCandidates[2];
			if ($this->config['referendumThresholdIsYesVoters']) {
				$thresholdReached = ($counts[$yesLabel] >= $threshold);
			} else {
				$thresholdReached = ($totalVotes >= $threshold);
			}
			
			# Show the result (or threshold not reached)
			$thresholdDescription = ($this->config['referendumThresholdIsYesVoters'] ? 'yes-voter' : 'voter turnout');
			if ($thresholdReached) {
				$passed = ($counts[$yesLabel] > $counts[$noLabel]);	// A referendum must have YES higher than NO
				$html .= "\n<p class=\"winner\">The referendum was " . ($passed ? "<strong>PASSED</strong> (and the {$thresholdDescription} threshold of " . htmlspecialchars ($this->config['referendumThresholdPercent']) . '% was reached)' : '<strong>NOT passed</strong>') . '.</p>';
			} else {
				$html .= "\n<p class=\"winner\">Referendum NOT passed: the referendum {$thresholdDescription} threshold of " . htmlspecialchars ($this->config['referendumThresholdPercent']) . "% was not reached.</p>";
			}
		} else {
			# State the winners
			$html .= "\n<p class=\"winner\">" . ($totalCandidates == 1 ? 'The winner ' : 'Winners ') . 'should be verified manually from the first-preference counts above, or undertake a full STV count manually.</p>';
			#!# This explicit declaration is disabled until the question of RON overruling other votes is further researched, or supported natively by OpenSTV; there needs to be a routine for recognising 'RON' first as a special candidate
			// $html .= "\n<p class=\"winner\">" . ($totalCandidates == 1 ? 'Winner is ' : 'Winners are ') . htmlspecialchars (implode ('; ', $candidates)) . '.</p>';
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to clean the HTML output from OpenSTV
	private function cleanCountOutput ($html)
	{
		# Clean non-body HTML, i.e. just leave the content
		$html = preg_replace ('~<!DOCTYPE.+<body>~s', '', $html);
		$html = str_replace (array ('</body>', '</html>'), '', $html);
		
		# Clean prefix, to avoid redisplaying the title and to avoid showing the tmpfile name
		$html = preg_replace ('~<h3 class="title">.+Loading ballots from file ([^<]+)<br>\n~s', '<p class="overview">', $html);
		
		# Clean table specification
		$html = str_replace ('<table class="rounds">', '<table class="rounds border lines">', $html);
		
		# Convert breaks to XHTML
		$html = str_replace ('<br>', '<br />', $html);
		
		# Add a class for the winner
		$html = str_replace ('<p>Winner is', '<p class="winner">Winner is', $html);
		$html = str_replace ('<p>Winners are', '<p class="winner">Winners are', $html);
		#!# Ideally remove the <br /> line-breaks in the winners paragraph
		
		# Return the cleaned HTML
		return $html;
	}
	
	
	// print out the voters that have voted
	private function listvoters ($perLine = 10)
	{
		# Re-check
		if (!$this->afterBallotView) {return false;}	// This check is extraneous as this function wouldn't have been called otherwise
		
		# Give the main data required for verification
		echo "\n<p>Total number of voters on the roll is: <strong>" . number_format ($this->registeredVoters) . '</strong>.</p>';
		echo "\n<p>Total number of votes cast" . ($this->splitElection ? ' electronically' : '') . ' was: <strong>' . number_format ($this->totalVoted) . '</strong>.</p>';
		
		# State whether the user voted
		echo "\n<p>" . ($this->userHasVoted ? 'You <strong>did</strong> vote.' : 'You <strong>did not</strong> vote.') . '</p>';
		
		# End here if the voter list has been disabled
		if ($this->config['disableListWhoVoted']) {
			echo "\n<p>For reasons of privacy, this system is configured not to show the list of those who voted. Instead, for assurance purposes, a statement is shown above that confirms whether you voted.</p>";
			return false;
		}
		
		# Prevent viewing the list if they are neither a voter or an official
		if (!$this->userIsRegisteredVoter && !$this->userIsElectionOfficial) {
			echo "\n<p>For reasons of data protection, the list of voters is visible only to those on the electoral roll.</p>";
			return false;
		}
		
		# Remind people that this is personal data
		echo "\n<p><strong>Note:</strong> for reasons of data protection, this list of users that have voted is visible only to those on the electoral roll and to the election officials.</p>";
		
		if(!($result = mysql_query("SELECT username FROM `{$this->voterTable}` WHERE voted='1';"))) return($this->error ("Vote list read failed."));
		
		#!# <pre> is generated still if no-one voted
		echo "\n\n<pre>\n";
		$count=1;
		while($row = mysql_fetch_row($result)){
			echo $row[0];
			if($count != $this->totalVoted) {
				echo ',';
			}
			if (($count % $perLine) ==0){
				echo "\n";
			}
			$count++;
		}
		echo "</pre>";
	}
	
	
	/* END OF PAGES AND VOTING FLOW SECTION */
	
	
	/* START OF PRINTED PAGES SECTION */
	
	
	# Function to create formatted, printable lists of who has already voted
	private function admin_paperroll ()
	{
		# Introduce purpose of this page
		echo "\n<p>This page is provided so that you can print off a list of voters, showing who has already voted, for use during the paper voting phase after online voting has closed.</p>";
		
		# Only allow viewability after close of online voting
		if (!$this->afterElection) {
			echo "\n<p>The paper ballot list is not available until close of online voting.</p>";
			return false;
		}
		
		# Get the data
		$query = "SELECT username,unit,forename,surname,voted FROM `{$this->voterTable}` ORDER BY unit,surname,forename;";
		if (!$voterData = $this->getData ($query)) {
			echo "\n<p>There was a problem getting the voter data.</p>";
			return false;
		}
		
		# Presentational changes
		foreach ($voterData as $index => $value) {
			$username = $value['username'];
			unset ($voterData[$index]['username']);
			if ($value['voted']) {
				$voterData[$index]['forename'] = '<s>' . htmlspecialchars ($voterData[$index]['forename']) . '</s>';
				$voterData[$index]['surname'] = '<s>' . htmlspecialchars ($voterData[$index]['surname']) . '</s>';
			}
			$voterData[$index]['voted'] = ($voterData[$index]['voted'] ? 'Already voted online' : '');
		}
		
		# Regroup the main data by unit
		$data = $this->regroup ($voterData, 'unit', $removeGroupColumn = true);
		$totalVoterUnits = count ($data);
		
		# Compile the HTML
		$startHtml  = '';
		if ($totalVoterUnits > 1) {
			$startHtml .= "\n<p><em><strong>On a modern browser, such as Firefox, each voter Unit (e.g. College/Department/division/etc.) should automatically start on a separate page when printing.</strong></em></p>";
		}
		$startHtml .= "\n<p>List created at " . date ('r') . '</p>';
		
		# Show the statistics
		$startHtml .= "\n<h2>Statistics</h2>";
		$startHtml .= "\n<p>Total eligible voters: " . number_format ($this->registeredVoters) . '</p>';
		$startHtml .= "\n<p>Total voted: " . number_format ($this->totalVoted) . '</p>';
		$startHtml .= "\n<p>Percentage: " . round (($this->totalVoted / $this->registeredVoters) * 100, 2) . '%</p>';
		if ($totalVoterUnits > 1) {
			$startHtml .= "\n<p>Total voter Units (Colleges/Departments/divisions/etc.): {$totalVoterUnits}</p>";
		}
		
		# Loop through and create the list for each unit and counts
		$unitsHtml  = '';
		$unitsStatisticsTable  = "\n<table class=\"lines compressed\">";
		$unitsStatisticsTable .= "\n\t<tr><th>Unit</th><th>Voted online</th><th>Total voters</th><th>Percentage voted online</th></tr>";
		foreach ($data as $unit => $users) {
			
			# Count the total voters in this Unit and the number of those who have voted in this Unit
			$votersThisUnit = count ($users);
			$haveVoted = 0;
			foreach ($users as $index => $attributes) {
				if ($attributes['voted']) {
					$haveVoted++;
				}
			}
			$percentageHaveVotedThisUnit = round (($haveVoted / $votersThisUnit) * 100, 2);
			
			# Compile the HTML
			$unitName = (strlen ($unit) ? htmlspecialchars ($unit) : '[Unnamed voter unit]');
			$unitsHtml .= "\n<h2 class=\"unit\">Electoral roll for: <u>{$unitName}</u>,<br />for ballot: <u>" . htmlspecialchars ($this->config['title']) . '</u></h2>';
			$unitsHtml .= "\n<p>Total voted online: {$haveVoted} / {$votersThisUnit} (= {$percentageHaveVotedThisUnit}%).</p>";
			$unitsHtml .= "\n<p>Those crossed out already have voted online. Do NOT give such people a voting paper.</p>";
			$unitsHtml .= "\n<p>As the remainder come to vote, draw a line through them.</p>";
			$unitsHtml .= "\n<table class=\"lines compressed paperroll\">";
			$unitsHtml .= "\n\t<tr><th>Forename</th><th>Surname</th><th>Voted?</th></tr>";
			foreach ($users as $index => $attributes) {
				$unitsHtml .= "\n\t<tr>";
				foreach ($attributes as $key => $value) {
					$unitsHtml .= "<td>{$value}</td>";
				}
				$unitsHtml .= "</tr>";
			}
			$unitsHtml .= "\n</table>";
			$unitsHtml .= "\n<hr />";
			
			# Add the stats for this Unit
			$unitsStatisticsTable .= "\n\t<tr><td>{$unitName}</td><td>{$haveVoted}</td><td>{$votersThisUnit}</td><td>{$percentageHaveVotedThisUnit} %</td></tr>";
		}
		$unitsStatisticsTable .= "\n</table>";
		
		# Add the statistics table, showing the vote levels for each Unit, at the end of the starting HTML
		$startHtml .= $unitsStatisticsTable;
		$startHtml .= "\n<hr />";
		
		# End marking (mainly for browser page-breaking)
		$unitsHtml .= "\n<h2 class=\"unit\">PRINTABLE ROLL ENDED</h2>";
		
		# Compile the overall HTML
		$html = $startHtml . $unitsHtml;
		
		# Show the formatted roll output
		echo $html;
	}
	
	
	# Helper function to regroup a data set into separate groups; from http://download.geog.cam.ac.uk/projects/application/
	private function regroup ($data, $regroupByColumn, $removeGroupColumn = true)
	{
		# Return the data unmodified if not an array or empty
		if (!is_array ($data) || empty ($data)) {return $data;}
		
		# Rearrange the data
		$rearrangedData = array ();
		foreach ($data as $key => $values) {
			$grouping = $values[$regroupByColumn];
			if ($removeGroupColumn) {
				unset ($data[$key][$regroupByColumn]);
			}
			$rearrangedData[$grouping][$key] = $data[$key];
		}
		
		# Return the data
		return $rearrangedData;
	}
	
	
	# Function to print emulated paper ballots
	private function admin_ballotpapers ()
	{
		# Introduce purpose of this page
		echo "\n<p>This page is provided so that you can print off pre-formatted ballot papers, for use during the paper voting phase after online voting has closed.</p>";
		
		# Only allow viewability after close of online voting
		if (!$this->afterElection) {
			echo "\n<p>The emulated paper ballot sheets are not available until close of online voting.</p>";
			return false;
		}
		
		# End if after the ballots are visible
		if ($this->afterBallotView) {
			echo "\n<p>The cast ballots have become viewable, and therefore the election has finished, so the paper ballots are no longer available.</p>";
			return false;
		}
		
		# Start the HTML
		$html  = "\n<p>Ballot paper generated at " . date ('r') . "</p>";
		$html .= "\n<p><em><strong>When printing this page, the Returning Officer must remove the automatic printing fields (e.g. date, URL) that the browser may add to avoid it looking like a webpage.</strong></em></p>";
		$html .= "\n<p>On a modern browser, such as Firefox, the ballot paper below should automatically start on a separate page when printing.</p>";
		if ($this->convertTo_CandidateToNumber) {
			$html .= "\n<p><em><strong>Note</strong>: Papers have been converted from the online-style Number:Candidate format to the paper-style Candidate:Number format.</em></p>";
		}
		$html .= "\n<h2 class=\"paperballots\">" . htmlspecialchars ($this->config['title']) . '</h2>';
		$html .= "\n<div class=\"graybox\">";
		
		# Add a stamp box
		$html .= "\n<div id=\"stampbox\">";
		$html .= "\n\t<p>This form is invalid without an official stamp:</p>";
		$html .= "\n</div>";
		
		# Give instructions on how to vote
		$html .= "\n<p><strong>How to vote:</strong></p>";
		if ($this->convertTo_CandidateToNumber) {
			$html .= "\n<p>Rank the candidates you would like to elect in order of preference:</p>";
			$html .= "\n<ul class=\"explanation\">\n\t<li>'<strong>1</strong>' for your first choice;</li>\n\t<li>'<strong>2</strong>' for your second choice;</li><li>and so on until you have ranked all your preferred candidates.</li>\n</ul>";
		} else {
			# This wording is taken from http://www.admin.cam.ac.uk/univ/so/pdfs/cso_4_ordinance01.105_170.pdf
			$html .= "\n<p>Enter against the figure 1 the name of the candidate to whom you give first preference.</p>";
			$html .= "\n<p>You may also enter, against the figures 2, 3, and so on, the names of other candidates in the order of your preference for them, continuing until you are indifferent. The order of your preferences is crucial. A later preference can be considered only if an earlier preference has received sufficient votes to qualify for election or has been excluded because of insufficient support. Under no circumstances can a later preference count against an earlier preference.</p>";
		}
		
		# Loop through each vote group to create the blocks of votes
		$elections = $this->config['electionInfo'];	// Explicitly create a copy
		foreach ($elections as $options) {
			
			# Assign the title
			$title = array_shift ($options);
			
			# Define the number of boxes
			$boxes = count ($options);	// Number of boxes should match the number of candidates
			
			# Add the heading for this vote
			$html .= "\n<h3>" . htmlspecialchars ($title) . '</h3>';
			
			# Deal with the special case of a referendum, which are consistent across formats
			$isReferendum = ($options[0] == 'referendum');
			if ($isReferendum) {
				$html .= "\n<p>Please indicate your preference with a simple 'X':</p>";
				$html .= "\n<table class=\"ballotpapers vote referendum\">";
				$html .= "\n\t<tr>\n\t\t<th>Yes</th>\n\t\t<th>No</th>\n\t</tr>";
				$html .= "\n\t<tr>\n\t\t<td>&nbsp;</td>\n\t\t<td>&nbsp;</td>\n\t</tr>";
				$html .= "\n</table>";
				
			} else {
				
				# Deal with the HTML for candidate=>number mode (i.e. paper voting style)
				if ($this->convertTo_CandidateToNumber) {
					$html .= "\n<table class=\"ballotpapers vote\">";
					foreach ($options as $index => $candidate) {
						$html .= "\n\t<tr>\n\t\t<td class=\"candidate\">" . htmlspecialchars ($candidate) . "</td>\n\t\t<td>&nbsp;</td>\n\t</tr>";
					}
					$html .= "\n</table>";
					
				# Deal with the HTML for number=>candidate mode (i.e. online voting style)
				} else {
					$html .= "\n<p>The candidates standing in this election are:</p>";
					$html .= "\n<ul>";
					foreach ($options as $index => $candidate) {
						$html .= "\n\t<li>" . htmlspecialchars ($candidate) . "</li>";
					}
					$html .= "\n</ul>";
					$html .= "\n<table class=\"ballotpapers vote\">";
					for ($box = 1; $box <= $boxes; $box++) {
						$html .= "\n\t<tr>\n\t\t<td class=\"position\">{$box}</td>\n\t\t<td>&nbsp;</td>\n\t</tr>";
					}
					$html .= "\n</table>";
				}
			}
		}
		
		# Surround the ballot paper with a box
		$html .= "\n</div>";
		
		# End marking (mainly for browser page-breaking)
		$html .= "\n<h2 class=\"paperballots\">PRINTABLE BALLOT ENDED</h2>";
		
		# Show the constructed page
		echo $html;
	}
	
	
	# Function to enable the Returning Officer to add paper votes
	private function admin_additionalvotes ()
	{
		# Start the HTML
		$html  = '';
		
		# Introduce purpose of this page
		echo "\n<p>This page is provided so that you can add in additional votes collected on paper, once the paper voting phase has closed.</p>";
		
		# Return no problems if functionality not enabled
		if (!$this->config['additionalVotesCsvDirectory']) {
			echo "\n<p>The functionality for adding additional votes after close of voting is not configured for this installation.</p>";
			return;
		}
		
		# Return no problems if not relevant to the current phase
		if (!$this->afterBallotView) {
			echo "\n<p>Additional votes can only be added following close of voting.</p>";
			return;
		}
		
		# Set the available statuses
		$statuses = array (
			'draft' => '<strong>Draft</strong> (results visible only to Returning Officer)',
			'final' => '<strong>Final</strong> (results visible to all users)',
		);
		
		# Determine if the form is submitted
		$formSubmitted = (isSet ($_POST) && isSet ($_POST['status']) && isSet ($_POST['additionalvotes']));
		
		# Define the filenames of the potential additional votes files, and create a registry of any files loaded, first clearing out any existing files if the form has been submitted
		$files = array ();
		$existingLoaded = array ();
		$deletedPrevious = array ();
		foreach ($statuses as $status => $label) {
			$files[$status] = $this->config['additionalVotesCsvDirectory'] . '/' . $this->config['id'] . '.' . $status . '.csv';
			if (is_file ($files[$status])) {
				if ($formSubmitted) {
					unlink ($files[$status]);
					$deletedPrevious[] = $status;
				} else {
					$existingLoaded[] = $status;
				}
			}
		}
		
		# Set any existing loaded data to be prefilled
		$initialAdditionalVotesValue = '';
		$initialStatusValue = 'draft';
		if ($this->additionalVotesFile) {
			$alreadyLoadedData = file_get_contents ($this->additionalVotesFile);
			$initialAdditionalVotesValue = str_replace (',', "\t", $alreadyLoadedData);	// Convert from (basic, unquoted) CSV to TSV
			$initialStatusValue = ($this->additionalVotesFileFinal ? 'final' : 'draft');
		}
		
		# Obtain the form values, and set the default
		$statusValue = ($formSubmitted && is_string ($_POST['status']) && array_key_exists ($_POST['status'], $statuses) ? $_POST['status'] : $initialStatusValue);
		$additionalVotesValue = ($formSubmitted && is_string ($_POST['additionalvotes']) ? $_POST['additionalvotes'] : $initialAdditionalVotesValue);
		
		# Obtain the expected fieldnames
		$votesTableFields = $this->votesTableFields ();
		$fieldnames = array_keys ($votesTableFields);
		
		# Process the TSV data (data pasted in from a spreadsheet will be TSV)
		$additionalVotesErrorMessage = false;
		$csvData = array ();
		if ($formSubmitted && $additionalVotesValue) {
			$csvData = $this->csvToArray ($additionalVotesValue, $separator = "\t", $fieldnames, $this->additionalVotePrefix, $emptyCellToCharacter = '0', $additionalVotesErrorMessage);
		}
		
		# If the paper vote takes place after the online vote, i.e. uses the same electoral roll, ensure the total number of submitted votes is not more than the number of voters
		if ($this->paperVotingFollowsOnlineVoting) {
			if ($csvData) {
				if (!$additionalVotesErrorMessage) {	// I.e. don't do this check until the user submission format is correct
					$totalOnlineVotes = $this->totalVoted;
					$totalPaperVotes = count ($csvData);
					if (($totalOnlineVotes + $totalPaperVotes) > $this->registeredVoters) {
						$additionalVotesErrorMessage = 'You have entered ' . number_format ($totalPaperVotes) . ' additional ' . ($totalPaperVotes == 1 ? 'vote' : 'votes') . ' cast on paper. When added to the ' . number_format ($totalOnlineVotes) . ' votes cast online, this is more than the number of voters (' . number_format ($this->registeredVoters) . ') on the electoral roll you loaded.';
					}
				}
			}
		}
		
		# Determine the form status
		$formComplete = ($formSubmitted && strlen ($statusValue) && strlen ($additionalVotesValue) && !$additionalVotesErrorMessage);
		
		# Create the form to add the votes
		$html .= "\n<p>Using the form below, you can add in additional votes collected on paper.</p>";
		$html .= "\n<p>It is <strong>your</strong> responsibility to check the validity of the votes you are pasting in.</p>";
		if ($existingLoaded) {
			$html .= "\n" . '<p>There is previously-added additional vote data (' . implode (' and ', $existingLoaded) . ') loaded, as shown below. Pressing the submit button below will clear this existing additional vote data.</p>';
		}
		if ($deletedPrevious) {
			$html .= "\n" . '<p>Previously-added additional vote data (' . implode (' and ', $deletedPrevious) . ') has been cleared.</p>';
		}
		$html .= "\n<form method=\"post\" action=\"./?" . __FUNCTION__ . '">';
		if ($formSubmitted && !$statusValue) {
			$html .= "\n" . '<p class="warning"><em>You didn\'t select a status value!</em></p>';
		}
		$html .= "\n" . '<p>';
		foreach ($statuses as $status => $label) {
			$html .= "\n\t" . "<input type=\"radio\" name=\"status\" id=\"status_{$status}\" value=\"{$status}\"" . ($statusValue == $status ? ' checked="checked"' : '') . " /><label for=\"status_{$status}\"> {$label}</label><br />";
		}
		$html .= "\n" . '</p>';
		$html .= "\n" . '<p>Paste in the contents of your spreadsheet; see notes below about the format.</p>';
		if ($formSubmitted && !$additionalVotesValue) {
			$html .= "\n" . '<p class="warning"><em>You didn\'t enter your spreadsheet data!</em></p>';
		}
		if ($additionalVotesErrorMessage) {
			$html .= "\n" . '<p class="warning"><em>' . $additionalVotesErrorMessage . '</em></p>';
		}
		$html .= "\n" . '<textarea name="additionalvotes" cols="100" rows="15">' . htmlspecialchars ($additionalVotesValue) . '</textarea>';
		$html .= "\n" . '<p><input type="submit" /></p>';
		$html .= "\n</form>";
		
		# Describe the format
		$html .= "\n" . '<p class=\"comment\">The first row must be of the fieldnames, i.e.: <em>' . implode ("\t", $fieldnames) . '</em> .<br />';
		$html .= "\n" . "Each result row must begin with a token starting <em>{$this->additionalVotePrefix}</em>, i.e. <em>{$this->additionalVotePrefix}1</em> then <em>{$this->additionalVotePrefix}2</em>, etc., one vote set per line.<br />";
		$html .= "\n" . "There must not be any empty rows between the lines.<br />";
		$html .= "\n" . "If no expression of a preference, insert either 0 or leave the cell empty. If left empty, the system will insert the value 0 upon submission in such cases.</p>";
		$html .= "\n" . '<p><em>Example:</em></p>';
		$html .= "\n" . $this->samplePaperVotesSpreadsheet ($fieldnames);
		
		# End if the form is not complete
		if (!$formComplete) {
			echo $html;
			return $html;
		}
		
		# Clear the form HTML
		$html = '';
		
		# Convert the CSV array to a string
		$csvString  = '';
		$csvString .= implode (',', $fieldnames) . "\n";
		foreach ($csvData as $token => $row) {
			$csvString .= $token . ',' . implode (',', $row) . "\n";
		}
		$csvString = trim ($csvString);
		
		# Write the file and its contents; the additionalVotesSetupOk() routine has already established it is writable
		$filename = $files[$statusValue];
		file_put_contents ($filename, $csvString);
		
		# Read out the result
		$contents = file_get_contents ($filename);
		$html .= "\n" . '<p><strong>Your file has been correctly saved, and reads as below.</strong></p>';
		if ($statusValue == 'draft') {
			$html .= "\n" . '<p><strong>Please <a href="./?results">check the results page</a></strong> to ensure this is correct. Currently the data is only visible to you as Returning Officer.</p>';
			$html .= "\n" . '<p><strong>You must then <a href="./?admin_additionalvotes">return to set the data as final</a></strong> for it to be visible to all users.</p>';
		} else {
			$html .= "\n" . '<p><strong>The <a href="./?results">combined, final result</a> is now visible</strong> to those on the electoral roll.</p>';
		}
		$html .= "\n<pre>";
		$html .= "\n" . htmlspecialchars ($contents);
		$html .= "\n</pre>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to generate a sample paper votes spreadsheet
	private function samplePaperVotesSpreadsheet ($fieldnames)
	{
		# Generate the HTML
		$html  = "\n" . '<table class="border lines compressed regulated">';
		$html .= "\n\t" . '<tr>';
		foreach ($fieldnames as $fieldname) {
			$html .= "<td>{$fieldname}</td>";
		}
		$html .= '</tr>';
		$generateSamples = 4;
		for ($i = 1; $i <= $generateSamples; $i++) {
			$html .= "\n\t" . '<tr>';
			$html .= "\n\t<td>additionalvote{$i}</td>";
			foreach ($this->config['electionInfo'] as $voteSet => $candidates) {
				array_shift ($candidates);	// Skip label
				$totalCandidates = count ($candidates);
				$availableChoices = array_merge (range (1, $totalCandidates), array_fill (0, $totalCandidates, 0));	// Each candidate (e.g. 1,2,3) plus possibility of not voting for each (0,0,0)
				foreach ($candidates as $candidate) {
					$choiceIndex = array_rand ($availableChoices);	// Make a choice
					$result = $availableChoices[$choiceIndex];	// Select the value
					unset ($availableChoices[$choiceIndex]);	// Remove from list
					if ($result == '0') {$result = '<span class="heavilyfaded">0</span>';}	// 0 is optional, so show heavily faded-out
					$html .= "\n\t<td>" . $result . '</td>';
				}
			}
			$html .= "\n\t" . '</tr>';
		}
		$html .= "\n" . '</table>';
		
		# Return the HTML
		return $html;
	}
	
	
	/* END OF PRINTED PAGES SECTION */
	
	
	// RFC2289 defines a list of words for human-friendly one-time key exchange.
	// Copyright (C) The Internet Society (1998).  All Rights Reserved.
	private $rfc2289words = array (
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
		"YOKE"
	);
}

?>
