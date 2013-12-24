<?php

function M($model=''){
	$model = empty($model) ? substr(CONTROLLER, 0, -10) : $model;
	return Model::load($model);
}

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}