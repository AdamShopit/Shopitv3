<div id="content">
	
	<div class="table">

		<h2><?=$form_title;?></h2>
	
		<?php if (validation_errors()) { ?>
		<p class="error_notice">Sorry, we found some errors with your collection information. Please check below.</p>
		<?php } ?>

		<div class="table-row">
			<h3>Collection Information</h3>
		</div>

		<div class="table-row">
			<label>Collection group:</label>
			<select name="collection_group" id="collection_group" class="dropdown">
				<option value="0"<?=is_selected('0', set_value('collection_group', $collection->collection_group));?>>None</option>
				<?php
				if ($groups > 0):
				foreach ($groups as $group) {
				?>
				<option value="<?=$group->id;?>"<?=is_selected($group->id, set_value('collection_group', $collection->collection_group));?>><?=$group->group_label;?></option>
				<?php
				}
				endif;
				?>
			</select>
		</div>
	
		<div class="table-row">
			<label>Collection name: <span class="red">*</span></label> <input name="collection_name" id="collection_name" maxlength="100" value="<?=set_value('collection_name',$collection->collection_name);?>" size="75" class="textbox required"/>
			<?=form_error('collection_name');?>
		</div>

		<?php if ($this->uri->segment(2) != 'create' && $this->uri->segment(2) != 'insert'):?>
		<div class="table-row">
			<label>Page slug (URL): <span class="red">*</span></label>
			<?=site_root().'collections/';?>
			 <input name="collection_slug" id="collection_slug" value="<?=set_value('collection_slug',$collection->collection_slug);?>" class="textbox required" size="35" <?=tooltip("The collection slug is automatically created based on the collection's name but can be changed if required. Only alphanumeric characters are permitted.");?>/>
			 <?=form_error('collection_slug');?>
		</div>
		<?php endif;?>
	
		<div class="table-row">
			<label>Collection description:</label>
			<div class="editor">
				<textarea name="collection_desc" id="collection_desc" class="textbox tinymce <?=codebox();?>"><?=set_value('collection_desc',hidep($collection->collection_desc));?></textarea>
				<?=form_error('collection_desc');?>
			</div>
		</div>

		<?php if (!empty($collection->collection_image)):?>
		<div class="table-row">
			<label>Current collection image:</label>
			<img src="<?=site_root('docs/'.$collection->collection_image);?>" alt="" class="thumbnail" style="max-width:200px;" />
		</div>
		
		<div class="table-row">
			<label>&nbsp;</label>
			<input type="checkbox" name="delete_collection_image" value="true" /> Remove image?
		</div>
		<?php endif; ?>
	
		<div class="table-row">
			<label>Attach new collection image:<br/><span class="smallprint">(Only jpg, gif or png accepted)</span></label>
			<input type="file" name="collection_image" id="collection_image" class="uploadbox" />
		</div>
		
		<div class="table-row formnote">
			Note: Collection images aren't resized automatically.
		</div>
		
		<div class="table-row">
			<h3>Search Engine Optimisation</h3>
		</div>
	
		<div class="table-row">
			<label>Page title:</label> <input name="collection_meta_title" id="collection_meta_title" value="<?=set_value('collection_meta_title',$collection->collection_meta_title);?>" size="75" class="textbox" <?=tooltip("Adding a short accurate page title can help your page get listed correctly in the key search engines. If you do not enter one here then the collection name will be used. This title appears in the browser's title bar.");?>/>
			<?=form_error('collection_meta_title');?>
		</div>

		<div class="table-row">
			<label>Custom heading:</label> 
			<input name="collection_custom_heading" id="collection_custom_heading" value="<?=set_value('collection_custom_heading',$collection->collection_custom_heading);?>" class="textbox" size="75" <?=tooltip("If you would prefer to have a custom heading displayed on the collection page instead of the default collection name, you can enter it here. This will appear as a H1 heading.");?> />
			<?=form_error('collection_custom_heading');?>
		</div>
	
		<div class="table-row">
			<label>Page description:</label> <textarea name="collection_meta_description" id="collection_meta_description" class="textbox" rows="3" <?=tooltip("A short description of no more than 25 words will help your page in the search engines.");?>><?=set_value('collection_meta_description',$collection->collection_meta_description);?></textarea>
			<?=form_error('collection_meta_description');?>
		</div>
	
		<div class="table-row">
			<label>Page keywords: <br/><span class="smallprint">(Separate with commas)</span></label> <textarea name="collection_meta_keywords" id="collection_meta_keywords" class="textbox" rows="2" <?=tooltip("Add 10 or less keywords to help the search engines identify this page's content. Separate each keyword with a comma, e.g. petware, pet bowl...");?>><?=set_value('collection_meta_keywords',$collection->collection_meta_keywords);?></textarea>
			<?=form_error('collection_meta_keywords');?>
		</div>

		<div class="table-row">
			<label>Custom tags:</label> <textarea name="collection_meta_custom" id="collection_meta_custom" class="textbox <?=codebox();?>" rows="3" <?=tooltip("Use this field to enter any custom head tags.");?>><?=set_value('collection_meta_custom', $collection->collection_meta_custom);?></textarea>
			<?=form_error('collection_meta_custom');?>
		</div>

		<input type="hidden" name="collection_id" value="<?=$collection->collection_id;?>" />
			
	</div>

</div>

<div id="sidebar">
	<h3>What are collections?</h3>
	
	<p>Collections are very similar to categories with the main difference being that you can organise products into sets and organise them in the order you want customers to see them.</p>
	
	<p>Examples could be the perfect BBQ, Mother's Day gifts, Father's Day gifts, Winter specials, etc.</p>
	
	<p>Collections can be turned off and on whenever you like.</p>
</div>