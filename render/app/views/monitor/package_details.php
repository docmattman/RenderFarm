<?php
$total = strtotime($package->completed) - strtotime($package->stamp);
?>
<div class="packageStatus progress" data-progress="<?php echo $package->progress; ?>">
	<?php if($package->status == 'working'): ?>
		<div class="packageProgressBar"></div>
	<?php else: ?>
		<?php echo ucfirst($package->status); ?>
	<?php endif; ?>
</div>

<h1>
	Package: <?php echo $package->id; ?>
</h1>

<ul class="timeElements" data-progress="<?php echo $package->progress; ?>">
	<li class="detail">
		Submitted: <span class="stamp val"><?php echo $package->stamp; ?></span>
	</li>
	<li class="completed detail">
		Completed: <span class="completed val"><?php echo $package->completed; ?></span>
	</li>	
	<li class="completed detail">
		Total Time: <span class="duration val"><?php echo gmdate('H:i:s', $total); ?></span>
	</li>
</ul>

<ul class="trackingDetails">
	<li class="path detail">
		Queue Directory: <span class="val"><?php echo $package->path; ?></span>
	</li>
	<?php if($package->tracking_id): ?>
		<li class="tracking_id detail">
			Tracking ID: <span class="val"><?php echo $package->tracking_id; ?></span>
		</li>
	<?php endif; ?>
	<?php if($package->tracking_description): ?>
		<li class="tracking_description detail">
			Tracking Description: <span class="val"><?php echo $package->tracking_description; ?></span>
		</li>
	<?php endif; ?>
</ul>

<div class="detail">Jobs: <span class="jobs val"><?php echo count($package->jobs); ?></span></div>


<table class="jobsTable data">
	<thead>
		<tr>
			<th>ID</th>
			<th>File</th>
			<th>Size</th>
			<th>Length</th>
			<th>Preset</th>
			<th>Worker</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($package->jobs as $j): ?>
		<tr class="jobItem" data-id="<?php echo $j->id; ?>" data-status="<?php echo $j->status; ?>">
			<td><?php echo $j->id; ?></td>
			<td><?php echo $j->file; ?></td>
			<td><?php echo getSizeFromBytes($j->file_size); ?></td>
			<td><?php echo gmdate('H:i:s', $j->file_duration); ?></td>
			<td><?php echo $j->preset; ?></td>
			<td>
				<a class="worker" href="javascript:;" title="View worker..." data-id="<?php echo $j->worker_id; ?>"><?php echo $j->worker_name; ?></a>
			</td>
			<td class="progress" data-progress="<?php echo $j->progress; ?>">
				<div class="status"><?php echo ucfirst($j->status); ?></div>
				<div class="jobProgressBar"></div>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>