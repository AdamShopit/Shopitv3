<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="4"><h2><?=$form_title;?><a href="#edit">Create Snippet</a></h2></td>
				</tr>
				<tr>
					<th width="25%">Title</th>
					<th width="50%">Tag</th>
					<th width="15%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($snippet_groups) {
			foreach($snippet_groups as $group) {
				$group_title = ($group->group_id == 0) ? "Ungrouped" : $group->label;
			?>
				<tr class="table-row">
					<td colspan="3"><h3><?=$group_title;?></h3></td>
				</tr>

				<?php
				
				$i = 0; //(A) used for background colour
				$the_snippets = $this->pages_model->getSnippetsList($group->group_id);
				
				foreach($the_snippets as $snippet) {
			
					$i++; //(A)
					
					if ($i&1) { $post = 'even'; } 
					else { $post = 'odd'; } //(A);
				?>
				<tr class="table-row even">
					<td>
						<span class="valign"><?=$snippet->title;?></span>
						<?php
						// Display 'widget' icon
						if ($snippet->widget == 1) {
						echo sprintf('<img src="%s" class="valign" title="Widget" alt="Widget" />', template_directory('assets/images/icon-widgets.png'));
						}
						?>
					</td>
					<td>
						<?php
						if ($snippet->widget == 0) {
							echo sprintf('<code class="snippet select-on-click">{sn_%s}</code>', $snippet->label);
						} elseif ($snippet->widget == 1) {
							echo sprintf('<code class="snippet select-on-click">php:$sn_%s</code>', $snippet->label);
						}
						?>
					</td>
					<td align="center">
						<?php
						// Display edit/delete options if this user has permission.
						if ($snippet->widget == 0 || $this->permissions->access('can_access_admin_tools', FALSE)) {
						?>
						<a href="<?=site_url('pages/snippets/' . $snippet->id);?>#edit" class="button">Edit</a>
						<a href="<?=site_url('pages/deletesnippet/' . $snippet->id);?>" class="button ajaxdelete" rel="Are you sure you want to delete this snippet? This can not be undone.">Delete</a>
						<?php } else {
							// If they don't and this is a 'widget', just show a message.
							echo sprintf('<p class="smallprint">Edit via <a href="%s">store-front</a></p>', site_root());
						} 
						?>
					</td>
				</tr>
				<?php if ($snippet->notes != "") { ?>
				<tr>
					<td colspan="3" class="smallprint"><code><?=nl2br(htmlentities($snippet->notes));?></code></td>
				</tr>
				<?php } ?>
			<?php
				}
			}
			} else {
			?>
				<tr>
					<td colspan="3" align="center">You have not created any snippets yet.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3 id="edit">Edit snippet</h3>
			<?php } else { ?>
			<h3 id="edit">Create snippet</h3>
			<?php } ?>
		</div>
		
		<?php
		// Display the widget option if the user has permission to do so
		if ($this->permissions->access('can_access_admin_tools', FALSE)) {
		?>
		<div class="table-row">
			<label>Widget?</label>
			<label class="reset" for="snippet_widget">
				<input type="checkbox" id="snippet_widget" name="snippet_widget" value="1" <?=is_checked(1, set_value('snippet_widget', $edit->widget));?> /> 
				<small class="smallprint">Tick if this snippet contains dynamic data, e.g. Wordpress blog post ID references, Shopit product ID - see sidebar notes.</small>
			</label>
		</div>
		<?php } else {
			echo sprintf('<input type="hidden" name="snippet_widget" value="%s" />', set_value('snippet_widget', $edit->widget));
		} ?>
		
		<div class="table-row">
			<label>Title:</label>
			<input type="text" name="snippet_title" autocomplete="off" value="<?=set_value('snippet_title', $edit->title);?>" class="textbox match-title" size="75" maxlength="128" <?=tooltip('Enter a short description for this snippet so you can identify it.');?> />
			<?=form_error('snippet_title');?>
		</div>

		<div class="table-row">
			<label>Tag:</label>
			<input type="text" name="snippet_label" autocomplete="off" value="<?=set_value('snippet_label', $edit->label);?>" class="textbox<?php if (empty($edit)) { ?> match-tag<?php } ?>" size="75" maxlength="128" <?=tooltip('Enter a short unique label for the snippet here. Anything you enter here will be converted into tag code. &quot;sn_&quot; is automatically prepended to the tag.');?> />
			<?=form_error('snippet_label');?>
		</div>

		<div class="table-row">
			<label>Snippet group:</label>
			<select name="group_id" id="group_id" class="dropdown">
				<option value="0"<?=is_selected('0', set_value('group_id', $edit->group_id));?>>None</option>
				<?php
				if ($groups > 0):
				foreach ($groups as $group) {
				?>
				<option value="<?=$group->group_id;?>"<?=is_selected($group->group_id, set_value('group_id', $edit->group_id));?>><?=$group->label;?></option>
				<?php
				}
				endif;
				?>
			</select>
		</div>

		<div class="table-row">
			<label>Snippet:</label>
			<div class="editor">
				<textarea name="snippet_content" id="snippet_content" class="textbox <?=codebox();?>" rows="20"><?=set_value('snippet_content', $edit->content);?></textarea>
			</div>
			<?=form_error('snippet_content');?>
		</div>

		<div class="table-row">
			<label>Notes: <span class="smallprint">(HTML is allowed)</span></label>
			<textarea name="snippet_notes" id="snippet_notes" class="textbox <?=codebox();?>" rows="10" placeholder="It's good to add a note - Add one here."><?=set_value('snippet_notes', $edit->notes);?></textarea>
			<?=form_error('snippet_notes');?>
		</div>

		<div class="table-row">
			<label>Early parsing?</label>
			<select name="snippet_early_parsing" id="snippet_early_parsing" class="dropdown">
				<option value="0"<?=is_selected('0', set_value('snippet_early_parsing', $edit->early_parsing));?>>No</option>
				<option value="1"<?=is_selected('1', set_value('snippet_early_parsing', $edit->early_parsing));?>>Yes</option>
			</select>
		</div>

		<input type="hidden" name="snippet_id" value="<?=set_value('snippet_id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>What are snippets?</h3>
	<p>Snippets are pieces of information that have been used throughout the store front. These are usually setup by the designer.</p>
	<p>You can also add a snippet to any product or category description by simply inserting the tag reference in the editor.</p>
	
	<h3>"Widget" Snippets</h3>
	<p>Widget snippets are intended for dynamic data e.g. Wordpress blog post IDs. They are not editable via this page, but must be integrated via the store front editing options.</p>
	<p>It's content is stored in the form of a serialised array and is available via a php variable in the form <code class="redtext">$<em>[sn_snippet_tag]</em></code>.</p>
	<p>These snippets should be used in conjunction with the <code>snippet_widget()</code> function - see docs for usage.</p>
</div>