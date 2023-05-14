<?php
/*
Plugin Name: Subscribers Admin Customization
Plugin URI: https://fabien404.fr/
Description: Customizes the WordPress admin interface for subscribers.
Author: Fabien 404
Author Email: fabien@fabien404.fr
Author URI: https://fabien404.fr/
Version: 1.0.12
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

// Add the plugin settings page to the admin menu
add_action('admin_menu', 'subscribers_admin_customization_menu');
function subscribers_admin_customization_menu()
{
    add_options_page(
        'Subscribers Admin Customization',
        'Subscribers Customization',
        'manage_options',
        'subscribers-customization',
        'subscribers_customization_page'
    );
}

// Create the plugin settings page
function subscribers_customization_page()
{
    ?>
    <div class="wrap">
        <h1>Personnalisation de l'administration pour les abonnés</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('subscribers-customization');
            do_settings_sections('subscribers-customization');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">URL de redirection des abonnés qui se connectent</th>
                    <td><input type="text" name="login_redirect_url" value="<?php echo esc_attr(
                        get_option('login_redirect_url')
                    ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register the plugin settings
add_action('admin_init', 'subscribers_customization_settings');
function subscribers_customization_settings()
{
    register_setting('subscribers-customization', 'login_redirect_url');
}

// Redirect subscribers after login
add_filter('login_redirect', 'subscribers_custom_login_redirect', 10, 3);
function subscribers_custom_login_redirect($redirect_to, $request, $user)
{
    if (
        isset($user->roles) &&
        is_array($user->roles) &&
        in_array('subscriber', $user->roles)
    ) {
        $login_redirect_url = get_option('login_redirect_url');
        if ($login_redirect_url) {
            return $login_redirect_url;
        }
    }
    return $redirect_to;
}

// Disable plugin notifications for subscribers
add_action('admin_init', 'subscribers_disable_plugin_notifications');
function subscribers_disable_plugin_notifications()
{
    $user = wp_get_current_user();
    if (
        isset($user->roles) &&
        is_array($user->roles) &&
        in_array('subscriber', $user->roles)
    ) {
        add_action('admin_enqueue_scripts', 'subscribers_customization_styles');
    }
}

function subscribers_customization_styles()
{
    $custom_css = "
        .notice {
            display: none;
        }
    ";

    wp_add_inline_style('wp-admin', $custom_css);
}

// Customize user profile fields for subscribers
add_action('admin_init', 'subscribers_custom_profile_fields');
function subscribers_custom_profile_fields()
{
    $user = wp_get_current_user();
    if (
        isset($user->roles) &&
        is_array($user->roles) &&
        in_array('subscriber', $user->roles)
    ) {
        remove_all_actions('edit_user_profile');
        remove_all_actions('show_user_profile');
        remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
    }
}
