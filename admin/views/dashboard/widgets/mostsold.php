<?php
if ( $this->config->item('can_access_dashwidgets_stats') ) {
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="widget">
	<thead>
		<tr>
			<td colspan="4"><h2>Most Sold Items</h2></td>
		</tr>
	</thead>

	<tbody>
	<tr>
		<td colspan="4">

			<?php
			if ($totalSold > 0): ?>
			<div class="barchart">
			<?php
			
				foreach($mostSold as $sold) {
			
				$percent = number_format(($sold->product_count/$totalSold) * 100);
				$bar_width = $percent/1.75;

				//Extra padding
				$extra_padding = ($percent < 70) ? "padding: 0 5px;" : "";
			?>
				<div class="bar-label">
					<div class="bar">
						<div class="bar-marker" style="width:<?=$bar_width;?>%;<?=$extra_padding;?>" width="<?=$bar_width;?>%"></div>
					</div>
					<label><a href="<?=site_url("inventory/index/0/filter=true&s_productno=$sold->product_no");?>"><?=word_limiter($sold->product_name,6);?></a> <strong>(<?=$sold->product_count;?>)</strong></label>
				</div>
			<?php } ?>
			</div>
			<?php else: ?>
			<p>Not enough data to display results.</p>
			<?php endif; ?>
		
		</td>
	</tr>
	
	</tbody>		

</table>
<?php } ?>