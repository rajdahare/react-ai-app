<?php

// Set maximum size of uploaded files
ini_set('upload_max_filesize', '200M');

// Set maximum size of POST data allowed
ini_set('post_max_size', '200M');

// Set maximum execution time of each script
ini_set('max_execution_time', 600); // 300 seconds (5 minutes)

// Set maximum time allowed for each input to be parsed
ini_set('max_input_time', 600); // 300 seconds (5 minutes)




if (!is_user_logged_in() && ($_SERVER['REQUEST_URI'] == '/')) {
    //echo $_SERVER['REQUEST_URI'];
    if (isset($_GET['i'])) {
        echo '<pre>';
        var_dump($_SERVER);
    }
    //exit;
    ?>
    <script>
        location = 'my-account/';
    </script>
    <?php
}



add_action('admin_head', 'my_custom_fonts');
function my_custom_fonts()
{
    echo '<style>
    ul.order_notes li a.delete_note {
    color: #a00;
    display: none;
} 
.woocommerce-layout__header {
    top: 0px;
}
  </style>';
}
// hide order notes delete option in dashboard

if (strpos($_SERVER['REQUEST_URI'], 'my-account/dashboard')) {
    ?>
    <script>
        location = '<?= str_replace('my-account/dashboard', "services-1", $_SERVER['REQUEST_URI']); ?>';
    </script>
    <?php
}

/*if (strpos($_SERVER['REQUEST_URI'], 'my-account/dashboard')) { 
    ?>
    <script>
        location = '<?= str_replace('my-account/dashboard',"services-1",$_SERVER['REQUEST_URI']); ?>';
    </script>
    <?php
    //var_dump($_SERVER['SCRIPT_URI']); exit;
}*/

// hide order notes delete option in dashboard

add_action('wp_enqueue_scripts', 'basel_child_enqueue_styles', 1000);

function basel_child_enqueue_styles()
{

    $version = basel_get_theme_info('Version');

    if (basel_get_opt('minified_css')) {
        wp_enqueue_style('basel-style', get_template_directory_uri() . '/style.min.css', array('bootstrap'), $version);
    } else {
        wp_enqueue_style('basel-style', get_template_directory_uri() . '/style.css', array('bootstrap'), $version);
    }

    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('bootstrap'), $version);
}


/**  * Bypass logout confirmation.  */
function iconic_bypass_logout_confirmation()
{
    global $wp;
    if (isset($wp->query_vars['customer-logout'])) {
        wp_redirect(str_replace('&amp;', '&', wp_logout_url(wc_get_page_permalink('myaccount'))));
        exit;
    }
}
add_action('template_redirect', 'iconic_bypass_logout_confirmation');
/**  * Bypass logout confirmation.  */




/* Remove Category Meta from product */
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
/* Remove Category Meta from product End */


/* Disable Woocommerce for Unregistered Users */
function woocommerce_redirect()
{
    if (
        !is_user_logged_in()
        && (is_woocommerce() || is_cart() || is_checkout())
    ) {
        // feel free to customize the following line to suit your needs
        wp_safe_redirect(home_url('/services-1'));

        exit;
    }
}
add_action('template_redirect', 'woocommerce_redirect');
/* Disable Woocommerce for Unregistered Users End */


/* Redirect user to account page on empty cart */
add_filter('woocommerce_return_to_shop_redirect', 'cart_change_return_shop_url', 10, 3);
function cart_change_return_shop_url()
{
    return home_url();
}
/* Redirect user to account page on empty cart End */


/*====== After login redirect to home page showing all services ========= */
/*add_filter('woocommerce_login_redirect', 'custom_wp_login_redirect', 10, 3);
  function custom_wc_login_redirect( $redirect, $user ) {
  //$redirect = home_url();
  return $redirect;
}*/
/*====== After login redirect to home page showing all services ========= */

// Prevent access to page with ID of 747 (home page) and all children of this page
add_action('template_redirect', function () {

    // Get global post
    global $post;

    $page_id = 747;
    $demopage_id = 349620;
    if (is_page() && ($post->post_parent == $page_id || is_page($page_id) || is_page($demopage_id))) {

        // Set redirect to true by default
        $redirect = true;

        // If logged in do not redirect
        // You can/should place additional checks here based on user roles or user meta
        if (is_user_logged_in()) {
            $redirect = false;
        }

        // Redirect people without access to login page
        if ($redirect) {
            wp_redirect(esc_url(home_url('/my-account')), 307);
        }
    }

});
// Prevent access to page with ID of 747 (home page) and all children of this page

/*	
Create New Column in Orders Table Column Name: Order Name
*/
function add_order_name_column($columns)
{
    $new_columns = array();
    foreach ($columns as $key => $name) {
        $new_columns[$key] = $name;
        if ('order-date' === $key) {
            $new_columns['order-name'] = __('Order Name', 'textdomain');
            //$new_columns['files-Recvd'] = __( 'Files Recvd', 'textdomain' );
        }
    }


    $new_order = array(
        'order-number' => 2, // First column
        'alg_mowc_suborders' => 3, // Second column
        'order-date' => 4, // Third column
        'order-name' => 1, // Fourth column
        'order-status' => 5,  // Last column
        'order-total' => 6,  // Last column
        //'etc_delay' => 0,
        'order-actions' => 7,
        //'files-Recvd' => 8,
    );



    // Sort columns based on predefined order
    uksort($new_columns, function ($a, $b) use ($new_order) {
        return ($new_order[$a] ?? 99) <=> ($new_order[$b] ?? 99);
    });

    return $new_columns;
}
add_filter('woocommerce_my_account_my_orders_columns', 'add_order_name_column');
/* 	Create New Column in Orders Table End */


/*	Fill new column in orders table with data Column Name: Order Name */
add_action('woocommerce_my_account_my_orders_column_order-name', 'add_account_orders_column_rows');

function add_account_orders_column_rows($order)
{
    // Example with a custom field
    $order_url = $order->get_view_order_url();
    foreach ($order->get_items() as $item_id => $item) {

        $formatted_meta_data = $item->get_formatted_meta_data(' ', true);
        //$order_name_value = $formatted_meta_data[100001];
        foreach ($formatted_meta_data as $order_data) {
            if (($order_data->display_key == 'Order Name') || ($order_data->display_key == 'Order name')) {
                $order_name = $order_data->display_value;
                $str = $order_name;
                /*if (strlen($str) > 20)
                    $str = substr($str, 0, 20) . '...';*/
                echo ('<a href=' . $order_url . ' title="' . strip_tags($order_name) . '">' . $str . '</a>');
            }
        }
    }
}
/*	Fill new column in orders table with data End */



/*
Register meta boxes on Admin Side Meta box Name: ETC / Delay
*/
function etc_meta_boxes()
{
    add_meta_box('etc', 'ETC / Delay', 'etc_display_callback', 'shop_order', 'side', 'high');
}
add_action('add_meta_boxes', 'etc_meta_boxes');
/*	Register meta boxes on Admin Side End */

/* 	Meta box ETC / Delay display callback */
function etc_display_callback($post)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    ?>
    <p class="meta-options hcf_field">
        <input id="etc_text" type="text" name="etc_text"
            value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'etc_text', true)); ?>">
    </p>
    <?php
}
/*	Meta box ETC / Delay display callback End */


