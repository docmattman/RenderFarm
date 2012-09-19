<?php 
require_once __DIR__.'/main.php';

if(empty($_REQUEST)) die();


switch($_REQUEST['action'])
{
	case "share":	// From share location
		// Make sure folder exists
		$dir = realpath($brain['paths']['shares'].'/'.urldecode($_REQUEST['name']));
		$queueDir = $brain['paths']['queue'].'/'.microtime(true);
		
		// Make sure there is a manifest
		$manifest = $dir."/manifest.txt";
		$mf = file_get_contents($manifest);
		$f = shell_exec("mv ".escapeshellarg($manifest)." ".escapeshellarg("_".$manifest));
		if(!file_exists($manifest))			// Remove folder and log error
		{
			//shell_exec("rm ".escapeshellarg($dir)." -R");
			die(json_encode(array('error' => 'Package manifest not found')));
		}
		
		// Move this folder to the queue
		//$f = shell_exec("chmod 0777 -R ".escapeshellarg($dir)." 2>&1");
		$f = shell_exec("mkdir ".escapeshellarg($queueDir));		
		$f = shell_exec("cp -r ".escapeshellarg($dir)." ".escapeshellarg($queueDir)." 2>&1");
		//$f = shell_exec("mv ".escapeshellarg($queueDir."/_manifest.txt")." ".escapeshellarg($queueDir."/manifest.txt"));
		//$f .= shell_exec("rm -R ".escapeshellarg($dir)." 2>&1");
		die(json_encode(array('success' => '1', 'msg' => $f)));
	case "folder":	// Submit folder of files
		if(empty($_REQUEST['name'])) break;
	
		// Make sure folder exists
		if(empty($dir)) $dir = realpath($brain['paths']['incoming'].'/'.$_REQUEST['name']);
		if(!is_dir($dir)) die(json_encode(array('error' => 'Folder not found')));
		$queueDir = $brain['paths']['queue'].'/'.microtime(true);
		
		// Make sure there is a manifest
		$manifest = $dir."/manifest.txt";
		if(!file_exists($manifest))			// Remove folder and log error
		{
			shell_exec("rm ".escapeshellarg($dir)." -R");
			die(json_encode(array('error' => 'Package manifest not found')));
		}
		
		// Move this folder to the queue
		//$f = shell_exec("chmod 0777 -R ".escapeshellarg($dir)." 2>&1");
		$f = shell_exec("mv ".escapeshellarg($dir)." ".escapeshellarg($queueDir)." 2>&1");
		//$f .= shell_exec("rm -R ".escapeshellarg($dir)." 2>&1");
		die(json_encode(array('success' => '1', 'msg' => $f)));
}
