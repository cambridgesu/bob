<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: vote.php 130 2007-02-20 16:42:40Z dme26 $
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2007
-->

<?php
  $bob = false;
  require_once("./BOB.php");
?>

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $bob->title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
      body {font-family: sans-serif;}
	  table.vote {border: 1px; border-collapse: collapse; border-spacing: 0px;}
      table.vote td, table.vote th {border: 2px #ddd solid; padding: 3px;}
	  .votemsg {border:1px solid #bbbbbb; background: #eeeeee; padding: 4px;}
	  div.problem {color: red; border: 2px solid red; padding: 2px 10px;}
	  option {color: #603;}
	  h2 {font-size: 1.3em; margin-top: 2.2em; margin-bottom: 0.6em;}
	  .comment {color: #444;}
    </style>
	
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
  </head>
  <body>
    <h1><?php echo $bob->title; ?></h1>
<?php $bob->voteWF(); /* Voting workflow */ ?>
    <hr />
    <?php echo "<address>Contacts: {$bob->htmlTech} or {$bob->htmlRO}</address>\n"; ?>
  </body>
</html>
