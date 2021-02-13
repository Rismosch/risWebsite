<?php
if( file_exists(dirname(__FILE__).'/nicejson/nicejson.php') ) {
	include_once dirname(__FILE__).'/nicejson/nicejson.php';
}

require_once dirname(__FILE__).'/../Mobile_Detect.php';
$detect = new Mobile_Detect;

$json = array(
	'version' => $detect->getScriptVersion(),

	'headerMatch' => $detect->getMobileHeaders(),

	'uaHttpHeaders' => $detect->getUaHttpHeaders(),

	'uaMatch' => array(
		'phones'   => $detect->getPhoneDevices(),
		'tablets'  => $detect->getTabletDevices(),
		'browsers' => $detect->getBrowsers(),
		'os'       => $detect->getOperatingSystems(),
		'utilities' => $detect->getUtilities()
	)
);

$fileName = dirname(__FILE__).'/../Mobile_Detect.json';
if (file_put_contents(
	$fileName, 
	function_exists('json_format') ? json_format($json) : json_encode($json)
)) {
	echo 'Done. Check '.realpath($fileName).' file.';
}
else {
	echo 'Failed to write '.realpath($fileName).' to disk.';
}
