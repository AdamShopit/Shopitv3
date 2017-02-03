<?php
if ( $this->config->item('can_access_dashwidgets_realtime') ) {
?>
<div class="table widget realtime_widget">
	<h2>Today's Activity Stream</h2>
	
	<div class="widget_scrollable">
	<?php 
	$i = 0;
	if ($activityfeed) {
	?>
		<ul id='realtime_widget_content'>
		<?php
		foreach ($activityfeed as $event) {

			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
			
			switch($event->message_type) {
				case "basket":
					$event->link = site_url('inventory/index/0/filter=true&s_category=&s_productno=id%3A'.$event->myid);
					$event->label = "(#$event->myid)";
					$event->connection = '<a href="'.$event->link.'">'.$event->label.'</a>';
					$event->icon = '<img class="valign" src="'.template_directory('assets/images/icon-cart.png').'" alt="Add to Basket" />';
					$event->highlight = "";
					break;
				
				case "order":
					$event->link = site_url('orders/view/'.$event->myid);
					$event->label = "(#$event->myid)";
					$event->connection = '<a href="'.$event->link.'">'.$event->label.'</a>';
					$event->icon = '<img class="valign" src="'.template_directory('assets/images/icon-money.png').'" alt="New Order" />';
					$event->highlight = "";
					break;

				case "search":
					$event->link = '';
					$event->label = "";
					$event->connection = "";
					$event->icon = '<img class="valign" src="'.template_directory('assets/images/icon-keyword.png').'" alt="Search" />';
					$event->highlight = "";
					break;

			}
		?>
			<li class="<?=$post.$event->highlight;?>">
				<span class="message"><i><?=$event->icon;?></i> <label><?=strip_tags($event->message, '<strong><a><em>');?> <?=$event->connection;?></label></span> <em class="smallprint"><?=realtime_ago(strtotime($event->realtime_date));?></em>
			</li>
		<?php } ?>
		</ul>
	<?php } else { ?>
		<ul id="realtime_widget_content">
			<li><p><strong>Nothing to report yet!</strong><br/>This panel will updated automatically when there's new activity.</p></li>
		</ul>
	<?php } ?>
	</div>
</div>
<?php } ?>