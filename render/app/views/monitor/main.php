<?php
$packages = array();
$q = mysql_query("SELECT *, (SELECT COUNT(*) FROM jobs WHERE package_id = packages.id) AS jobs FROM packages ORDER BY stamp DESC, id DESC");
while($r = mysql_fetch_object($q))
{
	$packages[] = $r;
}

$workers = array();
$q = mysql_query("SELECT *, IF(check_in > (NOW() - INTERVAL ".$brain['available_worker_threshold']." SECOND), 1, 0) AS available FROM workers ORDER BY available DESC, name ASC");
while($r = mysql_fetch_object($q))
{
	$workers[] = $r;
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>Render Farm Monitor</title>

	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="style/jquery-ui.css">
	<link rel="stylesheet" href="style/monitor.css">
	<script src="js/jquery.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="js/monitor.js" type="text/javascript"></script>
	<style>
		
	</style>
</head>
<body>
	
	<div class="brainInfo">Brain: <?php echo getMyIP(); ?></div>
	
	<div id="tabs">
		<ul>
			<li><a href="#packages">Encodes</a></li>
			<li><a href="#workers">Workers</a></li>
			<li><a href="#settings">Settings</a></li>
		</ul>
		<div id="packages">
			<?php include __DIR__.'/packages.php'; ?>
		</div>
		<div id="workers">
			<?php include __DIR__.'/workers.php'; ?>
		</div>
		<div id="settings">
			<label>
				<input type="checkbox" name="autoEnableWorkers" onclick="setSettingVal('auto_enable_workers', (this.checked) ? 1 : 0);" value="1" <?php if($brain['settings']['auto_enable_workers']): ?>checked="checked"<?php endif; ?> /> Auto-enable newly connected workers
			</label>			
		</div>
	</div>

</body>
</html>