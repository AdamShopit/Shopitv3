<div id="content">
	
	<div class="table">
		<h2><?=$form_title;?></h2>

		<div class="container" style="overflow:scroll;">

			<table cellpadding="0" cellspacing="0" border="0" style="white-space:nowrap;margin:0;">
				<?=$preview;?>
			</table>
			
		</div>

	</div>

</div>

<div id="sidebar">
	<h3>Preview of your data</h3>
	<p>Here's a preview of the data from your spreadsheet. If everything looks ok, go ahead and import!</p>
</div>

<input type="hidden" name="file_upload" value="<?=$file;?>" />
<input type="hidden" name="first_row_header" value="<?=$this->input->post('first_row_header');?>" />
<input type="hidden" name="unique_id" value="<?=$this->input->post('unique_id');?>" />