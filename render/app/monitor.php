<?php
require_once __DIR__.'/main.php';

// AJAX refresh
$response = array();
switch($_REQUEST['action'])
{
	case 'refresh':			// Refresh package information		
		// Get info for requested packages
		$q = mysql_query("SELECT * FROM packages WHERE id IN('".implode("','", $_REQUEST['packages'])."')");
		while($r = mysql_fetch_assoc($q))
		{
			$response['packages'][$r['id']] = $r;
		}
		
		// Get info for selected package jobs
		$q = mysql_query("SELECT j.*, w.name AS worker_name, w.ip AS worker_ip
		FROM jobs AS j
		LEFT JOIN workers AS w ON w.id = j.worker_id
		WHERE j.package_id = '".mysql_escape_string($_REQUEST['active'])."'");
		while($r = mysql_fetch_assoc($q))
		{
			$response['jobs'][$r['id']] = $r;
		}
		
		// Get info for workers
		$q = mysql_query("SELECT *, IF(check_in > (NOW() - INTERVAL ".$brain['available_worker_threshold']." SECOND), 1, 0) AS available FROM workers");
		while($r = mysql_fetch_assoc($q))
		{
			$response['workers'][$r['id']] = $r;
		}
		
		die(json_encode($response));
		
	case 'package_details':
		// Get info for requested package
		$q = mysql_query("SELECT * FROM packages WHERE id = '".mysql_escape_string($_REQUEST['id'])."'");
		$package = mysql_fetch_object($q);
		
		// Get all the jobs in the package
		$jobs = array();
		$q = mysql_query("SELECT j.*, w.name AS worker_name, w.ip AS worker_ip
		FROM jobs AS j
		LEFT JOIN workers AS w ON w.id = j.worker_id
		WHERE j.package_id = '".mysql_escape_string($_REQUEST['id'])."'
		ORDER BY j.id ASC");
		while($r = mysql_fetch_object($q))
		{
			$jobs[] = $r;
		}
		$package->jobs = $jobs;
		
		$response['html'] = getView(__DIR__.'/views/monitor/package_details.php', array('package' => $package));
		die(json_encode($response));
		
	case 'worker_details':
		// Get info for requested worker
		$q = mysql_query("SELECT *, IF(check_in > (NOW() - INTERVAL ".$brain['available_worker_threshold']." SECOND), 1, 0) AS available FROM workers WHERE id = '".mysql_escape_string($_REQUEST['id'])."'");
		$worker = mysql_fetch_object($q);
		
		$response['html'] = getView(__DIR__.'/views/monitor/worker_details.php', array('worker' => $worker));
		die(json_encode($response));
		
	case 'worker_set_endisabled':		// Disable worker from working
		$q = mysql_query("UPDATE workers SET enabled = '".mysql_escape_string($_REQUEST['enabled'])."' WHERE id = '".mysql_escape_string($_REQUEST['id'])."'");
		die(json_encode($response));
			
	case 'worker_instruct':		// Send the worker an instruction
		$q = mysql_query("INSERT INTO workers_instructions (worker_id, cmd, extra) VALUES ('".mysql_escape_string($_REQUEST['id'])."', '".mysql_escape_string($_REQUEST['cmd'])."', '')");
		die(json_encode($response));
		
	case 'set_setting':		// Disable worker from working
		$q = mysql_query("REPLACE INTO settings (setting_key, setting_value) VALUES ('".mysql_escape_string($_REQUEST['key'])."', '".mysql_escape_string($_REQUEST['value'])."')");
		die(json_encode($response));
}


include __DIR__.'/views/monitor/main.php';
