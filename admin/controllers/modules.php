<?php
class Modules extends CI_Controller {
	
	function Modules() {
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
	}
	
	function index() {
		redirect('dashboard');
	}

	#------------------------------------------------------
	# Module: //!Coupons
	#------------------------------------------------------
	function coupons() {
	
		$this->permissions->access('can_module_coupons');

		$this->load->dbforge();
		
		switch ($this->uri->segment(3)) {

			// Delete a special offer
			case 'delete':
				$this->modules_model->deleteCoupon($this->uri->segment(4));
				break;
				
			// Bulk apply coupon
			case 'apply':
				// Decode uri segment 4 which contains the coupon's 
				// database id and value to set
				$segment = base64_decode($this->uri->segment(4));
				$coupon = explode('-', $segment);
				$this->modules_model->applyCouponToAllProducts("coupon_$coupon[0]", $coupon[1]);
				
				// Redirect
				$message = ($coupon[1] == "1") ? "applied to" : "revoked from";
				$this->session->set_flashdata('notice', sprintf('Coupon %s all products.', $message));
				redirect('modules/coupons');
				break;

			// Create/edit a special offer
			default:

				$this->form_validation->set_message('required', 'required');
				$this->form_validation->set_message('valid_email', 'invalid email');
				$this->form_validation->set_message('max_length', ' ');
				$this->form_validation->set_message('exact_length', ' ');
				$this->form_validation->set_message('numeric', 'invalid');
				$this->form_validation->set_message('matches', 'not matching!');
				$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

				$this->form_validation->set_rules('coupon_label', 'Label', 'trim|required');
				$this->form_validation->set_rules('coupon_code', 'Code', 'trim|required');
				$this->form_validation->set_rules('coupon_discount', 'Discount', 'trim|required');
				$this->form_validation->set_rules('coupon_maxspend', 'Max spend', 'trim|numeric');
				$this->form_validation->set_rules('coupon_expires', 'Expiry date', 'trim');
				$this->form_validation->set_rules('coupon_maxuses', 'Max uses', 'trim|numeric');
		
				if ($this->form_validation->run() == FALSE):
		
					$data['title']	 = 'Inventory > Coupons';

					$data['coupons'] = $this->modules_model->getCoupons();

					//Get special offer data to edit
					if ($this->uri->segment(3) == 'edit') {
					$data['edit'] = $this->modules_model->getCoupon($this->uri->segment(4));
					}
			
					$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
					$data['form_title'] = 'Manage Coupons';
					$data['form_close'] = '</form>';
					if ($this->uri->segment(3) == 'edit') {
						$data['form_cancel_link'] = site_url('modules/coupons');
					} else {
						$data['form_cancel_link'] = site_url('inventory');
					}
					
					$data['content'] = 'modules/coupons';
					$this->load->view('global/template',$data);

				else:
				
					//Save and redirect
					if ($this->input->post('coupon_id') != '') {
						$this->modules_model->updateCoupon($this->input->post('coupon_id'));
					} else {
						$this->modules_model->createCoupon();
					}
					
					$this->session->set_flashdata('notice','Coupon updated.');
					redirect('modules/coupons');
				
				endif;
	
				break;
		
		}
	
	}
	
}