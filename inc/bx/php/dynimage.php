<?php

require_once('../autoload.php');
include_once('../../../inc/bx/init.php');
bx_init::start('conf/config.xml', '../../..');

$request = urldecode($_SERVER['REQUEST_URI']);

$pipeline = bx_dynimage_request::getPipelineByRequest($request);
$parameters = bx_dynimage_request::getParametersByRequest($request);

$config = new bx_dynimage_config($pipeline);
$dynimage = new bx_dynimage_dynimage();
$dynimage->driver = $config->getDriver();
$dynimage->validator = $config->getValidator();
$dynimage->filters = $config->getFilters();
$dynimage->printImage();


