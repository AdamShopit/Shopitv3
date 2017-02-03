<div id="content">
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="7"><h2><?=$form_title;?></h2></td>
			</tr>
			<tr>
				<th width="33%">&nbsp;</th>
				<th width="33%">Shopit</th>
				<th width="34%">Your File</th>
			</tr>
		</thead>
		<tbody>
			<?=$columns;?>
		</tbody>
	</table>
</div>

<div id="sidebar">
	<h3>Match-up Columns</h3>
	<p>Match up the columns in your spreadsheet to the database fields.</p>
	<p>You'll get a chance to preview a data sample on the next screen before it begins import.</p>
</div>

<input type="hidden" name="file_upload" value="<?=$file;?>" />
<input type="hidden" name="first_row_header" value="<?=$this->input->post('first_row_header');?>" />