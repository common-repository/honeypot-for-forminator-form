<?php
/*
Options Page
Plugin: Honeypot for Forminator Forms
Since: 0.1
Author: KGM Servizi
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Options page
add_action( 'admin_menu', 'hfff_add_settings_page' );
function hfff_add_settings_page() {
    add_submenu_page( 'tools.php', 'Honeypot for Forminator', 'Honeypot for Forminator', 'manage_options', 'hfff-plugin', 'hfff_render_settings_page' );
}

function hfff_render_settings_page() { 
	$notice = hfff_get_admin_notice( get_current_user_id() );
	if ( !empty( $notice ) && is_array( $notice ) ) {
		$status = array_key_exists('status', $notice) ? $notice['status'] : 'success';
		$message = array_key_exists('message', $notice) ? $notice['message'] : '';
		echo '<div class="notice notice-' . esc_attr( $status ) . ' is-dismissible" style="font-size: 18px; height: 50px; display: flex; align-items: center;">' . esc_html( $message ) . '</div>';
	} ?>
    <div class="wrap">
        <h2>Honeypot for Forminator Settings</h2>
        <form method="post" action="">
            <table class="form-table"> 
            <tr>
                <th>
                    <label>Field Name</label>
                </th>          
                <td>
                    <input id="hfff_field_name" name="hfff_field_name" value="<?php echo esc_attr( get_option( 'hfff_field_name' ) ); ?>" type="text" readonly="readonly" />
                    <span class="dashicons dashicons-image-rotate" style="font-size: 30px; cursor: pointer;" onclick="hfff_field_name_generator_js()"></span><br>
                    <p><?php _e( 'Updating this field regularly can prevent SPAM.', $GLOBALS['hfff_text_domain'] ); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label>Spam prevented</label>
                </th>          
                <td>
                    <input id="hfff_log" name="hfff_log" value="<?php echo esc_attr( get_option( 'hfff_log' ) ); ?>" type="text" readonly="readonly" />
                </td>
            </tr>
        </table>
        <?php wp_nonce_field( 'hfff_save_settings', 'hfff_nonce' ); ?>
        <input type="submit" name="submit-hfff-settings" class="button-primary" value="Save Changes" />
    </div>
    <script type="text/javascript">
        function hfff_field_name_generator_js() {
            const names = ['name', 'surname', 'email', 'phone', 'telephone', 'mobile'];
            var number  = Math.floor( Math.random() * 999 );
            var name    = hfff_random_array( names );

            jQuery('#hfff_field_name').val(name+number);
        }
        function hfff_random_array( array ) {
            return array[ Math.floor( ( Math.random() * array.length ) ) ];
        }
    </script>
<?php }

if ( isset( $_POST['submit-hfff-settings'] ) ) {
    hfff_save_settings();
}
function hfff_save_settings() {	
	if ( isset( $_POST['hfff_nonce'] ) && wp_verify_nonce( $_POST['hfff_nonce'], 'hfff_save_settings' ) ) {		
		if ( empty( $_POST['hfff_field_name'] ) ) {
			hfff_set_admin_notice( get_current_user_id(), "Field Name can't be empty!", 'error' );
		} else {
			update_option( 'hfff_field_name', sanitize_text_field( $_POST['hfff_field_name'] ) );

			$GLOBALS['hfff_field_name'] = get_option('hfff_field_name');

			hfff_set_admin_notice( get_current_user_id(), 'Settings updated', 'success' );
		}
	} else {
		hfff_set_admin_notice( get_current_user_id(), 'Nonce error, please try again.', 'error' );
	}
	return $return;
}

function hfff_set_admin_notice($id, $message, $status = 'success') {
    set_transient( 'hfff_setting_notice' . '_' . $id, [
        'message' => $message,
        'status'  => $status
    ], 30);
}

function hfff_get_admin_notice($id) {
    $transient = get_transient( 'hfff_setting_notice' . '_' . $id );
    return $transient;
}
