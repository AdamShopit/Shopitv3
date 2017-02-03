<?php 
if ($this->config->item('ssl_on') == true) {
	$ssl['ssl'] = true;
} else {
	$ssl['ssl'] = false;
}
$this->load->view('global/header',$ssl);
?>

<div id="cart">
<?php $this->load->view($content,$ssl);?>
</div>

<?php 
$this->load->view('global/footer',$ssl);
?>