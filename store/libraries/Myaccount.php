<?php 
#------------------------------------------------------
# Module: Login/My Account
#------------------------------------------------------
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Myaccount {

	#------------------------------------------------------
	# Display login box
	# - redirect_url is used to redirect back to
	# - the page we were on
	#------------------------------------------------------
    public function display_login_box($redirect_url=null)
    {
    	$CI =& get_instance();
    	
    	// Set the redirect url
    	if ($redirect_url == null) {
	    	$redirect_url = current_url();
    	}

		$html = '<form action="'.site_url('store/myaccount/login').'" method="post">
		<div id="AccountLogin" class="carttable">
			<div class="shopit-account-login">
				<h3>Got an account? Login here...</h3>' . 
				$CI->session->flashdata('notice') . '
				<div class="carttable-row">
					<label>Email:</label>
					<input type="text" name="AccountEmail" id="AccountEmail" class="email cart-textbox" maxlength="45" value="" autocomplete="off" />
				</div>
				<div class="carttable-row">
					<label>Password:</label>
					<input type="password" name="AccountPass" id="AccountPass" class="cart-textbox" maxlength="35" value="" autocomplete="off" />
				</div>
				<div class="carttable-row">
					<label>&nbsp;</label>
					<input type="submit" name="login" class="btnAccount" value="Login" />
					<input type="hidden" name="redirect_url" value="'.$redirect_url.'" />
					<input type="hidden" name="current_url" value="'.current_url().'" />
					<a href="' . site_url('store/myaccount/remindme') . '">Forgot password?</a>
				</div>
			</div>';
		if (!is_not_basket()) {
		$html .= '<div class="shopit-account-login">
				<h3>Not registered? Continue below</h3>
	
				<p>If you\'ve not registered yet then simply complete your billing and delivery details below. You will also be given the opportunity to create a password for your new account.</p>
			</div>';
		}
		
		$html .= '</div>
		</form>';

		return $html;

    }

	#------------------------------------------------------
	# Forgot password
	# - redirect_url is used to redirect back to
	# - the page we were on
	#------------------------------------------------------
    public function display_forgotpassword_box()
    {
    	$CI =& get_instance();
?>
		<?=$CI->session->flashdata('notice');?>

		<form action="<?=site_url('store/myaccount/remindme');?>" method="post">
		<div id="AccountLogin" class="carttable">
			<h3>Forgot your password?</h3>
			<p>Enter your <strong>account email address</strong> below and we'll send a <strong>new password</strong> direct to your inbox.</p>
			<div class="carttable-row">
				<label>Email:</label>
				<input type="text" name="AccountEmail" id="AccountEmail" class="cart-textbox" value="" maxlength="45" />
			</div>
			<div class="carttable-row">
				<label>&nbsp;</label>
				<input type="submit" name="login" class="btnAccount" value="Reset password" />
				<input type="hidden" name="redirect_url" value="<?=current_url();?>" />
			</div>
		</div>
		</form>
<?php 
    }


	#------------------------------------------------------
	# Creates account (adds details to database)
	#------------------------------------------------------
	public function create_account() {
	
		$CI =& get_instance();
		
		$CI->load->helper('security');
		
		//check for no session i.e. not logged in...AND user has entered a password
		if ($CI->session->userdata('account_user') == '' && $_POST['Password'] != '' ) {
				
			//check if user already exists
			$CI->db->where('account_user',$_POST['Email']);
			$query = $CI->db->get('accounts');

			//Communication Preferences
			$pref_newsletter = (!empty($_POST['pref_newsletter'])) ? 1 : 0;
			
			//if not, add them
			if ($query->num_rows() < 1) {
			
				// Create some easier to use variables
				$firstname  = $CI->security->xss_clean($_POST['BillingFirstname']);
				$lastname   = $CI->security->xss_clean($_POST['BillingSurname']);
				$email		= $CI->security->xss_clean($_POST['Email']);
				
				$data = array(
					'account_user' 		=> $email,
					'account_pass' 		=> password_hash($CI->security->xss_clean($_POST['Password']), PASSWORD_DEFAULT),
					'last_login'   		=> date('Y-m-d H:i:s', time()),
					'account_title'		=> $CI->security->xss_clean($_POST['BillingTitle']),
					'account_firstname' => $firstname,
					'account_surname'	=> $lastname,
					'account_company'	=> $CI->security->xss_clean($_POST['BillingCompany']),
					'account_address1'	=> $CI->security->xss_clean($_POST['BillingAddress1']),
					'account_address2'	=> $CI->security->xss_clean($_POST['BillingAddress2']),
					'account_city'		=> $CI->security->xss_clean($_POST['BillingCity']),
					'account_postcode'	=> $CI->security->xss_clean($_POST['BillingPostcode']),
					'account_country'	=> $CI->security->xss_clean($_POST['BillingCountry']),
					'account_phone'		=> $CI->security->xss_clean($_POST['Phone']),
					'pref_newsletter'	=> $pref_newsletter,
				);
			
				$CI->db->insert('accounts', $data);
				
				// Also add them to the email lists.
				// Setting the $account_id to 0 forces them to be added to the list 
				// as a new customer. The additional check of whether they really need
				// to be added will be donw within the cmSubscribe() function.
				$account_id = 0; 
				cmSubscribe($account_id, $pref_newsletter, $firstname, $lastname, $email);

				// return the new account_id
				return $CI->db->insert_id();
		
			}
			else {
				// Get the existing id
				foreach ($query->result() as $user ){
					return $user->account_id;
				}
			}
		
		//session exists already
		} else {
		
			//return the account_id from the existing session
			return $CI->session->userdata('account_id');
		
		}
				
	}

	#------------------------------------------------------
	# Updates last login record for this user
	#------------------------------------------------------
	function update_lastlogin_time($account_id) {
	
		$CI =& get_instance();
		
		$data = array(
			'last_login' => date('Y-m-d H:i:s', time()),
		);
		
		$CI->db->where('account_id',$account_id);
		$CI->db->update('accounts',$data);
	
	}

	#------------------------------------------------------
	# My Account options
	#------------------------------------------------------
	public function links() {
		
		$CI =& get_instance();
		
		if ($CI->session->userdata('account_loggedin')) {
			$html  = '<li><a href="'.site_url('store/myaccount').'">'.$this->get_info('firstname')."'s Account</a></li>\n";
			$html .= '<li><a href="'.site_url('store/myaccount/logout').'">Logout</a></li>'."\n";
		} else {
			$html = '<li><a href="'.site_url('store/myaccount').'">My Account</a></li>'."\n";
		}
		
		return $html;
		
	}

	#------------------------------------------------------
	# Check: is user logged in?
	# @returns TRUE
	#------------------------------------------------------
	function user_logged_in() {
		
		$shopit =& get_instance();
		
		if ($shopit->session->userdata('account_loggedin')) {
			return TRUE;
		} else {
			return FALSE;
		}
	
	}

	#------------------------------------------------------
	# Get customer's details once they are logged in
	# - works via the session
	#------------------------------------------------------
	function get_info($request = 'firstname') {
	
		$CI =& get_instance();
		
		switch ($request) {
		
			case 'id':
				return $CI->session->userdata('account_id');
				break;

			case 'user';
			case 'email':
				return $CI->session->userdata('account_user');
				break;

			case 'title':
				return $CI->session->userdata('account_title');
				break;
				
			case 'firstname':
				return ucwords($CI->session->userdata('account_firstname'));
				break;
		
			case 'surname':
				return ucwords($CI->session->userdata('account_surname'));
				break;

			case 'company':
				return $CI->session->userdata('account_company');
				break;

			case 'address1':
				return $CI->session->userdata('account_address1');
				break;

			case 'address2':
				return $CI->session->userdata('account_address2');
				break;

			case 'city':
				return $CI->session->userdata('account_city');
				break;

			case 'postcode':
				return $CI->session->userdata('account_postcode');
				break;

			case 'country':
				return $CI->session->userdata('account_country');
				break;

			case 'phone':
				return $CI->session->userdata('account_phone');
				break;

			case 'pref_newsletter':
				return $CI->session->userdata('pref_newsletter');
				break;

		}
	
	}

	#------------------------------------------------------
	# Check user has an account
	# - if true, returns result data
	#------------------------------------------------------
	function checkuser($username, $password) {
		
		$CI =& get_instance();
		$CI->load->helper('security');
		
		// First check if username exists
		$user = $this->checkuser_email($username);

		if (!empty($user)) {
			// Verify submitted password against the user's account. If true, return
			// user details else return false
			if (!empty($password) and password_verify($password, $user->account_pass)) {
				return $user;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	#------------------------------------------------------
	# Check if user email exists
	# - if true, returns result data
	#------------------------------------------------------
	function checkuser_email($username) {
		
		$CI =& get_instance();
		$CI->load->helper('security');
			
		$CI->db->where('account_user',$username);
		$query = $CI->db->get('accounts');
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
	
	}

	#------------------------------------------------------
	# Generate a password
	# - The letter l (lowercase L) and the number 1
	# - have been removed, as they can be mistaken
	# - for each other.
	#------------------------------------------------------
	function generate_password() {

		$chars = "AaBbCcDdEeFfGgHhiJjKkMmNnoPpQqRrSsTtUuVvWwXxYyZz023456789"; 
	    srand((double)microtime()*1000000); 
	    $i = 0; 
	    $password = '' ; 
	
	    while ($i <= 12) { 
	        $num = rand() % 33; 
	        $tmp = substr($chars, $num, 1); 
	        $password = $password . $tmp; 
	        $i++; 
	    } 
	
	    return $password; 
					
	}

	#------------------------------------------------------
	# Attach new password to this user's account
	#------------------------------------------------------
	function attach_password($account_id, $password) {
	
		$CI =& get_instance();
		$CI->load->helper('security');
		
		$data = array(
			'account_pass' => password_hash($password, PASSWORD_DEFAULT),
		);
		$CI->db->where('account_id',$account_id);
		
		if ($CI->db->update('accounts',$data) ) {
			return TRUE;
		}

	}

	#------------------------------------------------------
	# Retrieve user's orders
	# - From last 90 days
	#------------------------------------------------------
	function get_orders($account_id,$limit=4) {
	
		$CI =& get_instance();
		
		$CI->db->where('account_id',$account_id);
		$CI->db->where('DATE_SUB(CURDATE(),INTERVAL 90 DAY) <= date(order_date)','',FALSE);
		$CI->db->where('order_ref IS NOT NULL');
		$CI->db->where('site', $CI->config->item('site'));
		$CI->db->order_by('order_date','desc');
		$query = $CI->db->get('orders');
		
		if ($query->num_rows() > 0 )
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Retrieve order
	#------------------------------------------------------
	function get_order($order_id,$account_id) {
	
		$CI =& get_instance();
		
		$CI->db->where('order_id',$order_id);
		$CI->db->where('account_id',$account_id);
		$CI->db->where('site', $CI->config->item('site'));
		$CI->db->order_by('order_date','desc');
		$query = $CI->db->get('orders');
		
		if ($query->num_rows() > 0 )
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Save updated user details
	# - Also manages the email subscription
	#------------------------------------------------------
	function save_account_updates($account_id) {
	
		$CI =& get_instance();
		
		$CI->load->helper('security');
		$CI->load->model('basket_model');

		// Get countries and find country name from array via submitted value (key)
		$countries = $CI->basket_model->getAllCountries();
		$userCountry = $countries[$_POST['BillingCountry']]->country_name;

		// Define the newsletter preference. If 1, then 'yes' else 'no'.
		$pref_newsletter = ($CI->security->xss_clean($_POST['pref_newsletter']) > 0) ? 1 : 0;
		
		$data = array(
					'account_user' 		=> $CI->security->xss_clean($_POST['Email']),
					//'last_login'   		=> date('Y-m-d H:i:s', time()),
					'account_title'		=> $CI->security->xss_clean($_POST['BillingTitle']),
					'account_firstname' => $CI->security->xss_clean($_POST['BillingFirstname']),
					'account_surname'	=> $CI->security->xss_clean($_POST['BillingSurname']),
					'account_company'	=> $CI->security->xss_clean($_POST['BillingCompany']),
					'account_address1'	=> $CI->security->xss_clean($_POST['BillingAddress1']),
					'account_address2'	=> $CI->security->xss_clean($_POST['BillingAddress2']),
					'account_city'		=> $CI->security->xss_clean($_POST['BillingCity']),
					'account_postcode'	=> $CI->security->xss_clean($_POST['BillingPostcode']),
					'account_country'	=> $CI->security->xss_clean($userCountry),
					'account_phone'		=> $CI->security->xss_clean($_POST['Phone']),
					'pref_newsletter'	=> $pref_newsletter,
				);
		
		//Check if password needs changing
		if ($_POST['Password'] == $_POST['cPassword'] && $_POST['Password']!=''){
			$data['account_pass'] = password_hash($CI->security->xss_clean($_POST['Password']), PASSWORD_DEFAULT);
		}

		// Manage the mailchimp subscription depending on the posted $pref_newsletter field
		if ($pref_newsletter == 0) {
			cmUnsubscribe($data['account_user']);
		} else {
			cmSubscribe(0, 1, $data['account_firstname'], $data['account_surname'], $data['account_user']);
		}
		
		// Update user info
		$CI->db->where('account_id',$account_id);
		if ($CI->db->update('accounts',$data)){
		
			return TRUE;
		
		}
	
	}
}
?>
