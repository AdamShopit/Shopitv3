<?php $this->load->view('reports/header');?>

<?php if ($this->uri->segment(2) != 'welcome') { ?>
<p id="btn-print"><a href="#" onclick="window.print();return false;" class="button">Print report</a></p>
<?php } ?>

<?php $this->load->view($content);?>

<?php $this->load->view('reports/footer');?>