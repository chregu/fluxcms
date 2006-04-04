<?php

require_once('../autoload.php');
include_once('../../../inc/bx/init.php');
bx_init::start('conf/config.xml', '../../..');

//$pipeline = bx_dynimage_request::getPipelineByRequest($request);
//$config = new bx_dynimage_config($request, $pipeline);

$request = urldecode($_SERVER['REQUEST_URI']);
$dynimage = new bx_dynimage_dynimage($request);
$dynimage->printImage();


