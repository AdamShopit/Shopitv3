<div id="content">
	
	<div class="table">

		<h2><?=$form_title;?></h2>
	
		<?php if (validation_errors()) { ?>
		<p class="error_notice">Sorry, we found some errors with your category information. Please check below.</p>
		<?php } ?>

		<div class="table-row">
			<h3>Page Details &amp; Content</h3>
		</div>
	
		<div class="table-row">
			<label>Hidden page?</label>
			<select name="page_visible" id="page_visible" class="dropdown">
				<?php if ($document->page_visible == '') {
					$page_visible = 1;
				} else {
					$page_visible = $document->page_visible;
				}?>
				<option value="1"<?=is_selected(1,set_value('page_visible',$page_visible));?>>No (Will appear on menu)</option>
				<option value="0"<?=is_selected(0,set_value('page_visible',$page_visible));?>>Yes</option>
			</select>
		</div>
	
		<div class="table-row">
			<label>Include in sitemap?</label>
			<select name="page_sitemap" id="page_sitemap" class="dropdown">
				<?php if ($document->page_sitemap == '') {
					$page_sitemap = 1;
				} else {
					$page_sitemap = $document->page_sitemap;
				}?>
				<option value="1"<?=is_selected(1,set_value('page_sitemap',$page_sitemap));?>>Yes</option>
				<option value="0"<?=is_selected(0,set_value('page_sitemap',$page_sitemap));?>>No</option>
			</select>
		</div>
		
		<div class="table-row">
			<label>Channel:</label>
			<select name="page_site" id="page_site" class="dropdown">
			<?php
			foreach($locations as $channel) {
			?>
				<option value="<?=$channel->shortname;?>" <?=is_selected($channel->shortname, set_value('page_site', $document->site));?>><?=$channel->name;?></option>
			<?php } ?>
			</select>
		</div>
	
		<div class="table-row">
			<label>Page title: <span class="red">*</span></label> <input name="page_title" value="<?=set_value('page_title',$document->page_name);?>" class="textbox required" size="75" <?=tooltip("The page title will appear on the menu in the store.");?>/>
			<?=form_error('page_title');?>
		</div>
		
		<?php 
		if ($this->uri->segment(2) != 'create' && $this->uri->segment(2) != 'insert'):
		if ($document->page_lock != 0) { ?>
		<input type="hidden" name="page_slug" value="<?=$document->page_slug;?>" />
		<?php } else { ?>
		<div class="table-row notapplicable">
			<label>Page slug (URL):</label>
			<?=site_root('page/');?>
			 <input name="page_slug" id="page_slug" value="<?=set_value('page_slug',$document->page_slug);?>" class="textbox required" size="35" <?=tooltip("The page slug is automatically created based on the page's name but can be changed if required. Only alphanumeric characters are permitted.");?>/>
			<?=form_error('page_slug');?>
		</div>
		<?php 
		}
		endif; ?>
	
		<?php if ($document->page_lock == 0): ?>
		<div class="table-row">
			<label>Page redirect: </label> <input name="page_redirect" id="page_redirect" rel="notapplicable" value="<?=set_value('page_redirect',$document->page_redirect);?>" class="textbox" size="75" <?=tooltip("If you'd like to setup a page that simply redirects to another website or page, enter the full address above e.g. http://www.example.co.uk/page.html");?>/>
			<?=form_error('page_redirect');?>
		</div>	
		<?php endif; ?>
	
		<div class="table-row notapplicable">
			<label>Page content:</label> 
			<div class="editor">
				<textarea name="page_content" id="page_content" class="textbox tinymce <?=codebox();?>"><?=set_value('page_content', hidep($document->page_content));?></textarea>
			</div>
			<?=form_error('page_content');?>
		</div>
		
		<?php if ($this->uri->segment(2) != 'create' && $this->uri->segment(2) != 'insert'): ?>
		<div class="table-row">
			<label>Page order:</label> <input name="page_order" id="page_order" value="<?=set_value('page_order',$document->page_order);?>" class="textbox" size="5"/>
			<?=form_error('page_order');?>
		</div>
		<?php else: ?>
		<input type="hidden" name="page_order" value="0" />
		<?php endif; ?>
	
		<div class="table-row notapplicable">
			<label>Page template:</label>
			<select name="page_template" id="page_template" class="dropdown">
				<option value=""<?=is_selected("",set_value('page_template',$document->page_template));?>>Default template</option>
			<?php
			$templates = opendir($_SERVER['DOCUMENT_ROOT'] . '/store/views/');
		
			while (false !== ($file = readdir($templates))) {
		    
		    	if (preg_match('%.php%',$file)){
		    	
		        	$content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/store/views/'. $file);
					$content = explode("\n",$content,2);	        	
		        	
		        	if (preg_match('%Template:%',$content[1])) {
		        		$template = explode ("\n",$content[1]);
		        		$template = $template[0];
		        		$template_name = str_replace('//Template:',"",$template);
		        		$template_name = trim($template_name);	        		
		       ?>
		       		<option value="<?=$file;?>"<?=is_selected($file,set_value('page_template',$document->page_template));?>><?=$template_name;?></option>
		       <?php
		        	}
		        }
		
			}
		
			closedir($templates);
			?>
			</select>
		</div>
		
		<div class="table-row notapplicable">
			<h3>Search Engine Optimisation</h3>
		</div>
	
		<div class="table-row notapplicable">
			<label>Page title (meta):</label> <input name="page_meta_title" id="page_meta_title" value="<?=set_value('page_meta_title',$document->page_meta_title);?>" class="textbox" size="75" <?=tooltip("Adding a short accurate page title can help your page get listed correctly in the key search engines. If you do not enter one here then the page title you entered at the very top of this page will be used. This title appears in the browser's title bar.");?> />
			<?=form_error('page_meta_title');?>
		</div>

		<div class="table-row notapplicable">
			<label>Custom heading:</label> 
			<input name="page_custom_heading" id="page_custom_heading" value="<?=set_value('page_custom_heading',$document->page_custom_heading);?>" class="textbox" size="75" <?=tooltip("If you would prefer to have a custom heading displayed on the page instead of the page title you entered at the very top, you can enter it here. This will appear as a H1 heading.");?> />
			<?=form_error('page_custom_heading');?>
		</div>
	
		<div class="table-row notapplicable">
			<label>Page description:</label> <textarea name="page_meta_description" id="page_meta_description" class="textbox" rows="3" <?=tooltip("A short description of no more than 25 words will help your page in the search engines.");?>><?=set_value('page_meta_description',$document->page_meta_description);?></textarea>
			<?=form_error('page_meta_description');?>
		</div>
	
		<div class="table-row notapplicable">
			<label>Page keywords: <br/><span class="smallprint">(Separate with commas)</span></label> <textarea name="page_meta_keywords" id="page_meta_keywords" class="textbox" rows="2" <?=tooltip("Add keywords to help the search engines identify the content of this page. Separate each keyword with a comma, e.g. petware, pet bowl, dog bowl ...");?>><?=set_value('page_meta_keywords', $document->page_meta_keywords);?></textarea>
			<?=form_error('page_meta_keywords');?>
		</div>

		<div class="table-row notapplicable">
			<label>Custom tags:</label> <textarea name="page_meta_custom" id="page_meta_custom" class="textbox <?=codebox();?>" rows="3" <?=tooltip("Use this field to enter any custom head tags.");?>><?=set_value('page_meta_custom', $document->page_meta_custom);?></textarea>
			<?=form_error('page_meta_custom');?>
		</div>
			
		<input type="hidden" name="page_id" value="<?=$document->page_id;?>" />
		<input type="hidden" name="existing_url" value="<?=$document->page_slug;?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Add/Edit Page or Post</h3>
	<p>Manage your content from this page.</p>
</div>