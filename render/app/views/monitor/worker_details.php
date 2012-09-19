<div class="workerStatus">
	<?php if($worker->available): ?>
		<img class="availabilityIndicator" src="style/status_available.png" /> <span class="status"><?php echo ucfirst($worker->status); ?></span>
	<?php else: ?>
		<img class="availabilityIndicator" src="style/status_unavailable.png" /> Offline
	<?php endif; ?>
</div>

<h1>
	<?php echo $worker->name; ?>
</h1>

<ul class="details">
	<li class="detail">
		ID: <span class="id val"><?php echo $worker->id; ?></span>
	</li>
	<li class="detail">
		IP Address: <span class="ip val"><?php echo $worker->ip; ?></span>
	</li>
	<li class="detail">
		Operating System: <span class="stamp val"><?php echo $worker->os; ?></span>
	</li>
	<li class="detail">
		Last Check-In: <span class="check_in val"><?php echo $worker->check_in; ?></span>
	</li>
</ul>

<ul class="actionsMenu">
	<?php if($worker->enabled): ?>
		<li>
			<a href="javascript:;" onclick="workerEnDisable(0);" title="Disable worker (will not be used for encoding)">Disable Worker</a>
		</li>
	<?php else: ?>
		<li>
			<a href="javascript:;" onclick="workerEnDisable(1);" title="Enable worker (will be used for encoding)">Enable Worker</a>
		</li>
	<?php endif; ?>
	
	<?php if($worker->available): ?>
		<li>
			<a href="javascript:;" onclick="workerReboot();" title="Restart worker machine">Reboot Worker</a>
		</li>
	<?php else: ?>
		
	<?php endif; ?>
</ul>