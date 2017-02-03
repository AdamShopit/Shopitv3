<div id="content">
	
	<div class="table">
		<input type="hidden" name="this_cat_id" value="<?=$this->uri->segment(3);?>" />

		<h2><?=$form_title;?></h2>
		
		<div class="table-row">
			<p>There are <strong><?=$total_product_count;?></strong> products across <strong><?=$subcat_count;?></strong> categories.</p>
		</div>
	
		<?php if ($total_product_count > 0):?>
		
		<div class="table-row">
			<p>Deleting this category will also delete all sub-categories if any. Please select a category where you would like these products moved to:</p>
		</div>
		
		<div class="table-row">
			<select name="new_cat_id" id="new_cat_id" class="dropdown">
				<?php 
				foreach($categories as $category):
					
					if ($category->cat_father_id == 0):
					
					// parent category
					if ($category->cat_id == $this->uri->segment(3)){
					} else {
	
						echo '<option value="'.$category->cat_id.'">'.$category->cat_name.'</option>' . "\n";
						
						// subcategory
						$data['subcategories'] = $this->category_model->getSubCategories($category->cat_id); 
						
						if ($data['subcategories'] != ''):
						
							foreach ($data['subcategories'] as $subcategory):
							
								if ($subcategory->cat_id == $this->uri->segment(3)){
								} else {
									echo '<option value="'.$subcategory->cat_id.'">-- '.$subcategory->cat_name.'</option>' . "\n";
								}
							
								//third categories module
								$thirdcategories = $this->category_model->getSubCategories($subcategory->cat_id);
								if ($thirdcategories != ''):
									foreach ($thirdcategories as $thirdcategory):
										if ($thirdcategory->cat_father_id == $this->uri->segment(3) || $thirdcategory->cat_id == $this->uri->segment(3)){
										} else {
												echo '<option value="'.$thirdcategory->cat_id.'">&nbsp;&nbsp;&nbsp;-- '.$thirdcategory->cat_name.'</option>' . "\n";
										}
									endforeach;
								endif;
		
							endforeach;
						
						endif;
						
					}
	
					endif;
					
				endforeach; 
				?>
			</select>
		</div>
	
		<?php endif; ?>

	</div>

</div>

<div id="sidebar">

</div>