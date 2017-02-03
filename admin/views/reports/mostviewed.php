<h1><?=$report_title;?></h1>

<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="left" width="5%">#</th>
			<th class="left" width="20%">Product code</th>
			<th class="left" width="65%">Product Name</th>
			<th width="10%">Views</th>
		</tr>
	</thead>
	<tbody>
	<?php
	if ($mostviewed > 0) {
		$i = 1;
		foreach($mostviewed as $item): 
	?>
		<tr>
			<td class="left"><?=$i++;?></td>
			<td class="left"><?=$item->product_no;?></td>
			<td class="left"><?=$item->product_name;?></td>
			<td><?=$item->product_views;?></td>
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