<div class="workersTableContainer">
	<table class="workersTable data">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Name</th>
				<th>IP</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody class="enabled">
			<tr>
				<td class="groupHeader" colspan="4">Enabled Workers</td>
			</tr>
			<?php foreach($workers as $w): if(!$w->enabled) continue; ?>
			<tr class="workerItem dataItem" data-id="<?php echo $w->id; ?>" data-status="<?php echo $w->status; ?>" data-available="<?php echo $w->available; ?>">
				<td class="available">
					<?php if($w->available): ?>
						<img class="availabilityIndicator" src="style/status_available.png" />
					<?php else: ?>
						<img class="availabilityIndicator" src="style/status_unavailable.png" />
					<?php endif; ?>
				</td>
				<td><?php echo $w->name; ?></td>
				<td class="ip"><?php echo $w->ip; ?></td>
				<td>
					<span class="status"><?php echo ucfirst($w->status); ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tbody>
			<tr>
				<td class="groupSeparator" colspan="4">&nbsp;</td>
			</tr>
		</tbody>
		<tbody class="disabled">
			<tr>
				<td class="groupHeader" colspan="4">Disabled Workers</td>
			</tr>
			<?php foreach($workers as $w): if($w->enabled) continue; ?>
			<tr class="workerItem dataItem" data-id="<?php echo $w->id; ?>" data-status="<?php echo $w->status; ?>" data-available="<?php echo $w->available; ?>">
				<td class="available">
					<?php if($w->available): ?>
						<img class="availabilityIndicator" src="style/status_available.png" />
					<?php else: ?>
						<img class="availabilityIndicator" src="style/status_unavailable.png" />
					<?php endif; ?>
				</td>
				<td><?php echo $w->name; ?></td>
				<td class="ip"><?php echo $w->ip; ?></td>
				<td>
					<span class="status"><?php echo ucfirst($w->status); ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<div class="detailsContainer workerItem" data-id=""></div>
