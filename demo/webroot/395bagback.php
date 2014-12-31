<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
set_time_limit(0);

$handle = fopen ("./3.txt", "rb");
$j = 0;
while (!feof($handle)) {
  $c = fgets($handle);
  $c = trim($c);
   preg_match("/礼包id:(.*)用户id：(.*)，/", $c, $matches);
   var_dump($matches);exit;
  //var_dump($bag);exit;
  $j++;
}
if(empty($bag)) exit("empty\r\n");

$i = 0;
foreach($bag as $v){
	$bid = $v[0];
	$uid = $v[1];
	if(empty($bid) || empty($uid)){
		oo::logs()->debug( date( 'Y-m-d H:i:s' ) .' empty '.$bid. ' ' . $uid, 'act395/sendbackerr');
		continue;
  }
  $res = oo::bag(395, 67)->act($bid, $uid);
  if(!empty($res['callback'])){
	$i++;
	oo::logs()->debug( date( 'Y-m-d H:i:s' ) .' ok '.$bid. ' ' . $uid, 'act395/sendbackok');
  }else{
	oo::logs()->debug( date( 'Y-m-d H:i:s' ) .' fail '.$bid. ' ' . $uid, 'act395/sendbackerr');
  }
}

fclose($handle);

echo "应发{$j},实发{$i}";