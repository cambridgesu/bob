# BOB: a Basic On line Ballot box [1]

First things first. Electronic voting is a thorny topic, and unavoidably so. Please think very carefully about the risks involved, and definitely do not assume that I've done so for you.

The BOB system is designed to be an electronic version of a ballot box for Single Transferable Voting elections.

Being a ballot box, the BOB system doesn't do any vote counting for you. In fact, it doesn't really do much at all. However, since the voting records are electronic, the counting process is still significantly sped up. Full automation of STV elections does not appeal to me—at the very least some stages of manual involvement should be secured within the vote counting process.

The BOB system is intended to be used in a context where people will verify that their votes were recorded correctly after the ballot: thus the software itself cannot cause an undetectable manipulation of the election's result (although this approach introduces other potential problems, as discussed below).

I hope this software might be useful to you, but as the GPL says, this code doesn't come with any warranties! None of the people involved with writing, testing or checking this code have been contracted to do so. While I would be very pleased to know that it is assisting larger organisations' elections, the code was not originally written with that target in mind. Of course, I haven't set out to write a completely broken system either!

## Warnings about unavoidable BOB voting process risks

The returning officer and anyone with access to the webserver, including read access to its logs, will know the times at which votes were cast and may therefore be able to break privacy to some extent.
The plain-text emails used to return receipts to voters and to the RO are vulnerable to interception. Read access to mailserver logs is also a possible privacy leak. Election officials may wish to disable the email receipts if this concerns them.
The use of unique anonymous tokens to identify voters opens the system to coercion and vote-selling. There is essentially no way of fixing this without breaking more important properties such as usability and verifiability.
Further discussion of the risks and threats involved with BOB voting are included within the documentation in the distribution files. It is up to you to decide whether you are willing to take on these risks.

## Credits

