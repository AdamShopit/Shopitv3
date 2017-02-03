<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="5"><h2>Manage Shipping VAT</h2></td>
			</tr>
			<tr>
				<th width="50%">Country</th>
				<th width="50%">VAT Exempt?</th>
			</tr>
		</thead>
	
		<tbody>
	
			<?php 
			$i = 0; //(A) used for background colour
			if ($countries > 0) {
			foreach($countries as $country): 
						
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
		
			?>
			<tr class="<?=$post;?> country-vat" id="<?=$country->id;?>">
				<td><?=$country->country_name;?></td>
				<td>
					<input type="checkbox" name="vat[]" rel="<?=$country->id;?>" id="vat_<?=$country->id;?>" class="vat-exempt" value="1" <?=is_checked('1',$country->vat_exempt);?> />
				</td>
			</tr>
	
			<?php endforeach;
			} else { ?>
			<tr>
				<td colspan="2">Oh dear! There are no countries listed. Please call Dubbed Creative urgently.</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	
</div>

<div id="sidebar">
	<h3>About shipping VAT</h3>
	<p></p>
</div>