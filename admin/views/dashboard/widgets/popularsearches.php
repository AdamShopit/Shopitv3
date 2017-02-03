<?php
if ( $this->config->item('can_access_dashwidgets_stats') ) {
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="widget">
	<thead>
		<tr>
			<td colspan="4"><h2>Popular searches</h2></td>
		</tr>
	</thead>

	<tbody>
	<tr>
		<td colspan="4">
			
			<?php if ($totalSearches > 0): ?>
			<div class="barchart">
			<?php foreach($popularSearches as $search) {
			
				$percent = number_format(($search->searches/$totalSearches) * 100);
				$bar_width = $percent/1.75;

				//Extra padding
				$extra_padding = ($percent < 70) ? "padding: 0 5px;" : "";
			?>
				<div class="bar-label">
					<div class="bar">
						<div class="bar-marker" style="width:<?=$bar_width;?>%;<?=$extra_padding;?>" width="<?=$bar_width;?>%"></div>
					</div>
					<label><?=$search->term;?> <strong>(<?=$search->searches;?>)</strong></label>
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