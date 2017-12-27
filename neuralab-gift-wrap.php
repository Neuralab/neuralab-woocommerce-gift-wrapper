<?php
/**
 * Plugin Name:  Neuralab WooCommerce Gift Wrapper
 * Description:  Simple plugin which offers gift wrap for bought merchandise
 * Version:      1.0
 * Author:       Petak @ Neuralab
 * Author URI:   neuralab.net
 * Text Domain:  neuralab-giftwrap
 * Domain Path:  /lang/
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.2.6
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Check if WooCommerce plugin is active
 *
 * @return bool Active state
 */
function neuralab_gift_wrap_woocommerce_active() {
  return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
}

/**
 * Return if WooCommerce is not active
 */
if ( ! neuralab_gift_wrap_woocommerce_active() ) {
  return;
}

/**
 * Load plugin textdomain.
 */
function neuralab_gift_wrap_textdomain() {
  load_plugin_textdomain( 'neuralab-giftwrap', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
}
add_action( 'plugins_loaded', 'neuralab_gift_wrap_textdomain' );

/**
 * Add settings link to plugin list
 */
function neuralab_gift_wrap_add_settings_link( $links ) {
  $settings_link = '<a href="edit.php?post_type=product&page=gift_wrap_section_menu">' . __( 'Settings' ) . '</a>';
  $links[]       = $settings_link;
  return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'neuralab_gift_wrap_add_settings_link' );

/**
 * Registers field options for gift wrap settings page
 */
function neuralab_gift_wrap_settings() {
  register_setting( 'gift_wrap', 'gift_wrap_enable' );
  register_setting( 'gift_wrap', 'gift_wrap_value' );
  register_setting( 'gift_wrap', 'gift_wrap_tax' );

  add_settings_field( 'gift_wrap_enable', __( 'Enable', 'neuralab-giftwrap' ), 'neuralab_gift_wrap_enable', 'gift_wrap', 'gift_wrap_section' );
  add_settings_field( 'gift_wrap_value', __( 'Price', 'neuralab-giftwrap' ), 'neuralab_gift_wrap_value', 'gift_wrap', 'gift_wrap_section' );
  add_settings_field( 'gift_wrap_tax', __( 'Tax', 'neuralab-giftwrap' ), 'neuralab_gift_wrap_tax', 'gift_wrap', 'gift_wrap_section' );
  add_settings_field( 'gift_wrap_feedback', __( 'Feedback', 'neuralab-giftwrap' ), 'neuralab_gift_wrap_feedback', 'gift_wrap', 'gift_wrap_section' );
}
add_action( 'admin_init', 'neuralab_gift_wrap_settings' );


/**
 * Gift wrap settings enable option
 *
 * @return string Enable gift wrap input
 */
function neuralab_gift_wrap_enable() {
  $enabled = (int) get_option( 'gift_wrap_enable', 0 );
  ?>
  <label>
    <input id="gift_wrap_enable" type="checkbox" name="gift_wrap_enable"  value="1" <?php checked( $enabled, 1 ) ?>>
    <?php _e( 'Enable gift wrapping', 'neuralab-giftwrap' ); ?>
  </label>
  <?php
}

/**
 * Gift wrap settings price option
 *
 * @return string Enter price input
 */
function neuralab_gift_wrap_value() {
  $gift_wrap_price = (float) get_option( 'gift_wrap_value' );
  ?>
  <input id="gift_wrap_value" type="number" name="gift_wrap_value" step="0.01" min="0"  value="<?php echo $gift_wrap_price ?>">
  <?php
}

/**
 * Gift wrap settings tax apply
 *
 * @return string Apply tax on price
 */
function neuralab_gift_wrap_tax() {
  $enabled = (int) get_option( 'gift_wrap_tax', 0 );
  ?>
  <label>
    <input id="gift_wrap_tax" type="checkbox" name="gift_wrap_tax"  value="1" <?php checked( $enabled, 1 ) ?>>
    <?php _e( 'Apply tax rates to gift wrapping', 'neuralab-giftwrap' ); ?>
    <small><?php _e( '(Be sure to enable tax in WooCommerce settings)', 'neuralab-giftwrap' ); ?></small>
  </label>
  <?php
}

/**
 * Gift wrap plugin feedback link section
 *
 * @return string Feedback link and text
 */
function neuralab_gift_wrap_feedback() {
  ?>
  <p><?php _e( 'You can provide feedback or comments on plugin\'s <a href="#">GitHub repo</a>.', 'neuralab-giftwrap' ); ?> </p>
  <?php
}


/**
 * Add gift wrap submenu settings inside of Products menu
 */
function neuralab_gift_wrap_add_submenu() {
  add_submenu_page( 'edit.php?post_type=product', 'Gift wrap', __( 'Gift Wrapper', 'neuralab-giftwrap' ), 'manage_options', 'gift_wrap_section_menu', 'neuralab_gift_wrap_settings_fields' );
}
add_action( 'admin_menu', 'neuralab_gift_wrap_add_submenu' );

/**
 * Creates options for gift wrap settings page
 *
 * @return string Gift wrap settings
 */
function neuralab_gift_wrap_settings_fields() {
  ?>
  <div class="wrapper">
    <h2><?php _e( 'Neuralab WooCommerce Gift Wrapper Settings', 'neuralab-giftwrap' ) ?></h2>
    <form method="post" action="options.php">
      <table class="form-table">
        <?php settings_fields( 'gift_wrap' ) ?>
        <?php do_settings_fields( 'gift_wrap', 'gift_wrap_section' ); ?>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

/**
 * Check if gift wrap option is enabled in its settings
 *
 * @return bool Display gift wrap option
 */
function neuralab_gift_wrap_option_enabled() {
  $gift_wrap_enable = (bool) get_option( 'gift_wrap_enable' );
  $gift_wrap_value  = (bool) get_option( 'gift_wrap_value' );

  if ( $gift_wrap_enable && $gift_wrap_value ) {
    return true;
  } else {
    return false;
  }
}

/**
 * Creates gift wrap option to display on checkout
 *
 * @return string Gift wrap option displayed to customer
 */
function neuralab_gift_wrap_option() {
  if ( neuralab_gift_wrap_option_enabled() ) {
    ?>
    <div class="gift-wrap">
      <h3><?php _e( 'Add Gift Wrapping', 'neuralab-giftwrap' ); ?> </h3>
      <?php
      woocommerce_form_field(
        'gift-wrap-check', [
          'type'        => 'checkbox',
          'class'       => [ 'gift-wrap-input' ],
          'label'       => sprintf( __( 'Wrap as a gift (%s)', 'neuralab-giftwrap' ), neuralab_gift_wrap_get_price_formatted() ),
          'placeholder' => '',
        ], WC()->checkout()->get_value( 'gift-wrap-check' )
      );
      ?>
    </div>
    <?php
  }
}
add_action( 'woocommerce_checkout_billing', 'neuralab_gift_wrap_option', 100 );

/**
 * Add plugin assets
 */
function neuralab_gift_wrap_scripts() {
  if ( is_checkout() ) {
    wp_enqueue_script( 'neuralab-gif-wrap-js', plugin_dir_url( __FILE__ ) . 'assets/js/gift-wrap.js', [ 'jquery' ], '1.0.0', true );
  }
}
add_action( 'wp_enqueue_scripts', 'neuralab_gift_wrap_scripts' );

/**
 * Check if tax rates should be applied
 *
 * @return bool Apply tax
 */
function neuralab_gift_wrap_tax_enabled() {
  $gift_wrap_tax = (bool) get_option( 'gift_wrap_tax' );
  if ( $gift_wrap_tax ) {
    return true;
  } else {
    return false;
  }
}

/**
 * Fetch raw price for further processing
 *
 * @return float Raw price
 */
function neuralab_gift_wrap_get_price() {
  return (float) get_option( 'gift_wrap_value' );
}

/**
 * Calculating tax on price and returning flat or taxed price
 *
 * @param  boolean $formatted Should the returned price be wrapped in wp_price
 * @return string             Formatted price
 * @return float              Raw price
 */

function neuralab_gift_wrap_tax_price( $formatted = false ) {
  if ( neuralab_gift_wrap_tax_enabled() ) {
    $woocommerce_tax_rates     = WC_Tax::get_rates();
    $gift_wrap_price_tax_array = WC_Tax::calc_tax( neuralab_gift_wrap_get_price(), $woocommerce_tax_rates, wc_prices_include_tax() );
    $gift_wrap_price_tax_raw   = WC_Tax::get_tax_total( $gift_wrap_price_tax_array );
    $gift_wrap_price_tax       = wc_round_tax_total( $gift_wrap_price_tax_raw );

    if ( $formatted ) {
      return neuralab_gift_wrap_get_price() + $gift_wrap_price_tax;
    } else {
      if ( wc_prices_include_tax() ) {
        return neuralab_gift_wrap_get_price() - $gift_wrap_price_tax;
      } else {
        return neuralab_gift_wrap_get_price();
      }
    }
  }
  return neuralab_gift_wrap_get_price();
}

/**
 * Get formatted price for front end
 *
 * @return string Formatted price
 */
function neuralab_gift_wrap_get_price_formatted() {
  if ( neuralab_gift_wrap_tax_enabled() ) {
    if ( ! wc_prices_include_tax() && WC()->cart->tax_display_cart === 'incl' ) {
      return wc_price( neuralab_gift_wrap_tax_price( true ) ) . ' ' . WC()->countries->inc_tax_or_vat();
    } elseif ( wc_prices_include_tax() && WC()->cart->tax_display_cart === 'excl' ) {
      return wc_price( neuralab_gift_wrap_tax_price() ) . ' ' . WC()->countries->ex_tax_or_vat();
    }
  }
  return wc_price( neuralab_gift_wrap_get_price() );
}

/**
 * Add gift wrap fee based on checkout data (if gift wrap checkbox is checked)
 *
 * @param  string $post_data Data from WooCommerce checkout
 */
function neuralab_gift_wrap_add_fee() {
  if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! neuralab_gift_wrap_option_enabled() ) {
    return;
  }
  /**
   * Ignore linter warning for not using nonce verification, not needed since WooCommerce takes care of it
   * @codingStandardsIgnoreStart
   */
  if ( isset( $_POST['post_data'] ) ) {
    parse_str( $_POST['post_data'], $post_data );
  } else {
    $post_data = $_POST;
  }
  /** @codingStandardsIgnoreEnd */

  if ( isset( $post_data['gift-wrap-check'] ) ) {
    WC()->cart->add_fee( esc_html( __( 'Gift Wrap', 'neuralab-giftwrap' ) ), neuralab_gift_wrap_tax_price(), neuralab_gift_wrap_tax_enabled() );
  }
}
add_action( 'woocommerce_cart_calculate_fees', 'neuralab_gift_wrap_add_fee' );

/**
 * Creating gift wrap fee suffix to display on checkout review
 *
 * @return string $suffix Fee suffix
 */
function neuralab_gift_wrap_fee_suffix() {
  $suffix = null;

  if ( WC()->cart->tax_display_cart === 'excl' && wc_prices_include_tax() ) {
    $suffix = WC()->countries->ex_tax_or_vat();
  } elseif ( WC()->cart->tax_display_cart === 'incl' && ! wc_prices_include_tax() ) {
    $suffix = WC()->countries->inc_tax_or_vat();
  }
  return ' <small>' . $suffix . '</small>';
}

/**
 * Adding gift wrap fee suffix
 *
 * @return string $cart_totals_fee_html Fee with suffix
 */

function neuralab_gift_wrap_add_fee_suffix( $cart_totals_fee_html, $fee ) {
  $gift_wrap_fee_id = sanitize_title( __( 'Gift Wrap', 'neuralab-giftwrap' ) ); /** Using WooCommerce function for sanitizing id so it only applies on gift wrap fee */

  if ( neuralab_gift_wrap_tax_enabled() && $fee->id === $gift_wrap_fee_id ) {
    $cart_totals_fee_html .= neuralab_gift_wrap_fee_suffix();
  }
  return $cart_totals_fee_html;
}
add_filter( 'woocommerce_cart_totals_fee_html', 'neuralab_gift_wrap_add_fee_suffix', 10, 2 );
