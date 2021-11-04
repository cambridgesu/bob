# BOB: a Basic On line Ballot box

This repository tracks the live code of https://camb.vote

The BOB system is designed to be an electronic version of a ballot box for Single Transferable Voting elections.

Vote counting can be done for you, if you enable the option for running the results through an OpenSTV count.

The BOB system is intended to be used in a context where people will verify that their votes were recorded correctly after the ballot: thus the software itself cannot cause an undetectable manipulation of the election's result (although this approach introduces other potential problems, as discussed below).

We hope this software might be useful to you, but as the GPL says, this code doesn't come with any warranties.

This software is primarily being maintained for use within the CUSU online voting system - https://www.cusu.cam.ac.uk/elections/system/ .

Over 333,333 votes have been cast without challenge in over 2,709 ballots created by 211 organisations around Cambridge University, using BOB, as of January 2021. The archive of these can be seen on the CUSU voting server for those with a University login.

## Warnings about unavoidable BOB voting process risks

First things first: Electronic voting is a thorny topic, and unavoidably so. Please think very carefully about the risks involved. However, we have given a great deal of consideration and put much work into the BOB software and its optional administrative GUI on security and assurance issues.

* The returning officer and anyone with access to the webserver, including read access to its logs, will know the times at which votes were cast and may therefore be able to break privacy to some extent.
* The plain-text emails used to return receipts to voters and to the returning officer are vulnerable to interception. Read access to mailserver logs is also a possible privacy leak. Election officials may wish to disable the email receipts if this concerns them.
* The use of unique anonymous tokens to identify voters opens the system to coercion and vote-selling. There is essentially no way of fixing this without breaking more important properties such as usability and verifiability.
* Further discussion of the risks and threats involved with BOB voting are included within the documentation in the distribution files. It is up to you to decide whether you are willing to take on these risks.

## Credits

* David Eyers - http://www.cl.cam.ac.uk/~dme26/ - wrote the [core electronic vote collection system](http://www.cl.cam.ac.uk/~dme26/proj/BOB/)
* Martin Lucas-Smith - particularly added extra checks and balances, administration, configuration and installation support, and removed Perl installation stage
* David Turner (@cl.cam)
* Simon Hopkins (previously @gradunion.cam)
* Robert Whittaker (previously @gradunion.cam)

## Installation

Installation is guided by the BOB system itself, so all you need to do is:

1. Download a release file.
1. Decompress the release into a web-accessible folder: if working on a command line, a directory BOB will be created in your current working directory.
1. Point your browser to your webserver's URL for the directory created above.
1. Follow the directions provided until your BOB installation is ready.

## License

The code is licensed under the version 3 of the GPL.
