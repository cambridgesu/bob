#!/usr/bin/perl -w

# This file is part of the Basic On-line Ballot-box (BOB).
# $Id: genElection.pl 83 2007-02-10 11:35:43Z dme26 $
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
<Files ".ht*">
  deny from all
</Files>
<Files "dbpass">
  deny from all
</Files>
EOF
    close $f;
}

if($f=openIfNoFile("dbpass",0600)){
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
${electionPHP}
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
    print $f <<EOF;

// RFC2289 defines a list of words for human-friendly one-time key exchange.
// Copyright (C) The Internet Society (1998).  All Rights Reserved.
\$rfc2289words=array(
"A",     "ABE",   "ACE",   "ACT",   "AD",    "ADA",   "ADD",
"AGO",   "AID",   "AIM",   "AIR",   "ALL",   "ALP",   "AM",    "AMY",
"AN",    "ANA",   "AND",   "ANN",   "ANT",   "ANY",   "APE",   "APS",
"APT",   "ARC",   "ARE",   "ARK",   "ARM",   "ART",   "AS",    "ASH",
"ASK",   "AT",    "ATE",   "AUG",   "AUK",   "AVE",   "AWE",   "AWK",
"AWL",   "AWN",   "AX",   "AYE",   "BAD",   "BAG",   "BAH",   "BAM",
"BAN",   "BAR",   "BAT",   "BAY",   "BE",    "BED",   "BEE",   "BEG",
"BEN",   "BET",   "BEY",   "BIB",   "BID",   "BIG",   "BIN",   "BIT",
"BOB",   "BOG",   "BON",   "BOO",   "BOP",   "BOW",   "BOY",   "BUB",
"BUD",   "BUG",   "BUM",   "BUN",   "BUS",   "BUT",   "BUY",   "BY",
"BYE",   "CAB",   "CAL",   "CAM",   "CAN",   "CAP",   "CAR",   "CAT",
"CAW",   "COD",   "COG",   "COL",   "CON",   "COO",   "COP",   "COT",
"COW",   "COY",   "CRY",   "CUB",   "CUE",   "CUP",   "CUR",   "CUT",
"DAB",   "DAD",   "DAM",   "DAN",   "DAR",   "DAY",   "DEE",   "DEL",
"DEN",   "DES",   "DEW",   "DID",   "DIE",   "DIG",   "DIN",   "DIP",
"DO",    "DOE",   "DOG",   "DON",   "DOT",   "DOW",   "DRY",   "DUB",
"DUD",   "DUE",   "DUG",   "DUN",   "EAR",   "EAT",   "ED",    "EEL",
"EGG",   "EGO",   "ELI",   "ELK",   "ELM",   "ELY",   "EM",    "END",
"EST",   "ETC",   "EVA",   "EVE",   "EWE",   "EYE",   "FAD",   "FAN",
"FAR",   "FAT",   "FAY",   "FED",   "FEE",   "FEW",   "FIB",   "FIG",
"FIN",   "FIR",   "FIT",   "FLO",   "FLY",   "FOE",   "FOG",   "FOR",
"FRY",   "FUM",   "FUN",   "FUR",   "GAB",   "GAD",   "GAG",   "GAL",
"GAM",   "GAP",   "GAS",   "GAY",   "GEE",   "GEL",   "GEM",   "GET",
"GIG",   "GIL",   "GIN",   "GO",    "GOT",   "GUM",   "GUN",   "GUS",
"GUT",   "GUY",   "GYM",   "GYP",   "HA",    "HAD",   "HAL",   "HAM",
"HAN",   "HAP",   "HAS",   "HAT",   "HAW",   "HAY",   "HE",    "HEM",
"HEN",   "HER",   "HEW",   "HEY",   "HI",    "HID",   "HIM",   "HIP",
"HIS",   "HIT",   "HO",   "HOB",   "HOC",   "HOE",   "HOG",   "HOP",
"HOT",   "HOW",   "HUB",   "HUE",   "HUG",   "HUH",   "HUM",   "HUT",
"I",     "ICY",   "IDA",   "IF",    "IKE",   "ILL",   "INK",   "INN",
"IO",    "ION",   "IQ",   "IRA",   "IRE",   "IRK",   "IS",    "IT",
"ITS",   "IVY",   "JAB",   "JAG",   "JAM",   "JAN",   "JAR",   "JAW",
"JAY",   "JET",   "JIG",   "JIM",   "JO",    "JOB",   "JOE",   "JOG",
"JOT",   "JOY",   "JUG",   "JUT",   "KAY",   "KEG",   "KEN",   "KEY",
"KID",   "KIM",   "KIN",   "KIT",   "LA",    "LAB",   "LAC",   "LAD",
"LAG",   "LAM",   "LAP",   "LAW",   "LAY",   "LEA",   "LED",   "LEE",
"LEG",   "LEN",   "LEO",   "LET",   "LEW",   "LID",   "LIE",   "LIN",
"LIP",   "LIT",   "LO",   "LOB",   "LOG",   "LOP",   "LOS",   "LOT",
"LOU",   "LOW",   "LOY",   "LUG",   "LYE",   "MA",    "MAC",   "MAD",
"MAE",   "MAN",   "MAO",   "MAP",   "MAT",   "MAW",   "MAY",   "ME",
"MEG",   "MEL",   "MEN",   "MET",   "MEW",   "MID",   "MIN",   "MIT",
"MOB",   "MOD",   "MOE",   "MOO",   "MOP",   "MOS",   "MOT",   "MOW",
"MUD",   "MUG",   "MUM",   "MY",    "NAB",   "NAG",   "NAN",   "NAP",

"NAT",   "NAY",   "NE",   "NED",   "NEE",   "NET",   "NEW",   "NIB",
"NIL",   "NIP",   "NIT",   "NO",    "NOB",   "NOD",   "NON",   "NOR",
"NOT",   "NOV",   "NOW",   "NU",    "NUN",   "NUT",   "O",     "OAF",
"OAK",   "OAR",   "OAT",   "ODD",   "ODE",   "OF",    "OFF",   "OFT",
"OH",    "OIL",   "OK",   "OLD",   "ON",    "ONE",   "OR",    "ORB",
"ORE",   "ORR",   "OS",   "OTT",   "OUR",   "OUT",   "OVA",   "OW",
"OWE",   "OWL",   "OWN",   "OX",    "PA",    "PAD",   "PAL",   "PAM",
"PAN",   "PAP",   "PAR",   "PAT",   "PAW",   "PAY",   "PEA",   "PEG",
"PEN",   "PEP",   "PER",   "PET",   "PEW",   "PHI",   "PI",    "PIE",
"PIN",   "PIT",   "PLY",   "PO",    "POD",   "POE",   "POP",   "POT",
"POW",   "PRO",   "PRY",   "PUB",   "PUG",   "PUN",   "PUP",   "PUT",
"QUO",   "RAG",   "RAM",   "RAN",   "RAP",   "RAT",   "RAW",   "RAY",
"REB",   "RED",   "REP",   "RET",   "RIB",   "RID",   "RIG",   "RIM",
"RIO",   "RIP",   "ROB",   "ROD",   "ROE",   "RON",   "ROT",   "ROW",
"ROY",   "RUB",   "RUE",   "RUG",   "RUM",   "RUN",   "RYE",   "SAC",
"SAD",   "SAG",   "SAL",   "SAM",   "SAN",   "SAP",   "SAT",   "SAW",
"SAY",   "SEA",   "SEC",   "SEE",   "SEN",   "SET",   "SEW",   "SHE",
"SHY",   "SIN",   "SIP",   "SIR",   "SIS",   "SIT",   "SKI",   "SKY",
"SLY",   "SO",    "SOB",   "SOD",   "SON",   "SOP",   "SOW",   "SOY",
"SPA",   "SPY",   "SUB",   "SUD",   "SUE",   "SUM",   "SUN",   "SUP",
"TAB",   "TAD",   "TAG",   "TAN",   "TAP",   "TAR",   "TEA",   "TED",
"TEE",   "TEN",   "THE",   "THY",   "TIC",   "TIE",   "TIM",   "TIN",
"TIP",   "TO",    "TOE",   "TOG",   "TOM",   "TON",   "TOO",   "TOP",
"TOW",   "TOY",   "TRY",   "TUB",   "TUG",   "TUM",   "TUN",   "TWO",
"UN",    "UP",    "US",   "USE",   "VAN",   "VAT",   "VET",   "VIE",
"WAD",   "WAG",   "WAR",   "WAS",   "WAY",   "WE",    "WEB",   "WED",
"WEE",   "WET",   "WHO",   "WHY",   "WIN",   "WIT",   "WOK",   "WON",
"WOO",   "WOW",   "WRY",   "WU",    "YAM",   "YAP",   "YAW",   "YE",
"YEA",   "YES",   "YET",   "YOU",   "ABED",  "ABEL",  "ABET",  "ABLE",
"ABUT",  "ACHE",  "ACID",  "ACME",  "ACRE",  "ACTA",  "ACTS",  "ADAM",
"ADDS",  "ADEN",  "AFAR",  "AFRO",  "AGEE",  "AHEM",  "AHOY",  "AIDA",
"AIDE",  "AIDS",  "AIRY",  "AJAR",  "AKIN",  "ALAN",  "ALEC",  "ALGA",
"ALIA",  "ALLY",  "ALMA",  "ALOE",  "ALSO",  "ALTO",  "ALUM",  "ALVA",
"AMEN",  "AMES",  "AMID",  "AMMO",  "AMOK",  "AMOS",  "AMRA",  "ANDY",
"ANEW",  "ANNA",  "ANNE",  "ANTE",  "ANTI",  "AQUA",  "ARAB",  "ARCH",
"AREA",  "ARGO",  "ARID",  "ARMY",  "ARTS",  "ARTY",  "ASIA",  "ASKS",
"ATOM",  "AUNT",  "AURA",  "AUTO",  "AVER",  "AVID",  "AVIS",  "AVON",
"AVOW",  "AWAY",  "AWRY",  "BABE",  "BABY",  "BACH",  "BACK",  "BADE",
"BAIL",  "BAIT",  "BAKE",  "BALD",  "BALE",  "BALI",  "BALK",  "BALL",
"BALM",  "BAND",  "BANE",  "BANG",  "BANK",  "BARB",  "BARD",  "BARE",
"BARK",  "BARN",  "BARR",  "BASE",  "BASH",  "BASK",  "BASS",  "BATE",
"BATH",  "BAWD",  "BAWL",  "BEAD",  "BEAK",  "BEAM",  "BEAN",  "BEAR",
"BEAT",  "BEAU",  "BECK",  "BEEF",  "BEEN",  "BEER",  "BEET",  "BELA",
"BELL",  "BELT",  "BEND",  "BENT",  "BERG",  "BERN",  "BERT",  "BESS",
"BEST",  "BETA",  "BETH",  "BHOY",  "BIAS",  "BIDE",  "BIEN",  "BILE",
"BILK",  "BILL",  "BIND",  "BING",  "BIRD",  "BITE",  "BITS",  "BLAB",
"BLAT",  "BLED",  "BLEW",  "BLOB",  "BLOC",  "BLOT",  "BLOW",  "BLUE",
"BLUM",  "BLUR",  "BOAR",  "BOAT",  "BOCA",  "BOCK",  "BODE",  "BODY",

"BOGY",  "BOHR",  "BOIL",  "BOLD",  "BOLO",  "BOLT",  "BOMB",  "BONA",
"BOND",  "BONE",  "BONG",  "BONN",  "BONY",  "BOOK",  "BOOM",  "BOON",
"BOOT",  "BORE",  "BORG",  "BORN",  "BOSE",  "BOSS",  "BOTH",  "BOUT",
"BOWL",  "BOYD",  "BRAD",  "BRAE",  "BRAG",  "BRAN",  "BRAY",  "BRED",
"BREW",  "BRIG",  "BRIM",  "BROW",  "BUCK",  "BUDD",  "BUFF",  "BULB",
"BULK",  "BULL",  "BUNK",  "BUNT",  "BUOY",  "BURG",  "BURL",  "BURN",
"BURR",  "BURT",  "BURY",  "BUSH",  "BUSS",  "BUST",  "BUSY",  "BYTE",
"CADY",  "CAFE",  "CAGE",  "CAIN",  "CAKE",  "CALF",  "CALL",  "CALM",
"CAME",  "CANE",  "CANT",  "CARD",  "CARE",  "CARL",  "CARR",  "CART",
"CASE",  "CASH",  "CASK",  "CAST",  "CAVE",  "CEIL",  "CELL",  "CENT",
"CERN",  "CHAD",  "CHAR",  "CHAT",  "CHAW",  "CHEF",  "CHEN",  "CHEW",
"CHIC",  "CHIN",  "CHOU",  "CHOW",  "CHUB",  "CHUG",  "CHUM",  "CITE",
"CITY",  "CLAD",  "CLAM",  "CLAN",  "CLAW",  "CLAY",  "CLOD",  "CLOG",
"CLOT",  "CLUB",  "CLUE",  "COAL",  "COAT",  "COCA",  "COCK",  "COCO",
"CODA",  "CODE",  "CODY",  "COED",  "COIL",  "COIN",  "COKE",  "COLA",
"COLD",  "COLT",  "COMA",  "COMB",  "COME",  "COOK",  "COOL",  "COON",
"COOT",  "CORD",  "CORE",  "CORK",  "CORN",  "COST",  "COVE",  "COWL",
"CRAB",  "CRAG",  "CRAM",  "CRAY",  "CREW",  "CRIB",  "CROW",  "CRUD",
"CUBA",  "CUBE",  "CUFF",  "CULL",  "CULT",  "CUNY",  "CURB",  "CURD",
"CURE",  "CURL",  "CURT",  "CUTS",  "DADE",  "DALE",  "DAME",  "DANA",
"DANE",  "DANG",  "DANK",  "DARE",  "DARK",  "DARN",  "DART",  "DASH",
"DATA",  "DATE",  "DAVE",  "DAVY",  "DAWN",  "DAYS",  "DEAD",  "DEAF",
"DEAL",  "DEAN",  "DEAR",  "DEBT",  "DECK",  "DEED",  "DEEM",  "DEER",
"DEFT",  "DEFY",  "DELL",  "DENT",  "DENY",  "DESK",  "DIAL",  "DICE",
"DIED",  "DIET",  "DIME",  "DINE",  "DING",  "DINT",  "DIRE",  "DIRT",
"DISC",  "DISH",  "DISK",  "DIVE",  "DOCK",  "DOES",  "DOLE",  "DOLL",
"DOLT",  "DOME",  "DONE",  "DOOM",  "DOOR",  "DORA",  "DOSE",  "DOTE",
"DOUG",  "DOUR",  "DOVE",  "DOWN",  "DRAB",  "DRAG",  "DRAM",  "DRAW",
"DREW",  "DRUB",  "DRUG",  "DRUM",  "DUAL",  "DUCK",  "DUCT",  "DUEL",
"DUET",  "DUKE",  "DULL",  "DUMB",  "DUNE",  "DUNK",  "DUSK",  "DUST",
"DUTY",  "EACH",  "EARL",  "EARN",  "EASE",  "EAST",  "EASY",  "EBEN",
"ECHO",  "EDDY",  "EDEN",  "EDGE",  "EDGY",  "EDIT",  "EDNA",  "EGAN",
"ELAN",  "ELBA",  "ELLA",  "ELSE",  "EMIL",  "EMIT",  "EMMA",  "ENDS",
"ERIC",  "EROS",  "EVEN",  "EVER",  "EVIL",  "EYED",  "FACE",  "FACT",
"FADE",  "FAIL",  "FAIN",  "FAIR",  "FAKE",  "FALL",  "FAME",  "FANG",
"FARM",  "FAST",  "FATE",  "FAWN",  "FEAR",  "FEAT",  "FEED",  "FEEL",
"FEET",  "FELL",  "FELT",  "FEND",  "FERN",  "FEST",  "FEUD",  "FIEF",
"FIGS",  "FILE",  "FILL",  "FILM",  "FIND",  "FINE",  "FINK",  "FIRE",
"FIRM",  "FISH",  "FISK",  "FIST",  "FITS",  "FIVE",  "FLAG",  "FLAK",
"FLAM",  "FLAT",  "FLAW",  "FLEA",  "FLED",  "FLEW",  "FLIT",  "FLOC",
"FLOG",  "FLOW",  "FLUB",  "FLUE",  "FOAL",  "FOAM",  "FOGY",  "FOIL",
"FOLD",  "FOLK",  "FOND",  "FONT",  "FOOD",  "FOOL",  "FOOT",  "FORD",
"FORE",  "FORK",  "FORM",  "FORT",  "FOSS",  "FOUL",  "FOUR",  "FOWL",
"FRAU",  "FRAY",  "FRED",  "FREE",  "FRET",  "FREY",  "FROG",  "FROM",
"FUEL",  "FULL",  "FUME",  "FUND",  "FUNK",  "FURY",  "FUSE",  "FUSS",
"GAFF",  "GAGE",  "GAIL",  "GAIN",  "GAIT",  "GALA",  "GALE",  "GALL",
"GALT",  "GAME",  "GANG",  "GARB",  "GARY",  "GASH",  "GATE",  "GAUL",
"GAUR",  "GAVE",  "GAWK",  "GEAR",  "GELD",  "GENE",  "GENT",  "GERM",

"GETS",  "GIBE",  "GIFT",  "GILD",  "GILL",  "GILT",  "GINA",  "GIRD",
"GIRL",  "GIST",  "GIVE",  "GLAD",  "GLEE",  "GLEN",  "GLIB",  "GLOB",
"GLOM",  "GLOW",  "GLUE",  "GLUM",  "GLUT",  "GOAD",  "GOAL",  "GOAT",
"GOER",  "GOES",  "GOLD",  "GOLF",  "GONE",  "GONG",  "GOOD",  "GOOF",
"GORE",  "GORY",  "GOSH",  "GOUT",  "GOWN",  "GRAB",  "GRAD",  "GRAY",
"GREG",  "GREW",  "GREY",  "GRID",  "GRIM",  "GRIN",  "GRIT",  "GROW",
"GRUB",  "GULF",  "GULL",  "GUNK",  "GURU",  "GUSH",  "GUST",  "GWEN",
"GWYN",  "HAAG",  "HAAS",  "HACK",  "HAIL",  "HAIR",  "HALE",  "HALF",
"HALL",  "HALO",  "HALT",  "HAND",  "HANG",  "HANK",  "HANS",  "HARD",
"HARK",  "HARM",  "HART",  "HASH",  "HAST",  "HATE",  "HATH",  "HAUL",
"HAVE",  "HAWK",  "HAYS",  "HEAD",  "HEAL",  "HEAR",  "HEAT",  "HEBE",
"HECK",  "HEED",  "HEEL",  "HEFT",  "HELD",  "HELL",  "HELM",  "HERB",
"HERD",  "HERE",  "HERO",  "HERS",  "HESS",  "HEWN",  "HICK",  "HIDE",
"HIGH",  "HIKE",  "HILL",  "HILT",  "HIND",  "HINT",  "HIRE",  "HISS",
"HIVE",  "HOBO",  "HOCK",  "HOFF",  "HOLD",  "HOLE",  "HOLM",  "HOLT",
"HOME",  "HONE",  "HONK",  "HOOD",  "HOOF",  "HOOK",  "HOOT",  "HORN",
"HOSE",  "HOST",  "HOUR",  "HOVE",  "HOWE",  "HOWL",  "HOYT",  "HUCK",
"HUED",  "HUFF",  "HUGE",  "HUGH",  "HUGO",  "HULK",  "HULL",  "HUNK",
"HUNT",  "HURD",  "HURL",  "HURT",  "HUSH",  "HYDE",  "HYMN",  "IBIS",
"ICON",  "IDEA",  "IDLE",  "IFFY",  "INCA",  "INCH",  "INTO",  "IONS",
"IOTA",  "IOWA",  "IRIS",  "IRMA",  "IRON",  "ISLE",  "ITCH",  "ITEM",
"IVAN",  "JACK",  "JADE",  "JAIL",  "JAKE",  "JANE",  "JAVA",  "JEAN",
"JEFF",  "JERK",  "JESS",  "JEST",  "JIBE",  "JILL",  "JILT",  "JIVE",
"JOAN",  "JOBS",  "JOCK",  "JOEL",  "JOEY",  "JOHN",  "JOIN",  "JOKE",
"JOLT",  "JOVE",  "JUDD",  "JUDE",  "JUDO",  "JUDY",  "JUJU",  "JUKE",
"JULY",  "JUNE",  "JUNK",  "JUNO",  "JURY",  "JUST",  "JUTE",  "KAHN",
"KALE",  "KANE",  "KANT",  "KARL",  "KATE",  "KEEL",  "KEEN",  "KENO",
"KENT",  "KERN",  "KERR",  "KEYS",  "KICK",  "KILL",  "KIND",  "KING",
"KIRK",  "KISS",  "KITE",  "KLAN",  "KNEE",  "KNEW",  "KNIT",  "KNOB",
"KNOT",  "KNOW",  "KOCH",  "KONG",  "KUDO",  "KURD",  "KURT",  "KYLE",
"LACE",  "LACK",  "LACY",  "LADY",  "LAID",  "LAIN",  "LAIR",  "LAKE",
"LAMB",  "LAME",  "LAND",  "LANE",  "LANG",  "LARD",  "LARK",  "LASS",
"LAST",  "LATE",  "LAUD",  "LAVA",  "LAWN",  "LAWS",  "LAYS",  "LEAD",
"LEAF",  "LEAK",  "LEAN",  "LEAR",  "LEEK",  "LEER",  "LEFT",  "LEND",
"LENS",  "LENT",  "LEON",  "LESK",  "LESS",  "LEST",  "LETS",  "LIAR",
"LICE",  "LICK",  "LIED",  "LIEN",  "LIES",  "LIEU",  "LIFE",  "LIFT",
"LIKE",  "LILA",  "LILT",  "LILY",  "LIMA",  "LIMB",  "LIME",  "LIND",
"LINE",  "LINK",  "LINT",  "LION",  "LISA",  "LIST",  "LIVE",  "LOAD",
"LOAF",  "LOAM",  "LOAN",  "LOCK",  "LOFT",  "LOGE",  "LOIS",  "LOLA",
"LONE",  "LONG",  "LOOK",  "LOON",  "LOOT",  "LORD",  "LORE",  "LOSE",
"LOSS",  "LOST",  "LOUD",  "LOVE",  "LOWE",  "LUCK",  "LUCY",  "LUGE",
"LUKE",  "LULU",  "LUND",  "LUNG",  "LURA",  "LURE",  "LURK",  "LUSH",
"LUST",  "LYLE",  "LYNN",  "LYON",  "LYRA",  "MACE",  "MADE",  "MAGI",
"MAID",  "MAIL",  "MAIN",  "MAKE",  "MALE",  "MALI",  "MALL",  "MALT",
"MANA",  "MANN",  "MANY",  "MARC",  "MARE",  "MARK",  "MARS",  "MART",
"MARY",  "MASH",  "MASK",  "MASS",  "MAST",  "MATE",  "MATH",  "MAUL",
"MAYO",  "MEAD",  "MEAL",  "MEAN",  "MEAT",  "MEEK",  "MEET",  "MELD",
"MELT",  "MEMO",  "MEND",  "MENU",  "MERT",  "MESH",  "MESS",  "MICE",

"MIKE",  "MILD",  "MILE",  "MILK",  "MILL",  "MILT",  "MIMI",  "MIND",
"MINE",  "MINI",  "MINK",  "MINT",  "MIRE",  "MISS",  "MIST",  "MITE",
"MITT",  "MOAN",  "MOAT",  "MOCK",  "MODE",  "MOLD",  "MOLE",  "MOLL",
"MOLT",  "MONA",  "MONK",  "MONT",  "MOOD",  "MOON",  "MOOR",  "MOOT",
"MORE",  "MORN",  "MORT",  "MOSS",  "MOST",  "MOTH",  "MOVE",  "MUCH",
"MUCK",  "MUDD",  "MUFF",  "MULE",  "MULL",  "MURK",  "MUSH",  "MUST",
"MUTE",  "MUTT",  "MYRA",  "MYTH",  "NAGY",  "NAIL",  "NAIR",  "NAME",
"NARY",  "NASH",  "NAVE",  "NAVY",  "NEAL",  "NEAR",  "NEAT",  "NECK",
"NEED",  "NEIL",  "NELL",  "NEON",  "NERO",  "NESS",  "NEST",  "NEWS",
"NEWT",  "NIBS",  "NICE",  "NICK",  "NILE",  "NINA",  "NINE",  "NOAH",
"NODE",  "NOEL",  "NOLL",  "NONE",  "NOOK",  "NOON",  "NORM",  "NOSE",
"NOTE",  "NOUN",  "NOVA",  "NUDE",  "NULL",  "NUMB",  "OATH",  "OBEY",
"OBOE",  "ODIN",  "OHIO",  "OILY",  "OINT",  "OKAY",  "OLAF",  "OLDY",
"OLGA",  "OLIN",  "OMAN",  "OMEN",  "OMIT",  "ONCE",  "ONES",  "ONLY",
"ONTO",  "ONUS",  "ORAL",  "ORGY",  "OSLO",  "OTIS",  "OTTO",  "OUCH",
"OUST",  "OUTS",  "OVAL",  "OVEN",  "OVER",  "OWLY",  "OWNS",  "QUAD",
"QUIT",  "QUOD",  "RACE",  "RACK",  "RACY",  "RAFT",  "RAGE",  "RAID",
"RAIL",  "RAIN",  "RAKE",  "RANK",  "RANT",  "RARE",  "RASH",  "RATE",
"RAVE",  "RAYS",  "READ",  "REAL",  "REAM",  "REAR",  "RECK",  "REED",
"REEF",  "REEK",  "REEL",  "REID",  "REIN",  "RENA",  "REND",  "RENT",
"REST",  "RICE",  "RICH",  "RICK",  "RIDE",  "RIFT",  "RILL",  "RIME",
"RING",  "RINK",  "RISE",  "RISK",  "RITE",  "ROAD",  "ROAM",  "ROAR",
"ROBE",  "ROCK",  "RODE",  "ROIL",  "ROLL",  "ROME",  "ROOD",  "ROOF",
"ROOK",  "ROOM",  "ROOT",  "ROSA",  "ROSE",  "ROSS",  "ROSY",  "ROTH",
"ROUT",  "ROVE",  "ROWE",  "ROWS",  "RUBE",  "RUBY",  "RUDE",  "RUDY",
"RUIN",  "RULE",  "RUNG",  "RUNS",  "RUNT",  "RUSE",  "RUSH",  "RUSK",
"RUSS",  "RUST",  "RUTH",  "SACK",  "SAFE",  "SAGE",  "SAID",  "SAIL",
"SALE",  "SALK",  "SALT",  "SAME",  "SAND",  "SANE",  "SANG",  "SANK",
"SARA",  "SAUL",  "SAVE",  "SAYS",  "SCAN",  "SCAR",  "SCAT",  "SCOT",
"SEAL",  "SEAM",  "SEAR",  "SEAT",  "SEED",  "SEEK",  "SEEM",  "SEEN",
"SEES",  "SELF",  "SELL",  "SEND",  "SENT",  "SETS",  "SEWN",  "SHAG",
"SHAM",  "SHAW",  "SHAY",  "SHED",  "SHIM",  "SHIN",  "SHOD",  "SHOE",
"SHOT",  "SHOW",  "SHUN",  "SHUT",  "SICK",  "SIDE",  "SIFT",  "SIGH",
"SIGN",  "SILK",  "SILL",  "SILO",  "SILT",  "SINE",  "SING",  "SINK",
"SIRE",  "SITE",  "SITS",  "SITU",  "SKAT",  "SKEW",  "SKID",  "SKIM",
"SKIN",  "SKIT",  "SLAB",  "SLAM",  "SLAT",  "SLAY",  "SLED",  "SLEW",
"SLID",  "SLIM",  "SLIT",  "SLOB",  "SLOG",  "SLOT",  "SLOW",  "SLUG",
"SLUM",  "SLUR",  "SMOG",  "SMUG",  "SNAG",  "SNOB",  "SNOW",  "SNUB",
"SNUG",  "SOAK",  "SOAR",  "SOCK",  "SODA",  "SOFA",  "SOFT",  "SOIL",
"SOLD",  "SOME",  "SONG",  "SOON",  "SOOT",  "SORE",  "SORT",  "SOUL",
"SOUR",  "SOWN",  "STAB",  "STAG",  "STAN",  "STAR",  "STAY",  "STEM",
"STEW",  "STIR",  "STOW",  "STUB",  "STUN",  "SUCH",  "SUDS",  "SUIT",
"SULK",  "SUMS",  "SUNG",  "SUNK",  "SURE",  "SURF",  "SWAB",  "SWAG",
"SWAM",  "SWAN",  "SWAT",  "SWAY",  "SWIM",  "SWUM",  "TACK",  "TACT",
"TAIL",  "TAKE",  "TALE",  "TALK",  "TALL",  "TANK",  "TASK",  "TATE",
"TAUT",  "TEAL",  "TEAM",  "TEAR",  "TECH",  "TEEM",  "TEEN",  "TEET",
"TELL",  "TEND",  "TENT",  "TERM",  "TERN",  "TESS",  "TEST",  "THAN",
"THAT",  "THEE",  "THEM",  "THEN",  "THEY",  "THIN",  "THIS",  "THUD",

"THUG",  "TICK",  "TIDE",  "TIDY",  "TIED",  "TIER",  "TILE",  "TILL",
"TILT",  "TIME",  "TINA",  "TINE",  "TINT",  "TINY",  "TIRE",  "TOAD",
"TOGO",  "TOIL",  "TOLD",  "TOLL",  "TONE",  "TONG",  "TONY",  "TOOK",
"TOOL",  "TOOT",  "TORE",  "TORN",  "TOTE",  "TOUR",  "TOUT",  "TOWN",
"TRAG",  "TRAM",  "TRAY",  "TREE",  "TREK",  "TRIG",  "TRIM",  "TRIO",
"TROD",  "TROT",  "TROY",  "TRUE",  "TUBA",  "TUBE",  "TUCK",  "TUFT",
"TUNA",  "TUNE",  "TUNG",  "TURF",  "TURN",  "TUSK",  "TWIG",  "TWIN",
"TWIT",  "ULAN",  "UNIT",  "URGE",  "USED",  "USER",  "USES",  "UTAH",
"VAIL",  "VAIN",  "VALE",  "VARY",  "VASE",  "VAST",  "VEAL",  "VEDA",
"VEIL",  "VEIN",  "VEND",  "VENT",  "VERB",  "VERY",  "VETO",  "VICE",
"VIEW",  "VINE",  "VISE",  "VOID",  "VOLT",  "VOTE",  "WACK",  "WADE",
"WAGE",  "WAIL",  "WAIT",  "WAKE",  "WALE",  "WALK",  "WALL",  "WALT",
"WAND",  "WANE",  "WANG",  "WANT",  "WARD",  "WARM",  "WARN",  "WART",
"WASH",  "WAST",  "WATS",  "WATT",  "WAVE",  "WAVY",  "WAYS",  "WEAK",
"WEAL",  "WEAN",  "WEAR",  "WEED",  "WEEK",  "WEIR",  "WELD",  "WELL",
"WELT",  "WENT",  "WERE",  "WERT",  "WEST",  "WHAM",  "WHAT",  "WHEE",
"WHEN",  "WHET",  "WHOA",  "WHOM",  "WICK",  "WIFE",  "WILD",  "WILL",
"WIND",  "WINE",  "WING",  "WINK",  "WINO",  "WIRE",  "WISE",  "WISH",
"WITH",  "WOLF",  "WONT",  "WOOD",  "WOOL",  "WORD",  "WORE",  "WORK",
"WORM",  "WORN",  "WOVE",  "WRIT",  "WYNN",  "YALE",  "YANG",  "YANK",
"YARD",  "YARN",  "YAWL",  "YAWN",  "YEAH",  "YEAR",  "YELL",  "YOGA",
  "YOKE" );
?>
EOF
    close $f;
    if((system "ln -s config.php config.txt")==0){
	print "+ Created config.txt symlink to config.php for source viewing.\n";
    }else{
	print "! Failed to created config.txt symlink to config.php.\n";
    }
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

    if($md5sumA = `md5sum vote.php`){
	$md5sumA =~ s/\s.*$//;
	$md5sumA =~ s/(....)/$1 /g;
	$md5sumA = "The PHP source code has the following MD5 sum: <kbd>$md5sumA</kbd>";
    }else{
	print "* Unable to calculate md5sum of vote.php.\n";
    }
    if($md5sumB = `md5sum config.php`){
	$md5sumB =~ s/\s.*$//;
	$md5sumB =~ s/(....)/$1 /g;
	$md5sumB = "The election configuration file has the following MD5 sum: <kbd>$md5sumB</kbd>";
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
code that makes it work</a> directly. $md5sumA. Also <a
href="config.txt">check the election configuration file</a>. $md5sumB.
</p>

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
            print "+ Created vote.txt symlink to vote.php for source viewing.\n";
        }else{
            print "! Failed to created vote.txt symlink to vote.php.\n";
	}
    }
}else{
    print "! vote.php does not appear to be present, but is necessary for the functioning of this system\n";
}

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
