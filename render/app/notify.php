<?php 
require_once __DIR__.'/main.php';

if(empty($_REQUEST)) die();


switch($_REQUEST['action'])
{
	case "job_complete":	// Job has been completed by a worker, see if the worker is done with all jobs and assign a queued job if there are more left
		if(empty($_REQUEST['id'])) break;
		
		// Get info about completed job
		$q = mysql_query("SELECT * FROM jobs WHERE id = '".mysql_escape_string($_REQUEST['id'])."'");
		$job = mysql_fetch_assoc($q);
		
		// Update the progress of the package
		$q = mysql_query("SELECT COUNT(*) FROM jobs WHERE package_id = '".$job['package_id']."'");
		list($total) = mysql_fetch_row($q);
		$q = mysql_query("SELECT COUNT(*) FROM jobs WHERE package_id = '".$job['package_id']."' AND status = 'complete'");
		list($complete) = mysql_fetch_row($q);
		$percent = intval($complete/$total * 100);
		$status = ($complete == $total) ? 'complete' : 'working';
		$completed = ($complete == $total) ? 'NOW()' : 'completed';
		$q = mysql_query("UPDATE packages SET progress = '".$percent."', status = '".$status."', completed = ".$completed." WHERE id = '".$job['package_id']."'");
		
		// See if there is any post-complete action
		if($complete == $total)
		{
			packageComplete($job['package_id']);
		}


		// If the worker has more jobs to do, stop
		$q = mysql_query("SELECT COUNT(*) FROM jobs WHERE worker_id = '".mysql_escape_string($job['worker_id'])."' AND status != 'complete'");
		list($num) = mysql_fetch_row($q);
		if($num > 0) break;
		
		// See if there are any queued jobs remaining
		$q = mysql_query("UPDATE jobs SET worker_id = '".mysql_escape_string($job['worker_id'])."' WHERE package_id = '".mysql_escape_string($job['package_id'])."' AND status = 'queued' LIMIT 1");
		break;
}