/* 	Save Meta box ETC / Delay data */
function hcf_save_meta_box($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if ($parent_id = wp_is_post_revision($post_id)) {
        $post_id = $parent_id;
    }
    $fields = ['etc_text'];
    foreach ($fields as $field) {
        if (array_key_exists($field, $_POST)) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'hcf_save_meta_box');
/* 	Save Meta box ETC / Delay data End */

/*	
Create New Column in Orders Table Column Name: ETC / Delay*/
// function add_etc_delay_column( $columns ) {
// 	$new_columns = array();
// 	foreach ( $columns as $key => $name ) {
// 		$new_columns[ $key ] = $name;

// 		if ( 'order-total' === $key ) {
// 			$new_columns['etc_delay'] = __( 'ETC/Delay', 'textdomain' );
// 		}
// 	}
// 	return $new_columns;
// }
// add_filter( 'woocommerce_my_account_my_orders_columns', 'add_etc_delay_column' );
/* 	Create New Column in Orders Table End */


/*	
Fill new column in orders table with data Column Name: ETC / Delay
*/
add_action('woocommerce_my_account_my_orders_column_etc_delay', 'add_account_etc_delay_column_rows');
function add_account_etc_delay_column_rows($order)
{
    foreach ($order->get_items() as $item_id => $item) {
        $etc_delay = esc_attr(get_post_meta($order->ID, 'etc_text', true));
        if ($etc_delay === "") {
            echo "-";
        } else {
            echo $etc_delay;
        }
    }
}
/*	Fill new column in orders table with data End */


/*	Remove Product Link from Cart Page */
function sv_remove_cart_product_link($product_link, $cart_item, $cart_item_key)
{
    $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
    return $product->get_title();
}
add_filter('woocommerce_cart_item_name', 'sv_remove_cart_product_link', 10, 3);
/*	Remove Product Link from Cart Page End */


/*	Remove Product Image from Cart Page */
add_filter('woocommerce_cart_item_thumbnail', '__return_false');
/*	Remove Product Image from Cart Page End */


function wooc_extra_register_fields()
{ ?>
    <p class="form-row form-row-first">
        <label for="reg_billing_first_name"><?php _e('First name', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if (!empty($_POST['billing_first_name']))
            esc_attr_e($_POST['billing_first_name']); ?>" required />
    </p>
    <p class="form-row form-row-last">
        <label for="reg_billing_last_name"><?php _e('Last name', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if (!empty($_POST['billing_last_name']))
            esc_attr_e($_POST['billing_last_name']); ?>" required />
    </p>
    <p class="form-row form-row-wide">
        <label for="reg_billing_phone"><?php _e('Phone', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="number" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php if (!empty($_POST['billing_phone']))
            esc_attr_e($_POST['billing_phone']); ?>" required />
    </p>
    <div class="clear"></div>
    <?php
}
add_action('woocommerce_register_form_start', 'wooc_extra_register_fields', 20);


/**
 * register fields Validating.
 */
function wooc_validate_extra_register_fields($username, $email, $validation_errors)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
        $validation_errors->add('billing_first_name_error', __('<strong>Error</strong>: First name is required!', 'woocommerce'));
    }
    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {
        $validation_errors->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required!.', 'woocommerce'));
    }
    if (isset($_POST['billing_phone']) && empty($_POST['billing_phone'])) {
        $validation_errors->add('billing_phone_error', __('<strong>Error</strong>: Phone number is required!.', 'woocommerce'));
    }
    if (isset($_POST['email']) && empty($_POST['email'])) {
        $validation_errors->add('email_error', __('<strong>Error</strong>: Email is required!.', 'woocommerce'));
    }
    if (isset($_POST['terms']) && empty($_POST['terms'])) {
        $validation_errors->add('terms_error', __('<strong>Error</strong>: Mandatory field not selected!.', 'woocommerce'));
    }
    return $validation_errors;
}
add_action('woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3);

/**
 * Below code save extra fields.
 */
function wooc_save_extra_register_fields($customer_id)
{

    if (isset($_POST['billing_first_name'])) {
        //First name field which is by default
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
        // First name field which is used in WooCommerce
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
    }
    if (isset($_POST['billing_last_name'])) {
        // Last name field which is by default
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
        // Last name field which is used in WooCommerce
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
    }
    if (isset($_POST['billing_phone'])) {
        // Phone input filed which is used in WooCommerce
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
}

add_action('woocommerce_created_customer', 'wooc_save_extra_register_fields');

// Add term and conditions check box on registration form
add_action('woocommerce_register_form', 'add_terms_and_conditions_to_registration', 20);
function add_terms_and_conditions_to_registration()
{

    if (wc_get_page_id('terms') > 0 && is_account_page()) {
        ?>
        <p class="form-row terms wc-terms-and-conditions">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                    name="terms" id="terms" required oninvalid="this.setCustomValidity('Mandatory field not selected!')"
                    onchange="this.setCustomValidity('') " /> <span class="required"> * </span>
                By creating an account at RebookÃ¼, you certify that you are either a professional photographer or business
                that:
                <ul>
                    <li>Has full rights and permissions to all images being sent to RebookÃ¼</li>
                    <li>You are not using our services for personal use</li>
                    <li>You authorize RebookÃ¼ to bill your credit card 2x the total of all your transactions on your account if
                        it is discovered that you are not a professional photographer or business</li>
                </ul>
            </label>
            <input type="hidden" name="terms-field" value="1" />
        </p>
        <?php
    }
}
// Display message on my order page only
add_action('woocommerce_before_account_orders', 'message_before_myorder', 20);
function message_before_myorder($has_orders)
{
    $message = '<div class="wpb_wrapper myorder-msg"><h3>Please wait 2-3 minutes, and then refresh your page before clicking the view files button below</h3>
</div>';
    echo $message;
}
// display message on my order page only end

// Validate required term and conditions check box
/*
add_action( 'woocommerce_register_post', 'terms_and_conditions_validation', 20, 3 );
function terms_and_conditions_validation( $username, $email, $validation_errors ) {
    if ( ! isset( $_POST['terms'] ) )
        $validation_errors->add( 'terms_error', __( 'Mandatory field should be selected!', 'woocommerce' ) );

    return $validation_errors;
}*/

add_filter('woocommerce_registration_error_email_exists', function ($html) {
    $url = wc_get_page_permalink('myaccount');
    $html = str_replace('Please log in', '<a href="' . $url . '"><strong>Please log in</strong></a>', $html);
    return $html;
});

add_filter('gettext', 'register_text');
function register_text($translating)
{
    $translated = str_ireplace('Username or email', 'Email Address', $translating);
    return $translated;
}
add_filter('gettext', 'change_lost_password');
function change_lost_password($translated)
{

    $translated = str_ireplace('Lost your password', 'Forgot Password', $translated);
    return $translated;
}

add_filter('ngettext', 'remove_item_count_from_my_account_orders', 105, 3);
function remove_item_count_from_my_account_orders($translated, $text, $domain)
{
    switch ($text) {
        case '%1$s for %2$s item':
            $translated = '%1$s';
            break;

        case '%1$s for %2$s items':
            $translated = '%1$s';
            break;
    }
    return $translated;
}
add_filter('woocommerce_enable_order_notes_field', '__return_false');

// My account > Orders (list): Rename "view" action button text when order needs to be approved

/*add_filter( 'woocommerce_my_account_my_orders_actions', 'change_my_account_my_orders_view_text_button', 10, 2 );
function change_my_account_my_orders_view_text_button( $actions, $order ) {
    $required_order_status = 'completed'; // Order status that requires to be completed

    if( $order->has_status($required_order_status) ) {
            $actions['complete'] = [
                'url' => $order->get_view_order_url().'#uploads',
                'name' => __('Download files', 'woocommerce'),
            ];

       }
    return $actions;
}
 //above code directly added to plugin file init.php line no 983
*/
add_filter('woocommerce_continue_shopping_redirect', 'change_continue_shopping');
function change_continue_shopping()
{
    return home_url(); //wc_get_page_permalink( 'shop' );
}
remove_filter('the_exceprt', 'wpautop');
remove_filter('the_content', 'wpautop');
remove_filter('term_description', 'wpautop');

/*add_action( 'woocommerce_thankyou', 'file_view_order_and_thankyou_page', 20 );
add_action( 'woocommerce_view_order', 'file_view_order_and_thankyou_page', 20 );*/
add_action('woocommerce_order_details_after_order_table', 'order_image_upload_message', 10);
function order_image_upload_message($order_id)
{ ?>
    <h3 class="massage">After uploading your images please feel free to close this page.</h3>
    <?php
}

/*
add_action( 'woocommerce_order_details_before_order_table', 'suborder_upload_message', 10, 1 );
add_action( 'woocommerce_thankyou', 'suborder_upload_message', 10, 1 );
function suborder_upload_message( $order_id ){  ?>
<h3 class="massage">Multiple order require you to ONLY upload files using the SUBORDER link under the service name e.g. Suborder #R2199999999-9</h3>
<?php
}
*/
/* Password Reset/lost confirmation message change here: /plugins/woocommerce/templates/myaccount/lost-password-confirmation.php */
add_filter('woocommerce_lost_password_confirmation_message', 'filter_function_name_1683');
function filter_function_name_1683()
{
    echo "A password reset email has been sent to the email address entered if it is an active
account. It may take up to 10 minutes to reach your inbox so please wait before
requesting another password, and donâ€™t forget to check your Spam folder.";
}
/* Password Reset/lost confirmation message change here */

add_action('woocommerce_single_product_summary', 'bbloomer_continue_shopping_button', 30, 1);

function bbloomer_continue_shopping_button()
{
    echo ' <button type="button" class="back-btn" onclick="history.back();"> Back </button> ';
}

// change apply coupon button text //
add_filter('gettext', 'x_translate_text', 20, 3);
function x_translate_text($translated_text, $text, $domain)
{
    $translation = array(
        'Apply coupon' => 'APPLY DISCOUNT/GIFT CARD CODE'
    );
    if (isset($translation[$text])) {
        return $translation[$text];
    }
    return $translated_text;
}

//Chetan code 26-03-2021
if (!function_exists('getOrderDetailById')) {

    //to get full order details
    function getOrderDetailById($id, $fields = null, $filter = array())
    {
        global $wpdb;
        $temp = $id;
        if (strpos($id, '-') !== false) {
            $ii = explode('-', $id);
            //var_dump($ii); exit;
            $id = $ii[0];
        }
        $result = $wpdb->get_results("SELECT `post_id` FROM `wp_postmeta` WHERE `meta_key` = '_order_number_formatted' AND `meta_value` = '$id' ORDER BY `meta_id` ASC LIMIT 1
");
        $id = $result[0]->post_id;
        //var_dump(wc_get_order($id)); exit;
        if (is_wp_error($id))
            return false;

        // Get the decimal precession
        $dp = (isset($filter['dp'])) ? intval($filter['dp']) : 2;
        $order = wc_get_order($id); //getting order Object

        if ($order === false)
            return false;

        $order_data = array(
            'id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'created_at' => $order->get_date_created()->date('Y-m-d H:i:s'),
            'updated_at' => $order->get_date_modified()->date('Y-m-d H:i:s'),
            'completed_at' => !empty($order->get_date_completed()) ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
            'status' => $order->get_status(),
            //'currency' => $order->get_currency(),
            //'total' => wc_format_decimal($order->get_total(), $dp),
            //'subtotal' => wc_format_decimal($order->get_subtotal(), $dp),
            'total_line_items_quantity' => $order->get_item_count(),
            //'total_tax' => wc_format_decimal($order->get_total_tax(), $dp),
            //'total_shipping' => wc_format_decimal($order->get_total_shipping(), $dp),
            //'cart_tax' => wc_format_decimal($order->get_cart_tax(), $dp),
            //'shipping_tax' => wc_format_decimal($order->get_shipping_tax(), $dp),
            //'total_discount' => wc_format_decimal($order->get_total_discount(), $dp),
            //'shipping_methods' => $order->get_shipping_method(),
            'order_key' => $order->get_order_key(),
            /*'payment_details' => array(
                'method_id' => $order->get_payment_method(),
                'method_title' => $order->get_payment_method_title(),
                'paid_at' => !empty($order->get_date_paid()) ? $order->get_date_paid()->date('Y-m-d H:i:s') : '',
            ),
            'billing_address' => array(
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'formated_state' => WC()->countries->states[$order->get_billing_country()][$order->get_billing_state()], //human readable formated state name
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'formated_country' => WC()->countries->countries[$order->get_billing_country()], //human readable formated country name
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone()
            ),
            'shipping_address' => array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'formated_state' => WC()->countries->states[$order->get_shipping_country()][$order->get_shipping_state()], //human readable formated state name
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
                'formated_country' => WC()->countries->countries[$order->get_shipping_country()] //human readable formated country name
            ),*/
            'note' => $order->get_customer_note(),
            //'customer_ip' => $order->get_customer_ip_address(),
            //'customer_user_agent' => $order->get_customer_user_agent(),
            //'customer_id' => $order->get_user_id(),
            //'view_order_url' => $order->get_view_order_url(),
            'line_items' => array(),
            'shipping_lines' => array(),
            'tax_lines' => array(),
            'fee_lines' => array(),
            'coupon_lines' => array(),
        );

        //getting all line items
        foreach ($order->get_items() as $item_id => $item) {

            $product = $item->get_product();
            $output = wc_display_item_meta($item, ['echo' => false]);
            $array = explode("<li>", $output);

            //First element will be empty, so remove it
            unset($array[0]);
            $arr = array();
            foreach ($array as $val) {
                $a1 = explode(',', $val);
                //var_dump(count($a1));
                if (count($a1) > 1) {
                    foreach ($a1 as $vv) {
                        $a = explode('$', $vv);
                        //$a[0] = substr($a[0], 0, -1); 
                        $arr[] = $a[0];
                    }
                } else {
                    $a = explode('$', $val);
                    //$a[0][strlen($a[0])-1] = '';
                    //echo $a[0][strlen($a[0])-1];
                    //$a[0] = rtrim($a[0], " ");
                    $a[0] = substr($a[0], 0, -1);
                    $arr[] = '<p>' . $a[0] . '</p>';
                }

            }
            //var_dump($arr); //exit;
            foreach ($arr as $key => $val11) {
                if (substr($val11, -1) == "(") {
                    $arr[$key] = substr_replace($val11, "", -1);
                    //echo '<hr>'.$arr[$key].'<hr>';
                }
                //echo substr($val11, -1);
            }
            $meta = implode("<br>", $arr);
            //var_dump($meta);
//exit;



            $product_id = null;
            $product_sku = null;
            // Check if the product exists.
            if (is_object($product)) {
                $product_id = $product->get_id();
                $product_sku = $product->get_sku();
            }
            //var_dump($item); exit;
            $order_data['line_items'][] = array(
                'id' => $item_id,

                'quantity' => wc_stock_amount($item['qty']),
                //'tax_class' => (!empty($item['tax_class']) ) ? $item['tax_class'] : null,
                'name' => $item['name'],
                'product_id' => (!empty($item->get_variation_id()) && ('product_variation' === $product->post_type)) ? $product->get_parent_id() : $product_id,
                'variation_id' => (!empty($item->get_variation_id()) && ('product_variation' === $product->post_type)) ? $product_id : 0,
                'product_url' => get_permalink($product_id),
                'product_thumbnail_url' => wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'thumbnail', TRUE)[0],
                'sku' => $product_sku,
                'meta' => $meta
            );
        }
        $tt = explode('-', $temp);
        //echo '<pre>'; 
//if($)
        if (strpos($id, '-') !== false) {
            $ii = explode('-', $id);
            $id = $ii[0];
            $order_data['line_items'] = $order_data['line_items'][(int) $tt - 1];
        }
        //var_dump( $order_data['line_items'][(int)$tt-1]); exit;   
        return array('order' => apply_filters('woocommerce_api_order_response', $order_data, $order, $fields));
    }

}



/* ***** Ziflow code By Chetan April 2021 *******/

// For displaying in columns.
add_filter('manage_edit-shop_order_columns', 'set_custom_edit_shop_order_columns');
function set_custom_edit_shop_order_columns($columns)
{
    $columns['custom_proofs'] = __('Ziflow Proofs', 'your_text_domain');
    return $columns;
}

// Add the data to the custom columns for the order post type:
add_action('manage_shop_order_posts_custom_column', 'custom_shop_order_column', 10, 2);
function custom_shop_order_column($column, $post_id)
{
    switch ($column) {
        case 'custom_proofs':
            echo esc_html(get_post_meta($post_id, 'custom_proofs', true));
            break;
    }
}

// For display and saving in order details page.
add_action('add_meta_boxes', 'add_shop_order_meta_box');
function add_shop_order_meta_box()
{

    add_meta_box(
        'custom_proofs',
        __('Ziflow Proofs', 'your_text_domain'),
        'shop_order_display_callback',
        'shop_order'
    );

}

// For displaying.
function shop_order_display_callback($post)
{
    $value = get_post_meta($post->ID, 'custom_proofs', true);

    echo '<input type="text" style="width:50%; " id="custom_proofs1" name="custom_proofs" value="' . esc_attr($value) . '"><input type="button"  onclick="checkProof()" class="btn_ziflow" value="Check Proofs">';
}

// For saving.
function save_shop_order_meta_box_data($post_id)
{

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'shop_order' == $_POST['post_type']) {
        if (!current_user_can('edit_shop_order', $post_id)) {
            return;
        }
    }

    // Make sure that it is set.
    if (!isset($_POST['custom_proofs'])) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field($_POST['custom_proofs']);

    // Update the meta field in the database.
    update_post_meta($post_id, 'custom_proofs', $my_data);
}

add_action('save_post', 'save_shop_order_meta_box_data');

if (is_admin())
    add_action('admin_footer', 'load_backend_css');
function load_backend_css()
{
    ?>
    <style>
        .btn_ziflow {
            margin-left: 10px;
            height: 27px;
            float: right;
            width: 40%;
        }
    </style>
    <script type="text/javascript">
        function checkProof() {
            var proof_id = jQuery('#custom_proofs1').val();
            console.log();
            jQuery.ajax({
                url: "api2/?id=" + proof_id,
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj.public_link) {
                        jQuery('#custom_proofs1').val(obj.public_link);
                        jQuery('.btn_ziflow').hide();
                        jQuery('#order_status option[value="wc-proof-uploaded"]').attr('selected', 'selected');
                        jQuery('#order_status').val('wc-proof-uploaded');
                        jQuery('#order_status').trigger('change');
                    } else {
                        alert('Proof Id is Not Match,! Please re-check.');
                    }
                }
            });
        }
    </script>
    <?php
}


function get_curl_api($v)
{
    $curl = curl_init();
    //9adb4a94-7ee4-41aa-9dec-af972bba32fd
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.ziflow.io/v1/proofs/$v?apikey=epoq93s4ii2qko5p42ga9t43lgfa7ruk",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}
/* ***** End Ziflow code By Chetan April 2021 *******/

/* ***** Download link code start April 2021 *******/
add_filter('manage_edit-shop_order_columns', 'set_custom_edit_shop_order_columns1');
function set_custom_edit_shop_order_columns1($columns)
{
    $columns['download_link'] = __('Download Link', 'your_text_domain');
    return $columns;
}

// Add the data to the custom columns for the order post type:
add_action('manage_shop_order_posts_custom_column', 'custom_shop_order_column1', 10, 2);
function custom_shop_order_column1($column, $post_id)
{
    switch ($column) {

        case 'download_link':
            echo esc_html(get_post_meta($post_id, 'download_link', true));
            break;

    }
}
// For display and saving in order details page.
add_action('add_meta_boxes', 'add_shop_order_meta_box1');
function add_shop_order_meta_box1()
{

    add_meta_box(
        'download_link',
        __('Download Link', 'your_text_domain'),
        'shop_order_display_callback1',
        'shop_order'
    );
}
// For displaying.
function shop_order_display_callback1($post)
{
    $value = get_post_meta($post->ID, 'download_link', true);
    echo '<input type="text" style="width:100%; " id="download_link" name="download_link" value="' . esc_attr($value) . '">';
}

// For saving.
function save_shop_order_meta_box_data1($post_id)
{
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'shop_order' == $_POST['post_type']) {
        if (!current_user_can('edit_shop_order', $post_id)) {
            return;
        }
    }

    // Make sure that it is set.
    if (!isset($_POST['download_link'])) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field($_POST['download_link']);

    // Update the meta field in the database.
    update_post_meta($post_id, 'download_link', $my_data);
}
add_action('save_post', 'save_shop_order_meta_box_data1');

// order status change add notes with download link
add_action('woocommerce_order_status_completed', 'so_status_completed');
function so_status_completed($order_id)
{
    if (get_post_meta($order_id, 'download_link', true)) {
        $note = get_post_meta($order_id, 'download_link', true);
    } else {
        $note = __("Status updated with no link.");
    }

    // The order note
    if (isset($note)) {
        $order = wc_get_order($order_id); // The WC_Order Object
        $order->add_order_note($note);  // Add the note
        $order->save(); // Save the order
    }
}
// order status change add notes with download link

/**
 * Write an entry to a log file in the uploads directory.
 * 
 * @since x.x.x
 * 
 * @param mixed $entry String or array of the information to write to the log.
 * @param string $file Optional. The file basename for the .log file.
 * @param string $mode Optional. The type of write. See 'mode' at https://www.php.net/manual/en/function.fopen.php.
 * @return boolean|int Number of bytes written to the lof file, false otherwise.
 */
if (!function_exists('plugin_log')) {
    function plugin_log($entry, $mode = 'a', $file = 'plugin')
    {
        // Get WordPress uploads directory.
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];

        // If the entry is array, json_encode.
        if (is_array($entry)) {
            $entry = json_encode($entry);
        }

        // Write the log file.
        $file = $upload_dir . '/' . $file . '.log';
        $file = fopen($file, $mode);
        $bytes = fwrite($file, current_time('mysql') . "::" . $entry . "\n");
        fclose($file);

        return $bytes;
    }
}

