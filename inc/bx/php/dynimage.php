<?php

require_once('../autoload.php');
include_once('../../../inc/bx/init.php');
bx_init::start('conf/config.xml', '../../..');

$request = urldecode($_SERVER['REQUEST_URI']);

//bx_dynimage_request::$basePath = '/dynimage';
$pipeline = bx_dynimage_request::getPipelineByRequest($request);
//$parameters = bx_dynimage_request::getParametersByRequest($request);

$config = new bx_dynimage_config($request, $pipeline);
$dynimage = new bx_dynimage_dynimage($config);

$dynimage->printImage();


