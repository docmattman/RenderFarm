<?php
require_once __DIR__.'/main.php';

packageComplete(16);
die('done');

$job = array();
$job['id'] = 'render1';
$job['profile'] = array('h264');
$job['target'] = '/render/app/tmp/done_files';
//$job['post_script'] = '/render/app/job_done.sh';

die(json_encode($job));