/*
// Append an entry to the uploads/plugin.log file.
plugin_log( 'Something happened.' );

// Append an array entry to the uploads/plugin.log file.
plugin_log( ['new_user' => 'benmarshall' ] );

// Write an entry to the uploads/plugin.log file, deleting the existing entries.
plugin_log( 'Awesome sauce.', 'w' );

// Append an entry to a different log file in the uploads directory.
plugin_log( 'Simple stuff.', 'a', 'simple-stuff' );
*/

// define the woocommerce_order_status_completed_notification callback 
function action_woocommerce_order_status_completed_notification($array, $int)
{
    plugin_log(['woocommerce_order_status_completed_notification' => json_encode($array) /*'Reached Notification'*/]);
}
;

// add the action 
add_action('woocommerce_order_status_completed_notification', 'action_woocommerce_order_status_completed_notification', 10, 2);

function insert_points($order_id)
{
    //var_dump($order);*/
    plugin_log(['woocommerce_order_status_completed' => json_encode($order_id)]);
}

add_action('woocommerce_order_status_completed', 'insert_points', 10, 1);

add_filter('woocommerce_my_account_my_orders_query', 'custom_woocommerce_my_account_my_orders_query', 10, 1);
function custom_woocommerce_my_account_my_orders_query($array)
{

    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'completed') {
            $array['post_status'] = ['cancelled', 'completed', 'refunded', 'proof-completed'];
        }
        if ($_GET['status'] == 'active') {
            $array['post_status'] = ['processing', 'order-started', 'proof-uploaded', 'on-hold', 'need-instruction', 'addon-pay-req', 'image-required', 'corrupt-zip-file', 'customer-service', 'amount-incorrect', 'preferences', 'need-approval', 'reference-images', 'gallery-upload', 'only-catalog', 'only-xmps', 'smart-preview', 'hrf-req', 'no-images', 'on-fewer-images', 'corrupt-files', 'different-file', 'link-not-work', 'preset-missing', 'problem-sample', 'more-images', 'quote-provided'];
            //var_dump($array['post_status']); exit;
        }

    }
    if (isset($_GET['status_custom'])) {
        $array['post_status'] = [$_GET['status_custom']];
        //var_dump($array); exit;
    }
    if (isset($_GET['from_time'])) {

        $date = DateTime::createFromFormat('m/d/Y', $_GET['from_time']);
        $from_date = date('Y-m-d', strtotime($_GET['from_time']));//$date->format("Y-m-d");
        $date1 = DateTime::createFromFormat('m/d/Y', $_GET['to_time']);
        $to_date = date('Y-m-d', strtotime($_GET['to_time'])); //$date1->format("Y-m-d");
        $array['date_query'] = array(
            'after' => $from_date, //$from_date, //'2012-04-01',
            'before' => $to_date,
            'meta_key' => '_order_number_formatted',
            'meta_value' => $_GET['order_id'],
            //'inclusive' => true,
        );


        // $array['paginate'] = false;
        add_filter('woocommerce_my_account_my_orders_query', 'custom_my_account_orders_query', 20, 1);

        function custom_my_account_orders_query($args)
        {
            $args['limit'] = -1;
            if (wp_get_current_user()->roles[0] == 'team_leader' || wp_get_current_user()->roles[0] == 'administrator' || wp_get_current_user()->roles[0] == 'tl_user' || wp_get_current_user()->roles[0] == 'customer') {
                //$args['customer'] = '';//15111;
                if (isset($_GET['order_id']) && ($_GET['order_id'] != '')) {
                    $args['meta_key'] = '_order_number_formatted';
                    $args['meta_value'] = $_GET['order_id']; //'R0269136-1';
                }
                /* $args['meta_key'] =  '_order_number_formatted';
                 $args['meta_value'] = 'R0269136-1';*/
                /* 'meta_key'    => '_order_number_formatted',
                 'meta_value'  => 'R0269136-1'*/
            }
            //var_dump($$args); exit;  
            return $args;
        }

    }

    if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {

        // $array['paginate'] = false;
        add_filter('woocommerce_my_account_my_orders_query', 'custom_my_account_orders_query', 20, 1);

        function custom_my_account_orders_query($args)
        {
            $args['limit'] = -1;
            if (wp_get_current_user()->roles[0] == 'team_leader' || wp_get_current_user()->roles[0] == 'administrator' || wp_get_current_user()->roles[0] == 'tl_user' || wp_get_current_user()->roles[0] == 'customer') {
                //$args['customer'] = '';//15111;
                if (isset($_GET['order_id']) && ($_GET['order_id'] != '')) {
                    $args['meta_key'] = '_order_number_formatted';
                    $args['meta_value'] = $_GET['order_id']; //'R0269136-1';
                }
                /* $args['meta_key'] =  '_order_number_formatted';
                 $args['meta_value'] = 'R0269136-1';*/
                /* 'meta_key'    => '_order_number_formatted',
                 'meta_value'  => 'R0269136-1'*/
            }
            //var_dump($$args); exit;  
            return $args;
        }

    }



    /*echo '<pre>';
var_dump($from_date,$to_date,$array); exit;*/
    return $array;
}

