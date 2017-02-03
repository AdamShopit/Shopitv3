<p>Hi <?=$firstname;?></p>

<p>A new account has been set up for you on the <?=$this->config->item('store_name');?>'s website.</p>

<p>Login to your account using the following details:</p>

<p>
	Username: <?=$email;?><br/>
	Password: <?=$password;?>
</p>

<p>You can change your password anytime after logging in.</p>

<p>
	Kind regards<br/>
	<?=$this->config->item('store_name');?>	
</p>