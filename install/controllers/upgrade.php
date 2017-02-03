<?php
#------------------------------------------------------
# Sample upgrade to update database/data from
# dcommerce to shopit platform
#------------------------------------------------------
class Upgrade extends Controller {
	
	function Upgrade() {
		parent::Controller();		
		$this->load->helper('security');
		$this->load->helper('file');
		
		$this->load->database();
		$this->load->dbforge();
	}
	
	//http://www.mydomain.co.uk/install/index.php/upgrade
	function index() {

		#------------------------------------------------------
		# Update existing tables
		#------------------------------------------------------		
		//Update accounts table
		$fields = array(
			'account_company' => array(
				'type' => 'varchar',
				'constraint' => 250,
				'null' => true,
			),
		);
		
		if (!$this->db->field_exists('account_company', 'accounts')) {
			$this->dbforge->add_column('accounts', $fields);
		}		
		
		unset($fields);
		
		//Update category table
		$fields = array(
			'cat_custom_heading' => array(
				'type' => 'text',
				'null' => true,
			),
		);
		
		if (!$this->db->field_exists('cat_custom_heading', 'category')) {
			$this->dbforge->add_column('category', $fields);
		}		
		
		unset($fields);

		//Update attributes table
		$fields = array(
			'attribute_order' => array(
				'type' => 'int',
				'constraint' => 2,
				'default' => 0,
				'null' => false,
			),
		);
		
		if (!$this->db->field_exists('attribute_order', 'attributes')) {
			$this->dbforge->add_column('attributes', $fields);
		}		
		
		unset($fields);

		//Update users table
		$fields = array(
			'tooltips' => array(
				'type' => 'varchar',
				'constraint' => 10,
				'default' => 'true',
				'null' => false,
			),
		);
		
		if (!$this->db->field_exists('tooltips', 'users')) {
			$this->dbforge->add_column('users', $fields);
		}		
		
		unset($fields);

		//Update product_options table
		$fields = array(
			'option_order' => array(
				'type' => 'int',
				'constraint' => 2,
				'null' => false,
				'default' => 0,
			),
		);
		
		if (!$this->db->field_exists('option_order', 'product_options')) {
			$this->dbforge->add_column('product_options', $fields);
		}		
		
		unset($fields);

		//Update collections table
		$fields = array(
			'collection_custom_heading' => array(
				'type' => 'text',
				'null' => true,
			),
		);
		
		if (!$this->db->field_exists('collection_custom_heading', 'collections')) {
			$this->dbforge->add_column('collections', $fields);
		}		
		
		unset($fields);

		//Update pages table
		$fields = array(
			'page_custom_heading' => array(
				'type' => 'text',
				'null' => true,
			),
			'page_lock' => array(
				'type' => 'int',
				'constraint' => 1,
				'null' => false,
				'default' => 0,
			),
			'page_type' => array(
				'type' => 'varchar',
				'constraint' => 50,
				'null' => false,
				'default' => 'page',
			),
			'page_date' => array(
				'type' => 'datetime',
				'null' => true,
			),
			'page_author' => array(
				'type' => 'varchar',
				'constraint' => 128,
				'null' => true,
			),
		);
		
		if (!$this->db->field_exists('page_custom_heading', 'pages')) {
			$this->dbforge->add_column('pages', $fields);
		}		
		
		unset($fields);

		//Update inventory table
		$fields = array(
			'product_excerpt' => array(
				'type' => 'text',
				'null' => true,
			),
			'product_file' => array(
				'type' => 'text',
				'null' => true,
			),
			'product_custom_heading' => array(
				'type' => 'text',
				'null' => true,
			),
			'priority' => array(
				'type' => 'int',
				'constraint' => 3,
				'null' => false,
				'default' => 0,
			),
			'date_added' => array(
				'type' => 'datetime',
				'null' => true,
			),
		);
		
		if (!$this->db->field_exists('product_custom_heading', 'inventory')) {
			$this->dbforge->add_column('inventory', $fields);
		}		
		
		unset($fields);

		//Update orders table
		$fields = array(
			'order_status_id' => array(
				'type' => 'int',
				'constraint' => 1,
				'null' => false,
				'default' => 0,
			),
			'dispatch_date' => array(
				'type' => 'date',
				'null' => true,
			),
			'dispatch_email' => array(
				'type' => 'date',
				'null' => true,
			),
			'site' => array(
				'type' => 'varchar',
				'constraint' => 128,
				'null' => false,
				'default' => 'website',
			),
		);
		
		if (!$this->db->field_exists('order_status_id', 'orders')) {
			$this->dbforge->add_column('orders', $fields);
		}		
		
		unset($fields);

		#------------------------------------------------------
		# Create new tables
		#------------------------------------------------------		
		//Create attribute_set_templates
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'attribute_set_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
			'attribute_name' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'null' => true,
				),
			'attribute_value' => array(
					'type' => 'text',
					'null' => true,
				),
			'attribute_order' => array(
					'type' => 'int',
					'constraint' => 2,
					'null' => false,
					'default' => 0,
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('attribute_set_templates',true);
		unset($fields);
		
		//Create attribute_sets
		$fields = array(
			'attribute_set_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'attribute_set_label' => array(
					'type' => 'varchar',
					'constraint' => 128,
					'null' => false,
				),
			'attribute_set_desc' => array(
					'type' => 'text',
					'null' => true,
				),
		);
		