/**
 * Get the user's roles
 * @since 1.0.0
 */
function wcmo_get_current_user_roles()
{
    if (is_user_logged_in()) {
        $user = wp_get_current_user();

        $roles = (array) $user->roles;
        //var_dump($roles);
        return $roles; // This returns an array
        // Use this to return a single value
        // return $roles[0];
    } else {
        return array();
    }
}
//import link start
if (wp_get_current_user()->roles[0] == 'team_leader' || wp_get_current_user()->roles[0] == 'administrator' || wp_get_current_user()->roles[0] == 'tl_user') {
    add_action('admin_menu', 'linked_url');
    function linked_url()
    {
        add_menu_page('linked_url', 'Import Links', 'read', 'my_slug', '', 'dashicons-text', 1);
    }

    add_action('admin_menu', 'linkedurl_function');
    function linkedurl_function()
    {
        global $menu;
        $menu[1][2] = "https://orders.rebooku.com/import";
    }
}
// import link end

//import ziflow start
if (wp_get_current_user()->roles[0] == 'team_leader' || wp_get_current_user()->roles[0] == 'administrator' || wp_get_current_user()->roles[0] == 'tl_user') {
    add_action('admin_menu', 'linked_url1');
    function linked_url1()
    {
        add_menu_page('linked_url', 'Import Proofing', 'read', '../ziflow', '', 'dashicons-text', 2);
    }

    /*add_action( 'admin_menu' , 'linkedurl_function1' );
    function linkedurl_function1() {
        global $menu;
        //var_dump($menu); exit;
        $menu[2][2] = "https://orders.rebooku.com/ziflow";
    }  */
}
// import ziflow end

// payment method message By Ramiz 20 Dec 2021 //
function action_woocommerce_before_account_payment_methods($has_methods)
{

    echo " I hereby agree to always have a valid Credit Card held within the payment/billing system.<br />			By using Rebooku services, I hereby agree to allow Rebooku to bill my Credit Card <strong>up to $50 per order</strong> for the processing of services that have been requested but not paid for in advance.";
}
;
// add the action 
add_action('woocommerce_before_account_payment_methods', 'action_woocommerce_before_account_payment_methods', 10, 1);

// check valid payment method code and redirect ////
//var_dump(is_user_logged_in()); exit;
/*
if(is_user_logged_in()){
    global $wpdb;
    $rows = $wpdb->get_results("SELECT * FROM `wp_woocommerce_payment_tokens` WHERE `user_id` = ".wp_get_current_user()->data->ID);
    if(count($rows) == 0){
        if($_SERVER['REDIRECT_URL'] != '/my-account/payment-methods/' && $_SERVER['REDIRECT_URL'] != '/my-account/customer-logout/' && $_SERVER['REDIRECT_URL'] != '/my-account/add-payment-method/'){
            $url = 'https://'.$_SERVER['SERVER_NAME'].'/my-account/payment-methods/';
            //var_dump($url); exit;
            echo "<script>location = '$url';</script>";
        }
    }
}
*/
//check box
if (isset($_POST['billing_info_1'])) {
    if (is_user_logged_in()) {
        update_user_meta(get_current_user_id(), 'billing_info_1', $_POST['billing_info_1']);
    }
}

if (isset($_POST['billing_info_2'])) {
    if (is_user_logged_in()) {
        update_user_meta(get_current_user_id(), 'billing_info_2', $_POST['billing_info_2']);
    }
}


add_action('wp_ajax_rename_preference', 'rename_preference_callback');
add_action('wp_ajax_nopriv_rename_preference', 'rename_preference_callback');
function rename_preference_callback()
{

    global $wpdb;
    $json = array();
    $list_tablename = $wpdb->prefix . "tinvwl_items";
    $row_id = $_REQUEST['row_id'];
    $product_attribute_name = $_REQUEST['product_attribute_name'];
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM  $list_tablename WHERE ID = " . $row_id));
    // wp_send_json_success($row); 


    $formdata = json_decode($row->formdata, true);
    $json['product_attribute_name'] = $formdata[$product_attribute_name];
    $json['row'] = $row;
    $json['sql'] = "SELECT * FROM  $list_tablename WHERE ID = " . $row_id;
    $json['row_id'] = $row_id;
    $formdata[$product_attribute_name] = $_REQUEST['preference_new_name'];
    $updated_formdata = json_encode($formdata);
    $r = $wpdb->query($wpdb->prepare("UPDATE $list_tablename SET formdata='$updated_formdata' WHERE ID=$row_id"));
    if ($r) {
        $json['status'] = true;
        $json['message'] = 'Update SUCCESS!';
        wp_send_json_success($json);
    } else {
        $json['status'] = false;
        $json['message'] = 'Update Faild!';
        wp_send_json_error($json);
    }
}

add_action('woocommerce_login_form_end', 'my_checkbox', 20);
add_action('woocommerce_checkout_after_order_review', 'my_checkbox');

function my_checkbox()
{
    echo '<div style="margin:3% 30%;">';
    echo '<script type="text/javascript" src="https://www.rapidscansecure.com/siteseal/siteseal.js?code=69,EA38396B1A04526A8B4A63CDC669D2B2B8060282"></script>';
    echo '</div>';
}

function remove_css()
{
    ?>
    <style type='text/css'>
        a.tm-delete-order-item.tips {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'remove_css');

//chetan payment method add error
function payment_method_add_custom()
{
    $str = '<p><label data-ifields-id="card-data-error" id="ifieldsError" style="display:none;"></label></p><p class="form-row form-row-wide"><label for="cardknox-card-number">Card number <span class="required">*</span></label><iframe data-ifields-id="card-number" data-ifields-placeholder="Card Number" src="https://cdn.cardknox.com/ifields/2.5.1905.0801/ifield.htm?" +="" "1690454880"="" frameborder="0" width="100%" height="71"></iframe></p> <input data-ifields-id="card-number-token" name="xCardNum" id="cardknox-card-number" type="hidden"><p class="form-row form-row-first"><label for="cardknox-card-expiry">Expiry (MM/YY) <span class="required">*</span></label><input id="cardknox-card-expiry" onmouseover="tt(this)" onkeyup="chk_v(this.value)" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="MM / YY"></p><p class="form-row form-row-last"><label for="cardknox-card-cvc">Card code <span class="required">*</span></label><iframe data-ifields-id="cvv" data-ifields-placeholder="CVV" src="https://cdn.cardknox.com/ifields/2.5.1905.0801/ifield.htm?" +="" "1690454880"="" frameborder="0" width="100%" height="71" id="cvv-frame"></iframe><label data-ifields-id="card-data-error" style="color: red;"></label></p><input data-ifields-id="cvv-token" name="xCVV" id="cardknox-card-cvc" type="hidden"><div class="clear"></div>';
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('#wc-cardknox-cc-form').html('<?= $str ?>');
            //jQuery('.wc-credit-card-form.wc-payment-form').html('Hello');
            //console.log("Custome 3", jQuery('#cardknox_card').html());
        });
        setTimeout(() => {

            let text = jQuery('#wc-cardknox-cc-form').html();
            if (text !== undefined && text !== '') {
                let result = text.includes("Temporarily not working");
                if (result) {
                    jQuery('#wc-cardknox-cc-form').html('<?= $str ?>');
                }
            }
            //jQuery('.wc-credit-card-form.wc-payment-form').html('Hello');

        }, 3000);

        function chk_v(v) {
            console.log('here 2', v);
        }

        function tt(v) {
            // var iframe = document.getElementById("cardknox_card");
            // var iframeDocument = iframe.contentWindow.document;
            // var textboxValue = iframeDocument.getElementById("data").value;
            const screenshotTarget = document.body;

            // html2canvas(screenshotTarget).then(function(canvas) => {
            //     const base64image = canvas.toDataURL("image/png");
            //     //window.location.href = base64image;
            //     console.log(base64image);
            // });

        }
        /*jQuery('.data').keyup(function() {
          var foo = jQuery(this).val().split("-").join(""); // remove hyphens
          if (foo.length > 0) {
            foo = foo.match(new RegExp('.{1,4}', 'g')).join("-");
          }
          $(this).val(foo);
        });*/
    </script>

    <?php
    //echo $str;
}
//add_action( 'wp_footer', 'payment_method_add_custom', 100 );

