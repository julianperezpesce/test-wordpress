<?php
/**
 * Participate in lottery template
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $product, $post;

$current_user               = wp_get_current_user();
$min_tickets                = $product->get_min_tickets();
$max_tickets                = $product->get_max_tickets();
$lottery_num_winners        = $product->get_lottery_num_winners();
$lottery_participants_count = !empty($product->get_lottery_participants_count()) ? $product->get_lottery_participants_count() : '0';
$lottery_dates_to           = $product->get_lottery_dates_to();
$lottery_dates_from         = $product->get_lottery_dates_from();


 if(($product->is_closed() === FALSE ) and ($product->is_started() === TRUE )) : ?>			
    <div class="lottery-time" id="countdown"><?php echo apply_filters('time_text', __( 'Time left:', 'wc_lottery' ), $product->get_type()); ?> 
            <div class="main-lottery lottery-time-countdown" data-time="<?php echo $product->get_seconds_remaining() ?>" data-lotteryid="<?php echo $product->get_id() ?>" data-format="<?php echo get_option( 'simple_lottery_countdown_format' ) ?>"></div>
    </div>

    <div class='lottery-ajax-change'>

            <p class="lottery-end"><?php echo __( 'Lottery ends:', 'wc_lottery' ); ?> <?php echo  date_i18n( get_option( 'date_format' ),  strtotime( $lottery_dates_to ));  ?>  <?php echo  date_i18n( get_option( 'time_format' ),  strtotime( $lottery_dates_to ));  ?> <br />
                    <?php printf(__('Timezone: %s','wc_lottery') , get_option('timezone_string') ? get_option('timezone_string') : __('UTC+','wc_lottery').get_option('gmt_offset')) ?>
            </p>

            <?php if($min_tickets &&($min_tickets > 0)  ) : ?>
                    <p class="min-pariticipants"><?php  printf( __( "This lottery has a minimum of %d tickets", 'wc_lottery'), $min_tickets ); ?></p>
            <?php endif; ?>	

            <?php if( $max_tickets  &&( $max_tickets > 0 )  ) : ?>
                    <p class="max-pariticipants"><?php  printf( __( "This lottery is limited to %s tickets", 'wc_lottery' ),$max_tickets ) ; ?></p>
            <?php endif; ?>

                    <p class="cureent-participating"> <?php _e( 'Tickets sold:', 'wc_lottery' )?> <?php echo  $lottery_participants_count ;?></p>

            <?php if( $max_tickets  &&( $max_tickets > 0 )  && (get_option( 'simple_lottery_progressbar' ,'yes' ) == 'yes') ) : ?>
                    
            <div class="wcl-progress-meter <?php if($product->is_max_tickets_met()) echo 'full' ?>">
                <span class="zero">0</span>
                <span class="max"><?php echo $max_tickets ?></span>
                <progress  max="<?php echo $max_tickets ?>" value="<?php echo $lottery_participants_count ?>"  low="<?php echo $min_tickets ?>"></progress>
            </div>

            <?php endif; ?>	

            <?php if(  $lottery_num_winners > 0  ) : ?>

            <p class="max-pariticipants"><?php  printf( _n( "This lottery will have %d winner" , "This lottery will have %d winners", $lottery_num_winners , 'wc_lottery' ) ,$lottery_num_winners ) ; ?></p>

            <?php endif; ?>
    </div>	

<?php elseif (($product->is_closed() === FALSE ) and ($product->is_started() === FALSE )):?>
	
	<div class="lottery-time future" id="countdown"><?php echo  __( 'Lottery starts in:', 'wc_lottery' ) ?> 
		<div class="lottery-time-countdown future" data-time="<?php echo $product->get_seconds_to_lottery() ?>" data-format="<?php echo get_option( 'simple_lottery_countdown_format' ) ?>"></div>
	</div>
	
	<p class="lottery-starts"><?php echo  __( 'Lottery starts:', 'wc_lottery' ) ?> <?php echo  date_i18n( get_option( 'date_format' ),  strtotime( $lottery_dates_from ));  ?>  <?php echo  date_i18n( get_option( 'time_format' ),  strtotime( $lottery_dates_from ));  ?></p>
	<p class="lottery-end"><?php echo  __( 'Lottery ends:', 'wc_lottery' ); ?> <?php echo  date_i18n( get_option( 'date_format' ),  strtotime( $lottery_dates_to ));  ?>  <?php echo  date_i18n( get_option( 'time_format' ),  strtotime( $lottery_dates_to ));  ?> </p>
	
<?php endif; 
