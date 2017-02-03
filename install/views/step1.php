<p>Enter the details of the database you wish to connect to.</p>

<?php if($this->session->flashdata('message')){ echo '<p class="error">'.$this->session->flashdata('message').'</p>'; } ?>

<form action="<?=site_url('step2');?>" method="post">

	<div class="install_row">
		<input name="db_hostname" value="<?php echo $db['host'] ?>" class="install_textbox" placeholder="Database Host" autofocus/>
	</div>

	<div class="install_row">
		<input name="db_name" value="<?php echo $db['database'] ?>" class="install_textbox" placeholder="Database Name" />
	</div>

	<div class="install_row">
		<input name="db_username" value="<?php echo $db['user'] ?>" class="install_textbox" placeholder="Username" />
	</div>

	<div class="install_row">
		<input name="db_password" value="<?php echo $db['password'] ?>" class="install_textbox" placeholder="Password" />
	</div>

	<div class="install_row">
		<button type="submit">Next</button>
	</div>
	
</form>