		$this->dbforge->add_key('attribute_set_id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('attribute_sets',true);
		unset($fields);

		//Create productoption_set_templates
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'option_set_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
			'option_label' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'null' => true,
				),
			'option_criteria' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'null' => true,
				),
			'option_price' => array(
					'type' => 'decimal',
					'constraint' => '10,2',
					'null' => false,
					'default' => '0.00',
				),
			'option_order' => array(
					'type' => 'int',
					'constraint' => 2,
					'null' => false,
					'default' => 0,
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('productoption_set_templates',true);
		unset($fields);

		//Create productoption_sets
		$fields = array(
			'option_set_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'option_set_label' => array(
					'type' => 'varchar',
					'constraint' => 128,
					'null' => false,
				),
			'option_set_desc' => array(
					'type' => 'text',
					'null' => true,
				),
		);
		
		$this->dbforge->add_key('option_set_id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('productoption_sets',true);
		unset($fields);

		//Create redirection
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'old_url' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'null' => false,
				),
			'new_url' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'null' => false,
				),
			'status_code' => array(
					'type' => 'int',
					'constraint' => 3,
					'null' => false,
					'default' => 301,
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('redirection',true);
		unset($fields);

		//Create dashboard
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'uid' => array(
					'type' => 'int',
					'constraint' => 11	,
					'null' => true,
					'default' => 0,
				),
			'widget' => array(
					'type' => 'varchar',
					'constraint' => 128,
					'null' => true,
				),
			'settings' => array(
					'type' => 'text',
					'null' => true,
				),
			'order' => array(
					'type' => 'int',
					'constraint' => 2,
					'null' => false,
					'default' => 0,
				),
			'active' => array(
					'type' => 'int',
					'constraint' => 1,
					'null' => false,
					'default' => 0,
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('dashboard',true);
		unset($fields);

		//Create xitems
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'product_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
			'xitem_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
			'type' => array(
					'type' => 'varchar',
					'constraint' => 1,
					'null' => false,
					'default' => 'R',
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('xitems',true);
		unset($fields);

		//Create collection_items
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'collection_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => true,
				),
			'product_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => true,
				),
			'order' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('collection_items',true);
		unset($fields);

		//Create order_notes
		$fields = array(
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'order_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
			'author' => array(
					'type' => 'varchar',
					'constraint' => 128,
					'null' => true,
				),
			'date' => array(
					'type' => 'datetime',
					'null' => true,
				),
			'note' => array(
					'type' => 'text',
					'null' => true,
				),
		);
		
		$this->dbforge->add_key('id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('order_notes',true);
		unset($fields);

		//Create custom_field_templates
		$fields = array(
			'custom_field_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'custom_field_for' => array(
					'type' => 'varchar',
					'constraint' => 50,
					'null' => false,
				),
			'custom_field_label' => array(
					'type' => 'varchar',
					'constraint' => 50,
					'null' => false,
				),
			'custom_field_title' => array(
					'type' => 'varchar',
					'constraint' => 250,
					'null' => false,
				),
			'custom_field_type' => array(
					'type' => 'varchar',
					'constraint' => 250,
					'null' => false,
				),
			'custom_field_default' => array(
					'type' => 'text',
					'null' => true,
				),
		);
		
		$this->dbforge->add_key('custom_field_id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('custom_field_templates',true);
		unset($fields);

		//Create custom_field_values
		$fields = array(
			'custom_field_id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'auto_increment' => true, 
				),
			'id' => array(
					'type' => 'int',
					'constraint' => 11,
					'null' => false,
					'default' => 0,
				),
			'custom_field_label' => array(
					'type' => 'varchar',
					'constraint' => 50,
					'null' => false,
				),
			'custom_field_data' => array(
					'type' => 'text',
					'null' => true,
				),
		);
		
		$this->dbforge->add_key('custom_field_id');
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('custom_field_values',true);
		unset($fields);

		#------------------------------------------------------
		# Update data
		#------------------------------------------------------
		//Apply order_status_id, dispatch_date, dispatch_email
		$this->db->select('order_id, order_status, date_dispatched');
		$query = $this->db->get('orders');
		
		foreach($query->result() as $order) {
			switch($order->order_status) {
				case 'Completed';
				case 'Dispatched';
				case 'Dispatched Awaiting Payment';
				case 'Invoiced';
				case 'Packing';
				case 'Part Refunded';
				case 'Picking':
					$order_status_id = 2;
					break;
					
				case 'Payment Failed';
				case 'Refunded';
				case 'Cancelled';
				case 'Returned':
					$order_status_id = 1;
					break;
		
				default:
					$order_status_id = 0;
					break;
			}
			
			$data = array(
				'order_status_id' => $order_status_id,
				'dispatch_date' => $order->date_dispatched,
				'dispatch_email' => $order->date_dispatched,
			);
			$this->db->where('order_id', $order->order_id);
			$this->db->update('orders', $data);
		}
	
		unset($data);
		unset($query);

		//Apply collection_items
		$this->db->select('collection_id, collection_items');
		$query = $this->db->get('collections');
		
		foreach ($query->result() as $collection) {
		
			$products = explode(';', $collection->collection_items);
			
			foreach ($products as $product_id) {
				if (!empty($product_id)) {
					$i++;
					$data = array(
						'collection_id' => $collection->collection_id,
						'product_id' => $product_id,
						'order'	=> $i,
					);
					
					$this->db->insert('collection_items', $data);
				}
			}
		
		}
		
		unset($query);
		unset($data);

		//Apply inventory related items
		$this->db->select('product_id, related_items');
		$this->db->where('related_items is not null');
		$query = $this->db->get('inventory');
		
		foreach ($query->result() as $item) {
		
			$products = explode(';', $item->related_items);
			
			foreach ($products as $product_id) {
				if (!empty($product_id)) {
					$data = array(
						'product_id' => $item->product_id,
						'xitem_id' => $product_id,
						'type'	=> 'R',
					);
					
					$this->db->insert('xitems', $data);
				}
			}
		
		}

		$this->db->select('product_id, other_items');
		$this->db->where('other_items is not null');
		$query = $this->db->get('inventory');
		
		foreach ($query->result() as $item) {
		
			$products = explode(';', $item->other_items);
			
			foreach ($products as $product_id) {
				if (!empty($product_id)) {
					$data = array(
						'product_id' => $item->product_id,
						'xitem_id' => $product_id,
						'type'	=> 'S',
					);
					
					$this->db->insert('xitems', $data);
				}
			}
		
		}

		unset($query);
		unset($data);

		//Apply custom fields of invoice_payment_date, part_refund_date on orders
		/*
		$data = array(
			'custom_field_for' => 'orders',
			'custom_field_label' => 'custom_invoice-payment-date',
			'custom_field_title' => 'Invoice Payment Date',
			'custom_field_type' => 'date',
		);
		
		$this->db->insert('custom_field_templates', $data);
		unset($data);
		
		$this->db->select('order_id, invoice_payment_date');
		$this->db->where('invoice_payment_date is not null');
		$query = $this->db->get('orders');
		foreach($query->result() as $custom) {
			$data = array(
				'id' => $custom->order_id,
				'custom_field_label' => 'custom_invoice-payment-date',
				'custom_field_data' => $custom->invoice_payment_date,
			);
			$this->db->insert('custom_field_values', $data);
		}
		unset($query);
		unset($data);
		
		$data = array(
			'custom_field_for' => 'orders',
			'custom_field_label' => 'custom_part-refund-date',
			'custom_field_title' => 'Part Refund Date',
			'custom_field_type' => 'date',
		);
		
		$this->db->insert('custom_field_templates', $data);
		unset($data);

		$this->db->select('order_id, part_refund_date');
		$this->db->where('part_refund_date is not null');
		$query = $this->db->get('orders');
		foreach($query->result() as $custom) {
			$data = array(
				'id' => $custom->order_id,
				'custom_field_label' => 'custom_part-refund-date',
				'custom_field_data' => $custom->part_refund_date,
			);
			$this->db->insert('custom_field_values', $data);
		}
		unset($query);
		unset($data);
		*/

	} //end: function index()
}