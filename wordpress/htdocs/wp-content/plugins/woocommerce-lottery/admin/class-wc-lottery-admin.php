<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgenie.org
 * @since      1.0.0
 *
 * @package    wc_lottery
 * @subpackage wc_lottery/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wc_lottery
 * @subpackage wc_lottery/admin
 * @author     wpgenie <info@wpgenie.org>
 */
class wc_lottery_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wc_lottery    The ID of this plugin.
	 */
	private $wc_lottery;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The current path of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $path;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wc_lottery       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wc_lottery, $version, $path ) {

		$this->wc_lottery = $wc_lottery;
		$this->version    = $version;
		$this->path       = $path;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->wc_lottery, plugin_dir_url( __FILE__ ) . 'css/wc-lottery-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			if ( 'product' == get_post_type() ) {
				wp_register_script(
					'wc-lottery-admin',
					plugin_dir_url( __FILE__ ) . '/js/wc-lottery-admin.js',
					array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'timepicker-addon' ),
					$this->version,
					true
				);

				$params = array(
					'i18_max_ticket_less_than_min_ticket_error' => __( 'Please enter in a value greater than the min tickets.', 'wc_lottery' ),
					'i18_minimum_winers_error' => __( 'You must set at least one lottery winner', 'wc_lottery' ),
					'lottery_refund_nonce'     => wp_create_nonce( 'lottery-refund' ),
				);

				wp_localize_script( 'wc-lottery-admin', 'woocommerce_lottery', $params );
				wp_enqueue_script( 'wc-lottery-admin' );

				wp_enqueue_script(
					'timepicker-addon',
					plugin_dir_url( __FILE__ ) . '/js/jquery-ui-timepicker-addon.js',
					array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),
					$this->version,
					true
				);

				wp_enqueue_style( 'jquery-ui-datepicker' );
			}
		}

	}

	/**
	* Add to mail class
	*
	* @access public
	* @return object
	*
	*/
	public function add_to_mail_class( $emails ) {

		include_once 'emails/class-wc-email-lottery-win.php';
		include_once 'emails/class-wc-email-lottery-failed.php';
		include_once 'emails/class-wc-email-lottery-no-luck.php';
		include_once 'emails/class-wc-email-lottery-finished.php';
		include_once 'emails/class-wc-email-lottery-failed-users.php';
		include_once 'emails/class-wc-email-lottery-extended.php';

		$emails->emails['WC_Email_Lottery_Win']        = new WC_Email_Lottery_Win();
		$emails->emails['WC_Email_Lottery_Failed']     = new WC_Email_Lottery_Failed();
		$emails->emails['WC_Email_Lottery_Finished']   = new WC_Email_Lottery_Finished();
		$emails->emails['WC_Email_Lottery_No_Luck']    = new WC_Email_Lottery_No_Luck();
		$emails->emails['WC_Email_Lottery_Fail_Users'] = new WC_Email_Lottery_Fail_Users();
		$emails->emails['WC_Email_Lottery_Extended']   = new WC_Email_Lottery_Extended();

		return $emails;
	}

	/**
	 * register_widgets function
	 *
	 * @access public
	 * @return void
	 *
	 */
	function register_widgets() {

	}

	/**
	 * Add link to plugin page
	 *
	 * @access public
	 * @param  array, string
	 * @return array
	 *
	 */
	public function add_support_link( $links, $file ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return $links;
		}

		if ( $file == 'woocommerce-lottery/wc-lottery.php' ) {
			$links[] = '<a href="https://wpgenie.org/woocommerce-lottery/documentation/" target="_blank">' . __( 'Docs', 'wc_lottery' ) . '</a>';
			$links[] = '<a href="https://codecanyon.net/user/wpgenie#contact" target="_blank">' . __( 'Support', 'wc_lottery' ) . '</a>';
			$links[] = '<a href="https://codecanyon.net/user/wpgenie/" target="_blank">' . __( 'More WooCommerce Extensions', 'wc_lottery' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Add admin notice
	 *
	 * @access public
	 * @param  array, string
	 * @return array
	 *
	 */
	public function woocommerce_simple_lottery_admin_notice() {
		global $current_user;
		if ( current_user_can( 'manage_options' ) ) {
			$user_id = $current_user->ID;
			if ( get_option( 'Wc_lottery_cron_check' ) != 'yes' && ! get_user_meta( $user_id, 'lottery_cron_check_ignore' ) ) {
				echo '<div class="updated">
				<p>' . sprintf( __( 'Woocommerce Lottery recommends that you set up a cron job to check for finished lotteries: <b>%1$s/?lottery-cron=check</b>. Set it to every minute| <a href="%2$s">Hide Notice</a>', 'wc_lottery' ), get_bloginfo( 'url' ), add_query_arg( 'lottery_cron_check_ignore', '0' ) ) . '</p>
				</div>';
			}
			if ( get_option( 'woocommerce_enable_guest_checkout' ) == 'yes' ) {
				echo '<div class="error">
				<p>' . sprintf( __( 'Woocommerce Lottery can not work with enabled option "Allow customers to place orders without an account" please turn it off. <a href="%1$s">Accounts & Privacy settings</a>', 'wc_lottery' ), get_admin_url().'admin.php?page=wc-settings&tab=account' ) . '</p>
				</div>';
			}
		}
	}

	/**
	 * Add user meta to ignor notice about crons.
	 * @access public
	 *
	 */
	public function woocommerce_simple_lottery_ignore_notices() {
		global $current_user;
		$user_id = $current_user->ID;

		/* If user clicks to ignore the notice, add that to their user meta */
		if ( isset( $_GET['lottery_cron_check_ignore'] ) && '0' == $_GET['lottery_cron_check_ignore'] ) {
			add_user_meta( $user_id, 'lottery_cron_check_ignore', 'true', true );
		}

	}


	/**
	 * Add product type
	 * @param array
	 * @return array
	 *
	 */
	public function add_product_type( $types ) {
		$types['lottery'] = __( 'Lottery', 'wc_lottery' );
		return $types;
	}


	/**
	 * Adds a new tab to the Product Data postbox in the admin product interface
	 *
	 * @return void
	 *
	 */
	public function product_write_panel_tab( $product_data_tabs ) {
		$tab_icon = plugin_dir_url( __FILE__ ) . 'images/lottery.png';

		$lottery_tab = array(
			'lottery_tab' => array(
				'label'  => __( 'Lottery', 'wc_lottery' ),
				'target' => 'lottery_tab',
				'class'  => array( 'lottery_tab', 'show_if_lottery', 'hide_if_grouped', 'hide_if_external', 'hide_if_variable', 'hide_if_simple' ),
			),
		);

		return $lottery_tab + $product_data_tabs;
	}


	/**
	 * Adds the panel to the Product Data postbox in the product interface
	 *
	 * @return void
	 *
	 */
	public function product_write_panel() {
		global $post;
		$product = wc_get_product( $post->ID );

		echo '<div id="lottery_tab" class="panel woocommerce_options_panel">';

		woocommerce_wp_text_input(
			array(
				'id'                => '_min_tickets',
				'class'             => 'input_text',
				'size'              => '6',
				'label'             => __( 'Min tickets', 'wc_lottery' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '0',
				),
				'desc_tip'          => 'true',
				'description'       => __( 'Minimum tickets to be sold', 'wc_lottery' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_max_tickets',
				'class'             => 'input_text',
				'size'              => '6',
				'label'             => __( 'Max tickets', 'wc_lottery' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '0',
				),
				'desc_tip'          => 'true',
				'description'       => __( 'Maximum tickets to be sold', 'wc_lottery' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => '_max_tickets_per_user',
				'class'             => 'input_text',
				'size'              => '6',
				'label'             => __( 'Max tickets per user', 'wc_lottery' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '0',
				),
				'desc_tip'          => 'true',
				'description'       => __( 'Maximum tickets sold per user', 'wc_lottery' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_lottery_num_winners',
				'class'             => 'input_text',
				'size'              => '6',
				'label'             => __( 'Number of winners', 'wc_lottery' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '0',
				),
				'desc_tip'          => 'true',
				'description'       => __( 'Number of possible winners', 'wc_lottery' ),
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'            => '_lottery_multiple_winner_per_user',
				'wrapper_class' => 'lottery_single_winner_per_user',
				'label'         => __( 'Multiple prizes per user?', 'wc_lottery' ),
				'description'   => __( 'Allow multiple prizes for single user if there are multiple lottery winners', 'wc_lottery' ),
				'desc_tip'      => 'true',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_lottery_price',
				'class'             => 'input_text',
				'label'             => __( 'Price', 'wc_lottery' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '0',
				),
				'data_type'         => 'price',
				'desc_tip'          => 'true',
				'description'       => __( 'Lottery Price, put 0 for free lottery.', 'wc_lottery' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => '_lottery_sale_price',
				'class'             => 'input_text',
				'label'             => __( 'Sale Price', 'wc_lottery' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '0',
				),
				'data_type'         => 'price',
				'desc_tip'          => 'true',
				'description'       => __( 'Lottery Sale Price', 'wc_lottery' ),
			)
		);

		$lottery_dates_from = ( $date = get_post_meta( $post->ID, '_lottery_dates_from', true ) ) ? $date : '';
		$lottery_dates_to   = ( $date = get_post_meta( $post->ID, '_lottery_dates_to', true ) ) ? $date : '';

		echo '	<p class="form-field lottery_dates_fields">
					<label for="_lottery_dates_from">' . __( 'Lottery from date', 'wc_lottery' ) . '</label>
					<input type="text" class="short datetimepicker" name="_lottery_dates_from" id="_lottery_dates_from" value="' . $lottery_dates_from . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'wc_lottery' ) . __( 'YYYY-MM-DD HH:MM' ) . '"maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
				 </p>
				 <p class="form-field lottery_dates_fields">
					<label for="_lottery_dates_to">' . __( 'Lottery to date', 'wc_lottery' ) . '</label>
					<input type="text" class="short datetimepicker" name="_lottery_dates_to" id="_lottery_dates_to" value="' . $lottery_dates_to . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'wc_lottery' ) . __( 'YYYY-MM-DD HH:MM' ) . '" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
				</p>';

		$product_type = method_exists( $product, 'get_type' ) ? $product->get_type() : $product->product_type;
		if ( 'lottery' == $product_type && $product->get_lottery_closed() === '1'  ) {
			echo '<p class="form-field extend_dates_fields"><a class="button extend" href="#" id="extendlottery">' . __( 'Extend lottery', 'wc_lottery' ) . '</a>
				   <p class="form-field extend_lottery_dates_fields"> 
						<label for="_extend_lottery_dates_from">' . __( 'Extend Date', 'wc_lottery' ) . '</label>
						<input type="text" class="short datetimepicker" name="_extend_lottery_dates_to" id="_extend_lottery_dates_to" value="" placeholder="' . _x( 'To&hellip; YYYY-MM-DD HH:MM', 'placeholder', 'wc_lottery' ) . '" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
					</p>
					</p>'; 
		}
		if ( 'lottery' == $product_type && $product->is_closed() ) {
			echo '<p class="form-field relist_dates_fields"><a class="button relist" href="#" id="relistlottery">' . __( 'Relist', 'wc_lottery' ) . '</a>
				   <p class="form-field relist_lottery_dates_fields"> 
						<label for="_relist_lottery_dates_from">' . __( 'Relist Dates', 'wc_lottery' ) . '</label>
						<input type="text" class="short datetimepicker" name="_relist_lottery_dates_from" id="_relist_lottery_dates_from" value="" placeholder="' . _x( 'From&hellip; YYYY-MM-DD HH:MM', 'placeholder', 'wc_lottery' ) . '" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
					</p>
					<p class="form-field relist_lottery_dates_fields"> 
						<input type="text" class="short datetimepicker" name="_relist_lottery_dates_to" id="_relist_lottery_dates_to" value="" placeholder="' . _x( 'To&hellip; YYYY-MM-DD HH:MM', 'placeholder', 'wc_lottery' ) . '" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
					</p>';
				woocommerce_wp_checkbox(
					array(
						'value'         => 'no',
						'id'            => '_lottery_delete_log_on_relist',
						'wrapper_class' => 'relist_lottery_dates_fields',
						'label'         => esc_html__( 'Delete logs on relist?', 'wc_lottery' ),
						'description'   => esc_html__( "Delete all logs for this lottery on relist. It can't be undone!", 'wc_lottery' ),
						'desc_tip'      => 'true',
					)
				);

				echo '</p>';

		}
		wp_nonce_field( 'save_lottery_data_' . $post->ID, 'save_lottery_data' );
		do_action( 'woocommerce_product_options_lottery' );

		echo '</div>';
	}

	/**
	 * Saves the data inputed into the product boxes, as post meta data
	 *
	 *
	 * @param int $post_id the post (product) identifier
	 * @param stdClass $post the post (product)
	 *
	 */
	public function product_save_data( $post_id, $post ) {

		$product_type = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( wc_clean( $_POST['product-type'] ) );

		if ( $product_type == 'lottery' ) {

			$product = wc_get_product( $post_id );

			if ( isset( $_POST['_max_tickets'] ) && ! empty( $_POST['_max_tickets'] ) ) {

				update_post_meta( $post_id, '_manage_stock', 'yes' );

				if ( get_post_meta( $post_id, '_lottery_participants_count', true ) ) {
					update_post_meta( $post_id, '_stock', intval( wc_clean( $_POST['_max_tickets'] ) ) - intval( get_post_meta( $post_id, '_lottery_participants_count', true ) ) );
				} else {
					update_post_meta( $post_id, '_stock', wc_clean( $_POST['_max_tickets'] ) );
				}

				update_post_meta( $post_id, '_backorders', 'no' );
			} else {

				update_post_meta( $post_id, '_manage_stock', 'no' );
				update_post_meta( $post_id, '_backorders', 'no' );
				update_post_meta( $post_id, '_stock_status', 'instock' );

			}

			if ( isset( $_POST['_lottery_price'] ) && '' !== $_POST['_lottery_price'] ) {

				$lottey_price = wc_format_decimal( wc_clean( $_POST['_lottery_price'] ) );

				update_post_meta( $post_id, '_lottery_price', $lottey_price );
				update_post_meta( $post_id, '_regular_price', $lottey_price );
				update_post_meta( $post_id, '_price', $lottey_price );

			} else {
				delete_post_meta( $post_id, '_lottery_price' );
				delete_post_meta( $post_id, '_regular_price' );
				delete_post_meta( $post_id, '_price' );

			}

			if ( isset( $_POST['_lottery_sale_price'] ) && '' !== $_POST['_lottery_sale_price'] ) {
				$lottey_sale_price = wc_format_decimal( wc_clean( $_POST['_lottery_sale_price'] ) );
				update_post_meta( $post_id, '_lottery_sale_price', $lottey_sale_price );
				update_post_meta( $post_id, '_sale_price', $lottey_sale_price );
				update_post_meta( $post_id, '_price', $lottey_sale_price );
			} else {
				delete_post_meta( $post_id, '_lottery_sale_price' );
				delete_post_meta( $post_id, '_sale_price' );
			}
			if ( ( $_POST['_lottery_price'] == 0 || ! isset( $_POST['_lottery_price'] ) ) && ( ! isset( $_POST['_max_tickets_per_user'] ) or empty( $_POST['_max_tickets_per_user'] ) ) ) {
					update_post_meta( $post_id, '_sold_individually', 'yes' );
			}
			if ( isset( $_POST['_max_tickets_per_user'] ) && ! empty( $_POST['_max_tickets_per_user'] ) ) {
				update_post_meta( $post_id, '_max_tickets_per_user', wc_clean( $_POST['_max_tickets_per_user'] ) );
				if ( $_POST['_max_tickets_per_user'] <= 1 ) {
					update_post_meta( $post_id, '_sold_individually', 'yes' );
				} else {
					update_post_meta( $post_id, '_sold_individually', 'no' );
				}
			} else{
				delete_post_meta( $post_id, '_max_tickets_per_user' );
				update_post_meta( $post_id, '_sold_individually', 'no' );
			}

			if ( isset( $_POST['_lottery_num_winners'] ) && ! empty( $_POST['_lottery_num_winners'] ) ) {
				update_post_meta( $post_id, '_lottery_num_winners', wc_clean( $_POST['_lottery_num_winners'] ) );
				if ( $_POST['_lottery_num_winners'] <= 1 ) {
					update_post_meta( $post_id, '_lottery_multiple_winner_per_user', 'no' );
				} else {
					if ( isset( $_POST['_lottery_multiple_winner_per_user'] ) && ! empty( $_POST['_lottery_multiple_winner_per_user'] ) ) {
						update_post_meta( $post_id, '_lottery_multiple_winner_per_user', 'yes' );
					} else {
						update_post_meta( $post_id, '_lottery_multiple_winner_per_user', 'no' );
					}
				}
			}

			if ( isset( $_POST['_min_tickets'] ) ) {
				update_post_meta( $post_id, '_min_tickets', wc_clean( $_POST['_min_tickets'] ) );
			} else{
				delete_post_meta( $post_id, '_min_tickets' );
			}
			if ( isset( $_POST['_max_tickets'] ) ) {
				update_post_meta( $post_id, '_max_tickets', wc_clean( $_POST['_max_tickets'] ) );
			} else{
				delete_post_meta( $post_id, '_max_tickets' );
			}
			if ( isset( $_POST['_lottery_dates_from'] ) ) {
				update_post_meta( $post_id, '_lottery_dates_from', wc_clean( $_POST['_lottery_dates_from'] ) );
			}
			if ( isset( $_POST['_lottery_dates_to'] ) ) {
				update_post_meta( $post_id, '_lottery_dates_to', wc_clean( $_POST['_lottery_dates_to'] ) );
			}

			if ( isset( $_POST['_relist_lottery_dates_from'] ) && isset( $_POST['_relist_lottery_dates_to'] ) && ! empty( $_POST['_relist_lottery_dates_from'] ) && ! empty( $_POST['_relist_lottery_dates_to'] ) ) {
				$this->do_relist( $post_id, $_POST['_relist_lottery_dates_from'], $_POST['_relist_lottery_dates_to'] );
			}
			if ( isset( $_POST['_extend_lottery_dates_to'] ) && ! empty( $_POST['_extend_lottery_dates_to'] ) ) {
				$this->do_extend( $post_id, $_POST['_extend_lottery_dates_to'] );
			}
				
			if ( isset( $_POST['clear_on_hold_orders'] ) ) {
				delete_post_meta( $post_id, '_order_hold_on' );
			}



			$product->lottery_update_lookup_table();
		}
	}

	/**
	 * Relist  lottery
	 *
	 * @access public
	 * @param int, string, string
	 * @return void
	 *
	 */
	function do_relist( $post_id, $relist_from, $relist_to ) {
		global $wpdb;

		update_post_meta( $post_id, '_lottery_dates_from', stripslashes( $relist_from ) );
		update_post_meta( $post_id, '_lottery_dates_to', stripslashes( $relist_to ) );
		update_post_meta( $post_id, '_lottery_relisted', current_time( 'mysql' ) );
		delete_post_meta( $post_id, '_lottery_closed' );
		delete_post_meta( $post_id, '_lottery_started' );
		delete_post_meta( $post_id, '_lottery_has_started' );
		delete_post_meta( $post_id, '_lottery_fail_reason' );
		delete_post_meta( $post_id, '_lottery_participant_id' );
		delete_post_meta( $post_id, '_lottery_participants_count' );
		delete_post_meta( $post_id, '_lottery_winners' );
		delete_post_meta( $post_id, '_participant_id' );
		update_post_meta( $post_id, '_lottery_relisted', current_time( 'mysql' ) );
		delete_post_meta( $post_id, '_order_hold_on' );

		$lottery_max_tickets = get_post_meta( $post_id, '_max_tickets', true );
		update_post_meta( $post_id, '_stock', $lottery_max_tickets );
		update_post_meta( $post_id, '_stock_status', 'instock' );

		$order_id = get_post_meta( $post_id, '_order_id', true );
		// check if the custom field has a value
		if ( ! empty( $order_id ) ) {
			delete_post_meta( $post_id, '_order_id' );
		}

		$wpdb->delete(
			$wpdb->usermeta, array(
				'meta_key'   => 'my_lotteries',
				'meta_value' => $post_id,
			), array( '%s', '%s' )
		);

		if ( ! empty( $_POST['_lottery_delete_log_on_relist'] ) ) {

			if ( ! isset( $_POST['save_lottery_data'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['save_lottery_data'] ), 'save_lottery_data_' . $post_id ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
			} else {
				if ( 'yes' === sanitize_text_field( $_POST['_lottery_delete_log_on_relist'] ) ) {
					$this->del_lottery_logs( $post_id );
				}
			}
		}

		do_action( 'woocommerce_lottery_do_relist', $post_id, $relist_from, $relist_to );
	}
	/**
	 * Extend  lottery
	 *
	 * @access public
	 * @param int, string, string
	 * @return void
	 *
	 */
	function do_extend( $post_id, $extend_to ) {
		update_post_meta( $post_id, '_lottery_dates_to', stripslashes( $extend_to ) );
		update_post_meta( $post_id, '_lottery_extended', current_time( 'mysql' ) );
		delete_post_meta( $post_id, '_lottery_closed' );
		delete_post_meta( $post_id, '_lottery_fail_reason' );

		do_action( 'woocommerce_lottery_do_extend', $post_id, $extend_to);
	}
	/**
	 * Add lottery column in product list in wp-admin
	 *
	 * @access public
	 * @param array
	 * @return array
	 *
	 */
	function woocommerce_simple_lottery_order_column_lottery( $defaults ) {

		$defaults['lottery'] = "<img src='" . plugin_dir_url( __FILE__ ) . 'images/lottery.png' . "' alt='" . __( 'Lottery', 'wc_lottery' ) . "' />";

		return $defaults;
	}

	/**
	 * Add lottery icons in product list in wp-admin
	 *
	 * @access public
	 * @param string, string
	 * @return void
	 *
	 */
	function woocommerce_simple_lottery_order_column_lottery_content( $column_name, $post_ID ) {

		if ( $column_name == 'lottery' ) {
			$class = '';

			$product_data = wc_get_product( $post_ID );
			if ( $product_data ) {
				$product_data_type = method_exists( $product_data, 'get_type' ) ? $product_data->get_type() : $product_data->product_type;
				if ( is_object( $product_data ) && $product_data_type == 'lottery' ) {
					if ( $product_data->is_closed() ) {
						$class .= ' finished ';
					}

					echo "<img src='" . plugin_dir_url( __FILE__ ) . 'images/lottery.png' . "' alt='" . __( 'Lottery', 'wc_lottery' ) . "' class='$class' />";
				}
				if ( get_post_meta( $post_ID, '_lottery', true ) ) {
					echo "<img src='" . plugin_dir_url( __FILE__ ) . 'images/lottery.png' . "' alt='" . __( 'Lottery', 'wc_lottery' ) . "' class='order' />";
				}
			}
		}
	}

	/**
	 * Add dropdown to filter lottery
	 *
	 * @param  (wp_query object) $query
	 *
	 * @return Void
	 */
	function admin_posts_filter_restrict_manage_posts() {

		//only add filter to post type you want
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
			$values = array(
				'Active'   => 'active',
				'Finished' => 'finished',
				'Fail'     => 'fail',

			);
			?>
			<select name="wc_lottery_filter">
			<option value=""><?php _e( 'Lottery filter By ', 'wc_lottery' ); ?></option>
			<?php
				$current_v = isset( $_GET['wcl_filter'] ) ? $_GET['wcl_filter'] : '';
			foreach ( $values as $label => $value ) {
				printf(
					'<option value="%s"%s>%s</option>',
					$value,
					$value == $current_v ? ' selected="selected"' : '',
					$label
				);
			}
			?>
			</select>
			<?php
		}
	}

	/**
	 * If submitted filter by post meta
	 *
	 * make sure to change META_KEY to the actual meta key
	 * and POST_TYPE to the name of your custom post type
	 * @param  (wp_query object) $query
	 *
	 * @return Void
	 */
	function admin_posts_filter( $query ) {
		global $pagenow;

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' && is_admin() && $pagenow == 'edit.php' && isset( $_GET['wc_lottery_filter'] ) && $_GET['wc_lottery_filter'] != '' ) {

			switch ( $_GET['wc_lottery_filter'] ) {
				case 'active':
					$query->query_vars['meta_query'] = array(

						array(
							'key'     => '_lottery_closed',
							'compare' => 'NOT EXISTS',
						),
					);

					$taxquery = $query->get( 'tax_query' );
					if ( ! is_array( $taxquery ) ) {
						$taxquery = array();
					}
					$taxquery [] =
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => 'lottery',

						);

					$query->set( 'tax_query', $taxquery );
					break;
				case 'finished':
					$query->query_vars['meta_query'] = array(

						array(
							'key'     => '_lottery_closed',
							'compare' => 'EXISTS',
						),
					);

					break;
				case 'fail':
					$query->query_vars['meta_key']   = '_lottery_closed';
					$query->query_vars['meta_value'] = '1';

					break;

			}
		}
	}

	/**
	 *  Add lottery setings tab to woocommerce setings page
	 *
	 * @access public
	 *
	 */
	function lottery_settings_class( $settings ) {

				$settings[] = include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-settings-lottery.php';
				return $settings;
	}

	/**
	 *  Add meta box to the product editing screen
	 *
	 * @access public
	 *
	 */
	function woocommerce_simple_lottery_meta() {

		global $post;

		$product_data = wc_get_product( $post->ID );
		if ( $product_data ) {
			$product_data_type = method_exists( $product_data, 'get_type' ) ? $product_data->get_type() : $product_data->product_type;
			if ( $product_data_type == 'lottery' ) {
				add_meta_box( 'Lottery', __( 'Lottery', 'wc_lottery' ), array( $this, 'woocommerce_simple_lottery_meta_callback' ), 'product', 'normal', 'default' );
			}
		}

	}

	/**
	 *  Callback for adding a meta box to the product editing screen used in woocommerce_simple_lottery_meta
	 *
	 * @access public
	 *
	 */
	function woocommerce_simple_lottery_meta_callback() {

		global $post;
			$product_data    = wc_get_product( $post->ID );
			$lottery_history = apply_filters( 'woocommerce__lottery_history_data', $product_data->lottery_history() );
			$heading         = esc_html( apply_filters( 'woocommerce_lottery_history_heading', __( 'Lottery History', 'wc_lottery' ) ) );
			$lottery_winers  = get_post_meta( $post->ID, '_lottery_winners' );
			$order_hold_on   = get_post_meta( $post->ID, '_order_hold_on' );

			?>
			<?php
			if ( $order_hold_on ) {
				$orders_links_on_hold = '';
				echo '<p>';
				_e( 'Some on hold orders are preventing this lottery to end. Can you please check it! ', 'wc_lottery' );
				foreach ( array_unique( $order_hold_on ) as $key => $order_hold_on_id ) {
					$orders_links_on_hold .= "<a href='" . admin_url( 'post.php?post=' . $order_hold_on_id . '&action=edit' ) . "'>$order_hold_on_id</a>, ";
				}
				echo rtrim( $orders_links_on_hold, ', ' );
				echo "<form><input type='hidden' name='clear_on_hold_orders' value='1' >";
				echo " <br><button class='button button-primary clear_orders_on_hold'  data-product_id='" . $product_data->get_id() . "'>" . __( 'Clear all on hold orders! ', 'wc_lottery' ) . '</button>';
				echo '</form>';
				echo '</p>';

			}

			$lottery_relisted = $product_data->get_lottery_relisted();
			if ( ! empty( $lottery_relisted ) ) {
			?>
				<p><?php esc_html_e( 'Lottery has been relisted on:', 'wc_lottery' ); ?> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $lottery_relisted )).' '.date_i18n( get_option( 'time_format' ), strtotime( $lottery_relisted )); ?></p>
			<?php
			}

			?>
			<?php if ( ( $product_data->is_closed() === true ) and ( $product_data->is_started() === true ) ) : ?>
				<p><?php _e( 'Lottery has finished', 'wc_lottery' ); ?></p>
				<?php
				if ( $product_data->get_lottery_fail_reason() == '1' ) {
					echo '<p>';
					 _e( 'Lottery failed because there were no participants', 'wc_lottery' );
					 echo '</p>';
				} elseif ( $product_data->get_lottery_fail_reason() == '2' ) {
					echo '<p>';
					_e( 'Lottery failed because there was not enough participants', 'wc_lottery' );
					echo " <button class='button button-primary do-api-refund' href='#' id='lottery-refund' data-product_id='" . $product_data->get_id() . "'>";
					_e( 'Refund ', 'wc_lottery' );
					echo '</button>';
					echo '<div id="refund-status"></div>';
					echo '<//p>';
				}
				if ( $lottery_winers ) {
					
					if ( count( $lottery_winers ) === 1 ) {
						
						$winnerid = reset( $lottery_winers );
						if ( ! empty( $winnerid ) ) {
						?>
								<p><?php _e( 'Lottery winner is', 'wc_lottery' ); ?>: <span><a href='<?php get_edit_user_link( $winnerid ); ?>'><?php echo get_userdata( $winnerid )->display_name; ?></a></span></p>
							<?php } ?>
					<?php } else { ?>

						<p><?php _e( 'Lottery winners are', 'wc_lottery' ); ?>:
							<ul>
							<?php
							foreach ( $lottery_winers as $key => $winnerid ) {
								if ( $winnerid > 0 ) {
								?>
									<li><a href='<?php get_edit_user_link( $winnerid ); ?>'><?php echo get_userdata( $winnerid )->display_name; ?></a></li>
							<?php
								}
							}
							?>
							</ul>

						</p>

					<?php } ?>

				<?php } ?>

			<?php endif; ?>

			<h2><?php echo $heading; ?></h2>
			<table class="lottery-table">
					<thead>
						<tr>
							<th><?php _e( 'Date', 'wc_lottery' ); ?></th>
							<th><?php _e( 'User', 'wc_lottery' ); ?></th>
							<th><?php _e( 'Order', 'wc_lottery' ); ?></th>
							<th class="actions"><?php _e( 'Actions', 'wc_lottery' ); ?></th>
						</tr>
					</thead>

					<?php
					if ( $lottery_history ) :
					foreach ( $lottery_history as $history_value ) {

						if ( $history_value->date < $product_data->get_lottery_relisted() && ! isset( $displayed_relist ) ) {
							echo '<tr>';
							echo '<td class="date">'. date_i18n( get_option( 'date_format' ), strtotime( $product_data->get_lottery_dates_from() )).' '.date_i18n( get_option( 'time_format' ), strtotime( $product_data->get_lottery_dates_from() )) . '</td>';
							echo '<td colspan="4"  class="relist">';
							echo __( 'Lottery relisted', 'wc_lottery' );
							echo '</td>';
							echo '</tr>';
							$displayed_relist = true;
						}
						echo '<tr>';
						echo '<td class="date">'. date_i18n( get_option( 'date_format' ), strtotime( $history_value->date ) ).' '.date_i18n( get_option( 'time_format' ), strtotime( $history_value->date ) ). '</td>';
						echo "<td class='username'><a href='" . get_edit_user_link( $history_value->userid ) . "'>" . get_userdata( $history_value->userid )->display_name . '</a></td>';
						echo "<td class='username'><a href='" . admin_url( 'post.php?post=' . $history_value->orderid . '&action=edit' ) . "'>" . $history_value->orderid . '</a></td>';
						echo "<td class='action'> <a href='#' data-id='" . $history_value->id . "' data-postid='" . $post->ID . "'    >" . __( 'Delete', 'wc_lottery' ) . '</a></td>';
						echo '</tr>';
					}
					endif;
					?>
					<tr class="start">
						<?php
						if ( $product_data->is_started() === true ) {
							echo '<td class="date">' . date_i18n( get_option( 'date_format' ), strtotime( $product_data->get_lottery_dates_from() )).' '.date_i18n( get_option( 'time_format' ), strtotime( $product_data->get_lottery_dates_from() )) . '</td>';
							echo '<td colspan="3"  class="started">';
							echo apply_filters( 'lottery_history_started_text', __( 'Lottery started', 'wc_lottery' ), $product_data );
							echo '</td>';

						} else {
							echo '<td  class="date">' . date_i18n( get_option( 'date_format' ), strtotime( $product_data->get_lottery_dates_from() )).' '.date_i18n( get_option( 'time_format' ), strtotime( $product_data->get_lottery_dates_from() )) . '</td>';
							echo '<td colspan="3"  class="starting">';
							echo apply_filters( 'lottery_history_starting_text', __( 'Lottery starting', 'wc_lottery' ), $product_data );
							echo '</td>';
						}
						?>
					</tr>

				
			</table>
			</ul>
			<?php
	}
	 /**
	 * Lottery order hold on
	 *
	 * Checks for lottery product in order when order is created on checkout before payment
	 * @access public
	 * @param int, array
	 * @return void
	 */
	function lottery_order_hold_on( $order_id ) {

		$order = new WC_Order( $order_id );
		if ( $order ) {
			if ( $order_items = $order->get_items() ) {
				foreach ( $order_items as $item_id => $item ) {
					if ( function_exists( 'wc_get_order_item_meta' ) ){
						$item_meta = wc_get_order_item_meta( $item_id, '' );
					} else{
						$item_meta    = method_exists( $order, 'wc_get_order_item_meta' ) ? $order->wc_get_order_item_meta( $item_id ) : $order->get_item_meta( $item_id );
					}
					$product_id   = $this->get_main_wpml_product_id( $item_meta['_product_id'][0] );
					$product_data = wc_get_product( $product_id );
					if ( $product_data && $product_data->get_type() == 'lottery' ) {
						update_post_meta( $order_id, '_lottery', '1' );
						add_post_meta( $product_id, '_order_hold_on', $order_id );
					}
				}
			}
		}
	}
	 /**
	 * Lottery order
	 *
	 * Checks for lottery product in order and assign order id to lottery product
	 *
	 * @access public
	 * @param int, array
	 * @return void
	 */
	function lottery_order( $order_id ) {
		global $wpdb;
		$log = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE orderid=%d', $order_id ) );

		if ( ! is_null( $log ) ) {
			return;
		}

		$order = new WC_Order( $order_id );

		if ( $order ) {
			if ( $order_items = $order->get_items() ) {
				foreach ( $order_items as $item_id => $item ) {
					if ( function_exists( 'wc_get_order_item_meta' ) ){
						$item_meta = wc_get_order_item_meta( $item_id, '' );
					} else{
						$item_meta    = method_exists( $order, 'wc_get_order_item_meta' ) ? $order->wc_get_order_item_meta( $item_id ) : $order->get_item_meta( $item_id );
					}
					$product_id   = $this->get_main_wpml_product_id( $item_meta['_product_id'][0] );
					$product_data = wc_get_product( $product_id );
					if ( $product_data && $product_data->get_type() == 'lottery' ) {
						update_post_meta( $order_id, '_lottery', '1' );
						add_post_meta( $product_id, '_order_id', $order_id );
						delete_post_meta( $product_id, '_order_hold_on', $order_id );
						$log_ids = array();
						for ( $i = 0; $i < $item_meta['_qty'][0]; $i++ ) {
							add_post_meta( $product_id, '_participant_id', $order->get_user_id() );
							$participants = get_post_meta( $product_id, '_lottery_participants_count', true ) ? get_post_meta( $product_id, '_lottery_participants_count', true ) : 0;
							update_post_meta( $product_id, '_lottery_participants_count', intval( $participants ) + 1 );
							$this->add_lottery_to_user_metafield( $product_id, $order->get_user_id() );
							$log_ids[] = $this->log_participant( $product_id, $order->get_user_id(), $order_id, $item );
						}
						do_action( 'wc_lottery_participate', $product_id, $order->get_user_id(), $order_id, $log_ids, $item, $item_id );
					}
				}
			}
		}
	}


	/**
	 * Lottery order canceled
	 *
	 * Checks for lottery product in order and assign order id to lottery product
	 *
	 * @access public
	 * @param int, array
	 * @return void
	 */
	function lottery_order_canceled( $order_id ) {
		global $wpdb;

		$log = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE orderid=%d', $order_id ) );

		if ( is_null( $log ) ) {
			return;
		}

		$order = new WC_Order( $order_id );

		if ( $order ) {

			if ( $order_items = $order->get_items() ) {

				foreach ( $order_items as $item_id => $item ) {
					if ( function_exists( 'wc_get_order_item_meta' ) ){
						$item_meta = wc_get_order_item_meta( $item_id, '' );
					} else{
						$item_meta    = method_exists( $order, 'wc_get_order_item_meta' ) ? $order->wc_get_order_item_meta( $item_id ) : $order->get_item_meta( $item_id );
					}
					$product_id   = $this->get_main_wpml_product_id( $item_meta['_product_id'][0] );
					$product_data = wc_get_product( $product_id );
					if ( $product_data ) {
						$product_data_type = method_exists( $product_data, 'get_type' ) ? $product_data->get_type() : $product_data->product_type;
						if ( $product_data_type == 'lottery' ) {

							update_post_meta( $order_id, '_lottery', '1' );
							add_post_meta( $product_id, '_order_id', $order_id );
							delete_post_meta( $product_id, '_order_hold_on', $order_id );
							$log_ids = array();
							delete_post_meta( $product_id, '_participant_id', $order->get_user_id() );
							for ( $i = 0; $i < $item_meta['_qty'][0]; $i++ ) {


								$participants = get_post_meta( $product_id, '_lottery_participants_count', true ) ? get_post_meta( $product_id, '_lottery_participants_count', true ) : 0;

								if ( $participants > 0 ) {
									update_post_meta( $product_id, '_lottery_participants_count', intval( $participants ) - 1 );
								}
								$this->remove_lottery_from_user_metafield( $product_id, $order->get_user_id() );
								$log_ids[] = $this->delete_log_participant( $product_id, $order->get_user_id(), $order_id );

							}
							$count_from_lottery_logs =  $this->get_count_from_lottery_logs($product_id, $order->get_user_id() );
							if( !empty( $count_from_lottery_logs ) ){
								$i=0;
								while ( $i < intval( $count_from_lottery_logs ) ) {
									add_post_meta( $product_id, '_participant_id', $order->get_user_id() );
									$i++;
								}
							}

							do_action( 'wc_lottery_cancel_participation', $product_id, $order->get_user_id(), $order_id, $log_ids, $item, $item_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Lottery order failed
	 *
	 * Checks for lottery product in failed order
	 *
	 * @access public
	 * @param int, array
	 * @return void
	 */
	function lottery_order_failed( $order_id ) {
		global $wpdb;

		$order = new WC_Order( $order_id );

		if ( $order ) {

			if ( $order_items = $order->get_items() ) {

				foreach ( $order_items as $item_id => $item ) {
					if ( function_exists( 'wc_get_order_item_meta' ) ){
						$item_meta = wc_get_order_item_meta( $item_id, '' );
					} else{
						$item_meta    = method_exists( $order, 'wc_get_order_item_meta' ) ? $order->wc_get_order_item_meta( $item_id ) : $order->get_item_meta( $item_id );
					}
					$product_id   = $this->get_main_wpml_product_id( $item_meta['_product_id'][0] );
					$product_data = wc_get_product( $product_id );
					if ( $product_data ) {
						$product_data_type = method_exists( $product_data, 'get_type' ) ? $product_data->get_type() : $product_data->product_type;
						if ( $product_data_type == 'lottery' ) {
							delete_post_meta( $product_id, '_order_hold_on', $order_id );
						}
					}
					do_action( 'wc_lottery_cancel_participation_failed', $product_id, $order->get_user_id(), $order_id, $log_ids = null , $item, $item_id );
				}
			}
		}
	}



	/**
	 * Delete logs when lottery is deleted
	 *
	 * @access public
	 * @param  string
	 * @return void
	 *
	 */
	function del_lottery_logs( $post_id ) {
		global $wpdb;

		if ( $wpdb->get_var( $wpdb->prepare( 'SELECT lottery_id FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE lottery_id = %d', $post_id ) ) ) {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE lottery_id = %d', $post_id ) );
		}

		return true;
	}

	/**
	 * Delete logs when lottery is deleted
	 *
	 * @access public
	 * @param  string
	 * @return void
	 *
	 */
	function get_count_from_lottery_logs( $post_id, $user_id ) {
		global $wpdb;

		if ( $result = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*)  FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE lottery_id = %d AND userid = %d', $post_id, $user_id ) ) ) {
			return $result;
		}

		return 0;
	}


	/**
	 * Duplicate post
	 *
	 * Clear metadata when copy lottery
	 *
	 * @access public
	 * @param  array
	 * @return string
	 *
	 */
	function woocommerce_duplicate_product( $postid ) {

		$product = wc_get_product( $postid );

		if ( ! $product ) {
			return false;
		}
		if ( $product->get_type() != 'lottery' ) {
			return false;
		}

		delete_post_meta( $postid, '_lottery_participants_count' );
		delete_post_meta( $postid, '_lottery_closed' );
		delete_post_meta( $postid, '_lottery_fail_reason' );
		delete_post_meta( $postid, '_lottery_dates_to' );
		delete_post_meta( $postid, '_lottery_dates_from' );
		delete_post_meta( $postid, '_order_id' );
		delete_post_meta( $postid, '_lottery_winners' );
		delete_post_meta( $postid, '_participant_id' );
		delete_post_meta( $postid, '_lottery_started' );
		delete_post_meta( $postid, '_lottery_has_started' );
		delete_post_meta( $postid, '_lottery_relisted' );

		return true;

	}

	 /**
	 * Log participant
	 *
	 * @param  int, int
	 * @return void
	 *
	 */
	public function log_participant( $product_id, $current_user_id, $order_id ) {

		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'wc_lottery_log', array(
				'userid'     => $current_user_id,
				'lottery_id' => $product_id,
				'orderid'    => $order_id,
				'date'       => current_time( 'mysql' ),
			), array( '%d', '%d', '%d', '%s' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Log Lottery  participant
	 *
	 * @param  int, int
	 * @return void
	 *
	 */
	public function delete_log_participant( $product_id, $current_user_id, $order_id ) {

		global $wpdb;

		$log_id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'wc_lottery_log  WHERE userid= %d AND lottery_id=%d AND orderid=%d', $current_user_id, $product_id, $order_id ) );
		if ( $log_id ) {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE userid= %d AND lottery_id=%d AND orderid=%d', $current_user_id, $product_id, $order_id ) );
		}
		return $log_id;
	}

	/**
	 * Ajax delete participate entry
	 *
	 * Function for deleting participate entry in wp admin
	 *
	 * @access public
	 * @param  array
	 * @return string
	 *
	 */
	function wp_ajax_delete_participate_entry() {

		global $wpdb;

		if ( ! current_user_can( 'edit_product', $_POST['postid'] ) ) {
			die();
		}
		$log_id = $_POST['logid'] ? intval( $_POST['logid'] ) : false;
		$post_id = $_POST['postid'] ? intval( $_POST['postid'] ) : false;

		if ( $post_id && $log_id ) {
			$product      = wc_get_product( $post_id );
			$log          = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE id=%d',  $log_id ) );
			$participants = get_post_meta( $post_id, '_participant_id', false );
			if ( ! is_null( $log ) ) {
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'wc_lottery_log WHERE id= %d', $log_id ) );
				$pos = array_search( $log->userid, $participants );
				unset( $participants[ $pos ] );
				delete_post_meta( $post_id, '_participant_id' );
				delete_post_meta( $post_id, '_order_id', $log->orderid );
				if ( ! $product->is_user_participating()) {
					$this->remove_lottery_from_user_metafield( $post_id, $log->userid );
				}
				$count = get_post_meta( $post_id, '_lottery_participants_count', true ) ? get_post_meta( $post_id, '_lottery_participants_count', true ) : 0;

				if ( $count > 0 ) {
						update_post_meta( $post_id, '_lottery_participants_count', intval( $count ) - 1 );
				}

				foreach ( $participants as $key => $value ) {
						add_post_meta( $post_id, '_participant_id', $value );
				}
				do_action('wc_lottery_delete_participate_entry' , $post_id, $log_id);
				wp_send_json( 'deleted' );
				exit;
			}
			wp_send_json( 'failed' );
			exit;
		}
		wp_send_json( 'failed' );
		exit;
	}

	/**
	 * Sync meta with wpml
	 *
	 * Sync meta trough translated post
	 *
	 * @access public
	 * @param bool $url (default: false)
	 * @return void
	 *
	 */
	function sync_metadata_wpml( $data ) {

		global $sitepress;

		$deflanguage = $sitepress->get_default_language();

		if ( is_array( $data ) ) {
				$product_id = $data['product_id'];
		} else {
				$product_id = $data;
		}

		$meta_values = get_post_meta( $product_id );
		$orginalid   = $sitepress->get_original_element_id( $product_id, 'post_product' );
		$trid        = $sitepress->get_element_trid( $product_id, 'post_product' );
		$all_posts   = $sitepress->get_element_translations( $trid, 'post_product' );

		unset( $all_posts[ $deflanguage ] );

		if ( ! empty( $all_posts ) ) {

			foreach ( $all_posts as $key => $translatedpost ) {

				if ( isset( $meta_values['_max_tickets'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_max_tickets', $meta_values['_max_tickets'][0] );
				}
				if ( isset( $meta_values['_min_tickets'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_min_tickets', $meta_values['_min_tickets'][0] );
				}
				if ( isset( $meta_values['_lottery_num_winners'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_num_winners', $meta_values['_lottery_num_winners'][0] );
				}
				if ( isset( $meta_values['_lottery_dates_from'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_dates_from', $meta_values['_lottery_dates_from'][0] );
				}
				if ( isset( $meta_values['_lottery_dates_to'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_dates_to', $meta_values['_lottery_dates_to'][0] );
				}
				if ( isset( $meta_values['_lottery_closed'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_closed', $meta_values['_lottery_closed'][0] );
				}
				if ( isset( $meta_values['_lottery_fail_reason'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_fail_reason', $meta_values['_lottery_fail_reason'][0] );
				}
				if ( isset( $meta_values['_order_id'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_order_id', $meta_values['_order_id'][0] );
				}

				if ( isset( $meta_values['_lottery_participants_count'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_participants_count', $meta_values['_lottery_participants_count'][0] );
				}
				if ( isset( $meta_values['_lottery_winners'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_winners', $meta_values['_lottery_winners'][0] );
				}
				if ( isset( $meta_values['_participant_id'][0] ) ) {
						delete_post_meta( $translatedpost->element_id, '_participant_id' );
					foreach ( $meta_values['_lottery_winners'] as $key => $value ) {
							add_post_meta( $translatedpost->element_id, '_participant_id', $value );
					}
				}

				if ( isset( $meta_values['_regular_price'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_regular_price', $meta_values['_regular_price'][0] );
				}
				if ( isset( $meta_values['_lottery_wpml_language'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_wpml_language', $meta_values['_lottery_wpml_language'][0] );
				}
			}
		}
	}
	/**
	 *
	 * Add last language in use to custom meta of lottery
	 *
	 * @access public
	 * @param int
	 * @return void
	 *
	 */
	function add_language_wpml_meta( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

		$language = isset( $_SESSION['wpml_globalcart_language'] ) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
		update_post_meta( $product_id, '_lottery_wpml_language', $language );
	}

	function change_email_language( $product_id ) {

		global $sitepress;

		$lang = get_post_meta( $product_id, '_lottery_wpml_language', true );

		if ( $lang ) {

			$sitepress->switch_lang( $lang, true );
			unload_textdomain( 'woocommerce' );
			unload_textdomain( 'default' );
			wc()->load_plugin_textdomain();
			load_default_textdomain();
			global $wp_locale;
			$wp_locale = new WP_Locale();
		}
	}

	/**
	 * Handle a refund via the edit order screen.
	 */
	public static function lottery_refund() {

		check_ajax_referer( 'lottery-refund', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die( -1 );
		}

		$item_ids = array();
		$succes   = array();
		$error    = array();

		$product_id    = absint( $_POST['product_id'] );
		$refund_amount = 0;
		$refund_reason = __( 'Lottery failed. No minimum ticket sold', 'wc_lottery' );
		$refund        = false;
		$response_data = array();

		$orders = $this->get_product_orders( $product_id );

		$lottery_order_refunded = get_post_meta( $product_id, '_lottery_order_refunded' );

		foreach ( $orders as $key => $order_id ) {

			if ( in_array( $order_id, $lottery_order_refunded ) ) {
				$error[ $order_id ] = __( 'Lottery amount allready returned', 'wc_lottery' );
				continue;
			}

			try {

				// Validate that the refund can occur
				$order         = wc_get_order( $order_id );
				$order_items   = $order->get_items();
				$refund_amount = 0;

				// Prepare line items which we are refunding
				$line_items = array();
				$item_ids    = array();
				if ( $order_items = $order->get_items() ) {

					foreach ( $order_items as $item_id => $item ) {

						if ( function_exists( 'wc_get_order_item_meta' ) ) {
							$item_meta = wc_get_order_item_meta( $item_id, '' );
						} else {
							$item_meta = method_exists( $order, 'wc_get_order_item_meta' ) ? $order->wc_get_order_item_meta( $item_id ) : $order->get_item_meta( $item_id );
						}

						$product_data = wc_get_product( $item_meta['_product_id'][0] );
						if ( $product_data->get_type() == 'lottery' && $item_meta['_product_id'][0] == $product_id ) {
							$item_ids[]                            = $product_data->get_id();
							$refund_amount                         = wc_format_decimal( $refund_amount ) + wc_format_decimal( $item_meta['_line_total'] [0] );
							$line_items[ $product_data->get_id() ] = array(
								'qty'          => $item_meta['_qty'],
								'refund_total' => wc_format_decimal( $item_meta['_line_total'] ),
								'refund_tax'   => array_map( 'wc_format_decimal', $item_meta['_line_tax_data'] ),
							);

						}
					}
				}

				$max_refund = wc_format_decimal( $refund_amount - $order->get_total_refunded() );

				if ( ! $refund_amount || $max_refund < $refund_amount || 0 > $refund_amount ) {
					throw new exception( __( 'Invalid refund amount', 'wc_lottery' ) );
				}

				if ( WC()->payment_gateways() ) {
					$payment_gateways = WC()->payment_gateways->payment_gateways();
				}

				$payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;

				if ( isset( $payment_gateways[ $payment_method ] ) && $payment_gateways[ $payment_method ]->supports( 'refunds' ) ) {
					$result = $payment_gateways[ $payment_method ]->process_refund( $order_id, $refund_amount, $refund_reason );

					do_action( 'woocommerce_refund_processed', $refund, $result );

					if ( is_wp_error( $result ) ) {
						throw new Exception( $result->get_error_message() );
					} elseif ( ! $result ) {
						throw new Exception( __( 'Refund failed', 'wc_lottery' ) );
					} else {
						// Create the refund object
						$refund = wc_create_refund(
							array(
								'amount'     => $refund_amount,
								'reason'     => $refund_reason,
								'order_id'   => $order_id,
								'line_items' => $line_items,
							)
						);

						if ( is_wp_error( $refund ) ) {
							throw new Exception( $refund->get_error_message() );
						}

						add_post_meta( $product_id, '_lottery_order_refunded', $order_id );
					}

					// Trigger notifications and status changes
					if ( $order->get_remaining_refund_amount() > 0 || ( $order->has_free_item() && $order->get_remaining_refund_items() > 0 ) ) {
						/**
						 * woocommerce_order_partially_refunded.
						 *
						 * @since 2.4.0
						 * Note: 3rd arg was added in err. Kept for bw compat. 2.4.3.
						 */
						do_action( 'woocommerce_order_partially_refunded', $order_id, $refund->id, $refund->id );
					} else {
						do_action( 'woocommerce_order_fully_refunded', $order_id, $refund->id );

						$order->update_status( apply_filters( 'woocommerce_order_fully_refunded_status', 'refunded', $order_id, $refund->id ) );
						$response_data['status'] = 'fully_refunded';
					}

					do_action( 'woocommerce_order_refunded', $order_id, $refund->id );

					// Clear transients
					wc_delete_shop_order_transients( $order_id );
					$succes[ $order_id ] = __( 'Refunded', 'woocommerce' );

				} elseif ( isset( $payment_gateways[ $payment_method ] ) && ! $payment_gateways[ $payment_method ]->supports( 'refunds' ) ) {
					$error[ $order_id ] = esc_html__( 'Payment gateway does not support refunds', 'wc_lottery' );
				}
			} catch ( Exception $e ) {
				if ( $refund && is_a( $refund, 'WC_Order_Refund' ) ) {
					wp_delete_post( $refund->id, true );
				}

				$error[ $order_id ] = $e->getMessage();
			}
		}

		wp_send_json(
			array(
				'error'  => $error,
				'succes' => $succes,
			)
		);

	}

	/**
	 * Get the orders for a product
	 *
	 * @since 1.0.1
	 * @param int $id the product ID to get orders for
	 * @param string fields  fields to retrieve
	 * @param string $filter filters to include in response
	 * @param string $status the order status to retrieve
	 * @param $page  $page   page to retrieve
	 * @return array
	 */
	public function get_product_orders( $id ) {
		global $wpdb;

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$order_ids = $wpdb->get_col(
			$wpdb->prepare( 
				"SELECT order_id
				FROM {$wpdb->prefix}woocommerce_order_items
				WHERE order_item_id IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND meta_value = %d )
				AND order_item_type = 'line_item' ", $id 
			)
		);

		if ( empty( $order_ids ) ) {
			return array( 'orders' => array() );
		}

		return $order_ids;

	}

	/**
	 * Ouput custom columns for products.
	 *
	 * @param string $column
	 *
	 */
	public function render_product_columns( $column ) {

		global $post, $the_product;

		if ( empty( $the_product ) || $the_product->get_id() != $post->ID ) {
			$the_product = wc_get_product( $post );
		}

		if ( $column == 'product_type' ) {
			$the_product_type = method_exists( $the_product, 'get_type' ) ? $the_product->get_type() : $the_product->product_type;
			if ( 'lottery' == $the_product_type ) {
					$class  = '';
					$closed = $the_product->get_lottery_closed();
				if ( $closed == '2' ) {
					$class .= ' finished '; }

				if ( $closed == '1' ) {
					$class .= ' fail '; }

					echo '<span class="lottery-status ' . $class . '"></span>';
			}
		}

	}

	/**
	 * Search for [vendor] tag in recipients and replace it with author email
	 *
	 */
	public function add_vendor_to_email_recipients( $recipient, $object ) {
		$key         = false;
		$author_info = false;
		$arrayrec    = explode( ',', $recipient );
		if ( ! $object ) {
			return $recipient;
		}

		$post_id     = method_exists( $object, 'get_id' ) ? $object->get_id() : $object->id;
		$post_author = get_post_field( 'post_author', $post_id );
		if ( ! empty( $post_author ) ) {
			$author_info = get_userdata( $post_author );
			$key         = array_search( $author_info->user_email, $arrayrec );
		}

		if ( ! $key && $author_info ) {
			$recipient = str_replace( '[vendor]', $author_info->user_email, $recipient );

		} else {
			$recipient = str_replace( '[vendor]', '', $recipient );
		}

		return $recipient;
	}
	 /**
	 * Get main product id for multilanguage purpose
	 *
	 * @access public
	 * @return int
	 *
	 */
	function get_main_wpml_product_id( $id ) {

		return intval( apply_filters( 'wpml_object_id', $id, 'product', false, apply_filters( 'wpml_default_language', null ) ) );

	}
	/**
	* Add lottery to user custom field
	*
	* @access public
	* @return void
	*
	*/
	function add_lottery_to_user_metafield( $product_id, $user_id ) {

		$my_lotteries = get_user_meta( $user_id, 'my_lotteries', false );
		if ( is_array($my_lotteries) && ! in_array( $product_id, $my_lotteries ) ) {
				add_user_meta( $user_id, 'my_lotteries', $product_id, false );
		}
	}
	/**
	* Delete lottery from user custom field
	*
	* @access public
	* @return void
	*
	*/
	function remove_lottery_from_user_metafield( $product_id, $user_id ) {
		$my_lotteries = get_user_meta( $user_id, 'my_lotteries', false );
		if ( in_array( $product_id, $my_lotteries ) ) {
			delete_user_meta( $user_id, 'my_lotteries', $product_id );
		}

	}

	/**
	 * Run plugin update
	 * WooCommerce is known to be active and initialized
	 *
	 */
	public function update() {
		global $wpdb;
		if ( version_compare( get_site_option( 'wc_lottery_version' ), '1.1.15', '<' ) ) {
			$users = $wpdb->get_results( 'SELECT DISTINCT userid FROM ' . $wpdb->prefix . 'wc_lottery_log ', ARRAY_N );

			if ( is_array( $users ) ) {
				foreach ( $users as $user_id ) {
					$user_lotteries = $wpdb->get_results( 'SELECT DISTINCT lottery_id FROM ' . $wpdb->prefix . "wc_lottery_log WHERE userid = $user_id[0] ", ARRAY_N );

					if ( isset( $user_lotteries ) && ! empty( $user_lotteries ) ) {
						foreach ( $user_lotteries as $lottery ) {
							add_user_meta( $user_id[0], 'my_lotteries', $lottery[0], false );
						}
					}
				}
			}
			update_option( 'wc_lottery_version', $this->version );
		}
	}

}
