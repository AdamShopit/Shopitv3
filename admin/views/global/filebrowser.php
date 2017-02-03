<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<title>File Browser</title>

<link href="<?=template_directory('assets/styles/filebrowser.css');?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/scripts/fancybox2/jquery.fancybox.css?v=2.1.5');?>" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/fancybox2/jquery.fancybox.pack.js?v=2.1.5');?>"></script>
<script type="text/javascript">
$(document).ready(function() {
    
    //Load fancybox on links
    $('.preview').fancybox();

	$('.link').click(function(e){
		e.preventDefault();
		var url = $(this).attr('href');
		
		<?php
		// Set override (when we're not using markitup)
		$override = (isset($_GET['elementid'])) ? TRUE : FALSE;
		$callback_img_size = (isset($_GET['size'])) ? $this->input->get('size') : 35;
		
		// If this is an override, do something
		// Else continue working with markItUp plugin
		if ($override) {
			
			echo sprintf('parent.$(\'input[name="%s"]\').val(url);'."\n", $this->input->get('elementid'));
			
			// Make the get ID compatible - replace square brackets with underscores
			$my_id = str_replace('[', '_', $this->input->get('elementid'));
			$my_id = str_replace(']', '', $my_id);
			echo sprintf('parent.$(\'#%s\').html(\'<img src="\'+url+\'" class="valign" height="%s" />\');'."\n", $my_id, $callback_img_size);

		} else {
			if ($this->uri->segment(3) == 'image') {
			?>
			parent.$.markItUp({  replaceWith:'<img src="' + url + '" alt="[![Enter alternative text]!]" />' });
			<?php
			} else {
			?>
			parent.$.markItUp({  openWith:'<a href="' + url + '">[![Enter link text]!]', closeWith:'</a>' });
			<?php
			}
		} 
		?>
		
		//Close fancybox
		parent.$.fancybox.close();
		
	});

    //Search filters
    $('#s_files').keyup(function(){
    	myfilter('#s_files');
    });
    
    function myfilter(elementID) {
    	$('tr').addClass('filtered');
    	var field_val = $(elementID).val();
    	var field_attr = $(elementID).attr('id');
    	if (field_val != '') {
	    	$('.'+field_attr+':icontains("'+field_val+'")').closest('tr').removeClass('filtered');
	    } else {
    		$('tr').removeClass('filtered');
	    }
    }
	
});

//Case in-sensitive replacement function for jQuery's :contains() selector (which is case-sensitive)
$.expr[':'].icontains = function(obj, index, meta, stack){
	return (obj.textContent || obj.innerText || jQuery(obj).text() || '').toLowerCase().indexOf(meta[3].toLowerCase()) >= 0;
};
</script>
</head>

<body>

	<?php if($this->session->flashdata('notice') != ''): ?>
	<div id="notice"><?=$this->session->flashdata('notice');?></div>
	<?php endif;?>

	<h1><?=$title;?></h1>
	
	<div class="formblock">
		<input type="text" name="s_files" id="s_files" placeholder="Search files" size="20" />
	</div>
	
	<form name="upload" enctype="multipart/form-data" action="<?=site_url('filebrowser/upload');?>" method="post" class="upload-form formblock">
		<input type="file" name="file" /> <input type="submit" name="submit" value="Upload" size="2" />
		<input type="hidden" name="type" value="<?=$this->uri->segment(3);?>" />
		<input type="hidden" name="redirect_url" value="<?=str_replace(site_url(), '', $redirect_url);?>" />
	</form>
	
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<th width="55%">Name</th>
				<th width="15%">Kind</th>
				<th width="10%">Size</th>
				<th width="20%">Modified</th>
			</tr>
		</thead>
		<tbody>
		<?php
		if ($files) {
			foreach($files as $file) {
		?>
				<tr rel="<?=$file['url'];?>">
					<td>
						<?php
						switch($file['type']) {
							//Images
							case 'jpeg';
							case 'jpg';
							case 'png';
							case 'tiff';
							case 'gif':
								$thumb = 'style="background-image:url('.site_url('filebrowser/thumb/'.$file['name']). ');"';
								break;
							
							//Document
							default:
								$thumb = 'style="background-image:url('.template_directory('assets/images/icon-file1.png').'); border-color:white;"';
								break;
						}
						?>
						<div class="thumb" <?=$thumb;?>><a href="<?=$file['url'];?>" class="link" rel="<?=$file['kind'];?>"></a></div>
						<a class="link s_files" href="<?=$file['url'];?>" rel="<?=$file['kind'];?>"><?=$file['name'];?></a>
					</td>
					<td><?=$file['kind'];?> <span class="light-grey"><?=$file['type'];?></span></td>
					<td><?=$file['size'];?></td>
					<td><?=$file['modified'];?></td>
				</tr>
		<?php
			}
		} else {
		?>
			<tr>
				<td colspan="3">No files here.</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</body>
</html>