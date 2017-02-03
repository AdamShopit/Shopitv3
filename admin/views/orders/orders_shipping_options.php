<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Shopit - <?=$title;?></title>

<link href="<?=template_directory('assets/styles/shopit.css');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<link href="<?=template_directory('assets/styles/shopit_styles.css');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>

<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery-ui.min.js?v=1.10.3');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.qtip.min.js');?>"></script>
<script type="text/javascript">

$(document).ready(function() {

	//Add border to last column header
	$('table').each(function(){
		$(this).find('th:last').addClass('last-column');
	});

	//Copy item to parent screen
	$('.copyitem').click(function(){
    	var shipping_price = $(this).closest('tr').find('input[name="lookup_shipping_price"]').val();
    	var shipping_method = $(this).closest('tr').find('input[name="lookup_shipping_method"]').val();
		$('input[name="order_shipping"]', window.parent.document).val(shipping_price);
		$('input[name="shipping_method"]', window.parent.document).val(shipping_method);
		parent.$.fancybox.close();	
	});

});
</script>
<style type="text/css">
	body {
		margin: 20px auto 0px auto;
	}
	table label {
		width: 75px;
		margin: 2px 0;
	}
	
	table select {
		width: 225px;
		margin: 2px 0;
	}
</style>
</head>

<body class="<?=body_class();?>" id="window">

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	
		<thead>
			<tr>
				<th width="25%">Country</th>
				<th width="20%">Shipping Label</th>
				<th width="35%">Criteria</th>
				<th width="15%">Shipping Cost</th>
				<th width="5%">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if ($shipping > 0) { 
			
			$i = 0; //(A) used for background colour 
			
			foreach ($shipping as $rule) {

				//Stop the foreach loop if category shipping rule is enforced for this order
				if ($stop_loop) {
					break;
				}

				//!Check criteria for category value
				// This takes priority, so no other rules should be displayed
				if (in_array($rule->operation, $cat_ids)) {
				
					if ($rule->criteria == 'category') {
						$suitable = 'highlight';
						$rule_operation = "";
						$value_1 = $this->category_model->getThisCategory($rule->operation)->cat_name;
						$value_2 = "";
					}
					
					//Create a flag to stop the foreach on the next loop
					$stop_loop = true;
	
				} else {
				
					$rule_operation = $rule->operation;

					// Check criteria for total and weight based rules
					if ($rule->criteria == 'total'):
						$metric  = $this->config->item('currency');
						$value_1 = $metric . number_format($rule->value,2);
						$value_2 = $metric . number_format($rule->value2,2);
					elseif ($rule->criteria == 'weight'):
						$metric  = "kg";
						$value_1 = $rule->value . $metric;
						$value_2 = $rule->value2 . $metric;
					elseif ($rule->criteria == 'category'):
						$rule_operation = NULL;
						$value_1 		= $this->category_model->getThisCategory($rule->operation)->cat_name;
						$value_2 		= NULL;
					endif;
	
					if ($rule->operation == 'between'):
						$value_2 = ' and ' . $value_2;
					else:
						$value_2 = ''; 
					endif;
				
					$i++; //(A)
					
					if ($i&1) { $post = 'odd'; } 
					else { $post = 'even'; } //(A);
	
					//Check criteria for total value
					if ($rule->criteria == 'total'):
					
						switch($rule->operation) {
						
							case 'less than':
								if ($total_price <= $rule->value):
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
								
							case 'more than':
								if ($total_price >= $rule->value):						
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
								
							case 'equal to':
								if ($total_price == $rule->value):
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
												
							case 'between':
								if ($total_price >= $rule->value && $total_price <= $rule->value2):
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
							
							default:
								$suitable = '';
								break;
			
						}
					
					//Check criteria for total weight value
					elseif ($rule->criteria == 'weight'):
					
						switch($rule->operation) {
						
							case 'less than':
								if ($total_weight <= $rule->value):
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
								
							case 'more than':
								if ($total_weight >= $rule->value):													
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
								
							case 'equal to':
								if ($total_weight == $rule->value):
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
			
							case 'between':
								if ($total_weight >= $rule->value && $total_weight <= $rule->value2):
									$suitable = 'highlight';
								else:
									$suitable = '';
								endif;
								break;
			
						}
					
					endif;

				}
		?>
			<tr class="<?=$post;?> <?=$suitable;?>" id="<?=$rule->rule_id;?>">
				<td><?=$rule->country;?></td>
				<td><?=$rule->rule_name;?></td>
				<td>
					<?=ucfirst($rule->criteria);?> is <?=$rule_operation;?>
					<strong>
						<?=$value_1;?><?=$value_2;?>
					</strong>
				</td>
				<td><?=$this->config->item('currency') . $rule->shipping;?></td>
				<td>
					<input type="hidden" name="lookup_shipping_price" value="<?=$rule->shipping;?>" />
					<input type="hidden" name="lookup_shipping_method" value="<?=$rule->rule_name;?>" />
					<input type="button" class="button copyitem" value="Select" />
				</td>
			</tr>
		<?php 
			}
		} else { 
		?>
			<tr>
				<td colspan="5" align="center">No shipping options found for <?=urldecode($this->uri->segment(3));?>.</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>

</body>
</html>