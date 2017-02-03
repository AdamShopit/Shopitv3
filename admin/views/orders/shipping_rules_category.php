<?php foreach($categories as $category):?>
<option value="<?=$category->cat_id;?>"<?=is_selected($category->cat_id,set_value('cat_id',$shipping->operation));?>><?=str_replace(' & ',' &amp; ',$category->cat_name);?></option>
<?php endforeach;?>