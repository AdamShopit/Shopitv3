<pre>
Template Note: This is the checkout template which is used if it exists, otherwise 
it will default to the basket template. All tags that are available in basket.php are also
available here. 

&mdash; Delete this file if it's not required for this site.
</pre>
<?php
// Load the basket template here unless you want 
// to do something different with this page
$this->load->view('cart/basket');
?>