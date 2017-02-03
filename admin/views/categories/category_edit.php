<div id="content">

	<div class="table">
	
	<h2><?=$form_title;?></h2>
	
	<?php if (validation_errors()) { ?>
	<p class="error_notice">Sorry, we found some errors with your category information. Please check below.</p>
	<?php } ?>

	<div class="table-row">
		<h3>Category Information</h3>
	</div>
	
	<div class="table-row">
		<label>Disable category?</label>
		<select name="cat_hide" id="cat_hide" class="dropdown">
			<option value="0"<?=is_selected('0',set_value('cat_hide',$cat->cat_hide));?>>No</option>
			<option value="1"<?=is_selected('1',set_value('cat_hide',$cat->cat_hide));?>>Yes</option>
		</select>
	</div>

	<div class="table-row">
		<label>Select parent category:</label>
		<select name="cat_father_id" id="cat_father_id" class="dropdown">
			<option value="0">None (I want this as a parent)</option>
			<?php
			foreach($categories as $category):
				
				if ($category->cat_father_id == 0) {
				
				$this_cat_father_id = ($this->uri->segment(2) == 'add' && $this->uri->segment(3) != '') ? $this->uri->segment(3) : $cat->cat_father_id;
				
				// parent category
				echo '<option value="'.$category->cat_id.'"'.is_selected($category->cat_id,set_value('cat_father_id',$this_cat_father_id)).'>'.$category->cat_name.'</option>' . "\n";
				
				// subcategory
				$data['subcategories'] = $this->category_model->getSubCategories($category->cat_id,false); 
				
				if ($data['subcategories'] != ''):
				
					foreach ($data['subcategories'] as $subcategory):
						echo '<option value="'.$subcategory->cat_id.'"'.is_selected($subcategory->cat_id,set_value('cat_father_id',$this_cat_father_id)).'>-- '.$subcategory->cat_name.'</option>' . "\n";
					endforeach;
				
				endif;
				
				}
				
			endforeach; 
			?>
		</select>
	</div>

	<div class="table-row">
		<label>Category name: <span class="red">*</span></label> <input name="cat_name" id="cat_name" value="<?=set_value('cat_name',$cat->cat_name);?>" class="textbox required" size="75" />
		<?=form_error('cat_name');?>
	</div>

	<?php if ($this->uri->segment(2) != 'add' && $this->uri->segment(2) != 'insert'):?>
	<div class="table-row">
		<label>Page slug (URL): <span class="red">*</span></label>
		<?php 
		if ($cat->cat_father_id == 0): 
			print site_root();
		else:
		 	print site_root() . ".../ "; 
		endif;
		?>
		 <input name="cat_slug" id="cat_slug" value="<?=set_value('cat_slug',$cat->cat_slug);?>" class="textbox required" size="30" <?=tooltip("The category slug is automatically created based on the category's name but can be changed if required. Only alphanumeric characters are permitted.");?>/>
		 <?=form_error('cat_slug');?>
	</div>
	<?php endif;?>

	<div class="table-row">
		<label>Category description:</label> 
		<div class="editor">
			<textarea name="cat_desc" id="cat_desc" class="textbox tinymce <?=codebox();?>"><?=set_value('cat_desc', hidep($cat->cat_desc));?></textarea>
		</div>
		<?=form_error('cat_desc');?>
	</div>

	<div class="table-row">
		<label>Excerpt:</label>
		<div class="editor">
			<textarea name="cat_excerpt" id="cat_excerpt" class="textbox tinymce tinymce-short <?=codebox();?>"><?=set_value('cat_excerpt', hidep($cat->cat_excerpt));?></textarea>
		</div>
		<?=form_error('cat_excerpt');?>
	</div>

	<?php if (library_exists('categoryicons')): ?>
	<?php if (!empty($cat->cat_image)):?>
	<div class="table-row">
		<label>Current category image:</label>
		<img src="<?=site_root();?>docs/<?=$cat->cat_image;?>" alt="" class="thumbnail" />
	</div>
	
	<div class="table-row">
		<label>&nbsp;</label>
		<input type="checkbox" name="delete_cat_image" value="true" /> Remove image?
	</div>
	<?php endif; ?>

	<div class="table-row">
		<label>Attach new category image:<br/><span class="smallprint">(Only jpg, gif or png accepted)</span></label>
		<input type="file" name="cat_image" id="cat_image" class="uploadbox" />
	</div>
	
	<div class="table-row formnote">
		Note: Category images aren't resized automatically.
	</div>
	<?php endif; ?>

	<div class="table-row">
		<h3>Search Engine Optimisation</h3>
	</div>
	
	<div class="table-row">
		<label>Page title:</label> <input name="cat_meta_title" id="cat_meta_title" size="75" value="<?=set_value('cat_meta_title',$cat->cat_meta_title);?>" class="textbox frm-metatitle" <?=tooltip("Adding a short accurate page title can help your page get listed correctly in the key search engines. If you do not enter one here then the category name will be used. This title appears in the browser's title bar.");?> />
		<?=form_error('cat_meta_title');?>
	</div>

	<div class="table-row">
		<label>Custom heading:</label> 
		<input name="cat_custom_heading" id="cat_custom_heading" value="<?=set_value('cat_custom_heading',$cat->cat_custom_heading);?>" class="textbox" size="75" <?=tooltip("If you would prefer to have a custom heading displayed on the category page instead of the default category name, you can enter it here. This will appear as a H1 heading.");?> />
		<?=form_error('cat_custom_heading');?>
	</div>

	<div class="table-row">
		<label>Page description:</label> <textarea name="cat_meta_description" id="cat_meta_description" class="textbox" rows="3" <?=tooltip("A short description of no more than 25 words will help your page in the search engines.");?> ><?=set_value('cat_meta_description',$cat->cat_meta_description);?></textarea>
		<?=form_error('cat_meta_description');?>
	</div>

	<div class="table-row">
		<label>Page keywords: <br/><span class="smallprint">(Separate with commas)</span></label> <textarea name="cat_meta_keywords" id="cat_meta_keywords" class="textbox" rows="2" <?=tooltip("Add 10 or less keywords to help the search engines identify this page's content. Separate each keyword with a comma, e.g. petware, pet bowl...");?> ><?=set_value('cat_meta_keywords',$cat->cat_meta_keywords);?></textarea>
		<?=form_error('cat_meta_keywords');?>
	</div>

	<div class="table-row">
		<label>Custom tags:</label> <textarea name="cat_meta_custom" id="cat_meta_custom" class="textbox <?=codebox();?>" rows="3" <?=tooltip("Use this field to enter any custom head tags that are required for this page.");?> ><?=set_value('cat_meta_custom',$cat->cat_meta_custom);?></textarea>
		<?=form_error('cat_meta_custom');?>
	</div>
		
	<input type="hidden" name="cat_id" value="<?=$cat->cat_id;?>" />
	<input type="hidden" name="existing_url" value="<?=$cat->cat_url;?>" />
	
	</div>

</div>

<div id="sidebar">
	<h3>Tips</h3>
	<p>The category description will appear on the category page before any products are listed. We recommend that a short description is entered that contains some keywords.</p>
</div>