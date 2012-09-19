<?php

global $brain;
$brain = array();
$brain['paths']['queue'] = '/render/queue/';
$brain['paths']['shares'] = '/render/shares/';
$brain['paths']['working'] = '/render/working/';
$brain['paths']['completed'] = '/render/completed/';
$brain['paths']['incoming'] = '/render/incoming/';
$brain['paths']['tmp'] = __DIR__.'/tmp/';

$brain['available_worker_threshold'] = 20;		// A worker is only considered "available" if it has checked in within this number of seconds from NOW()
$brain['job_progress_time_increment'] = 2;		// Number of seconds between updating job progress


// MySQL database info
$brain['db']['host'] = '10.0.0.60';
$brain['db']['username'] = 'root';
$brain['db']['password'] = 'brain';
$brain['db']['database'] = 'render';



// Preset commands
$brain['presets']['h264'] = array
(
	'ext' => 'mp4', 
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);
$brain['presets']['ipod'] = array
(
	'ext' => 'm4v',
	'resCheck' => true,
	'width' => 320,
	'height' => 240,
	'args' => "-b 384k -vcodec libx264 -r 23.976 -acodec libfaac -ac 2 -ar 44100 -ab 64k -crf 22 -deinterlace"
);
$brain['presets']['h264_dvd'] = array
(
	'ext' => 'mp4',
	'resCheck' => true,
	'width' => 720,
	'height' => 480,
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);
$brain['presets']['h264_480p'] = array
(
	'ext' => 'mp4',
	'resCheck' => true,
	'width' => 640,
	'height' => 480,
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);
$brain['presets']['h264_720p'] = array
(
	'ext' => 'mp4',
	'resCheck' => true,
	'width' => 1280,
	'height' => 720,
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);
$brain['presets']['h264_1080p'] = array
(
	'ext' => 'mp4',
	'resCheck' => true,
	'width' => 1920,
	'height' => 1080,
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);
$brain['presets']['h264_2k'] = array
(
	'ext' => 'mp4',
	'resCheck' => true,
	'width' => 2048,
	'height' => 1536,
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);
$brain['presets']['h264_4k'] = array
(
	'ext' => 'mp4',
	'resCheck' => true,
	'width' => 4096,
	'height' => 3072,
	'args' => "-vcodec libx264 -crf 22 -acodec libfaac -ab 128k"
);