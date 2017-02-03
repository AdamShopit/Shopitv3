<?php

class Dashboard extends CI_Controller {

	function Dashboard()
	{
		parent::__construct();

		$this->load->database();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */
		
		$this->load->model('dashboard_model');
		$this->load->model('reports_model');
		$this->load->model('orders_model');
		$this->load->helper('text');
		$this->load->dbforge();
		$this->load->helper('cookie');
		$this->load->library('widgets');
	}
	
	#------------------------------------------------------
	# The Dashboard itself
	#------------------------------------------------------
	function index()
	{

		//Get Monday/Sunday for last week
		$last_week = get_mondayandsunday( get_last_sunday() );
				
		//Check for user widgets
		$data['widgets'] = $this->dashboard_model->hasWidgets(); //true or false
		
		//Get my widgets
		$data['mywidgets'] = $mywidgets = $this->dashboard_model->myWidgets();
		
		if ($mywidgets > 0):
			$i = 0;
			foreach ($mywidgets as $mywidget) {
				
				$i++; //(A)
				
				if ($i&1) { 
					$data['mywidgets_1'][] = array(
						'id' 	 => $mywidget->id,
						'widget' => $mywidget->widget,
					);
				} else {
					$data['mywidgets_2'][] = array(
						'id' 	 => $mywidget->id,
						'widget' => $mywidget->widget,
					);
				} //(A);
				
			}
		endif;

		//New orders
		$data['newOrders'] = $this->reports_model->newOrders(14);
		
		//Stock alerts
		$data['lowStock'] = $this->reports_model->stockAlerts();

		//Most viewed
		$data['mostViewed']  = $this->reports_model->mostViewed();
		$data['totalViewed'] = $this->reports_model->totalViewed();
		//End.
			
		//Most sold
		$data['mostSold']  = $this->reports_model->mostSold();
		$data['totalSold'] = $this->reports_model->totalSold();
		//End.
		
		//Popular searches
		$data['popularSearches'] = $this->reports_model->popularSearches();
		$data['totalSearches']   = $this->reports_model->totalSearches();
		//End.

		//Undispatched orders
		$data['undispatchedOrders'] = $this->reports_model->undispatchedOrders(0);
		
		//Sales summary
		$today 		= $data['today_date'] 	 = date('Y-m-d', strtotime('today'));
		$yesterday 	= $data['yesterday_date'] = date('Y-m-d', strtotime('yesterday'));

		$thismonth_from = $data['thismonth_from'] = date('Y-m-01 00:00:00', strtotime('today'));
		$thismonth_to 	= $data['thismonth_to']   = date('Y-m-t 23:59:59',strtotime($thismonth_from));
		
		$lastmonth_from = $data['lastmonth_from'] = date('Y-m-01 00:00:00', strtotime('first day of previous month'));
		$lastmonth_to 	= $data['lastmonth_to']   = date('Y-m-t 23:59:59', strtotime('first day of previous month'));
		
		$thisyear_from = $data['thisyear_from'] = date('Y-01-01 00:00:00',strtotime('this year'));
		$thisyear_to   = $data['thisyear_to'] 	= date('Y-12-31 23:59:59',strtotime('this year'));
		
		$lastweek_from = "$last_week->monday 00:00:00";
		$lastweek_to   = "$last_week->sunday 23:59:59";

		$data['today']	   = $this->reports_model->theDay($today);
		$data['yesterday'] = $this->reports_model->theDay($yesterday);
		$data['thisweek']  = $this->reports_model->weeklySalesTotals();
		$data['lastweek']  = $this->reports_model->weeklySalesTotals($lastweek_from, $lastweek_to);
		$data['thismonth'] = $this->reports_model->monthlySalesTotals($thismonth_from, $thismonth_to);
		$data['lastmonth'] = $this->reports_model->monthlySalesTotals($lastmonth_from, $lastmonth_to);
		
		$data['total_alltime']  = $this->reports_model->totalRevenue();
		$data['total_thisyear'] = $this->reports_model->monthlySalesTotals($thisyear_from, $thisyear_to);

		$data['total_thismonth'] = $this->reports_model->totalRevenue($thismonth_from, $thismonth_to);
		$data['total_lastmonth'] = $this->reports_model->totalRevenue($lastmonth_from, $lastmonth_to);

		//Todays/yesterdays orders
		$data['todaysOrders'] = $this->reports_model->theDaysOrders($today);
		$data['yesterdaysOrders'] = $this->reports_model->theDaysOrders($yesterday);

		//For dashboard alert
		$data['todaysOrderCount'] = $this->reports_model->theDay($today)->no_orders;
		
		//Chart data
		$data['chart_this_year'] = $this->reports_model->chartMonthlyTotals( date('Y', time()) );
		$data['chart_last_year'] = $this->reports_model->chartMonthlyTotals( date('Y', time()) - 1 );

		//Activity feed
		$data['activityfeed'] = $this->reports_model->realTime();

		//Send data to dashboard
		$data['title']	 = 'Dashboard';
		$data['content'] = 'dashboard/dashboard';
		$this->load->view('global/template', $data);
	}

	#------------------------------------------------------
	# Save dashboard customisations
	#------------------------------------------------------
	function save() {
	
		$this->dashboard_model->saveWidgets();
		redirect('dashboard');
	
	}

	#------------------------------------------------------
	# Real-Time widget - Ajax
	#------------------------------------------------------
	function realtime() {

		//Set no caching
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
		header("Cache-Control: no-store, no-cache, must-revalidate"); 
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		$data['activityfeed'] = $this->reports_model->realTime();
		$this->load->view('dashboard/widgets/activityfeed', $data);
#		echo "<pre>" . print_r($data, true) . "</pre>";
		
	}

}