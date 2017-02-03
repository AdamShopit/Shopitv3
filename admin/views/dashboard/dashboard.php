<div id="content">
<?php
if ($widgets != true) {
	if(!empty($this->session->userdata('firstname'))) {
		$name = $this->session->userdata('firstname');
	} else {
		$name = ucfirst($this->session->userdata('username'));
	}
?>
	<div class="table welcome">
		<img src="<?=template_directory('assets/images/widgets-intro.jpg');?>" alt="" title="" style="float:right;" />
		<h1>Aloha <?=$name?>!</h1>
		<p>Welcome to <strong>your</strong> new dashboard!</p>
		<p>There's nothing on it right now, but you can start adding information widgets using the <strong>'Customise Dashboard'</strong> panel on the far right.</p>
		
		<p>If this is your first login, you may also want to <a href="<?=site_url('users/update/'.base64_encode( $this->encrypt->encode($this->session->userdata('uid') )));?>">change your password</a> to something more memorable.</p>
		<br clear="all" />
	</div>
<?php 
} else {
?>
	<?php 
	// Load the sales chart
	$this->load->view('dashboard/widgets/chart');
	?>

	<div id="widget_col1">
	<?php
	if (!empty($mywidgets_1)) {
	foreach($mywidgets_1 as $w):
	
		$data['widget_id'] = $w['id'];

		switch ($w['widget']) {
		
			case "New orders";
				$this->load->view('dashboard/widgets/neworders', $data);
				break;

			case "Stock alerts";
				$this->load->view('dashboard/widgets/lowstock', $data);
				break;

			case "Sales summary";
				$this->load->view('dashboard/widgets/summary', $data);
				break;

			case "Popular searches";
				$this->load->view('dashboard/widgets/popularsearches', $data);
				break;

			case "Most viewed items";
				$this->load->view('dashboard/widgets/mostviewed', $data);
				break;

			case "Most sold items";
				$this->load->view('dashboard/widgets/mostsold', $data);
				break;

			case "Undispatched";
				$this->load->view('dashboard/widgets/undispatchedorders', $data);
				break;

			case "Todays orders";
				$this->load->view('dashboard/widgets/todaysorders', $data);
				break;
			
			case "Yesterdays orders";
				$this->load->view('dashboard/widgets/yesterdaysorders', $data);
				break;

			case "Todays activity";
				$this->load->view('dashboard/widgets/activityfeed', $data);
				break;
		
		}
	endforeach;
	}
	?>
	</div>

	<div id="widget_col2">
	<?php
	if (!empty($mywidgets_2)) {
	foreach($mywidgets_2 as $w):

		$data['widget_id'] = $w['id'];

		switch ($w['widget']) {
		
			case "New orders";
				$this->load->view('dashboard/widgets/neworders', $data);
				break;

			case "Stock alerts";
				$this->load->view('dashboard/widgets/lowstock', $data);
				break;

			case "Sales summary";
				$this->load->view('dashboard/widgets/summary', $data);
				break;

			case "Popular searches";
				$this->load->view('dashboard/widgets/popularsearches', $data);
				break;

			case "Most viewed items";
				$this->load->view('dashboard/widgets/mostviewed', $data);
				break;

			case "Most sold items";
				$this->load->view('dashboard/widgets/mostsold', $data);
				break;

			case "Undispatched";
				$this->load->view('dashboard/widgets/undispatchedorders', $data);
				break;

			case "Todays orders";
				$this->load->view('dashboard/widgets/todaysorders', $data);
				break;
			
			case "Yesterdays orders";
				$this->load->view('dashboard/widgets/yesterdaysorders', $data);
				break;
			
			case "Todays activity";
				$this->load->view('dashboard/widgets/activityfeed', $data);
				break;
		
		}
		
	endforeach;
	}
	?>
	</div>
<?php 
} 
?>

</div>

<div id="sidebar">
	<h3>Customise dashboard</h3>
		
	<p><strong>Inactive widgets</strong></p>
	<p>Drag the widgets you'd like on your dashboard to the <em>active widgets</em> panel below.</p>
	
	<ul id="widgets-list" class="widgets-list">
		<?php
		$this->widgets->is_active('New orders', $mywidgets, $this->config->item('can_access_dashwidgets_orders'));
		$this->widgets->is_active('Sales summary', $mywidgets, $this->config->item('can_access_dashwidgets_sales'));
		$this->widgets->is_active('Stock alerts', $mywidgets, $this->config->item('can_access_dashwidgets_inventory'));
		$this->widgets->is_active('Popular searches', $mywidgets, $this->config->item('can_access_dashwidgets_stats'));
		$this->widgets->is_active('Most viewed items', $mywidgets, $this->config->item('can_access_dashwidgets_stats'));
		$this->widgets->is_active('Most sold items', $mywidgets, $this->config->item('can_access_dashwidgets_stats'));
		$this->widgets->is_active('Undispatched', $mywidgets, $this->config->item('can_access_dashwidgets_orders'));
		$this->widgets->is_active('Todays orders', $mywidgets, $this->config->item('can_access_dashwidgets_orders'));
		$this->widgets->is_active('Yesterdays orders', $mywidgets, $this->config->item('can_access_dashwidgets_orders'));
		$this->widgets->is_active('Todays activity', $mywidgets, $this->config->item('can_access_dashwidgets_realtime'));
		?>		
	</ul>

	<form action="<?=site_url('dashboard/save');?>" method="post">

		<p><strong>Active widgets</strong></p>

		<p>Here are the widgets on your dashboard. You can drag them to your preferred order. To remove a widget drag it to the <em>inactive widgets</em> panel above.</p>
		
		<ul id="widgets-list-active" class="widgets-list">
		<?php 
		if($widgets == true):
			foreach ($mywidgets as $w) {
		?>
			<li class="w-<?=slug($w->widget);?>">
				<label><?=$w->widget;?></label>
				<input type="hidden" name="widget[]" value="<?=$w->widget;?>" />
				<input type="hidden" name="id[]" value="<?=$w->id;?>" />
			</li>
		<?php
			}
		endif;
		?>

		</ul>
	
		<p align="center">
			<input type="submit" name="save" value="Save my dashboard" class="button" />
		</p>	

	</form>
		
</div>
