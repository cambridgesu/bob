<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
- $Id: rollcheck.php 77 2006-10-31 00:54:13Z dme26 $
-
- THIS FILE IS UNSTABLE AND/OR GIVES INCORRECT RESULTS.
- IT IS NOT INTENDED AS A CORE PART OF BOB. AS THE PROJECT
- PAGE SAYS, YOU SHOULD OVERSEE THE VOTE COUNTING DIRECTLY.
-
- You might find its initial invalid vote removal steps
- handy though...
-
- This file is part of the Basic Online Ballot-box (BOB).
- http://www.cl.cam.ac.uk/~dme26/proj/BOB/
- Released under GPL. See COPYING for details.
- Copyright David Eyers 2005-2007
-->

<?php
  // initialise all variables (i.e. thwart register_globals attacks)
  $bob = false;
  require("./BOB.php");
  $title .= ' - results';
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
<?php if($bob->adminOK()){ ?>
    <h2>Uncorrected votes</h2>
      <pre>
<?php 

    function print_vote($tok){
      global $e;
      printf("%20s ",$tok);
      $f2 = true;
      foreach($e[$tok] as $election => $prefs){
	$first = true;
	if($f2) { $f2=false; } else { echo " ;"; }
	foreach($prefs as $p){
	  if($first) { $first=false; } else { echo ','; }
	  printf("%2d",$p);	  
	}
      }
      echo "\n";
    }

$e = $bob->getVoteData(); 
foreach(array_keys($e) as $tok){
  print_vote($tok);
}
?>
      </pre>
    <h2>Sanitise invalid votes</h2>
    <pre>
<?php 
echo "Turn any later preference for a candidate already preferenced into a no-vote...\n";

foreach($e as $tok => $votes){
  $modified = false;
  foreach($votes as $election => $prefs){
    for($j=0; $j<count($prefs)-1; $j++){
      for($k=$j+1; $k<count($prefs); $k++){
	if($e[$tok][$election][$k] == $e[$tok][$election][$j] && $e[$tok][$election][$k]!=0){
	  $e[$tok][$election][$k] = 0;
	  $modified = true;
	}
      }
    }    
  }
  if($modified){
    print_vote($tok);
  }
}

echo "Turn all preferences later than the first no-vote into a no-vote...\n";

foreach($e as $tok => $votes){
  $modified = false;
  foreach($votes as $election => $prefs){
    $zero = false;
    for($j=0; $j<count($prefs); $j++){
      if($e[$tok][$election][$j] == 0){ 
	$zero = true;
      }
      if($e[$tok][$election][$j] != 0 && $zero){
	$e[$tok][$election][$j] = 0;
	$modified = true;
      }
    }    
  }
  if($modified){
    print_vote($tok);
  }
}

echo "Done invalid vote correcting.\n";
?>
    </pre>
    <h2>Corrected vote list</h2>
    <pre>
<?php foreach(array_keys($e) as $tok){ print_vote($tok); } ?>
    </pre>

    <h2>Some very approximate election calculations</h2>

<?php 

	$bob->voteDataKey();

function doElection($e,$numpos){
  $tmp = reset($e);
  $numprefs = count($tmp[0]);

  $donecs = array();
  $notdone = true;
  $progress = true;

  while($notdone && $progress){
    $progress = false;
    $surplusrepeat = true;
    while($surplusrepeat){
      $surplusrepeat = false;
      $counts = array();
      $total = 0;
      $expired = false;
      foreach($e as $k=>$v){
	while(count($v[0][0])>0 && $donecs[$v[0][0]]){
	  array_shift($v[0]);
	}
	if(count($v[0][0])==0 || $v[0][0]==0){ // this vote has expired
	  if(!$expired){
	    $expired = true;
	    print "Expired votes list:\n";
	  }
	  print "  $k\n";
	  unset($e[$k]);
	}else{
	  $total++;
	  $counts[$v[0][0]]+=$v[1];
	  $progress = true;
	}
      }
      if($expired){ print "Expired votes list end.\n"; }
    
      $quota = ceil($total/($numpos+1));
      print "Total valid votes = $total, number of places = $numpos, therefore quota = $quota\n";
    
      asort($counts);
      $surpluses = array();
      foreach($counts as $c=>$v){
	print "Candidate $c has $v votes.\n";
	if($v>=$quota){
	  $newweight = ($v-$quota)/$v;
	  print "  This is greater than or equal to the quota, so candidate $c is elected.\n";
       	  $donecs[$c] = true;
	  if(count($donecs)>=$numpos){
	    $notdone=false;
	    break;
	  }
	  print "  Will redistribute preferences at weight $newweight.\n";
	  $surpluses[$c] = $newweight;
	  $surplusrepeat=true;
	}
      }
      // distribute surpluses in one pass
      if($surplusrepeat && $notdone){
	foreach($e as $k=>$v){
	  $w = $surpluses[$v[0][0]];
	  if($w){ 
	    $e[$k][1] = $w;
	    array_shift($e[$k][0]);
	  }
	}
      } 
    } //end while($surplusrepeat)
    
    if($notdone){
      // eliminate instead
      reset($counts);
      $elim = key($counts);
      if($elim){
	print "The candidate: $elim had the fewest votes and was thus eliminated.\n";
	$donecs[$elim] = true;
	foreach($e as $k=>$v){
	  if($v[0][0]==$elim){
	    array_shift($e[$k][0]);
	  }
	}
      }
    }
  } //end while($notdone)
}

$numElections = count(reset($e));
print "<p>Number of elections: $numElections</p>\n";
for($pickElection=0; $pickElection<$numElections; $pickElection++){
  print "<h3>".(1+$pickElection).": Election of {$bob->positionInfo[$pickElection]} candidate".($bob->positionInfo[$pickElection]>1?'s':'')." to the position: {$bob->electionInfo[$pickElection][0]} </h3>\n";
//gather election data
  $edata = array();
  foreach($e as $tok => $votes){
    $edata[$tok] = array($votes[$pickElection],1.0);
  }
  echo "<pre>",doElection($edata,$bob->positionInfo[$pickElection]),"</pre>";
}
?>

<?php }else{ ?>
<p>The election is still in progress, so this service is not available.</p>
<?php } ?>
    <hr />
    <address><?php echo 'Contact: ',$htmlTech,' or the ',$htmlRO; ?></address>
  </body>
</html>
