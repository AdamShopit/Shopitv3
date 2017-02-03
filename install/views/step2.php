<p>Please provide the admin user account information.</p>

<?php if($this->session->flashdata('message')){ echo '<p class="error">'.$this->session->flashdata('message').'</p>'; } ?>

<form action="<?=site_url('step3');?>" method="post">

<div class="install_row">
	<p>Username: <strong>admin</strong></p>
</div>

<div class="install_row">
	<input name="admin_password" value="" class="install_textbox" placeholder="Password" autofocus/>
</div>

<div class="install_row">
	<input name="admin_email" type="email" value="" class="install_textbox" placeholder="Email"/>
</div>

<div class="install_row">
	<input name="admin_firstname" class="install_textbox" placeholder="First Name"/>
</div>

<div class="install_row">
	<input name="admin_lastname" class="install_textbox" placeholder="Last Name"/>
</div>

<div class="install_row">
	<button type="submit">Next</button>
</div>

</form>