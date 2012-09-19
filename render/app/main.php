<?php
require_once __DIR__.'/config.php';

// Connect to db
$conn = mysql_connect($brain['db']['host'], $brain['db']['username'], $brain['db']['password']);
$db = mysql_select_db($brain['db']['database'], $conn);

// Get system settings
$q = mysql_query("SELECT * FROM settings");
while($r = mysql_fetch_assoc($q))
{
	$brain['settings'][$r['setting_key']] = $r['setting_value'];
}

// Get available workers, prioritized by "ready" status, only workers that have checked in in the last 20 seconds will be included
function getWorkersReadyPrioritized()
{
	global $brain;
	$workers = array();
	$q = mysql_query("SELECT *, IF(status = 'ready', 1, 0) AS ready FROM workers WHERE check_in > (NOW() - INTERVAL ".$brain['available_worker_threshold']." SECOND) ORDER BY ready DESC");
	while($r = mysql_fetch_assoc($q))
	{
		$workers[] = $r;
	}
	return $workers;
}

// Package has completed
function packageComplete($id)
{
	global $brain;
	//()

	// Get the manifest to see if we're moving encodes
	$q = mysql_query("SELECT path FROM packages WHERE id = '".mysql_escape_string($id)."'");
	list($path) = mysql_fetch_row($q);
	$pdir = realpath($brain['paths']['queue'].$path);
	$manifest = json_decode(file_get_contents($pdir.'/manifest.txt'), true);
	switch($manifest['target_type'])
	{
		case "share":
			$edir = realpath($brain['paths']['shares'].$manifest['target_path']);

			// Move encoded files
			$files = scandir($pdir.'/encodes');
			foreach($files as $f)
			{
				if($f == '.' || $f == '..') continue;
				$f = shell_exec("mv -f ".escapeshellarg($pdir.'/encodes/'.$f)." ".escapeshellarg($edir.'/'.$f)." 2>&1");
			}

			// Move original files
			$files = scandir($pdir.'/files');
			foreach($files as $f)
			{
				if($f == '.' || $f == '..') continue;
				$f = shell_exec("mv -f ".escapeshellarg($pdir.'/files/'.$f)." ".escapeshellarg($edir.'/'.$f)." 2>&1");
			}

			// Clean up and delete
			$f = shell_exec("rm -R -f ".escapeshellarg($pdir)." 2>&1");
			break;
	}
}

// Get friendly file size from bytes
function getSizeFromBytes($bytes, $precision=2)
{
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
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

// Get the ip address
function getMyIP()
{
	return shell_exec('hostname -I');
}


// Get a view file
function getView($view, $vars = array())
{
	global $brain;
  	$file = $view;

	// Make sure view exists
  	if(file_exists($file) == false)
  	{
   		throw new \Exception('View not found in '. $file);
    	return false;
  	}
  
  	// Get the view with content
  	extract($vars);
	ob_start();
  	include($file);
  	$buffer = ob_get_contents();
    @ob_end_clean();
   
   	return $buffer;
}  
