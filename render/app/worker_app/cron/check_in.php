<?php
require_once __DIR__.'/../main.php';
$request = array('priority' => 100/*, 'status' => 'ready'*/);

// If we have items in the "working" dir, then adjust status
/*
$items = scandir($worker['paths']['working']);
if(count($items) > 2) $request['status'] = 'working';
*/

// Hostname
$request['name'] = $worker['me']['name'];

// Ip address
$request['ip'] = $worker['me']['ip'];

// UUID
$request['uuid'] = $worker['me']['uuid'];

// System information
$request['os'] = PHP_OS;

// Build the request
$qStr = "";
foreach($request as $k => $v)
{
    $qStr .= 'w_'.$k."=".urlencode($v)."&";
}

/*
$cmd = "/usr/bin/curl ".escapeshellarg('http://brain/render/check_in.php?'.$qStr)." -k";
$f = `$cmd`;
*/

$response = json_decode(file_get_contents($worker['paths']['brain_url'].'render/check_in.php?'.$qStr), true);
if($response['instructions']) obeyInstructions($response['instructions']);