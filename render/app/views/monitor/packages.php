<div class="packagesTableContainer">
	<table class="packagesTable data">
		<thead>
			<tr>
				<th>ID</th>
				<th>Date Submitted</th>
				<th>Jobs</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($packages as $p): ?>
			<tr class="packageItem dataItem" data-id="<?php echo $p->id; ?>" data-status="<?php echo $p->status; ?>">
				<td><?php echo $p->id; ?></td>
				<td><?php echo $p->stamp; ?></td>
				<td><?php echo $p->jobs; ?></td>
				<td class="progress" data-progress="<?php echo $p->progress; ?>">
					<?php if($p->status == 'complete'): ?>
						Complete
					<?php else: ?>
						<div class="packageProgressBar"></div>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<div class="detailsContainer packageItem" data-id=""></div>