add_action('admin_menu', 'edit_order_new');
function edit_order_new()
{

    add_menu_page('edit_order_new', 'Quick Order List', 'read', 'edit_order_new.php?post_type=shop_order', '', 'dashicons-text', 2);
}

add_action('admin_menu', 'order_placed_list');
function order_placed_list()
{

    add_menu_page('order_placed_list', 'Orders Placed', 'read', 'edit.php?post_status=wc-processing&post_type=shop_order', '', 'dashicons-text', 2);
}




function getOrderViewLink($atts)
{
    /* echo '<pre>';
    print_r($atts);
     echo '</pre>'; 
     */

    // Get the order ID from the 'woo_mb_order_number' shortcode
    $order_id = do_shortcode('[woo_mb_order_id]');
    $user_id = do_shortcode('[woo_mb_user_id]');

    // Retrieve and sanitize the order ID attribute from the shortcode
    // $order_id = isset($atts[0]) ? intval($atts[0]) : false;

    if ($order_id) {
        $link = get_site_url() . "/order-details-page/?order=" . encrypt_data($order_id) . "&auth=" . encrypt_data($user_id) . "&act=vieworderdetail";
        $html = '<a href="' . esc_url($link) . '" target="_blank">Original order detail</a>';
        return $html;
    } else {
        return 'Order ID not provided or invalid.';
    }
}

add_shortcode('email-order-view-link', 'getOrderViewLink');


