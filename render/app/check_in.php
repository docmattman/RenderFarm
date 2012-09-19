<?php 
require_once __DIR__.'/main.php';

if(empty($_REQUEST['w_uuid'])) die();
$_REQUEST['w_id'] = $_REQUEST['w_name'].'_'.$_REQUEST['w_uuid'];

// Get all the w_ values from $_REQUEST
$fields = array();
$values = array();
$updates = array();
foreach($_REQUEST as $k => $v)
{
    if(substr($k, 0, 2) != "w_") continue;
	$f = substr($k, 2);
	$v = mysql_escape_string(urldecode($v));
    $fields[] = $f;
    $values[] = $v;
	if($f == 'id') continue;
	$updates[] = $f." = '".$v."'";
}
$updates[] = "check_in = NOW()";

// Auto enable new workers
if(!in_array('enabled', $fields)) 
{
	$fields[] = 'enabled';
	$values[] = $brain['settings']['auto_enable_workers'];
}


// Get the status of the worker
$status = 'ready';
$q = mysql_query("SELECT COUNT(*) FROM jobs WHERE worker_id = '".mysql_escape_string($_REQUEST['w_id'])."' AND status = 'working'");
list($num) = mysql_fetch_row($q);
if($num > 0) $status = 'working';
$fields[] = 'status';
$values[] = $status;

// Save to db
$q = mysql_query("INSERT INTO workers (".implode(',', $fields).") VALUES ('".implode("','", $values)."')
ON DUPLICATE KEY UPDATE ".implode(",", $updates)) or die("Error: ".mysql_error());


// See if there are any instructions to send back
$response = array();
$q = mysql_query("SELECT * FROM workers_instructions WHERE worker_id = '".mysql_escape_string($_REQUEST['w_id'])."'");
while($r = mysql_fetch_assoc($q))
{
	$response['instructions'][] = $r;	
}
die(json_encode($response));