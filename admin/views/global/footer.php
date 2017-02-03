	<br clear="all" class="clearall"/>
	</div><!-- end: shopit_content -->

	<div id="wrapper_floating-bar">
		<div id="floating-bar">
			<?php if (empty($form_open)) { ?>
			<p id="user-aloha">
				<a href="https://en.gravatar.com/"><?=$this->shopit->gravatar($this->session->userdata('email'), 22);?></a>
				<strong class="valign"><?=$this->session->userdata('firstname');?></strong>
			</p>
			<div id="user-options">
				<?=$this->shopit->version_check();?>
				
				<?php if ($this->permissions->access('can_supersearch', false)) { ?>
				<form id="supersearch" method="get" action="<?=site_url('search/index');?>">
					<label><i class="fa fa-search"></i></label>
					<input type="search" name="s" value="<?=$keyword;?>" placeholder="Search" onclick="$(this).select();" />
				</form>
				<?php } ?>
				
				<?php if ($this->config->item('arrivals_url') != "" && $this->config->item('can_manage_users') == 1) { ?>
				<a href="<?=$this->config->item('arrivals_url');?>" target="_blank">arrivals</a> | 
				<?php } ?>
				
				<a href="<?=site_root();?>">browse store</a> | 
				
				<?php if ($this->permissions->access('can_manage_users', false)) { ?>
				<a href="<?=site_url('users');?>">manage users</a> | 
				<?php } else { ?>
				<a href="<?=site_url('users/update/'.base64_encode( $this->encrypt->encode($this->session->userdata('uid')) ));?>">change my password</a> | 
				<?php } ?>
				
				<a href="<?=site_url('logout');?>">logout</a>
			</div>
			<?php } else { 
				if (!isset($form_submit_label)) {
					$form_submit_label = 'Save';
				}
			?>
			<p style="float:left;"><strong>All done?</strong> <input type="submit" name="submit" value="<?=$form_submit_label;?>" class="form-button" />
			<a href="<?=$form_cancel_link;?>">Cancel</a></p>
			<?php } ?>
		</div>
	</div>
<?=$form_close;?>

<!-- To quicken page load, javascripts added here -->
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery-ui.min.js?v=1.10.3');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.form.min.js');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.validate.min.js');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.jeditable.mini.js');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.autocomplete.min.js');?>"></script>
<!-- Load: Markitup! editor -->
<script type="text/javascript" src="<?=template_directory('assets/scripts/markitup/jquery.markitup.min.js');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/markitup/sets/html/set.min.js');?>"></script>
<!-- End: Markitup! editor -->
<script type="text/javascript" src="<?=template_directory('assets/scripts/fancybox2/jquery.fancybox.pack.min.js?v=2.1.5');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/shopit.min.js?'.date('Ymd'));?>"></script>
<?php if ($this->uri->segment(1) == "orders") {?>
<script type="text/javascript" src="<?=template_directory('assets/scripts/shopit.orderbuilder.min.js?'.date('Ymd'));?>"></script>
<?php } ?>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.qtip.min.js');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.stickytableheaders.min.js');?>"></script>

<?php
// New feature notification
echo $this->shopit->new_version_popup();
?>
</body>
</html>