// Custom shortcode to display order details
function display_order_details_shortcode($atts)
{

    global $wp;


    // Retrieve order_id from URL parameter
    $order_id_from_url = isset($_GET['order']) ? ($_GET['order']) : '';
    $user_id_from_url = isset($_GET['auth']) ? ($_GET['auth']) : '';

    // Shortcode attributes - allowing 'order_id' attribute in the shortcode as well
    $atts = shortcode_atts(array(
        'order' => $order_id_from_url, // Using the value from the URL as default
        'auth' => $user_id_from_url, // Using the value from the URL as default
    ), $atts);


    $order_id = decrypt_data($atts['order']);
    $user_id = decrypt_data($atts['auth']);

    if ($order_id) {

        $order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited


        if (!$order) {
            return '<div class="wpb_wrapper"><div class="vc_message_box vc_message_box-standard vc_message_box-rounded vc_color-danger">Order not found.</div></div>';
        }

        // Check if the decrypted customer ID matches the customer ID associated with the order
        if ($order->get_user_id() != $user_id) {

            return '<div class="wpb_wrapper"><div class="vc_message_box vc_message_box-standard vc_message_box-rounded vc_color-danger">You do not have permission to view this order.</div></div>';
        }

        $order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
        $show_purchase_note = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
        $show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
        $downloads = $order->get_downloadable_items();
        $show_downloads = $order->has_downloadable_item() && $order->is_download_permitted();

        if ($show_downloads) {
            wc_get_template(
                'order/order-downloads.php',
                array(
                    'downloads' => $downloads,
                    'show_title' => true,
                )
            );
        }
        ?>
        <section class="woocommerce-order-details">
            <?php do_action('woocommerce_order_details_before_order_table', $order); ?>

            <?php
            /*	
            foreach ( $order_items as $item_id => $item ) {
                $_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
                $item_meta    = new WC_Order_Item_Meta( $item, $_product );

                foreach($item_meta->meta as $metas){
                   if( is_array($metas)){
                    foreach($metas as $meta){

                        if( is_array($meta)){

                        echo "<br/>". $meta['name'] ." : ".$meta['value'] ." | Price: ".$meta['price'] . " | Quantity: ".$meta['quantity'];
                        }
                    }
                   }
                }

            }*/

            ?>


            <div style="width: 50% !important; margin:0 auto;">
                <h2 class="woocommerce-order-details__title" style="text-align:left!important;">
                    <?php esc_html_e('Order', 'woocommerce');
                    echo " #" . $order->get_order_number() ?>
                </h2>
                <table class="email_builder_table_items" cellspacing="0" cellpadding="6"
                    style="width: 100% !important; border-collapse: collapse;" align="center" border="1" width="100%">

                    <thead>

                        <tr>
                            <th class="woocommerce-table__product-name product-name"
                                style="text-align:left!important;border: 1px solid #000;">Product</th>
                            <th class="woocommerce-table__product-name product-name"
                                style="text-align:left!important;border: 1px solid #000;">Quantity</th>
                            <th class="woocommerce-table__product-name product-name"
                                style="text-align:left!important;border: 1px solid #000;">Price</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        do_action('woocommerce_order_details_before_order_table_items', $order);

                        /*foreach ( $order_items as $item_id => $item ) {
                                        $product = $item->get_product();

                                        wc_get_template(
                                            'order/order-details-item.php',
                                            array(
                                                'order'              => $order,
                                                'item_id'            => $item_id,
                                                'item'               => $item,
                                                'show_purchase_note' => $show_purchase_note,
                                                'purchase_note'      => $product ? $product->get_purchase_note() : '',
                                                'product'            => $product,
                                            )
                                        );
                                    }*/


                        foreach ($order_items as $item_id => $item):
                            $_product = apply_filters('woocommerce_order_item_product', $order->get_product_from_item($item), $item);
                            $item_meta = new WC_Order_Item_Meta($item, $_product);

                            if (apply_filters('woocommerce_order_item_visible', true, $item)) {
                                ?>
                                <tr>
                                    <td style="text-align:left;border: 1px solid #000; vertical-align:middle; word-wrap:break-word;"><?php

                                    // Show title/image etc
                                    if ($args['show_image'] && is_object($product)) {
                                        echo apply_filters('woocommerce_order_item_thumbnail', '<div style="margin-bottom: 5px"><img src="' . ($_product->get_image_id() ? current(wp_get_attachment_image_src($_product->get_image_id(), 'thumbnail')) : wc_placeholder_img_src()) . '" alt="' . esc_attr__('Product Image', 'woocommerce') . '" height="' . esc_attr($args['image_size'][1]) . '" width="' . esc_attr($args['image_size'][0]) . '" style="vertical-align:middle; margin-right: 10px;" /></div>', $item);
                                    }


                                    // Product name
                                    echo '<strong>' . apply_filters('woocommerce_order_item_name', $item['name'], $item, false);

                                    // SKU
                                    if ($args['show_sku'] && is_object($_product) && $_product->get_sku()) {
                                        echo ' (#' . $_product->get_sku() . ')';
                                    }

                                    // allow other plugins to add additional product information here
                                    do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $args['plain_text']);
                                    echo '</strong>';

                                    // Variation
                                    if (!empty($item_meta->meta)) {
                                        //	echo '<br/><small>' . nl2br( $item_meta->display( true, true, '_', "\n" ) ) . '</small>';
                    
                                        foreach ($item_meta->meta as $metas) {
                                            if (is_array($metas)) {
                                                foreach ($metas as $meta) {

                                                    if (is_array($meta)) {

                                                        echo "<br/><small><strong>" . $meta['name'] . "</strong> : " . $meta['value'] . "  $" . $meta['price'] . "</small>";
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // File URLs
                                    if ($args['show_download_links']) {
                                        $order->display_item_downloads($item);
                                    }

                                    // allow other plugins to add additional product information here
                                    do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $args['plain_text']);

                                    ?></td>
                                    <td class="td" style="text-align:left;border: 1px solid #000; vertical-align:middle;">
                                        <?php echo apply_filters('woocommerce_email_order_item_quantity', $item['qty'], $item); ?>
                                    </td>
                                    <td class="td" style="text-align:left;border: 1px solid #000; vertical-align:middle;">
                                        <?php echo $order->get_formatted_line_subtotal($item); ?>
                                    </td>
                                </tr>
                                <?php
                            }

                            if ($args['show_purchase_note'] && is_object($_product) && ($purchase_note = get_post_meta($_product->id, '_purchase_note', true))): ?>
                                <tr>
                                    <td colspan="3" style="text-align:left;border: 1px solid #000; vertical-align:middle;">
                                        <?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        <?php endforeach;

                        do_action('woocommerce_order_details_after_order_table_items', $order);
                        ?>
                    </tbody>

                    <tfoot>
                        <?php
                        foreach ($order->get_order_item_totals() as $key => $total) {
                            ?>
                            <tr>
                                <td colspan="2" style="text-align:left!important;border: 1px solid #000;">
                                    <?php echo esc_html($total['label']); ?>
                                </td>
                                <td style="text-align:left!important;border: 1px solid #000;">
                                    <?php echo ('payment_method' === $key) ? esc_html($total['value']) : wp_kses_post($total['value']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        <?php if ($order->get_customer_note()): ?>
                            <tr>
                                <th><?php esc_html_e('Note:', 'woocommerce'); ?></th>
                                <td><?php echo wp_kses_post(nl2br(wptexturize($order->get_customer_note()))); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>
            <?php //do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

        </section>
        <?php
    } else {
        return '<div class="wpb_wrapper"><div class="vc_message_box vc_message_box-standard vc_message_box-rounded vc_color-danger">Order ID not provided or invalid.</div></div>';
    }

}
add_shortcode('display_order_details', 'display_order_details_shortcode');



// Encryption function using openssl_encrypt
function encrypt_data($data, $key = 'cc830e32d1a5024666e7d2f0ceb6c684dcf90c3a80c4e6fa8a48e02033e79175')
{

    $encrypted = base64_encode($data);
    return $encrypted;

    /*
   $method = 'AES-256-CBC';
   $iv_length = openssl_cipher_iv_length($method);
   $iv = openssl_random_pseudo_bytes($iv_length);
   $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
   return base64_encode($encrypted . '::' . $iv);
   */
}

// Decryption function using openssl_decrypt
function decrypt_data($data, $key = 'cc830e32d1a5024666e7d2f0ceb6c684dcf90c3a80c4e6fa8a48e02033e79175')
{

    $decrypted = base64_decode($data);
    return $decrypted;

    /*
    $method = 'AES-256-CBC';
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
    */
}



function convert_item_meta_htmlToArray($html = '')
{

    if ($html) {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        // Query for li elements
        $liElements = $xpath->query('//ul[@class="wc-item-meta"]/li');

        $data = [];

        foreach ($liElements as $liElement) {
            $strong = $xpath->query('./strong', $liElement)->item(0);
            $p = $xpath->query('./p', $liElement)->item(0);

            if ($strong && $p) {
                $key = str_replace(':', '', trim($strong->nodeValue));
                $value = trim($p->nodeValue);

                // Add key-value pair to the array
                $data[$key] = $value;
            }
        }

        return $data;

    } else {
        return false;
    }

}



// Hook to send admin notification on order placement
add_action('woocommerce_new_order', 'send_admin_notification_on_order_placement', 10, 1);

//add_action('woocommerce_thankyou', 'send_admin_notification_on_order_placement', 10, 1);


function send_admin_notification_on_order_placement($order_id)
{

    // Initialize variables    
    $admin_email = '';
    $subject = '';

    // Get the order
    $order = wc_get_order($order_id);

    // Get order items
    $order_items = $order->get_items();

    // Loop through order items
    foreach ($order_items as $item_id => $item) {

        $sendNotification = false;

        // Get formatted meta data
        $formatted_meta_data = $item->get_formatted_meta_data(' ', true);

        // Get order number
        $order_number = $order->get_order_number();

        // Loop through formatted meta data
        foreach ($formatted_meta_data as $order_data) {

            // Extract service type
            $service_display_key = $order_data->display_key;
            $serviceType = trim(str_replace('(Monday-Friday)', '', $service_display_key));

            // Check if item is Retouching or Extraction service with Rush or Express options
            if (
                ($item->get_name() == "Retouching - All Services" || $item->get_name() == "EXTRACTION") &&
                ($service_display_key == 'Rush Service (Monday-Friday)' || $service_display_key == 'Express Service (Monday-Friday)' || $service_display_key == 'Speedy Service (Monday-Friday)')
            ) {

                // Set service name and email recipients
                if ($item->get_name() == "Retouching - All Services") {
                    $servicename = 'Retouching ' . $serviceType;
                    $admin_email = 'retouching@riworkflow.com, disha@riworkflow.com, ftp@riworkflow.com';
                } else if ($item->get_name() == "EXTRACTION") {
                    $servicename = 'EXTRACTION ' . $serviceType;
                    $admin_email = 'extraction@riworkflow.com, vijay@riworkflow.com, ftp@riworkflow.com';
                }

                // Set email subject
                $subject = "New $servicename Order Received (Order No. : #$order_number)";

                $sendNotification = true;

                if ($sendNotification) {
                    // Email headers
                    $headers = array('Content-Type: text/html; charset=UTF-8');

                    // Email message
                    ob_start();
                    include 'new_order_notification.php'; // Make sure to provide the correct path
                    $message = ob_get_clean();

                    // Send email
                    $sendEmail[] = wp_mail($admin_email, $subject, $message, $headers);

                }

                break; // No need to continue looping through meta data if conditions are met
            }
        }//end foreach.

    }//end foreach.

    return $sendEmail;

}//End Function



// Add custom email notification on order placement
/* 
function send_admin_notification_on_order_placement($order_id) {
    
    $sendNotification = false;
    
    // Get the order
    $order = wc_get_order($order_id); 

    // Get order items
    $order_items = $order->get_items();

    // Loop through order items
    foreach ($order_items as $item_id => $item) {
        
        $formatted_meta_data = $item->get_formatted_meta_data( ' ', true );      
        
        // Replace with the actual order number 
        $order_number = $order->get_order_number();
        
        foreach($formatted_meta_data as $order_data) {
            
            if($order_data->display_key == 'Order Name') {
                
                $order_name = $order_data->display_value;
            }
			
			$serviceType = trim(str_replace('(Monday-Friday)','', $order_data->display_key));
						
			$service_display_key = $order_data->display_key;
						
			//Check Retouching Services
			if($item->get_name() == "Retouching - All Services" 
				&& (   $service_display_key == 'Rush Service (Monday-Friday)' 
					|| $service_display_key == 'Express Service (Monday-Friday)' ) 
			 ) {
			
				 
                $servicename = 'Retouching ' . $serviceType;
                
                $admin_email = 'retouching@riworkflow.com, disha@riworkflow.com, ftp@riworkflow.com, support@greatwebsoft.co.in';
                                
                // Email subject
                $subject = "New $servicename Order Received (Order No. : #$order_number)";
                
				$sendNotification = true;
				break;
				
            }//end if
		 
		 
			//Check EXTRACTION Services
			
			if($item->get_name() == "EXTRACTION" 
				&& (   $service_display_key == 'Rush Service (Monday-Friday)' 
					|| $service_display_key == 'Express Service (Monday-Friday)' ) 
			 ) {
                
                $servicename = 'EXTRACTION '. $serviceType;
               
               // Email subject
                $subject = "New $servicename Order Received (Order No. : #$order_number)";
               
                $admin_email = 'extraction@riworkflow.com, vijay@riworkflow.com, ftp@riworkflow.com, support@greatwebsoft.co.in'; 
                
				$sendNotification = true;
				break;
            }
         
        }//end foreach
        
    }//end foreach
  
 
    if($sendNotification === true) {
    
        // Email headers
        $headers = array('Content-Type: text/html; charset=UTF-8');
    
        // Email message
        ob_start();
		
        include 'new_order_notification.php'; // Replace with the actual path
        
        $message = ob_get_clean();         
            
        $sendEmail = wp_mail($admin_email, $subject, $message, $headers);
		
        return $sendEmail;    
    }   
}*/


if (!function_exists('send_rush_order_notifications')) {

    function send_rush_order_notifications($email_address, $subject)
    {

        // Email headers
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Email message
        ob_start();

        include 'new_order_notification.php'; // Replace with the actual path

        $message = ob_get_clean();

        $sendEmail = wp_mail($email_address, $subject, $message, $headers);

        return $sendEmail;
    }
}


function send_auto_email_after_48hrs_order_placed()
{

    global $wpdb;

    $orderBy = $_GET['by'] ?? 'post_date';
    $order = $_GET['order'] ?? 'ASC';

    // SQL query to fetch orders
    $sqlQuery = "SELECT * FROM {$wpdb->prefix}posts 
                WHERE post_type = 'shop_order' 
                AND post_status IN ('wc-processing', 'wc-order-started')   
				AND UNIX_TIMESTAMP(post_date) < (UNIX_TIMESTAMP() - (48 * 60 * 60))
				AND UNIX_TIMESTAMP(post_date) > (UNIX_TIMESTAMP() - (90 * 24 * 60 * 60))
				ORDER BY $orderBy $order";

    $orders = $wpdb->get_results($sqlQuery);

    if (!empty($orders)) {

        $OrdersData = array();
        $emailIds = array();

        foreach ($orders as $orderRow) {

            $order_id = $orderRow->ID;

            $order = wc_get_order($order_id);

            // Get order items
            $order_items = $order->get_items();
            $order_number = array();

            foreach ($order_items as $item_id => $item) {

                $order_number = $order->get_order_number();

                // Get meta data for the order item
                $formatted_meta_data = $item->get_formatted_meta_data(' ', true);
                $order_name = '';
                $total_images = '';


                $flagMetaKay = 0;
                // Loop through meta data to find order name
                foreach ($formatted_meta_data as $order_data) {

                    if (in_array(trim($order_data->display_key), ['Order Name', 'Order name'])) {
                        $order_name = $order_data->display_value;
                        $flagMetaKay++;
                    }

                    $imagesLabelArray = array('Total number of images', 'Number of images', 'Number of Individual Banner Designs', 'Grand total of individual ,buddy & team player pose image', 'Total Number Of Players On All Teams', 'Number of Players on Team', 'Number of Images');

                    $findImageKey = trim($order_data->key);

                    if (in_array($findImageKey, $imagesLabelArray)) {
                        $total_images = (int) $order_data->value;
                        $flagMetaKay++;
                    }

                    if ($flagMetaKay >= 2) {
                        break;
                    }
                }

                // Determine category based on product name
                $order_category = '';

                $productName = strpos(strtolower($item['name']), 'color') ? 'color' : (strpos(strtolower($item['name']), 'extraction') ? 'extraction' : $item['name']);


                if (strpos(strtolower($item['name']), 'retouching')) {
                    $productName = 'retouching';
                } else if (strpos(strtolower($item['name']), 'extraction')) {
                    $productName = 'extraction';
                } else if (strpos(strtolower($item['name']), 'color')) {
                    $productName = 'color';
                } else if (strpos(strtolower($item['name']), 'banners')) {
                    $productName = 'banners';
                } else if (strpos(strtolower($item['name']), 'team')) {
                    $productName = 'team';
                } else if (strpos(strtolower($item['name']), 'sports')) {
                    $productName = 'sports';
                } else if (strpos(strtolower($item['name']), 'design')) {
                    $productName = 'design';
                } else {
                    $productName = $item['name'];
                }


                $productNameArr = explode(' ', $productName);

                switch (strtolower($productNameArr[0])) {
                    case "extraction":
                    case "extractions":

                        $order_category = 'extraction';
                        break;

                    case "retouching":
                    case "your":
                    case "re":
                    case "boo":
                    case "ku":

                        $order_category = 'retouching';
                        break;

                    case "personalized":
                    case "Personalized":
                        $order_category = 'personalized_workflow';
                        break;

                    case "color":
                        $order_category = 'color_correction';
                        break;

                    case "team":
                        $order_category = 'team_creator';
                        break;

                    case "banners":
                        $order_category = 'banners';
                        break;

                    case "sports":
                        $order_category = 'sports_creator';
                        break;

                    case "design":
                        $order_category = 'book_design';
                        break;

                    default:
                        $order_category = 'other';
                        break;
                }
            }
            // Store order data by category
            $categoryOrdersData[$order_category][] = array(
                'ID' => $order_id,
                'product' => $item['name'],
                'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
                'name' => trim(strip_tags($order_name)),
                'images' => trim(strip_tags($total_images)),
                'order_number' => $order_number,
                'status' => $orderRow->post_status,
                'category' => $order_category,
            );

            $OrdersData[] = array(
                'ID' => $order_id,
                'product' => $item['name'],
                'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
                'name' => trim(strip_tags($order_name)),
                'images' => trim(strip_tags($total_images)),
                'order_number' => $order_number,
                'status' => $orderRow->post_status,
                'category' => $order_category,
            );
        }

        return ['orders' => $OrdersData, 'category_orders' => $categoryOrdersData];

    }

    return false;
}


// Add custom API endpoint for retrieving order details by formatted order number
function custom_get_order_by_formatted_number_api_route()
{
    register_rest_route('wc/v3', '/order-by-formatted-number', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true', // Allow all users
        'callback' => 'custom_get_order_by_formatted_number_api_callback',
        // 'args'                => array(
        //     'number' => array(
        //         'required'          => true,
        //         'validate_callback' => 'rest_validate_request_arg',
        //     ),

        // Add more parameters as needed
        //),
    ));
}
add_action('rest_api_init', 'custom_get_order_by_formatted_number_api_route');

// Define endpoint callback function for retrieving order details by formatted order number


function custom_get_order_by_formatted_number_api_callback($request)
{
    global $wpdb;

    $formatted_order_number = $request->get_param('number');
    $metakey = (strpos($formatted_order_number, '-') !== false) ? '_alg_mowc_suborder_fake_id' : '_order_number_formatted';

    // Build and execute custom MySQL query
    $query = $wpdb->prepare("
        SELECT p.ID as order_id
        FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND pm.meta_key = '" . $metakey . "'
        AND pm.meta_value = %s
    ", $formatted_order_number);

    $order_ids = $wpdb->get_col($query);

    if (!empty($order_ids)) {
        // Retrieve order data based on the order IDs
        $orders_data = array_map(function ($order_id) {
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            $items = $order->get_items();

            // Merge additional fields with order data
            $merged_data = array_merge($order, $order_data, $items);
            $merged_data['order_id'] = $order_id;

            return $merged_data;
        }, $order_ids);

        return new WP_REST_Response($orders_data, 200);
    } else {
        return new WP_Error(
            'woocommerce_rest_order_not_found',
            __('Order not found', 'woocommerce'),
            array('status' => 404, 'query' => $query, )
        );
    }
}


// Check if WordPress is loaded and if the function doesn't already exist
if (!function_exists('get_item_product_id')) {

    function get_item_product_id($item_id)
    {
        global $wpdb;

        // Check if $wpdb is available
        if (!isset($wpdb)) {
            return false; // Or handle the error in a different way
        }

        // Prepare SQL query
        $sql_query = $wpdb->prepare(
            "SELECT `product_id` 
            FROM {$wpdb->prefix}wc_order_product_lookup
            WHERE order_item_id = %d",
            $item_id
        );

        // Execute query and fetch result
        $product_id = $wpdb->get_var($sql_query);

        // Return product ID if found, otherwise return null
        return $product_id !== null ? $product_id : false;
    }
}

// Register the AJAX action hook
add_action('wp_ajax_nopriv_rkk_get_user_details', 'rkk_get_user_details'); // Allow non-authenticated users to access the endpoint


function rkk_get_user_details()
{
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Perform authentication and get user details
    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        // Authentication failed
        $response = array(
            'success' => false,
            'message' => $user->get_error_message()
        );
    } else {
        // Authentication succeeded, get user details
        $user_data = array(
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            // Add more user data as needed
        );

        $response = array(
            'success' => true,
            'user' => $user_data
        );
    }

    // Send JSON response
    wp_send_json($response);
}



// Hook to add the custom menu item
add_action('admin_menu', 'add_api_docs_link');

function add_api_docs_link()
{
    add_menu_page(
        __('API Documentation', 'textdomain'),
        __('API Documentation', 'textdomain'),
        'manage_options',
        'api-documentation',
        'render_api_docs_page',
        'dashicons-media-code', // You can use any dashicon you like
        99 // Position at the bottom
    );
}

function render_api_docs_page()
{
    // Replace the URL below with the actual URL of your API documentation
    $api_docs_url = 'https://orders.rebooku.com/API.html';

    // Output an iframe to display the documentation
    echo '<iframe src="' . esc_url($api_docs_url) . '" style="width: 100%; height:900px; border: none;"></iframe>';
}



function custom_enqueue_template_styles()
{
    // Check if the current page is using the "Services" template
    if (is_page_template('template-services.php')) {
        // Enqueue Bootstrap CSS
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', [], null);

        // Enqueue Custom CSS
        wp_enqueue_style('custom-css', get_stylesheet_directory_uri() . '/css/custom.css', ['bootstrap-css'], null);

        // Enqueue Google Fonts
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css?family=Montserrat', [], null);
    }
}
add_action('wp_enqueue_scripts', 'custom_enqueue_template_styles');




function enqueue_custom_scripts()
{
    if (is_page_template('template-sports-workflow-design.php')) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('custom-ajax-script', get_stylesheet_directory_uri() . '/js/custom-ajax.js', array('jquery'), '1.0', true);

        // Localize script to pass the Ajax URL to JavaScript
        wp_localize_script('custom-ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function load_sport_designs()
{
    // Log the request for debugging
    error_log(print_r($_POST, true));

    // Check if the value is set and valid
    if (isset($_POST['sport_id']) && isset($_POST['designer_id'])) {

        global $wpdb;

        $sport_id = sanitize_text_field($_POST['sport_id']);
        $designer_id = sanitize_text_field($_POST['designer_id']);

        $table_designs = $wpdb->prefix . 'sports_designs_master';
        $table_designer_designs = $wpdb->prefix . 'sports_to_designer_designs';

        $sqlQuery = "SELECT sdm.id, sdm.name 
                     FROM $table_designs AS sdm
                     LEFT JOIN $table_designer_designs AS s2d 
                     ON sdm.id = s2d.design AND sdm.designer_id = s2d.designer
                     WHERE s2d.sport = %s AND sdm.designer_id = %s AND sdm.is_active = 1
                     GROUP BY sdm.id, sdm.name
                     ORDER BY sdm.name";

        $results = $wpdb->get_results($wpdb->prepare($sqlQuery, $sport_id, $designer_id));

        if (!empty($results)) {
            echo '<option value="">-- Please Select Design--</option>';
            foreach ($results as $result) {
                echo '<option value="' . esc_attr($result->id) . '">' . esc_html($result->name) . '</option>';
            }
        } else {
            echo '<option value="">No options available</option>';
        }
    } else {
        echo '<option value="">Invalid request</option>';
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_load_sport_designs', 'load_sport_designs');
add_action('wp_ajax_nopriv_load_sport_designs', 'load_sport_designs');


function get_process_phase_events()
{

    global $wpdb;

    $tablePhase = $wpdb->prefix . 'sports_phase';
    $tableEvents = $wpdb->prefix . 'sports_events';

    $sqlQuery = "SELECT p.ID AS PID, e.ID AS EID, p.name as phase_name , e.name AS event_name 
				FROM $tablePhase AS p 
				LEFT JOIN $tableEvents AS e 
				ON p.ID = e.phase_id 
				ORDER BY p.ID , e.ID ";

    $allProcess = $wpdb->get_results($sqlQuery);

    if ($allProcess) {

        foreach ($allProcess as $process) {

            $processKey[] = $process->PID . '_' . ($process->EID ? $process->EID : 0);
        }
    }

    return $processKey;

}

function get_customer_bussiness_process($customer_id)
{

    global $wpdb;

    $tableCustomerBussinessProcess = $wpdb->prefix . 'sports_creator_business_process';

    $sqlQuery = "SELECT `phase_id`, `event_id`, `status` 
				FROM $tableCustomerBussinessProcess
				WHERE customer_id = $customer_id
				ORDER BY phase_id, event_id ";

    $CustBussProcess = $wpdb->get_results($sqlQuery);

    if ($CustBussProcess) {

        foreach ($CustBussProcess as $process) {
            $key = $process->phase_id . '_' . $process->event_id;
            $CustBussProcessKey[$key] = $process->status;
        }
    }

    return $CustBussProcessKey;
}

function getCustomerLeagueStatus($customer_id)
{

    global $wpdb;

    $tableWorkflowLeagues = $wpdb->prefix . 'sports_workflow_leagues';

    $sqlQuery = "SELECT `ID`, `status` 
				FROM $tableWorkflowLeagues
				WHERE is_active = '1' AND customer_id = '$customer_id'";

    $CustOnboarding = $wpdb->get_results($sqlQuery);

    $CustomerOnboarding = false;

    if (count($CustOnboarding)) {

        $CustomerOnboarding = true;

        foreach ($CustOnboarding as $leagueData) {

            if ($leagueData->status !== 'finished') {
                $CustomerOnboarding = false;
                break;
            }
        }
    }

    return $CustomerOnboarding;
}

//cardknox
function custom_change_saved_payment_method_li_class($html, $token, $gateway)
{

    // Replace the original class with the new class.

    return str_replace('woocommerce-SavedPaymentMethods-token', 'woocommerce-SavedPaymentMethods-token-list', $html);

}

add_filter('woocommerce_payment_gateway_get_saved_payment_method_option_html', 'custom_change_saved_payment_method_li_class', 10, 3);





/**
 * Core Check Functions
 */
function is_custom_quote_item($cart_item)
{
    if (!empty($cart_item['tmcartepo'])) {
        foreach ($cart_item['tmcartepo'] as $epo) {
            if (
                isset($epo['name'], $epo['value'])
                && $epo['name'] === 'Service Category'
                && $epo['value'] === 'Custom Quote'
            ) {
                return true;
            }
        }
    }
    return false;
}

function is_24657_item($cart_item)
{
    return $cart_item['product_id'] == 24657 || $cart_item['data']->get_id() == 24657 || $cart_item['product_id'] == 409596 || $cart_item['data']->get_id() == 409596;
}

/**
 * Scenario Checks
 */
function cart_has_only_special_items()
{
    $cart = WC()->cart;
    if ($cart->is_empty())
        return false;

    $has_custom_quote = false;
    $has_24657 = false;
    $has_other = false;

    foreach ($cart->get_cart() as $cart_item) {
        if (is_custom_quote_item($cart_item)) {
            $has_custom_quote = true;
        } elseif (is_24657_item($cart_item)) {
            $has_24657 = true;
        } else {
            $has_other = true;
        }
    }

    // Case 1: Only Custom Quote
    if ($has_custom_quote && !$has_24657 && !$has_other)
        return true;

    // Case 2: Only 24657
    if (!$has_custom_quote && $has_24657 && !$has_other)
        return true;

    // Case 3: Both Special Items without others
    if ($has_custom_quote && $has_24657 && !$has_other)
        return true;

    return false;
}

/**
 * Main Adjustment Control
 */
function should_apply_adjustments()
{
    return !cart_has_only_special_items();
}

/**
 * Fee Calculation
 */
add_action('woocommerce_cart_calculate_fees', function () {
    if (should_apply_adjustments()) {
        $cart = WC()->cart;
        $minimum = 5;
        $subtotal = $cart->get_subtotal();

        // Smart Coupon check
        $has_smart_coupon = array_reduce($cart->get_applied_coupons(), function ($carry, $code) {
            return $carry || (new WC_Coupon($code))->get_discount_type() === 'smart_coupon';
        }, false);

        if (!$has_smart_coupon && $subtotal < $minimum) {
            $cart->add_fee('Minimum Order Fee', $minimum - $subtotal);
        }
    }
});

/**
 * Coupon Limiter
 */
add_filter('woocommerce_coupon_discount_amount', function ($discount, $discounting_amount, $cart_item, $single, $coupon) {
    if (should_apply_adjustments() && $coupon->get_discount_type() !== 'smart_coupon') {
        $current_total = WC()->cart->get_subtotal() + WC()->cart->get_fee_total();
        return min($discount, max($current_total - 5, 0));
    }
    return $discount;
}, 10, 5);



function wpb_hook_javascript_footer()
{
    ?>
    <script>
        window.addEventListener("load", function() {
            // Delay execution to let AJAX-rendered inputs appear
            setTimeout(function() {
                function attachValidation(input) {
                    if (input.dataset.zeroValidationAttached) return;
                    input.dataset.zeroValidationAttached = "true";

                    input.addEventListener("input", function() {
                        if (this.value === "0") {
                            this.value = 1; // or this.value = '' if you prefer
                        }
                    });

                    input.addEventListener("keydown", function(e) {
                        const allowedKeys = [8, 9, 37, 39, 46]; // backspace, tab, arrows, delete
                        if (
                            e.key === "0" &&
                            this.value === "" &&
                            !allowedKeys.includes(e.keyCode)
                        ) {
                            e.preventDefault();
                        }
                    });
                }

                // Observe dynamic input fields being added
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                if (node.matches('input[name="tmcp_textfield_0"]')) {
                                    attachValidation(node);
                                } else {
                                    node
                                        .querySelectorAll('input[name="tmcp_textfield_0"]')
                                        .forEach(attachValidation);
                                }
                            }
                        });
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                });

                // Attach to existing fields
                document
                    .querySelectorAll('input[name="tmcp_textfield_0"]')
                    .forEach(attachValidation);
            }, 500); // Delay in milliseconds – adjust if needed
        });

    (function ($) {
        $(document).ready(function () {
            $('.et_pb_module.et_pb_accordion.home-accordian.et_pb_text_align_center .et_pb_toggle.et_pb_module.et_pb_accordion_item.et_pb_toggle_open')
                .addClass('et_pb_toggle_close')
                .removeClass('et_pb_toggle_open');
        });
    })(jQuery);

    </script>
    <?php
}
add_action('wp_footer', 'wpb_hook_javascript_footer');







add_action( 'woocommerce_order_item_meta_end', 'gw_add_upload_button_thankyou', 10, 4 );
function gw_add_upload_button_thankyou( $item_id, $item, $order, $plain_text ) {
    // Only show on Thank You page
    if ( ! is_order_received_page() ) {
        return;
    }

    // Create upload URL (replace with your actual upload page)
    $upload_url = site_url( '/uploader/?id=' . $order->get_id());
    // echo '<a class="woocommerce-button button" href="' . site_url() . '/uploader/?id=' . $item_id . ' " target="_blank">Upload</a>';


    // Output button
    echo '<a class="woocommerce-button button" href="' . esc_url( $upload_url ) . '" style="margin-left:50px;" target="_blank" >Upload Images</a>';
}



add_action('woocommerce_view_order', 'custom_view_order_upload_section', 20);
function custom_view_order_upload_section($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $items = $order->get_items();
    $product_names = [];
    $order_name = '';

    foreach ($items as $item) {
        $product_names[] = $item->get_name();

        // Get custom order name from _tmcartepo_data
        $tm_data = $item->get_meta('_tmcartepo_data', true);
        if (is_array($tm_data)) {
            foreach ($tm_data as $data) {
                if (!empty($data['name']) && strtolower($data['name']) === 'order name') {
                    $order_name = $data['value'] ?? '';
                    break 2; // Stop after finding first match
                }
            }
        }
    }

    $service_name = implode(', ', $product_names);

    // Get uploaded file count
    $uploaded_files = $order->get_meta('_uploaded_files');
    if (!is_array($uploaded_files)) {
        $uploaded_files = [];
    }
    $file_count = count($uploaded_files);

    $upload_url = add_query_arg([
        'order_id' => $order_id
    ], site_url('/uploader'));

    $view_files_url = site_url( '/my-account/view-order/' . $order_id . '/#' );
    ?>

    <div style="margin-top:20px; font-family:Arial, sans-serif;">

        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; padding:15px; font-size:16px;">
            <div>Order Number: <strong>#<?php echo esc_html($order->get_order_number()); ?></strong></div>
            <div>Service: <strong><?php echo esc_html($service_name); ?></strong></div>
            <div>Order Name: <strong><?php echo esc_html($order_name); ?></strong></div>
        </div>

        <div style="padding:0 15px; margin-bottom:10px;">
            Total number of files we have received - <span id="file-count"><?php echo esc_html($file_count); ?></span>
        </div>

        <div style="display:flex; gap:5px; padding:0 15px;">
            <a href="<?php echo esc_url($upload_url); ?>" style="background:#2ad89f; text-decoration:none; border:none; padding:10px 20px; cursor:pointer; color:black; font-weight:bold;">
                UPLOAD FILES
            </a>

            <div style="background:#2ad89f; padding:10px 15px; color:black; font-weight:bold; flex:1;">
                To VIEW FILES. Please wait 2-3 minutes and click refresh before selecting VIEW ALL
            </div>

            <a href="<?php echo esc_url($view_files_url); ?>" style="background:black; color:white; text-decoration:none; border:none; padding:10px 20px; cursor:pointer; font-weight:bold;">
                VIEW ALL
            </a>
        </div>
    </div>
    <?php
}

add_action( 'wp_enqueue_scripts', function() {
    // Load Select2 CSS and JS from WordPress core or CDN
    wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true );
});


function dd_force_basel_css_last() {
    // Remove Basel’s default CSS first
    wp_dequeue_style('basel-style');
    wp_deregister_style('basel-style');

    // Re-enqueue Basel CSS with very high priority, after everything else
    wp_enqueue_style(
        'basel-style',
        get_template_directory_uri() . '/style.css',
        array(), // no dependencies
        '6.8.2'
    );
}
add_action('wp_enqueue_scripts', 'dd_force_basel_css_last', 999);


//add pwf code menu in wp-admin dashbaord
add_action('admin_menu', function () {
    add_menu_page(
        'PWF',                // Page title
        'PWF',                // Menu label in sidebar
        'manage_options',     // Capability
        'mysite-code',        // Slug (required but not used for redirect)
        function () {
            // Redirect to external/internal link
            wp_redirect('http://170.9.252.208/pwf-workflow/');
            exit;
        },
        'dashicons-admin-links', // Icon
        25
    );
});

// Add hidden input with final price on single product page
add_action( 'woocommerce_after_add_to_cart_button', 'add_final_price_hidden_input' );
function add_final_price_hidden_input() {
    global $product;

    if ( ! $product ) return;

    $price = wc_get_price_to_display( $product );

    echo '<input type="hidden" id="final_product_price" name="final_product_price" value="' . esc_attr( $price ) . '" />';
}

add_action( 'wp_footer', 'update_final_price_hidden_input' );
function update_final_price_hidden_input() {
    if ( ! is_product() ) return;
    ?>
    <script type="text/javascript">
    jQuery(function($){
        function updateHiddenPrice() {
            // Try to read the currently displayed product price
            var priceText = $(".woocommerce-variation-price .woocommerce-Price-amount").last().text() 
                         || $(".summary .price .woocommerce-Price-amount").last().text();

            if (priceText) {
                var cleanPrice = priceText.replace(/[^0-9.,]/g, ''); // strip currency symbols
                $("#final_product_price").val(cleanPrice);
            }
        }

        // Run once on load
        updateHiddenPrice();

        // When variation is selected
        $(document).on("show_variation hide_variation found_variation", function(){
            updateHiddenPrice();
        });

        // When WooCommerce updates totals (extra options plugins often trigger this)
        $(document).on("updated_wc_div updated_cart_totals change", function(){
            updateHiddenPrice();
        });

        // Also when custom option fields are changed
        $(document).on("change", ".tm-extra-product-options input, .tm-extra-product-options select", function(){
            setTimeout(updateHiddenPrice, 300); // slight delay for calculation
        });
    });
    </script>
    <?php
}