<?php
$form_title = ($form_title == '') ? "Upload Data" : $form_title;
$max_rows = number_format(intval(ini_get("max_input_vars")), 0);
?>
<div id="content">
	<div class="table">
	
		<form action="<?=$form_url;?>" method="post" enctype="multipart/form-data">
		
			<h2><?=$form_title;?></h2>
			
			<div class="table-row">
				<h3>Select file to upload</h3>
				<p>We only support the CSV file format at the moment. <span class="redtext">Only the first <strong><?=$max_rows;?></strong> rows will be imported.</span></p>
				<p class="smallprint" style="margin-top:20px;">
					<label for="first_row_header" style="text-transform:none;width:auto;"><input type="checkbox" name="first_row_header" id="first_row_header" value="YES" checked="checked" /> &nbsp;First row are headers (do not include in the import)</label>
				</p>
				<br clear="all" />
			</div>
			
			<div class="table-row">
				<input type="file" name="file_upload" class="uploadbox" />
				<input type="submit" name="Submit" value="Upload file" class="button" />
				<span id="feedback"></span>
			</div>
		
		</form>
		
	</div>
</div>

<div id="sidebar">
	<h3>Upload Data</h3>
	<p>Use this tool to import data into your Shopit! database.</p>
</div>