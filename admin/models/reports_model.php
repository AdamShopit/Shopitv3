<?php

class Reports_model extends CI_Model {
	
	function Reports_model() {
		parent::__construct();
	}

	//! General Reports & Dashboard Widgets

	#------------------------------------------------------
	# Totals
	#------------------------------------------------------
	//Total revenue all time
	function totalRevenue($from=null, $to=null) {

		$sql = "SELECT
					COUNT(order_id) AS no_orders,
					SUM(order_total) AS	items,
					SUM(order_shipping) AS shipping,
					SUM(order_vat) AS tax,
					SUM(qty) AS products
				FROM (	
					SELECT
						order_id,
						order_total,
						order_shipping,
						order_vat,
						(SELECT SUM(product_qty) FROM orders_inventory WHERE order_id = orders.order_id) AS qty
					FROM orders
					WHERE order_status_id = 2 ";
		if ($from != null && $to !=null):
		$sql .= "AND order_date >= '$from'
				AND order_date <= '$to' ";
		endif;
		$sql .= ") AS totals";

		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	//Total per quarter
	function totalQuarter($from=null, $to=null) {

		$this->db->select('count(order_id) as no_orders');
		$this->db->select_sum('order_total', 'items');
		$this->db->select_sum('order_shipping', 'shipping');
		$this->db->select_sum('order_vat', 'tax');
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}

	}


	#------------------------------------------------------
	# Report: The Day's Sales
	#------------------------------------------------------
	function theDay($date) {
		
		$day_start = $date . ' 00:00:00';
		$day_end = $date . ' 23:59:59';
		
		$this->db->select('count(order_id) as no_orders');
		$this->db->select_sum('order_total', 'items');
		$this->db->select_sum('order_shipping', 'shipping');
		$this->db->select_sum('order_vat', 'tax');
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $day_start);
		$this->db->where('order_date <=', $day_end);
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Report: Weekly Sales
	#------------------------------------------------------
	function weekSales($from=null, $to=null) {
		
		$this->db->select('order_ref, order_date, order_total, order_shipping, order_vat, (order_total + order_shipping + order_vat) as total');
		$this->db->where('order_status_id','2');
		if (!empty($from) && !empty($to)) {
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		} else {
		$today = date('Y-m-d');
		$this->db->where("order_date >= DATE(DATE_ADD('$today -7', interval 0-weekday('$today') day))");
		$this->db->where("order_date <= DATE(DATE_ADD('$today -7', interval 6-weekday('$today') day))");
		}
		$this->db->order_by('order_date','asc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}

	}

	function weekSalesTotals($from=null, $to=null) {
	
		$this->db->select_sum('order_total', 'items');
		$this->db->select_sum('order_shipping', 'shipping');
		$this->db->select_sum('order_vat', 'tax');
		$this->db->where('order_status_id','2');
		if (!empty($from) && !empty($to)) {
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		} else {
		$today = date('Y-m-d');
		$this->db->where("order_date >= DATE(DATE_ADD('$today -7', interval 0-weekday('$today') day))");
		$this->db->where("order_date <= DATE(DATE_ADD('$today -7', interval 6-weekday('$today') day))");
		}
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Report: Weekly Sales
	# - Week should be from Monday to Sunday
	#------------------------------------------------------
	function weeklySales($from=null, $to=null) {

		$this->db->select('WEEKOFYEAR(order_date) as week_no, MONTH(order_date) as month_no, YEAR(order_date) as year_no, sum(order_total) as order_total, sum(order_shipping) as order_shipping, sum(order_vat) as order_vat, sum(order_total + order_shipping + order_vat) as total, count(order_id) as no_orders, DATE(DATE_ADD(order_date, interval 0-weekday(order_date) day)) as week_monday', false);
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		$this->db->group_by('week_monday');
		$this->db->order_by('order_date');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	function weeklySalesTotals($from=null, $to=null, $week_no=null) {

		$this->db->select('DATE(DATE_ADD(order_date, interval 0-weekday(order_date) day)) as week_monday, WEEK(order_date,7) as week_no, MONTH(order_date) as month_no, YEAR(order_date) as year_no, count(order_id) as no_orders', false);
		$this->db->select_sum('order_total', 'items');
		$this->db->select_sum('order_shipping', 'shipping');
		$this->db->select_sum('order_vat', 'tax');
		$this->db->where('order_status_id', 2);
		if ($from == null && $to == null) {
			$this->db->where('WEEK(order_date,7) = WEEK(CURDATE(),7) '.$week_no);
			$this->db->where('YEAR(order_date) = YEAR(CURDATE())');
		} else {
			$this->db->where('order_date >=', $from);
			$this->db->where('order_date <=', $to);
		}
		$this->db->order_by('year_no','asc');
		$this->db->order_by('week_no','asc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Report: Monthly Sales
	#------------------------------------------------------
	function monthlySales($from=null, $to=null) {

		$this->db->select('MONTH(order_date) as month_no, MONTHNAME(order_date) as month_name, YEAR(order_date) as year_no, sum(order_total) as order_total, sum(order_shipping) as order_shipping, sum(order_vat) as order_vat, sum(order_total + order_shipping + order_vat) as total, count(order_id) as no_orders', false);
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		$this->db->group_by('MONTH(order_date)');
		$this->db->order_by('year_no','asc');
		$this->db->order_by('month_no','asc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	function monthlySalesTotals($from=null, $to=null) {

		$this->db->select('count(order_id) as no_orders');
		$this->db->select_sum('order_total', 'items');
		$this->db->select_sum('order_shipping', 'shipping');
		$this->db->select_sum('order_vat', 'tax');
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		$this->db->group_by('YEAR(order_date)');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Report: Yearly Sales
	#------------------------------------------------------
	function yearlySales() {

		$this->db->select('YEAR(order_date) as year_no, sum(order_total) as order_total, sum(order_shipping) as order_shipping, sum(order_vat) as order_vat, sum(order_total + order_shipping + order_vat) as total, count(order_id) as no_orders', false);
		$this->db->where('order_status_id','2');
		$this->db->group_by('YEAR(order_date)');
		$this->db->order_by('year_no','desc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	function yearlySalesTotals() {

		$this->db->select('count(order_id) as no_orders');
		$this->db->select_sum('order_total', 'items');
		$this->db->select_sum('order_shipping', 'shipping');
		$this->db->select_sum('order_vat', 'tax');
		$this->db->where('order_status_id','2');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Report/Widget: Most Sold Items
	#------------------------------------------------------
	function mostSold($limit=10) {

		$this->db->select('
			product_id,
			product_no,
			count(product_id) as product_count,
			product_name,
			sum(product_price) as total
		', false);
		$this->db->from('orders_inventory');
		$this->db->where('product_price > ', 0);
		$this->db->where('(select order_status_id from orders where order_id = orders_inventory.order_id) = 2');
		$this->db->group_by('product_id');
		$this->db->order_by('product_count','desc');
		if ($limit > 0) {
		$this->db->limit($limit);
		}
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
	
	}

	function totalSold() {
		
		$sql = 'select sum(product_qty) as totalsold
				from 
					(select product_qty
					from orders_inventory
					where product_price > 0
					and (select order_status_id from orders where order_id = orders_inventory.order_id) = 2
					order by product_qty desc
					)
					as subquery';

		$query = $this->db->query($sql);
		return $query->row()->totalsold;
		
	}

	#------------------------------------------------------
	# Report/Widget: Most Viewed Items
	#------------------------------------------------------
	function mostViewed($limit=10) {
	
		$this->db->select('product_no, product_name, product_views');
		$this->db->where('product_views > 0');
		$this->db->where('product_disabled', 0);
		$this->db->where('archived', 0);
		$this->db->order_by('product_views','desc');
		if ($limit > 0) {
		$this->db->limit($limit);
		}
		
		$query = $this->db->get('inventory');
				
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	
	}
	
	function totalViewed($limit=10) {
		
		$sql = 'select sum(product_views) as totalviews
				from (select product_views
					  from inventory
					  where product_views > 0
					  order by product_views desc
					  limit '.$limit.')
				as subquery';

		$query = $this->db->query($sql);
		return $query->row()->totalviews;
		
	}

	#------------------------------------------------------
	# Report/Widget: Popular Searches
	#------------------------------------------------------
	function popularSearches($limit=10) {
		
		$this->db->select('term, count(term) as searches');
		$this->db->group_by('term');
		$this->db->order_by('searches','desc');
		if ($limit > 0) {
		$this->db->limit($limit);
		}		
		$query = $this->db->get('searches');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	
	}

	function totalSearches() {
	
		$query = $this->db->get('searches');
		return $query->num_rows();
		
	}

	#------------------------------------------------------
	# Report: Top Customers
	#------------------------------------------------------
	function topCustomers($limit=10) {
				
		$this->db->select('order_id, CONCAT_WS(" ", billing_title, billing_firstname, billing_surname) as billing_name, billing_city, customer_email, max(order_date) as last_order, (select count(t1.order_id) from orders t1 where (t1.order_status_id = 2) and t1.billing_address1 = t2.billing_address1 and t1.billing_city = t2.billing_city) as orders', false);
		$this->db->from('orders t2');

		if ($limit > 0) {
		$this->db->limit($limit);
		}
		$this->db->group_by('billing_address1,billing_city');
		$this->db->order_by('orders desc');

		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}

	}

	#------------------------------------------------------
	# Widget: New orders
	#------------------------------------------------------
	function newOrders($limit=6) {
		
		$this->db->select('order_id, order_date, billing_firstname, billing_surname, order_status, (order_total + order_shipping + order_vat) as total', false);
		$this->db->where('order_status_id','2');
		$this->db->order_by('order_date', 'desc');
		if ($limit > 0) {
		$this->db->limit($limit);
		}
		$query = $this->db->get('orders');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
		
	}

	#------------------------------------------------------
	# Widget: Undispatched orders
	#------------------------------------------------------
	function undispatchedOrders($limit=6) {
		
		$status = $this->orders_model->getDefaultStatus('Dispatched');
		
		$this->db->select('order_id, order_date, billing_firstname, billing_surname, order_status, (order_total + order_shipping + order_vat) as total', false);
		$this->db->where('order_status_id','2');
		$this->db->where('order_status !=', $status->value);
		$this->db->where('dispatch_date is null');
		$this->db->order_by('order_date', 'desc');
		if ($limit > 0) {
		$this->db->limit($limit);
		}
		$query = $this->db->get('orders');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
		
	}

	#------------------------------------------------------
	# Widget: Stock alerts
	#------------------------------------------------------
	function stockAlerts($limit=6) {
	
		$this->db->where('location_1 < 5');
		$this->db->order_by('location_1', 'asc');
		if ($limit > 0) {
		$this->db->limit($limit);
		}
		$query = $this->db->get('inventory');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Widget: The Day's Sales
	# - used for any date
	#------------------------------------------------------
	function theDaysOrders($date) {
		
		$day_start = $date . ' 00:00:00';
		$day_end = $date . ' 23:59:59';
		
		$this->db->select('order_id, order_date, billing_firstname, billing_surname, order_status, (order_total + order_shipping + order_vat) as total', false);
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $day_start);
		$this->db->where('order_date <=', $day_end);
		$this->db->order_by('order_date','asc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Chart: Get monthly sales totals for this year
	#------------------------------------------------------
	function chartMonthlyTotals($year) {
		
		$is_this_year = ($year == date('Y')) ? true : false;
		
		if (!$is_this_year) {
			$this->db->cache_on();
		}
		
		// Loop through each month and get the data
		for ($i = 1; $i <= 12; $i++) {
		
			// Get what we need from the database
			$this->db->select("sum(order_total + order_shipping + order_vat) as total");
			$this->db->from('orders');
			$this->db->where('MONTH(order_date)', $i);
			$this->db->where('YEAR(order_date)', $year);
			$this->db->where('order_status_id', 2);
			$query = $this->db->get();
			
			// Create a new array (and round up the figure to non-decimal)
			$totals[] = ceil($query->row()->total);
			
			// Clear some memory
			$query->free_result();
		
		}
		
		// Convert the array into a comma-separated string
		$data = implode(',', $totals);

		$this->db->cache_off();
		
		// Return the array
		return $data;
		
	}

	#------------------------------------------------------
	# Panic Statusboard: Get monthly sales totals 
	# for this year
	#------------------------------------------------------
	function statusboard($year) {
		
		$is_this_year = ($year == date('Y')) ? true : false;
		
		if (!$is_this_year) {
			$this->db->cache_on();
		}
		
		// Loop through each month and get the data
		for ($i = 1; $i <= 12; $i++) {
		
			// Get what we need from the database
			$this->db->select("sum(order_total + order_shipping + order_vat) as total");
			$this->db->from('orders');
			$this->db->where('MONTH(order_date)', $i);
			$this->db->where('YEAR(order_date)', $year);
			$this->db->where('order_status_id', 2);
			$query = $this->db->get();
			
			// Create this month's date
			$date = strtotime("$year-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-01");
			
			// Sort the total
			$amount = ($query->row()->total > 0) ? ceil($query->row()->total) : 0;
			
			// Create a new array
			$totals[] = '{ "title" : "' . date('F', $date) . '", "value" : ' . $amount . ' }';			

			// Clear some memory
			$query->free_result();
		
		}
		
		// Convert the array into a comma-separated string
		$data = implode(',', $totals);

		$this->db->cache_off();
		
		// Return the array
		return $data;
		
	}

	#------------------------------------------------------
	# Today's activity in realtime
	#------------------------------------------------------
	function realTime($date=NULL) {
		
		if ($date == NULL) {
			$date = date('Y-m-d', strtotime('today'));
		}
		
		// Set the date range
		$day_start = $date . ' 00:00:00';
		$day_end   = $date . ' 23:59:59';

		// The SQL
		$sql = "select concat('<strong>&ldquo;', product_name, '&rdquo;</strong> added to basket') as message, product_id as myid, basket_date as 
				realtime_date, 'basket' as message_type
				from basket
				where basket_date >= '$day_start'
				and basket_date <= '$day_end'
				UNION
				select 
					concat(
						billing_firstname, 
						' ', 
						billing_surname, 
						' <strong>paid ".$this->config->item('currency')."', 
						(CASE WHEN transaction_total > 0 THEN transaction_total ELSE (order_total + order_shipping + order_vat) END), 
						'</strong> via ',
						transaction_type
						) as message, 
					order_id as myid, 
					order_date as realtime_date, 
					'order' as message_type
				from orders
				where order_date >= '$day_start'
				and order_date <= '$day_end'
				and order_status_id = 2
				UNION
				select concat('Searched for <strong>&ldquo;', term, '&rdquo;</strong>') as message, '' as myid, timestamp as realtime_date, 'search' as message_type
				from searches
				where timestamp >= '$day_start'
				and timestamp <= '$day_end'
				order by realtime_date desc";
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
		
	}

}