<?php
require_once '/render/app/config.php';

// Connect to db
$db = new \PDO('mysql:host='.$brain['db']['host'].';dbname='.$brain['db']['database'], $brain['db']['username'], $brain['db']['password']);


// Setup some prepared statements
$worker['statements']['my_record'] = $db->prepare('SELECT * FROM workers WHERE id = ?');
$worker['statements']['my_status'] = $db->prepare('SELECT status FROM workers WHERE id = ?');
$worker['statements']['jobs'] = $db->prepare
(
	'SELECT j.*, p.tracking_id, p.path
	FROM jobs AS j
	LEFT JOIN packages AS p ON j.package_id = p.id
	LEFT JOIN workers AS w ON j.worker_id = w.id
	WHERE j.status = ?'
);
$worker['statements']['set_worker_status'] = $db->prepare('UPDATE workers SET status = ? WHERE id = ?');
$worker['statements']['set_package_status'] = $db->prepare('UPDATE packages SET status = ? WHERE id = ?');
$worker['statements']['set_job_status'] = $db->prepare('UPDATE jobs SET status = ? WHERE id = ?');
$worker['statements']['set_job_worker'] = $db->prepare('UPDATE jobs SET worker_id = ? WHERE id = ?');
$worker['statements']['set_job_progress'] = $db->prepare('UPDATE jobs SET progress = ? WHERE id = ?');
$worker['statements']['outstanding_package_jobs_count'] = $db->prepare('SELECT COUNT(*) FROM jobs WHERE status != \'complete\' AND package_id = ?');
$worker['statements']['delete_instruction'] = $db->prepare('DELETE FROM workers_instructions WHERE id = ?');

// Get worker record
function getWorkerRecord()
{
	global $worker;
	$worker['statements']['my_record']->execute(array($worker['me']['id']));
	$r = $worker['statements']['my_record']->fetch();
	return $r;
}

// Get worker status
function getStatus()
{
	global $worker;
	$worker['statements']['my_status']->execute(array($worker['me']['id']));
	$r = $worker['statements']['my_status']->fetch();
	return $r['status'];
}


// Get worker jobs
function getJobs($status="queued")
{
	global $worker;
	$worker['statements']['jobs']->execute(array($status));
	$r = $worker['statements']['jobs']->fetchAll();
	return $r;
}

// Get the number of non-complete jobs in a package
function outstandingPackageJobsCount($pid)
{
	global $worker;
	$worker['statements']['outstanding_package_jobs_count']->execute(array($pid));
	$r = $worker['statements']['outstanding_package_jobs_count']->fetch();
	return $r[0];
}


// Get ffmpeg video info
function getVidInfoFF($file, $log=true)
{
	$info = array();
	$output = shell_exec("/usr/local/bin/ffmpeg -i ".escapeshellarg($file)." 2>&1");
	if($log) logStr($output);
	
	// Duration
	preg_match('/Duration: (\d{2}:\d{2}:\d{2}\.\d{2})/', $output, $dMatches);
	$info['duration_stamp'] = $dMatches[1];
	list($h, $m, $s) = explode(":", $info['duration_stamp']);
	$info['duration'] = intval(($h*3600) + ($m*60) + $s);
	
	// Resolution
	if(!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*) (?P<width>[0-9]*)x(?P<height>[0-9]*)/', $output, $rMatches))
	{
		preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*) (?P<width>[0-9]*)x(?P<height>[0-9]*)\)/', $output, $rMatches);
	}
    if(!empty($rMatches['width']) && !empty($rMatches['height']))
    {
        $info['width'] = $rMatches['width'];
        $info['height'] = $rMatches['height'];
    }
    return $info;
}


// Follow instructions from the brain
function obeyInstructions(array $instructions=NULL)
{
	global $worker;
	foreach($instructions as $i)
	{
		switch($i['cmd'])
		{
			case "reboot":		// Reboot machine
				$worker['statements']['delete_instruction']->execute(array($i['id']));
				shell_exec('reboot');
		}
	}
}

/*
UPDATE packages SET status = 'queued';
UPDATE jobs SET status = 'queued';
UPDATE workers SET status = 'ready';
*/