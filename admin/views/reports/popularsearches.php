<h1><?=$report_title;?></h1>

<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="left" width="5%">#</th>
			<th class="left" width="65%">Term</th>
			<th width="30%">Searches</th>
		</tr>
	</thead>
	<tbody>
	<?php
	if ($searches > 0) {
		$i = 1;
		foreach($searches as $item): 
	?>
		<tr>
			<td class="left"><?=$i++;?></td>
			<td class="left"><?=$item->term;?></td>
			<td><?=$item->searches;?></td>
		</tr>
	<?php 
		endforeach; 
	} else {
	?>
	<tr>
		<td colspan="4"><center>No stats are available just yet, but don't worry, give it a little more time.</center></td>
	</tr>
	<?php } ?>
	</tbody>
</table>