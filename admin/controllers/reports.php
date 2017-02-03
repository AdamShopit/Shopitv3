<?php
class Reports extends CI_Controller {
	
	function Reports() {
		parent::__construct();

		$this->load->database();
		
		$this->load->helper('file');
		$this->load->model('settings_model');
		$this->load->model('reports_model');
		$this->load->model('orders_model');
		$this->load->library('pagination');	

		$this->settings_model->initConfig();

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

		$this->permissions->access('can_access_reports');

	}

	#------------------------------------------------------
	# Index page (Main template for reports)
	#------------------------------------------------------
	function index() {

		$data['title']	 = 'Reports';
		$data['content'] = 'reports/reports';
		
		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Welcome page
	#------------------------------------------------------
	function welcome() {
		$data['report_title'] = "Select a report";
		$data['content'] = 'reports/welcome';
		$this->load->view('reports/template',$data);
	}

	#------------------------------------------------------
	# This week's sales
	#------------------------------------------------------
	function weeksales() {
	
		//Get current week monday and sunday
		$week = get_mondayandsunday(date('Y-m-d'));
		$data['from'] = $week->monday;
		$data['to']   = $week->sunday;
	
		$data['sales']  = $this->reports_model->weekSales();
		$data['totals'] = $this->reports_model->weekSalesTotals();
		
		$data['report_title'] = "This week's sales";
		$data['content'] = 'reports/weeksales';
		$this->load->view('reports/template',$data);
		
	}

	#------------------------------------------------------
	# Weekly sales
	#------------------------------------------------------
	function weeklysales() {
		
		$from = $data['from'] = $this->input->post('s_from_year') . '-' . $this->input->post('s_from_month') . '-01 00:00:00';
		$to = $data['to'] = $this->input->post('s_from_year') . '-' . $this->input->post('s_from_month') . date('-t',strtotime($from)) . ' 23:59:59';
	
		if ($this->input->post('s_from_month')) {
		$data['sales'] = $this->reports_model->weeklySales($from, $to);
		$data['totals'] = $this->reports_model->weeklySalesTotals($from, $to);
		}
		
		$data['report_title'] = "Weekly sales";
		$data['content'] = 'reports/weeklysales';
		$this->load->view('reports/template',$data);
	
	}

	#------------------------------------------------------
	# Monthly sales
	#------------------------------------------------------
	function monthlysales() {
		
		$from = $data['from'] = $this->input->post('s_from_year') . '-01-01 00:00:00';
		$to = $data['to'] = $this->input->post('s_from_year') . '-12-31 23:59:59';
	
		if ($this->input->post('s_from_year')) {
		$data['sales'] = $this->reports_model->monthlySales($from, $to);
		$data['totals'] = $this->reports_model->monthlySalesTotals($from, $to);
		}
		
		$data['report_title'] = "Monthly sales";
		$data['content'] = 'reports/monthlysales';
		$this->load->view('reports/template',$data);
		
	}

	#------------------------------------------------------
	# Yearly sales
	#------------------------------------------------------
	function yearlysales() {
		
		$data['sales'] = $this->reports_model->yearlySales();
		$data['totals'] = $this->reports_model->yearlySalesTotals();
		
		$data['report_title'] = "Yearly sales";
		$data['content'] = 'reports/yearlysales';
		$this->load->view('reports/template',$data);
		
	}

	#------------------------------------------------------
	# Sales summary
	#------------------------------------------------------
	function salessummary() {
		
		$today 		= $data['today_date'] 	 = date('Y-m-d', strtotime('today'));
		$yesterday 	= $data['yesterday_date'] = date('Y-m-d', strtotime('yesterday'));

		//Get Monday/Sunday for last week
		$last_week = get_mondayandsunday( get_last_sunday() );

		$thismonth_from = $data['thismonth_from'] = date('Y-m-01 00:00:00', strtotime('today'));
		$thismonth_to 	= $data['thismonth_to']   = date('Y-m-t 23:59:59',strtotime($thismonth_from));
		
		$lastmonth_from = $data['lastmonth_from'] = date('Y-m-01 00:00:00',strtotime('last month'));
		$lastmonth_to 	= $data['lastmonth_to'] = date('Y-m-t 23:59:59',strtotime('last month'));
		
		//Yearly quarters
		$q1_from = $data['q1_from'] = date('Y-01-01 00:00:00',strtotime('this year'));
		$q1_to	 = $data['q1_to'] 	= date('Y-03-31 23:59:59',strtotime('this year'));
		$q2_from = $data['q2_from'] = date('Y-04-01 00:00:00',strtotime('this year'));
		$q2_to	 = $data['q2_to'] 	= date('Y-06-30 23:59:59',strtotime('this year'));
		$q3_from = $data['q3_from'] = date('Y-07-01 00:00:00',strtotime('this year'));
		$q3_to	 = $data['q3_to'] 	= date('Y-09-30 23:59:59',strtotime('this year'));
		$q4_from = $data['q4_from'] = date('Y-10-01 00:00:00',strtotime('this year'));
		$q4_to	 = $data['q4_to'] 	= date('Y-12-31 23:59:59',strtotime('this year'));

		$thisyear_from = $data['thisyear_from'] = date('Y-01-01 00:00:00',strtotime('this year'));
		$thisyear_to   = $data['thisyear_to'] 	= date('Y-12-31 23:59:59',strtotime('this year'));

		$data['today']	   = $this->reports_model->theDay($today);
		$data['yesterday'] = $this->reports_model->theDay($yesterday);
		$data['thisweek']  = $this->reports_model->weeklySalesTotals();
		$data['lastweek']  = $this->reports_model->weeklySalesTotals($last_week->monday . " 00:00:00", $last_week->sunday . " 23:59:59");
		$data['thismonth'] = $this->reports_model->monthlySalesTotals($thismonth_from, $thismonth_to);
		$data['lastmonth'] = $this->reports_model->monthlySalesTotals($lastmonth_from, $lastmonth_to);
		
		$data['total_q1']  		= $this->reports_model->totalQuarter($q1_from, $q1_to);
		$data['total_q2']  		= $this->reports_model->totalQuarter($q2_from, $q2_to);
		$data['total_q3']  		= $this->reports_model->totalQuarter($q3_from, $q3_to);
		$data['total_q4']  		= $this->reports_model->totalQuarter($q4_from, $q4_to);
		$data['total_alltime']  = $this->reports_model->totalRevenue();
		$data['total_thisyear'] = $this->reports_model->monthlySalesTotals($thisyear_from, $thisyear_to);

		$data['report_title'] = "Sales Summary";
		$data['content'] = 'reports/salessummary';
		$this->load->view('reports/template',$data);
	
	}

	#------------------------------------------------------
	# Quarterly sales (Financial)
	#------------------------------------------------------
	function quarterlysales() {
	
		$year_to	= $this->input->post('s_from_year'); //posted value
		$year_from 	= $year_to - 1;
		
		//Yearly quarters
		$q1_from = $data['q1_from'] = $year_from . '-04-06 00:00:00';
		$q1_to	 = $data['q1_to'] 	= $year_from . '-06-30 23:59:59';
		$q2_from = $data['q2_from'] = $year_from . '-07-01 00:00:00';
		$q2_to	 = $data['q2_to'] 	= $year_from . '-09-30 23:59:59';
		$q3_from = $data['q3_from'] = $year_from . '-10-01 00:00:00';
		$q3_to	 = $data['q3_to'] 	= $year_from . '-12-31 23:59:59';
		$q4_from = $data['q4_from'] = $year_to . '-01-01 00:00:00';
		$q4_to	 = $data['q4_to'] 	= $year_to . '-04-05 23:59:59';

		$thisyear_from = $data['thisyear_from'] = $q1_from;
		$thisyear_to   = $data['thisyear_to'] 	= $q4_to;

		$data['total_q1']  		= $this->reports_model->totalQuarter($q1_from, $q1_to);
		$data['total_q2']  		= $this->reports_model->totalQuarter($q2_from, $q2_to);
		$data['total_q3']  		= $this->reports_model->totalQuarter($q3_from, $q3_to);
		$data['total_q4']  		= $this->reports_model->totalQuarter($q4_from, $q4_to);
		$data['total_thisyear'] = $this->reports_model->totalRevenue($thisyear_from, $thisyear_to);

		$data['report_title'] = "Quarterly Sales (Financial)";
		$data['content'] = 'reports/quarterlysales';
		$this->load->view('reports/template',$data);
	
	}
	
	#------------------------------------------------------
	# Most sold (products)
	#------------------------------------------------------
	function mostsold() {
	
		$get = 100;
		
		$data['mostsold'] = $this->reports_model->mostSold($get);
		
		$data['report_title'] = "$get Most Sold Items";
		$data['content'] = 'reports/mostsold';
		$this->load->view('reports/template',$data);
	
	}

	#------------------------------------------------------
	# Most viewed (products)
	#------------------------------------------------------
	function mostviewed() {

		$get = 100;
	
		$data['mostviewed'] = $this->reports_model->mostViewed($get);
		
		$data['report_title'] = "$get Most Viewed Items";
		$data['content'] = 'reports/mostviewed';
		$this->load->view('reports/template',$data);
		
	}

	#------------------------------------------------------
	# Popular searches
	#------------------------------------------------------
	function popularsearches() {

		$get = 100;
		
		$data['searches'] = $this->reports_model->popularSearches($get);
		
		$data['report_title'] = "Most popular searches";
		$data['content'] = 'reports/popularsearches';
		$this->load->view('reports/template',$data);
	
	}
	
	#------------------------------------------------------
	# Top customers
	#------------------------------------------------------
	function topcustomers() {

		$get = 100;

		$data['customers'] = $this->reports_model->topCustomers($get);
		
		$data['report_title'] = "Top customers";
		$data['content'] = 'reports/topcustomers';
		$this->load->view('reports/template',$data);
	
	}
		
}