Most of the core electronic voting system code [2] was written by me ([David Eyers](http://www.cl.cam.ac.uk/~dme26/)). However the extra checks and balances, administration, configuration and installation support written by others now eclipse the core voting system in terms of lines of code. Significant contributions have been made by:

* Martin Lucas-Smith
* David Turner (@cl.cam)
* Simon Hopkins (@gradunion.cam)
* Robert Whittaker (previously @gradunion.cam)

## Releases

A great deal of refactoring work was done in early 2009 (mostly by people other than me) to shift BOB into being a project entirely written in PHP, instead of installation involving a combination of Perl, PHP, make, etc. The core workflow has only been very slightly modified since what was used in the legacy releases below.

Installation is now guided by the BOB system itself, so all you need to do is:

1. Download a release file.
1. Decompress the release into a web-accessible folder: if working on a command line, a directory BOB will be created in your current working directory.
1. Point your browser to your webserver's URL for the directory created above.
1. Follow the directions provided until your BOB installation is ready.

### BOB1.0.0 (ZIP)

This release tidies up the looping in the construction of the token string, so that it loops through the configuration rather than the posted data. In practice this will give the same result because the checks earlier in the code ensure that the posted data has the correct structure, but the refactored code is more explicit.

The [CUSU managed vote server](https://www.vote.cusu.cam.ac.uk/) is now running this version of BOB.

### BOB0.11.6 (ZIP)

This release of BOB incorporates a configuration option to support two databases: a staging database that is used prior to the ballot, and a live database that is used during the ballot. This facilitates independent database privileges being used on each database - e.g. the live database can be set up without the privilege to remove voters from the roll (although this would only occur due to a bug in BOB). It also includes a number of presentation tweaks, and works with OpenSTV 1.5.

### BOB0.10.0 (ZIP)

For the first time, this release of BOB facilitates automated vote counting, using the [OpenSTV](http://www.openstv.org/) software. If OpenSTV is installed on the server, and BOB is configured to know where OpenSTV has been installed, BOB can incorporate the results directly. Otherwise, BOB can provide the count data in a format suitable for pasting into the graphical user interface of the OpenSTV software running on a different computer.

The [CUSU managed vote server](https://www.vote.cusu.cam.ac.uk/) includes this integrated OpenSTV support.

### BOB0.9.3 (ZIP)

A minor refactoring of version 0.9.2 done so as to assist the upcoming release of software to assist configuring BOB instances.

### BOB0.9.2 (ZIP)

BOB0.9.2 is the same software that underlies the managed ballot box server that CUSU use for their online voting in Lent Term 2009. Since the CUSU system is intended to facilitate Cambridge organisations running their own elections, if your needs are met by their service, it is highly likely that you should use it. CUSU have already done a great deal of work to mimise the risks involved with using BOB for electronic voting: a professional, impartial sysadmin has agreed to manage a dedicated, but physically isolated (including being isolated from CUSU) ballot-box server.

## Legacy releases—for historical interest only

The small amount of documentation within the BOB releases should guide you through installation:

1. Download one of the release files linked below,
1. change into the directory into which you want the BOB files to decompress (no subdirectories are created),
1. decompress the release archive,
1. edit the variables in `electionConfig.pm`,
1. then run make to be told what options you have.
Do let me know if you find code errors or things that can be improved. Keep in mind, however, that I am trying to keep this system as simple as practically possible. As a general rule, the earlier releases have simpler structure, but are missing convenient features. I can generate the intermediate versions on request if you have any pressing need for them.

### Release 0.7d - BOB0.7d.tar.gz

This release incorporates a variety of enhancements and is being used for the CUSU elections in March 2007. The basic logic remains unchanged although the system has been expanded to meet the needs of CUSU regarding election officials, the need for referendum support, to support the electronic then paper process they will use, etc. Much of the code extending this version has been written by others (thanks!), but it still passes my usual testing run with no problem, and the additional code looks fine to me (and has been audited by others too).

### Release 0.6d - BOB0.6d.tar.gz

This release is refactors the code in the hope that better structure will allow the system to be more easily extensible, and to shake off some of its older design mistakes. It's not particularly field tested yet, but would like to hear if it works for you. A number of extra PHP pages have been added. Some for public use: e.g. a page to display the results of the election when it is complete, and some for election official use, e.g. to monitor the number of people who have voted during the election, and to retrieve the list of people who have voted after the election is complete. There's even a vague attempt at calculating the results of an STV election, but I'd treat that as a toy for now (although would be interested to hear about its bugs).

### Release 0.5b - BOB0.5b.tar.gz

This release changes nothing in the voting workflow so should work interchangeably with release 0.5. This version refactors the main voting workflow into a class, with a view to more comprehensive refactoring in future. It also tightens up a few things that wouldn't be necessary on most Apache installations (even so - better safe than sorry). For example private files now get permission 600 rather than 660, and there's a extra (but unnecessary) level of .htaccess protection over the dbpass file. Having said all this, 0.5b is not a well field-tested release.

### Release 0.5 - BOB0.5.tar.gz

Compared to version 0.4, this version reverses the relationship between the people listed for different preferences. Rather than selecting a preference for each candidate, instead a candidate is selected for each preference. This brings the voting procedure better in line with the University of Cambridge Single Transferable Voting regulations. Version 0.5 has safer setup procedures, i.e. it's less likely admins will run into problems when they're trying to set up an election initially. Also, this version generates tokens consisting of short English words rather than letter sequences. Even so, for now Version 0.4 has been used for a larger number of elections than version 0.5.

### Release 0.4 - BOB0.4.tar.gz

Fixed a problem regarding randomness. Fixed a problem regarding checking user input - I'm not sure the result would have been much more than users being able to invalidly vote in creative ways, but the issue shouldn't be there at all of course.

***

* [1] or a "Broken On-line Ballot-box", depending on whether you're happy with how it works.
* [2] before sledging my horrible code, note that I don't normally turn to PHP or Perl to solve programming problems: they just happened to fit the need in this case.