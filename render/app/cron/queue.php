<?php
require_once __DIR__.'/../main.php';
//if($_REQUEST['debug'] != 1) die();

$qFiles = scandir($brain['paths']['queue']);
foreach($qFiles as $f)
{
	if($f == "." || $f == "..") continue;
	if(is_dir($brain['paths']['queue'].$f))	// Folder
	{
		$path = $brain['paths']['queue'].$f;
		$manifest = $path."/manifest.txt";
		if(!file_exists($manifest))			// Remove folder and log error
		{
			$cmd = "rm ".escapeshellarg($path)." -R";
			$r = `$cmd`;
			continue;
		}
		
		// Get the manifest file
		$m = trim(file_get_contents($path."/manifest.txt"));
		$job = json_decode($m, true);
		
		// Set some defaults 
		if(empty($job['profile'])) $job['profile'] = array('h264');

		// Check to see if package exists already
		$q = mysql_query("SELECT id FROM packages WHERE path = '".mysql_escape_string($f)."'");
		list($pid) = mysql_fetch_row($q);
		if(empty($pid))
		{
			// Create the encodes folder for completed files
			$c = "mkdir -m 0777 ".escapeshellarg($path.'/encodes');
			`$c`;
			
			// Submit the package
			$i = mysql_query("INSERT INTO packages (tracking_id, tracking_description, path, status) VALUES('".mysql_escape_string($job['tracking_id'])."', '".mysql_escape_string($job['tracking_description'])."', '".mysql_escape_string($f)."', 'queued')");
			$packageId = mysql_insert_id();
			
			// Assign all jobs to workers
			$items = scandir($path."/files");
			unset($items[0], $items[1]);
			$items = array_values($items);
			foreach($items as $i)
			{
				$fileSize = filesize($path.'/files/'.$i);
				$ffInfo = getVidInfoFF($path.'/files/'.$i, false);
				$fileDuration = $ffInfo['duration'];
				foreach($job['profile'] as $preset)
				{
					// Get the next worker to assign the job
					$q = mysql_query("INSERT INTO jobs (package_id, file, file_size, file_duration, preset, status) VALUES('".$packageId."', '".mysql_escape_string($i)."', '".mysql_escape_string($fileSize)."', '".mysql_escape_string($fileDuration)."', '".mysql_escape_string($preset)."', 'queued')");		
					
					// Increment current workers
					$cWorker++;
				}
				
			}
		}
//die("<pre>".print_r($items, true).print_r($job, true));		
	}
	else 	// File
	{
		$info = pathinfo($brain['paths']['queue']."/".$f);
		//$info['filename']
		//$info['extension']
// TODO: Make sure output file doesn't exist		
		// Move to working dir and start encode
		rename($brain['paths']['queue']."/".$f, $brain['paths']['working']."/".$f);

		/* Add redirection so we can get stderr. */
		$timeStart = microtime(true);
		$lastUpdate = 0;
$id = intval($timeStart);		
mysql_query("INSERT INTO jobs (id, progress) VALUES('".intval($id)."', '0')");
$count = 0;
		$lastDiff = 0;
		$cmd = "/usr/local/bin/ffmpeg -i ".escapeshellarg($brain['paths']['working']."/".$f)." -y -vcodec libx264 -crf 22 -acodec libfaac -ab 128k ".escapeshellarg($brain['paths']['completed']."/".$info['filename'].".mp4")." 2>&1";
		$handle = popen($cmd, 'r');
		while($line = fread($handle, 100))
		{
			// Write to log		
			file_put_contents($brain['paths']['tmp']."progress.txt", $line."\n", FILE_APPEND | LOCK_EX);
			
			$timeCurrent = microtime(true);
			$updateDiff = intval($timeCurrent - $lastUpdate);
			if($updateDiff > $brain['job_progress_time_increment'])		// Only update when larger than time difference
			{
mysql_query("UPDATE jobs SET progress = '".$count."' WHERE id = '".$id."'");
$count++;				
				$lastUpdate = $timeCurrent;
			}
		}
		
		// Update progress with last
		file_put_contents($brain['paths']['tmp']."progress.txt", "done", FILE_APPEND | LOCK_EX);
		pclose($handle);
	}
}