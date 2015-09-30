<?php

$arr = array(28,43,54,62,21,66,32,78,36,76,39);
$res = bubble_sort($arr);
var_dump($res);

$res = quick_sort($arr);
var_dump($res);

$hs = half_search($arr, 78);
var_dump($hs);
//Ã°ÅİÅÅĞò
function bubble_sort($arr){
	if(empty($arr) || !is_array($arr))	return $arr;
	$count=count($arr);
	if($count<2) return $arr;

	for($i=$count;$i>0;$i--){
		for($j=0;$j<$i;$j++){
			if($arr[$j]<$arr[$j-1]){
				$temp = $arr[$j-1];
				$arr[$j-1] = $arr[$j];
				$arr[$j] = $temp;
			}
		}
	}
	return $arr;
}

//¿ìËÙÅÅĞò
function quick_sort($arr){
	if(!is_array($arr)) return $arr;
	$len = count($arr);
	if($len<2) return $arr;
	$i=rand(1, $len-1);
	$left = $right = array();
	for($j=0;$j<$len;$j++){
		if($j==$i) continue;
		if($arr[$j]<$arr[$i]){
			$left[] = $arr[$j];
		}else{
			$right[] = $arr[$j];
		}
	}
	$left = quick_sort($left);
	$right = quick_sort($right);
	$res = array_merge($left, array($arr[$i]), $right);
	return $res;
}

//¶ş·Ö²éÕÒ
function half_search($arr, $val){
	if(!is_array($arr)) return null;
	$len = count($arr);
	if($len<2) return $arr;
	
	$i = 0;
	$m = intval(($i+$len)/2);
	while($i<$len){
		
		if($val<$arr[$m]){//ÔÚ×ó±ß
			$m -= 1;
		}elseif($val>$arr[$m]){
			$m += 1;
		}else{
			return $m;
		}
	}
	
	
	
}