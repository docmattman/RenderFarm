<?php
require_once __DIR__.'/../main.php';

// If worker isn't "ready", then stop
$wr = getWorkerRecord();
if($wr['enabled'] == false) die();
if($wr['status'] != "ready") die();
if(!is_dir($worker['paths']['brain_queue'])) die();		// Probably not mounted yet (after reboot)
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set("log_errors", 1);
ini_set("error_log", $worker['paths']['tmp'].'php-error.log');

// Get any jobs for worker
$jobs = getJobs();
if(empty($jobs)) die();
$j = $jobs[0];				// Only work on one job at a time

// Set job and worker working
$worker['statements']['set_worker_status']->execute(array('working', $worker['me']['id']));
$worker['statements']['set_package_status']->execute(array('working', $j['package_id']));
$worker['statements']['set_job_status']->execute(array('working', $j['id']));
$worker['statements']['set_job_worker']->execute(array($worker['me']['id'], $j['id']));

// Get the file we're working with
//$manifest = json_decode(file_get_contents($worker['paths']['brain_queue'].$j['path'].'/manifest.txt'));
$file = $worker['paths']['brain_queue'].$j['path'].'/files/'.$j['file'];
$fInfo = pathinfo($file);
$tmpFile = $worker['paths']['tmp'].$j['package_id'].'_'.$j['id'].'_'.$fInfo['filename'].'_'.$j['preset'].'.'.$brain['presets'][$j['preset']]['ext'];
$pass2Data = $worker['paths']['tmp'].$j['package_id'].'_'.$j['id'].'_'.$fInfo['filename'].'_'.$j['preset'].'_pass.log';
global $logFile;
$logFile = $worker['paths']['tmp'].$j['package_id'].'_'.$j['id'].'_'.$fInfo['filename'].'_'.$j['preset'].'.'.$brain['presets'][$j['preset']]['ext'].".txt";

// Get info for the file
$ffInfo = getVidInfoFF($file);
file_put_contents($logFile, "JOB: ".print_r($j, true)."\nVIDEO: ".print_r($ffInfo, true), LOCK_EX);
$ffExtra = '';

// See if there are resolution requirements for encode
$doEncode = true;
if($brain['presets'][$j['preset']]['resCheck'])
{
	if($ffInfo['width'] < $brain['presets'][$j['preset']]['width'] && $ffInfo['height'] < $brain['presets'][$j['preset']]['height']) 
	{
		logStr("---------------------------------------------\n\tSkipping Job: Target resolution is above source resolution\n---------------------------------------------");
		$doEncode = false;
	}
	else
	{
		// Get the target dimensions
		$w = intval($brain['presets'][$j['preset']]['width']);
		$h = intval(($ffInfo['height'] / $ffInfo['width']) * $w);
		$e = "-vf 'scale=".$w.":trunc(ow/a/vsub)*vsub'";
		
		// See if we are out of bounds and adjust by the height instead
		if($h > $brain['presets'][$j['preset']]['height'])
		{
			$h = intval($brain['presets'][$j['preset']]['height']);
			$w = intval(($ffInfo['width'] / $ffInfo['height']) * $h);
			$e = "-vf 'scale=trunc(oh/a/vsub)*vsub:".$h."'";
		}
		
		$ffExtra .= " ".$e;
		logStr('TARGET RESOLUTION: '.$w.'x'.$h);
	}
}

// Make sure we are good to go for the encode
if($doEncode)
{
	// First pass
	//$pass2Data
	$cmd = "/usr/local/bin/ffmpeg -i ".escapeshellarg($file)." -y -pass 1 -passlogfile ".escapeshellarg($pass2Data)." ".$brain['presets'][$j['preset']]['args']." ".$ffExtra." -threads 0 /dev/null";
	$f = shell_exec($cmd);

	// Start the encode
	$cmd = "/usr/local/bin/ffmpeg -i ".escapeshellarg($file)." -y -pass 2 -passlogfile ".escapeshellarg($pass2Data)."".$brain['presets'][$j['preset']]['args']." ".$ffExtra." -threads 0 ".escapeshellarg($tmpFile)." 2>&1";
	logStr("FFMPEG CMD:\n\n".$cmd);
	$handle = popen($cmd, 'r');
	$lastPercent = 0;
	while($line = fread($handle, 100))
	{
		// Only deal with frame line updates
		logStr($line);
		$line = trim($line);
		if(substr($line, 0, 6) != "frame=") continue;
		
		// Get the progress
		if($ffInfo['duration'] > 0) 
		{
			$sPos = strpos($line, "time=") + 5;
			$d = substr($line, $sPos, 8);
			list($h, $m, $s) = explode(":", $d);
			$cDuration = intval(($h*3600) + ($m*60) + $s);
			$percent = intval($cDuration/$ffInfo['duration']*100);
			if($percent != $lastPercent)
			{
				$worker['statements']['set_job_progress']->execute(array($percent, $j['id']));
				$lastPercent = $percent;
			}
		}
	}
	pclose($handle);
	
	// Move completed file to brain
	$completeDir = $worker['paths']['brain_queue'].$j['path'].'/encodes';
	$finalFile = $completeDir.'/'.$fInfo['filename'].'-'.$j['preset'].'.'.$brain['presets'][$j['preset']]['ext'];
	if(!is_dir($completeDir)) $f = shell_exec("mkdir ".escapeshellarg($completeDir));
	//if(file_exists($finalFile)) $f = shell_exec("rm ".escapeshellarg($finalFile));
	$f = shell_exec("mv -f ".escapeshellarg($tmpFile)." ".escapeshellarg($finalFile));
}

// See if this was the last job in the package and mark the whole package complete
if(outstandingPackageJobsCount($j['package_id']) < 1) 
{
	$worker['statements']['set_package_status']->execute(array('complete', $j['package_id']));
}
	
// Set as done and reset status of items
$worker['statements']['set_job_progress']->execute(array(100, $j['id']));
$worker['statements']['set_worker_status']->execute(array('ready', $worker['me']['id']));
$worker['statements']['set_job_status']->execute(array('complete', $j['id']));


// Notify brain that job is done
$f = file_get_contents($worker['paths']['brain_url'].'render/notify.php?action=job_complete&id='.$j['id']);









// Log string
function logStr($str)
{
	global $logFile;
	file_put_contents($logFile, $str."\n", FILE_APPEND | LOCK_EX);	
}
