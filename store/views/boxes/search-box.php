<form action="<?=site_url('search');?>" method="get">
	<input type="text" name="q" id="q" class="searchbox" value="<?=$this->input->post('q');?>" />
	<input type="submit" name="search" value="Search" />
</form>
