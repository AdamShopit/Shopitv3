<div id="content">
		
	<div class="table">
	
		<h2>Google Services</h2>
		
		<div class="table-row">
			<p>Below is a list of Google services that are available for your store. These services will already be mapped to Google's services so you will not need to do anything other than regenerate the XML files.</p>
		</div>
			
		<div class="table-row odd">
			<label class="label">Regenerate Feeds:</label>
			<a href="<?=site_url('options/googlise/manual');?>" class="button">Regenerate All XML Feeds</a>
		</div>
		
		<div class="table-row">
			<h3>Shopping/Merchant Center</h3>
		</div>
		
		<?php
		$i = 0; //(A) used for background colour
		foreach($locations as $channel) {
		
			$i++;
			$post = ($i&1) ? 'odd' : 'even';
			
			$xml_filename = "base_$channel->shortname.xml";
			
			// Get the filesize
			$fileinfo = @filesize(XMLPATH.$xml_filename);
			$filesize = byte_format( $fileinfo, 0);
		?>
		<div class="table-row <?=$post;?>">
			<label class="label"><?=$channel->name;?></label>
			<span class="smallprint lightgrey">Generated: <?=@get_filetime("/base/$xml_filename");?> (<?=$filesize;?>)</span>
			<a class="smallprint" href="<?=site_url("options/xmldownload/$xml_filename");?>">Download</a> | <a class="smallprint" href="<?=site_url("options/xmlpreview/$xml_filename");?>" target="_blank">Preview</a>
		</div>

		<?php } ?>

		<div class="table-row">
			<h3>Sitemap</h3>
		</div>
		
		<div class="table-row even">
			<label class="label">Sitemap (Webmaster Tools)</label>
			<span class="smallprint lightgrey">Generated: <?=get_filetime('/base/sitemap.xml');?></span>
			<a class="smallprint" href="<?=site_url('options/xmldownload/sitemap.xml');?>">Download</a> | <a class="smallprint" href="<?=site_url('options/xmlpreview/sitemap.xml');?>" target="_blank">Preview</a>
		</div>
		
	</div>
	
	<div class="table">
		
		<h2>Available Modules</h2>
		
		<div class="table-row odd">
			<label class="label">Category Icons</label>
			<?php if (library_exists('categoryicons')):?>
			<span class="badge badge-green">Installed</span>
			<?php else: ?>
			<span class="badge badge-grey">Purchase</span>
			<?php endif; ?>
			<span class="smallprint">Add icons or images to categories. Includes design adjustment.</span>
		</div>
	
		<div class="table-row even">
			<label class="label">My Account</label>
			<?php if (library_exists('myaccount')):?>
			<span class="badge badge-green">Installed</span>
			<?php else: ?>
			<span class="badge badge-grey">Purchase</span>
			<?php endif; ?>
			<span class="smallprint">Enable customers to login to your store to make purchasing quicker for returning customers.</span>
		</div>
		
		<div class="table-row odd">
			<label class="label">Location/Sales channels</label>
			<?php if (library_exists('stocklocations')):?>
			<span class="badge badge-green">Installed</span>
			<?php else: ?>
			<span class="badge badge-grey"><a href="<?=site_url('inventory/purchaselocations');?>">Purchase</a></span>
			<?php endif; ?>
			<span class="smallprint">Control products and stock levels for multiple locations/sites.</span>
		</div>

		<div class="table-row even">
			<label class="label">Product Filters</label>
			<?php if (library_exists('filters')):?>
			<span class="badge badge-green">Installed</span>
			<?php else: ?>
			<span class="badge badge-grey"><a href="<?=site_url('filters/purchase');?>">Purchase</a></span>
			<?php endif; ?>
			<span class="smallprint">Help customers find exactly what they are looking for with unlimited product filtering.</span>
		</div>
		
	</div>

</div>

<div id="sidebar">
	<h3>Site options</h3>
	<p>From here you can manage website services such as the Google Shopping and Webmaster Tools feeds.</p>
</div>