<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Lottery Product Class
 *
 * @class WC_Product_Lottery
 * 
 */ 
class WC_Product_Lottery extends WC_Product {

	public $post_type = 'product';
	public $product_type = 'lottery';

	/**
	 * Stores product data.
	 * 
	 * @var array
	 */
	protected $extra_data = array(
	   
	);

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $product
	 * 
	 */
	public function __construct( $product ) {
		global $sitepress;		
		date_default_timezone_set("UTC");		
		
		if(is_array($this->data))
			$this->data = array_merge( $this->data, $this->extra_data );
		
		parent::__construct( $product );		
		$this->is_closed();	
		$this->is_started();	
	}

	/**
	 * Returns the unique ID for this object.
	 * @return int
	 */
	public function get_id() {
		return $this->id; 
	}

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'lottery';
	}
	/**
	 * Get remaining seconds till lottery end
	 *
	 * @access public
	 * @return mixed
	 * 
	 */      
	function get_seconds_remaining() {
			if ($this->get_lottery_dates_to()){

				return strtotime($this->get_lottery_dates_to())  -  (get_option( 'gmt_offset' )*3600);

			} else {
				
				return FALSE;
			}			
	}    
	/**
	 * Get seconds till lottery starts
	 *
	 * @access public
	 * @return mixed
	 * 
	 */      
	function get_seconds_to_lottery() {
			if ($this->get_lottery_dates_from()){
				
				return strtotime($this->get_lottery_dates_from()) - (get_option( 'gmt_offset' )*3600);					

			} else {
				return FALSE;
			}			
	}    
	/**
	 * Has lottery started
	 *
	 * @access public
	 * @return mixed
	 * 
	 */ 
	function is_started() {

		if($this->get_lottery_has_started() === '1' ){
			return TRUE;
		}

		$id = $this->get_main_wpml_product_id();
		$lottery_dates_form = $this->get_lottery_dates_from();
		if (!empty($lottery_dates_form) ){
			$date1 = new DateTime($lottery_dates_form);
			$date2 = new DateTime(current_time('mysql'));
			if ($date1 < $date2){
					update_post_meta( $id, '_lottery_has_started', '1');
					delete_post_meta( $id, '_lottery_started');
					do_action('woocommerce_lottery_started',$id);

			} else{
					update_post_meta( $id, '_lottery_started', '0');
			}

			return ($date1 < $date2) ;				
		} else {
			update_post_meta( $id, '_lottery_started', '0');
			return FALSE;
		}
	}    
	/**
	 * Does user participate in lottery
	 *
	 * @access public
	 * @return mixed
	 * 
	 */      
	function user_participating($user_id) {
		
	   global $wpdb;
	   $result = $wpdb->get_row("SELECT 1 FROM ".$wpdb -> prefix."simple_lottery_log WHERE userid = $user_id");
	   
	   if ($result != null){
		   return TRUE;
	   } else {
		   return FALSE;
	   }
	   return FALSE;
	}
	/**
	 * Has lottery met min participants limit
	 *
	 * @access public
	 * @return mixed
	 * 
	 */      
	function is_min_tickets_met() {

		$min_tickets = $this->get_min_tickets();
		$lottery_participants_count = $this->get_lottery_participants_count();

		if (!empty($min_tickets) && $lottery_participants_count){

			return ( intval($lottery_participants_count) >= intval($min_tickets) );
		}			
		return true;
	}    
	/**
	 * Has lottery met max participants limit
	 *
	 * @access public
	 * @return mixed
	 * 
	 */      
	function is_max_tickets_met() {
		
		$max_tickets = $this->get_max_tickets();
		$lottery_participants_count = $this->get_lottery_participants_count();

		if (!empty($max_tickets) ){
						
			return ( $lottery_participants_count >= $max_tickets);

		} else{
			return false;
		}
		
		return true;
	}    
	/**
	 * Has lottery finished
	 *
	 * @access public
	 * @return mixed
	 * 
	 */      
	function is_finished() {
		$lottery_dates_to = $this->get_lottery_dates_to();
		if (!empty($lottery_dates_to)){
			
			$date1 = new DateTime($lottery_dates_to);
			$date2 = new DateTime(current_time('mysql'));
			return ($date1 < $date2) ;

		} else {
			return FALSE;
		}
	}	
	/**
	 * Is lottery closed
	 *
	 * @access public
	 * @return bool
	 * 
	 */      
	function is_closed() {

		$id = $this->get_main_wpml_product_id();

		if ($this->get_lottery_closed() && in_array($this->get_lottery_closed(), array('1','2')) ){

			return TRUE;

		} else {

			if (($this->is_finished() && $this->is_started()) or (get_option( 'simple_lottery_close_when_max' ) == 'yes' && $this->is_max_tickets_met() )  ){

				global $product, $post;

				if(get_post_meta( $this->get_main_wpml_product_id(), '_order_hold_on', true )){
					return TRUE;
				}

				$participants = get_post_meta( $id, '_participant_id'); 

				if ( empty($this->get_lottery_participants_count())){								
					
					
					update_post_meta( $id, '_lottery_closed', '1');
					update_post_meta( $id, '_lottery_fail_reason', '1');
					$order_id = FALSE;
					do_action('wc_lottery_close',  $id);
					do_action('wc_lottery_fail', array('lottery_id' => $id , 'reason' => __('There were no participants','wc_lottery') ));
					return FALSE;                        
				}
				
				if ( $this->is_min_tickets_met() == FALSE){
					
					update_post_meta( $id, '_lottery_closed', '1');
					update_post_meta( $id, '_lottery_fail_reason', '2');
					$order_id = FALSE;
					do_action('wc_lottery_close',  $id);
					do_action('wc_lottery_min_fail', array('participants' => $participants, 'product_id' => $id )); 
					do_action('wc_lottery_fail', array('lottery_id' => $id , 'reason' => __('The item did not make it to minimum participants','wc_lottery') ));
					return FALSE;
					
				}

				$participants = apply_filters( 'woocommerce_lottery_participants',  get_post_meta( $id, '_participant_id'), $id, $this ); 

				delete_post_meta( $id, '_lottery_winners');
				
				$winners = array();

				if ( is_array($participants) ){
					$i = 0;	
					while ( $i <= ( intval( $this->get_lottery_num_winners() ) - 1)) {
						$winners_key[$i] = mt_rand(0, count($participants) - 1);
						$winners[] = $participants[$winners_key[$i]];
						if($this->get_lottery_multiple_winner_per_user() == 'yes'){
							unset($participants[$winners_key[$i]]);
						} else{
							$participants = array_diff($participants, array($participants[$winners_key[$i]]));    
						}
						$participants = array_values($participants);
						$i++;
						if (count($participants) < $i){
							break;
						}
					}
				}
				
				
				$winners = apply_filters( 'woocommerce_lottery_winners',  $winners, $id, $this ); 

				foreach ($winners as $key => $userid) {
					add_post_meta( $id, '_lottery_winners', $userid);
					add_user_meta( $userid, '_lottery_win', $id);
					add_user_meta( $userid, '_lottery_win_'.$id.'_position', $key );
				}
				update_post_meta( $id, '_lottery_closed', '2');

				if ( 'yes' !== get_post_meta( $id, '_lottery_manualy_winners', true ) ) {
					do_action('wc_lottery_close', $id);
					do_action('wc_lottery_won', $id);
				}

				return TRUE;

			} else {

				return FALSE;

			}	
		}
	}	
	/**
	 * Get lottery history
	 *
	 * @access public
	 * @return object
	 * 
	 */     
	function lottery_history($datefrom = FALSE, $user_id = FALSE) {			

		global $wpdb;
		$wheredatefrom ='';

		$id = $this->get_main_wpml_product_id();

		$relisteddate = get_post_meta( $id, '_lottery_relisted', true );
		if(!is_admin() && !empty($relisteddate)){
			$datefrom = $relisteddate;
		}

		if($datefrom){
			$wheredatefrom =" AND CAST(date AS DATETIME) > '$datefrom' ";
		}

		if($user_id){
			$wheredatefrom =" AND userid = $user_id";
		}
			
		$history = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'wc_lottery_log WHERE lottery_id =' . $id . $wheredatefrom.' ORDER BY  `date` DESC');

		return apply_filters( 'woocomerce_lottery_history', $history, $id, $user_id, $relisteddate );
	}
	
	
	/**
	 * Wrapper for get_permalink
	 * 
	 * @return string
	 * 
	 */
	public function get_permalink() {
		
			$id = $this->get_main_wpml_product_id();
			return get_permalink( $id );
	}
	/**
	 * Is user participating in lottery
	 *
	 * @access public
	 * @return bool
	 * 
	 */ 

	function is_user_participating($userid = FALSE ){

		
		
		$id = $this->get_main_wpml_product_id();
		
		if(!$userid) {
			$userid = get_current_user_id();
		}

		$participants = get_post_meta( $id, '_participant_id', false); 

		if($participants and is_array($participants)){
			
			return in_array($userid, $participants);        
			
		} else {

			return FALSE;
		}

		return false;
	}

	/**
	 * Is user participating in lottery
	 *
	 * @access public
	 * @return int
	 * 
	 */ 

	function count_user_tickets($userid = FALSE ){
		
		$id = $this->get_main_wpml_product_id();

		if(!$userid) {
			$userid = get_current_user_id();
		}

		$users_qty = array_count_values( get_post_meta($id, '_participant_id') );

		$current_user_qty = isset($users_qty[$userid]) ? intval($users_qty[$userid]) : 0;


		return $current_user_qty;
	}

	/**
	 * Get main product id for multilanguage purpose
	 *
	 * @access public
	 * @return int
	 * 
	 */ 

	function get_main_wpml_product_id(){

		global $sitepress;
		$_id = $this->get_id();
		if (function_exists('icl_object_id') && function_exists('pll_default_language')) { // Polylang with use of WPML compatibility mode
			$id = icl_object_id($_id,'product',false, pll_default_language());
			 if($id === null){
				$id = $this->id;
			}
		}
		elseif (function_exists('icl_object_id') && method_exists($sitepress, 'get_default_language')) { // WPML
			$id = icl_object_id($_id,'product',false, $sitepress->get_default_language());
			 if($id === null){
				$id = $this->id;
			}
		}
		else {
			$id = $_id;
		}

		return $id;

	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return string
	 */
	public function add_to_cart_text() {
		
		if ( $this->is_closed() ) {
		   
			$text =  __( 'View winners', 'wc_lottery' );

		} elseif( !$this->is_started() OR $this->is_max_tickets_met() ) {

			$text = __( 'Read more', 'wc_lottery' );

		} else {

			$text = __( 'Participate', 'wc_lottery' );
			

		}

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
			



	}

	/**
	 * Get max quantity which can be purchased at once.
	 *
	 * @since  3.0.0
	 * @return int Quantity or -1 if unlimited.
	 */
	public function get_max_purchase_quantity() {
		$max_tickets_per_user = $this->get_max_tickets_per_user() ? $this->get_max_tickets_per_user() : false;
		if($max_tickets_per_user !== false){
			   if( is_user_logged_in() ){
					$user_tickets = $this->count_user_tickets();
					return intval($max_tickets_per_user) - intval($user_tickets);
			   }
			   return $max_tickets_per_user;
		} 
		if ( $this->is_sold_individually() ) {
			return 1;
		}
		return $this->get_stock_quantity();
	}


	/**
	 * Get lottery start date .
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_dates_from( $context = 'view' ) {
		
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_dates_from', true );
		
	}

	/**
	 * Get lottery end date .
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_dates_to( $context = 'view' ) {
		
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_dates_to', true );
	}
	 /**
	 * Get lottery min tickets .
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_min_tickets( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_min_tickets', true );
	}
	 /**
	 * Get lottery max tickets .
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_max_tickets( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_max_tickets', true );
		
	}

	/**
	 * Get lottery participants count .
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_participants_count( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_participants_count', true );
		
	}

	/**
	 * Get lottery closed status.
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_closed( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_closed', true );
		
	}
	/**
	 * Get lottery started status.
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_started( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_started', true );
		
	}
	/**
	 * Get lottery has_started status.
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_has_started( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_has_started', true );
		
	}

	/**
	 * Get lottery closed status.
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_fail_reason( $context = 'view' ) {

		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_fail_reason', true );
		
	}

  
	
	/**
	 * Get lottery number of winners.
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_num_winners( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_num_winners', true );
		
	}

	/**
	 * Get lottery multiple winner per user.
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_multiple_winner_per_user( $context = 'view' ) {
		 
		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_multiple_winner_per_user', true );
		
	}


	/**
	 * Get lottery max ticket per user
	 *
	 * @since 1.1
	 * @param  string $context
	 * @return string
	 */
	public function get_max_tickets_per_user( $context = 'view' ) {

		return get_post_meta( $this->get_main_wpml_product_id(), '_max_tickets_per_user', true );
		
	}

	/**
	 * Get get_lottery_relisted
	 *
	 * @since 1.2.8
	 * @param  string $context
	 * @return string
	 */
	public function get_lottery_relisted( $context = 'view' ) {

		return get_post_meta( $this->get_main_wpml_product_id(), '_lottery_relisted', true );
		
	}
	
	public function lottery_update_lookup_table( ) {
		global $wpdb;

		$id    = absint( $this->get_main_wpml_product_id() );
		$table = 'wc_product_meta_lookup';
		$existing_data = wp_cache_get( 'lookup_table', 'object_' . $id );
		$update_data   = $this->lottery_get_data_for_lookup_table( $id );

		if ( ! empty( $update_data ) && $update_data !== $existing_data ) {
			$wpdb->replace(
				$wpdb->$table,
				$update_data
			);
			wp_cache_set( 'lookup_table', $update_data, 'object_' . $id );
		}
	}

	public function lottery_get_data_for_lookup_table( $id ){

		$price_meta   = (array) get_post_meta( $id, '_price', false );
		$manage_stock = get_post_meta( $id, '_manage_stock', true );
		$stock        = 'yes' === $manage_stock ? wc_stock_amount( get_post_meta( $id, '_stock', true ) ) : null;
		$price        = wc_format_decimal( get_post_meta( $id, '_price', true ) );
		$sale_price   = wc_format_decimal( get_post_meta( $id, '_sale_price', true ) );
		return array(
			'product_id'     => absint( $id ),
			'sku'            => get_post_meta( $id, '_sku', true ),
			'virtual'        => 'yes' === get_post_meta( $id, '_virtual', true ) ? 1 : 0,
			'downloadable'   => 'yes' === get_post_meta( $id, '_downloadable', true ) ? 1 : 0,
			'min_price'      => reset( $price_meta ),
			'max_price'      => end( $price_meta ),
			'onsale'         => $sale_price && $price === $sale_price ? 1 : 0,
			'stock_quantity' => $stock,
			'stock_status'   => get_post_meta( $id, '_stock_status', true ),
			'rating_count'   => array_sum( (array) get_post_meta( $id, '_wc_rating_count', true ) ),
			'average_rating' => get_post_meta( $id, '_wc_average_rating', true ),
			'total_sales'    => get_post_meta( $id, 'total_sales', true ),
		);
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @return string
	 */
	public function single_add_to_cart_text() {
		$price = $this->get_price();
		if ( $price ){
			$price             = apply_filters( 'formatted_woocommerce_price', number_format( $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ), $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
		}
		$text = sprintf(__( 'Participate now for <span class="atct-price" data-price="%s" data-id="%d">%s</span>', 'wc_lottery' ), $price, $this->get_id(), $price ? wc_price($price) : __('free', 'woocommerce'));
		return apply_filters( 'woocommerce_product_single_add_to_cart_text',$text, $this );
	}
	
}