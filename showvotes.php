<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: rollcheck.php 77 2006-10-31 00:54:13Z dme26 $
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2007
-->

<?php
  if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) $bob = false; // clear $bob if we're not being included ourselves
  require_once("./BOB.php");
  $title .= ' - view votes';
?>

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
    pre {border:1px solid #bbbbbb; background: #eeeeee; padding: 4px;}
    </style>
  </head>
  <body>
    <h1><?php echo $title; ?></h1>
<?php if($bob->afterElection()){ ?>
      <p>Vote tokens and the votes they are recorded with are shown below</p>
    <h2>Key to vote data</h2>
      <?php $bob->voteDataKey(); ?>
    <h2>List of votes (column vXpY is the Yth preference for election X)</h2>
      <?php $bob->listVotes();      
    }else{
?><p>The election has not finished, so viewing the complete ballot box is not yet possible.</p><?php
    } 
?>
    <hr />
    <address><?php echo 'Contact: ',$htmlTech,' or the ',$htmlRO; ?></address>
  </body>
</html>
