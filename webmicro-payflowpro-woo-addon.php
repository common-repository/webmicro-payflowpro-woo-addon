<?php
/**
 * Plugin Name: PayPal Payflow Pro WooCommerce Addon
 * Plugin URI: 
 * Description: This plugin adds a payment option in WooCommerce for customers to pay with their Credit Cards Via Paypal Payflow.
 * Version: 1.0.0
 * Author: Syed Nazrul Hassan
 * Author URI: https://nazrulhassan.wordpress.com/
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function paypal_payflow_init()
{


function add_paypal_payflow_gateway_class( $methods ) 
{
	$methods[] = 'WC_PayPalProPayflow_Gateway'; 
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_paypal_payflow_gateway_class' );

if(class_exists('WC_Payment_Gateway'))
{
	class WC_PayPalProPayflow_Gateway extends WC_Payment_Gateway 
	{
		
		public function __construct()
		{

		$this->id               = 'paypalpropayflow';
		$this->icon             = plugins_url( 'images/paypalpropayflow.png' , __FILE__ ) ;
		$this->has_fields       = true;
		$this->method_title     = 'PayPalPro Payflow Cards Settings';		
		$this->init_form_fields();
		$this->init_settings();
		
		$this->supports                 = array( 'products','refunds');
		
		$this->title               	   			= $this->get_option( 'paypalpropayflow_title' );
		$this->paypalpropayflow_partnerid    	= $this->get_option( 'paypalpropayflow_partnerid' );
		$this->paypalpropayflow_uniquevendorid  = $this->get_option( 'paypalpropayflow_uniquevendorid' );
		$this->paypalpropayflow_username     	= $this->get_option( 'paypalpropayflow_username' );
		$this->paypalpropayflow_password        = $this->get_option( 'paypalpropayflow_password' ); 
		$this->paypalpropayflow_authorize_only  = $this->get_option( 'paypalpropayflow_authorize_only' );
		$this->paypalpropayflow_sandbox         = $this->get_option( 'paypalpropayflow_sandbox');
		
		$this->paypalpropayflow_cardtypes       = $this->get_option( 'paypalpropayflow_cardtypes'); 
			
		if(!defined("PAYPALPAYFLOW_TRANSACTION_MODE"))
		{ define("PAYPALPAYFLOW_TRANSACTION_MODE"  , ($this->paypalpropayflow_authorize_only =='yes'? false : true)); }

		if(!defined("PAYPALPAYFLOW_SANDBOX"))
		{ define("PAYPALPAYFLOW_SANDBOX"  , ($this->paypalpropayflow_sandbox =='yes'? true : false)); }
		
		if (is_admin()) 
		{
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
			
		}

		public function admin_options()
		{
		?>
		<h3><?php _e( 'Paypal Payflow Pro addon for Woocommerce', 'woocommerce' ); ?></h3>
		<p><?php  _e( 'Payflow is a secure, open payment gateway which allows merchants to choose any Internet Merchant Account to accept debit or credit card payments and connect to any major processor.', 'woocommerce' ); ?></p>
		<table class="form-table">
		  <?php $this->generate_settings_html(); ?>
		</table>
		<?php
		}

		public function init_form_fields()
		{

		$this->form_fields = array(
		'enabled' => array(
		  'title' => __( 'Enable/Disable', 'woocommerce' ),
		  'type' => 'checkbox',
		  'label' => __( 'Enable Stripe', 'woocommerce' ),
		  'default' => 'yes'
		  ),
		'paypalpropayflow_title' => array(
		  'title' => __( 'Title', 'woocommerce' ),
		  'type' => 'text',
		  'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		  'default' => __( 'PayPalPro Payflow', 'woocommerce' ),
		  'desc_tip'      => true,
		  ),
		'paypalpropayflow_partnerid' => array(
		  'title' => __( 'Partner ID', 'woocommerce' ),
		  'type' => 'text',
		  'description' => __( 'This is the PayPalPro Payflow Partner ID.', 'woocommerce' ),
		  'default' => '',
		  'desc_tip'      => true,
		  'placeholder' => 'PayPalPro Payflow Partner ID'
		  ),
		
		'paypalpropayflow_uniquevendorid' => array(
		  'title' => __( 'Vendor ID', 'woocommerce' ),
		  'type' => 'text',
		  'description' => __( 'This is the PayPalPro Payflow Vendor ID.', 'woocommerce' ),
		  'default' => '',
		  'desc_tip'      => true,
		  'placeholder' => 'PayPalPro Payflow Vendor ID'
		  ),
		
		'paypalpropayflow_username' => array(
		  'title' => __( 'PayPalPro Payflow username', 'woocommerce' ),
		  'type' => 'text',
		  'description' => __( 'This is the PayPalPro Payflow username specified while registering.', 'woocommerce' ),
		  'default' => '',
		  'desc_tip'      => true,
		  'placeholder' => 'PayPalPro Payflow Username'
		  ),
		
		'paypalpropayflow_password' => array(
		  'title' => __( 'PayPalPro Payflow password', 'woocommerce' ),
		  'type' => 'text',
		  'description' => __( 'This is the PayPalPro Payflow password specified while registering.', 'woocommerce' ),
		  'default' => '',
		  'desc_tip'      => true,
		  'placeholder' => 'PayPalPro Payflow Password'
		  ),
		
		
		'paypalpropayflow_sandbox' => array(
		  'title'       => __( 'PayPalPro Payflow Sandbox', 'woocommerce' ),
		  'type'        => 'checkbox',
		  'label'       => __( 'Enable PayPalPro Payflow sandbox (Live Mode if Unchecked)', 'woocommerce' ),
		  'description' => __( 'If checked its in sanbox mode and if unchecked its in live mode', 'woocommerce' ),
		  'desc_tip'      => true,
		  'default'     => 'no',
		),
		
		'paypalpropayflow_authorize_only' => array(
		'title'       => __( 'Authorize Only', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Authorize Only Mode (Authorize & Capture If Unchecked)', 'woocommerce' ),
		'description' => __( 'If checked will only authorize the credit card only upon checkout.', 'woocommerce' ),
		'desc_tip'      => true,
		'default'     => 'no',
		),
		
		'paypalpropayflow_cardtypes' => array(
			 'title'    => __( 'Accepted Cards', 'woocommerce' ),
			 'type'     => 'multiselect',
			 'class'    => 'chosen_select',
			 'css'      => 'width: 350px;',
			 'desc_tip' => __( 'Select the card types to accept.', 'woocommerce' ),
			 'options'  => array(
				'mastercard'       => 'MasterCard',
				'visa'             => 'Visa',
				'discover'         => 'Discover',
				'amex' 		       => 'American Express',
				'jcb'		       => 'JCB',
				'dinersclub'       => 'Dinners Club',
			 ),
			 'default' => array( 'mastercard', 'visa', 'discover', 'amex' ),
			)

		
	  );
  		}
  		
  		
  		function is_available() {
            if ( ! in_array( get_woocommerce_currency(), apply_filters( 'paypalpayflowpro_woocommerce_supported_currencies', array( 'USD','AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR','GBP','HKD','HUF','JPY','NOK','NZD','PLN','SEK','SGD' ) ) ) ) return false;

            if(empty($this->paypalpropayflow_partnerid) || empty($this->paypalpropayflow_uniquevendorid) || empty($this->paypalpropayflow_username) || empty($this->paypalpropayflow_password) ) return false;

            return true;
        }




  		/*Get Card Types*/
		function get_card_type($number)
		{
		    $number=preg_replace('/[^\d]/','',$number);
		    if (preg_match('/^3[47][0-9]{13}$/',$number))
		    {
		        return 'amex';
		    }
		    elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',$number))
		    {
		        return 'dinersclub';
		    }
		    elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/',$number))
		    {
		        return 'discover';
		    }
		    elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/',$number))
		    {
		        return 'jcb';
		    }
		    elseif (preg_match('/^5[1-5][0-9]{14}$/',$number))
		    {
		        return 'mastercard';
		    }
		    elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/',$number))
		    {
		        return 'visa';
		    }
		    else
		    {
		        return 'unknown card';
		    }
		}// End of getcard type function
  		
  		
  		//Function to check IP
		function get_client_ip() 
		{
			$ipaddress = '';
			if (getenv('HTTP_CLIENT_IP'))
				$ipaddress = getenv('HTTP_CLIENT_IP');
			else if(getenv('HTTP_X_FORWARDED_FOR'))
				$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
			else if(getenv('HTTP_X_FORWARDED'))
				$ipaddress = getenv('HTTP_X_FORWARDED');
			else if(getenv('HTTP_FORWARDED_FOR'))
				$ipaddress = getenv('HTTP_FORWARDED_FOR');
			else if(getenv('HTTP_FORWARDED'))
				$ipaddress = getenv('HTTP_FORWARDED');
			else if(getenv('REMOTE_ADDR'))
				$ipaddress = getenv('REMOTE_ADDR');
			else
				$ipaddress = '0.0.0.0';
			return $ipaddress;
		}
		
		//End of function to check IP
  		
		



		/*Get Icon*/
		public function get_icon() {
		$icon = '';
		if(is_array($this->paypalpropayflow_cardtypes))
		{
        foreach ( $this->paypalpropayflow_cardtypes as $card_type ) {

				if ( $url = $this->paypalpropayflow_get_active_card_logo_url( $card_type ) ) {
					
					$icon .= '<img src="'.esc_url( $url ).'" alt="'.esc_attr( strtolower( $card_type ) ).'" />';
				}
			}
		}
		else
		{
			$icon .= '<img src="'.esc_url( plugins_url( 'images/paypalpropayflow.png' , __FILE__ ) ).'" alt="PayPalPro Payflow" />';	  
		}

         return apply_filters( 'paypalpropayflow_icon', $icon, $this->id );
		}
 
		public function paypalpropayflow_get_active_card_logo_url( $type ) {

		$image_type = strtolower( $type );
				return  WC_HTTPS::force_https_url( plugins_url( 'images/' . $image_type . '.png' , __FILE__ ) ); 
		}


		public function paypalpayfowpro_params($wc_order)
		{

				$exp_date         = explode( "/", sanitize_text_field($_POST['paypalpropayflow-card-expiry']));
				$exp_month        = str_replace( ' ', '', $exp_date[0]);
				$exp_year         = str_replace( ' ', '',$exp_date[1]);

				//if (strlen($exp_year) == 4) { $exp_year += 2000;}

				$paypalpayfowpro_params = array(
						'PARTNER'		=> $this->paypalpropayflow_partnerid,
						'VENDOR'		=> $this->paypalpropayflow_uniquevendorid,
						'USER'			=> $this->paypalpropayflow_username,
						'PWD'			=> $this->paypalpropayflow_password,
						'TENDER'        => 'C', // C for credit card
						'TRXTYPE'       => 'S', // S for Sale
						'ACCT'			=> sanitize_text_field(str_replace(' ','',$_POST['paypalpropayflow-card-number'])),
						'EXPDATE'       => $exp_month.$exp_year,
						'AMT'			=> $wc_order->order_total,
						'TAXAMT'        => $wc_order->get_total_tax(),
						
						'CURRENCY'      => get_woocommerce_currency(),
			            'CUSTIP'        => $this->get_client_ip(),
						'CVV2'          => sanitize_text_field($_POST['paypalpropayflow-card-cvc']),
						'PHONENUM'      => $wc_order->billing_phone,
			            'EMAIL'         => $wc_order->billing_email,
			            'INVNUM'        => $wc_order->get_order_number(),
			            
			            'BILLTOFIRSTNAME'=> $wc_order->billing_first_name,
						'BILLTOLASTNAME'=> $wc_order->billing_last_name,
						'BILLTOSTREET'  => $wc_order->billing_address_1,
						'BILLTOSTREET2' => $wc_order->billing_address_2,
						'BILLTOCITY'    => $wc_order->billing_city,
						'BILLTOSTATE'   => $wc_order->billing_state,
						'BILLTOZIP'     => $wc_order->billing_postcode,

						'SHIPTOFIRSTNAME'=> $wc_order->shipping_first_name,
						'SHIPTOLASTNAME'=> $wc_order->shipping_last_name,
						'SHIPTOSTREET'  => $wc_order->shipping_address_1,
						'SHIPTOSTREET2' => $wc_order->shipping_address_2,
						'SHIPTOCITY'    => $wc_order->shipping_city,
						'SHIPTOSTATE'   => $wc_order->shipping_state,
						'SHIPTOZIP'     => $wc_order->shipping_postcode
						
				);
			return $paypalpayfowpro_params;
		}


		    /*Start of credit card form */
  		public function payment_fields() {
			$this->form();
		}

  		public function field_name( $name ) {
		return $this->supports( 'tokenization' ) ? '' : ' name="' . esc_attr( $this->id . '-' . $name ) . '" ';
	}

  		public function form() {
		wp_enqueue_script( 'wc-credit-card-form' );
		$fields = array();
		$cvc_field = '<p class="form-row form-row-last">
			<label for="' . esc_attr( $this->id ) . '-card-cvc">' . __( 'Card Code', 'woocommerce' ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $this->id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . '/>
		</p>';
		$default_fields = array(
			'card-number-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '-card-number">' . __( 'Card Number', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name( 'card-number' ) . ' />
			</p>',
			'card-expiry-field' => '<p class="form-row form-row-first">
				<label for="' . esc_attr( $this->id ) . '-card-expiry">' . __( 'Expiry (MM/YY)', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="' . esc_attr__( 'MM / YY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
			</p>',
			'card-cvc-field'  => $cvc_field
		);
		
		 $fields = wp_parse_args( $fields, apply_filters( 'woocommerce_credit_card_form_fields', $default_fields, $this->id ) );
		?>

		<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
			<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>
			<?php
				foreach ( $fields as $field ) {
					echo $field;
				}
			?>
			<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
			<div class="clear"></div>
		</fieldset>
		<?php
		
	}
  		/*End of credit card form*/

		
		/*Process Payment*/
		public function process_payment( $order_id )
		{
			global $woocommerce;
			$wc_order 		= new WC_Order( $order_id );
			$grand_total 	= $wc_order->order_total;

			$cardtype = $this->get_card_type(sanitize_text_field(str_replace(' ','',$_POST['paypalpropayflow-card-number'])));
	 		if(!in_array($cardtype ,$this->paypalpropayflow_cardtypes ))
	 		{
	 			wc_add_notice('Merchant do not accept in '.$cardtype.' card',  $notice_type = 'error' );
	 			return ;die;
	 		}


			$params = $this->paypalpayfowpro_params($wc_order);
			foreach( $params as $key => $value )
			{ 
			$post_string .= urlencode( $key )."=".urlencode($value )."&"; 
			}
			$post_string = rtrim($post_string,"&");

			if(yes == PAYPALPAYFLOW_SANDBOX)
			{
				$gateway_url = 'https://pilot-payflowpro.paypal.com';
			}
			else
			{
				$gateway_url = 'https://payflowpro.paypal.com';
			}
			$curlrequest   = curl_init($gateway_url); 
			curl_setopt($curlrequest, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
			curl_setopt($curlrequest, CURLOPT_HEADER, 0);                    // set to 0 to eliminate header info from response
			curl_setopt($curlrequest, CURLOPT_TIMEOUT, 45);
			curl_setopt($curlrequest, CURLOPT_RETURNTRANSFER, 1);            // Returns response data instead of TRUE(1)
			//determines whether libcurl verifies that the server cert is for the server it is known as     
			curl_setopt($curlrequest, CURLOPT_SSL_VERIFYHOST, 2); 
			//determines whether curl verifies the authenticity of the peer's certificate
			curl_setopt($curlrequest, CURLOPT_SSL_VERIFYPEER, 1);        // Comment this line if you get no gateway response.
			$post_response = curl_exec($curlrequest);                               
			curl_close ($curlrequest);

			$resultarray  = explode('&', $post_response) ;
 			
			for($i=0;$i<count($resultarray);$i++) 
			{
				$rsponsedata = explode("=",$resultarray[$i]);
				$response_array[$rsponsedata[0]] = $rsponsedata[1];
			}

		
			if ( count($response_array) > 1 )
			{
				if( 0 == $response_array['RESULT'] ||  126 == $response_array['RESULT'] )
				{
					$wc_order->add_order_note( __( $response_array['RESPMSG'].' on '.date("d-m-Y h:i:s e").' with Transaction ID = '.$response_array['PNREF'].' Authorization Code '.$response_array['AUTHCODE'].', CVV2 match ='.$response_array['CVV2MATCH'].' Response Code = '.$response_array['RESULT'],  'woocommerce' ) );

					$wc_order->payment_complete($response_array['PNREF']);
					WC()->cart->empty_cart();
					return array (
						'result'   => 'success',
						'redirect' => $this->get_return_url( $wc_order ),
					   );
				}
				else
				{
					$wc_order->add_order_note( __( $response_array['RESPMSG'].' on '.date("d-m-Y h:i:s e").' with Transaction ID = '.$response_array['PNREF'].' Authorization Code '.$response_array['AUTHCODE'].', CVV2 match ='.$response_array['CVV2MATCH'].' Response Code = '.$response_array['RESULT'],  'woocommerce' ) );

					wc_add_notice($response_array['RESPMSG'], $notice_type = 'error' );
				}

			}


		}
		// end of function process_payment()


	}  // end of class WC_PayPalProPayflow_Gateway

} // end of if class exist WC_Gateway

}

/*Activation hook*/
add_action( 'plugins_loaded', 'paypal_payflow_init' );

function paypal_payflow_addon_activate() {

	if(!function_exists('curl_exec'))
	{
		 wp_die( '<pre>This plugin requires PHP CURL library installled in order to be activated </pre>' );
	}
}
register_activation_hook( __FILE__, 'paypal_payflow_addon_activate' );
/*Activation hook*/

/*Plugin Settings Link*/
function paypal_payflow_woocommerce_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=wc_paypalpropayflow_gateway">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'paypal_payflow_woocommerce_settings_link' );