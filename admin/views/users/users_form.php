<div id="content">
	<div class="table">
	
		<h2><?=$form_title;?></h2>

		<?php if ($this->permissions->access('can_manage_users', false)) { ?>
		<?php if (validation_errors()) { ?>
		<p class="error_notice">Sorry, we found some errors - please check below.</p>
		<?php } ?>
	
		<div class="table-row">
			<label>First name: <span class="red">*</span></label> <input name="firstname" id="firstname" value="<?=set_value('firstname',$user->firstname);?>" class="textbox" size="35" maxlength="25" />
			<?=form_error('firstname');?>
		</div>
	
		<div class="table-row">
			<label>Surname:</label> <input name="surname" id="surname" value="<?=set_value('surname', $user->surname);?>" class="textbox" size="35" maxlength="25"/>
			<?=form_error('surname');?>
		</div>

		<div class="table-row">
			<label>Email: <span class="red">*</span></label> <input name="email" id="email" value="<?=set_value('email', $user->email);?>" class="textbox" size="35" maxlength="65"/>
			<?=form_error('email');?>
		</div>
		
		<div class="table-row">
			<label>Username: <span class="red">*</span></label> <input name="username" id="username" value="<?=set_value('username', $user->username);?>" class="textbox" size="35" maxlength="25" autocomplete="off" />
			<?=form_error('username');?>
		</div>
		<?php } ?>
		
		<?php if (!$this->permissions->access('can_manage_users', false)) { ?>
		<div class="table-row">
			<p><strong>Please enter a new password below. Once set you will be logged out and will need to login again with your new password.</strong></p>
		</div>
		<?php
		} elseif ( $this->permissions->access('can_manage_users', false) && $this->uri->segment(2) == "update" ) {
		?>
		<div class="table-row">
			<p><strong>Leave passwords blank to keep the user's current password.</strong></p>
		</div>
		<?php } ?>
		<div class="table-row">
			<label>Password: <span class="red">*</span></label> <input type="password" name="password" id="password" value="<?=set_value('password');?>" class="textbox" size="35" maxlength="25" autocomplete="off" />
			<?=form_error('password');?>
		</div>
		
		<div class="table-row">
			<label>Confirm password: <span class="red">*</span></label> <input type="password" name="cpassword" id="cpassword" value="<?=set_value('cpassword');?>" class="textbox" size="35" maxlength="25" autocomplete="off" />
			<?=form_error('cpassword');?>
		</div>

		<?php if ($this->permissions->access('can_manage_users', false)) { ?>
		<div class="table-row">
			<label>Group:</label>
			<select name="group_id" class="dropdown">
				<?php 
				if ($user->uid == 1) {
				?>
				<option value="1" selected="selected">Admin</option>
				<?php
				} else {
					foreach($user_groups as $group) {
				?>
				<option value="<?=$group->group_id;?>" <?=is_selected($group->group_id, set_value('group_id', $user->group_id));?> ><?=$group->group_title;?></option>
				<?php 
					}
				}
				?>
			</select>
		</div>
		<?php } ?>
	
	</div>
</div>

<div id="sidebar">
	<h3>Change password</h3>
	<p>Try to choose a password that is between 6 and 10 characters and which contains a mix of letters and numbers.</p>
</div>