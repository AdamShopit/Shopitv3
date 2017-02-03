<div id="content">
	<?php
	if ($channels) {
	foreach($channels as $channel) {
	?>
	<form method="post" action="<?=site_url('pages/sortable');?>">
	<table cellpadding="0" cellspacing="0" border="0" class="sortable-pages">
		<thead>
			<tr>
				<td colspan="5"><h2><?=$form_title;?> - <?=$channel->name;?></h2></td>
			</tr>
			<tr>
				<th width="5%"><center>ID</center></th>
				<th width="30%">Page Title</th>
				<th width="60%">URL</th>
				<th width="5%"><center>Status</center></th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>
		<?php
		$i = 0; //(A) used for background colour

		//Get list of pages for this channel/site
		$pages = $this->pages_model->getPagesList($channel->site);
		
		if (!empty($pages)) {
		foreach($pages as $document):
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
		?>
		<tr class="<?=$post;?>">
			<td align="center"><?=$document->page_id;?></td>
			<td>
				<input type="hidden" name="page_id[]" value="<?=$document->page_id;?>" />
				<i class="valign draggable fa fa-bars"></i>
				<a href="<?=site_url('pages/edit/'.$document->page_id);?>"><?=htmlentities($document->page_name);?></a>
				<?php if (strlen($document->page_redirect) > 2): ?>
				<img src="<?=template_directory('assets/images/icon-redirectlink.png');?>" title="Redirect" alt="Redirect" />
				<?php endif; ?>
			</td>
			<td>
				<code>
				<?php
				if ($document->page_id == 1) {
					$url = site_root();
				} elseif($document->page_id > 1 && $channel->type != 'website' && $document->page_redirect == '') {
					$url = site_root("page/$document->page_slug");
				} elseif($channel->type == 'website') {
					$url = "http://$channel->note/page/$document->page_slug";
				} elseif(strlen($document->page_redirect) > 1) {
					$url = $document->page_redirect;
				}
				
				if (strlen($url) > 0) {
					echo $url . ' <a href="'.$url.'">&rarr;</a>';
				}
				?>
				</code>
			</td>
			<td align="center"><?=status($document->page_visible,1);?></td>
			<td>
				<ul class="actions">
					<li><a href="#" class="btn-action"><i class="fa fa-angle-down"></i></a>
						<ul>
							<li><a href="<?=site_url('pages/edit/'.$document->page_id);?>">Edit page</a></li>
							<?php if ($document->page_lock == 0): ?>
							<li><a href="<?=site_url('pages/delete/'.$document->page_id);?>" class="ajaxdelete" rel="Are you sure you want to delete this page?">Delete page</a></li>
							<?php endif; ?>
							<?php if (strlen($document->page_redirect) <= 1):?>
							<li><a href="<?=site_root('page/'.$document->page_slug);?>" target="_blank">Live preview</a></li>
							<?php endif; ?>
						</ul>
					</li>
				</ul>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php } //End $pages loop ?>
		</tbody>
	</table>
	</form>
	<?php 
		} //End $channels loop 
		
	} else {
	//Else display no pages message
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="sortable-pages">
		<thead>
			<tr>
				<td colspan="5"><h2><?=$form_title;?></h2></td>
			</tr>
			<tr>
				<th width="5%"><center>ID</center></th>
				<th width="30%">Page Title</th>
				<th width="60%">URL</th>
				<th width="5%"><center>Status</center></th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<td colspan="5" align="center">You have not published any pages yet.</td>
			</tr>
		</tbody>
	</table>
	<?php } ?>
</div>

<div id="sidebar">
	<h3>Sorting pages</h3>
	<p>From this screen you can sort pages into your preferred order simply by dragging each one to its new position.</p>
</div>