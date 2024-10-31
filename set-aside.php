<?php
/*
Plugin Name: Set Aside
Plugin URI: http://ten-fingers-and-a-brain.com/wordpress-plugins/set-aside/
Version: 0.2
Description: Lets you change the post format on all posts in a category. This should come in useful e.g. when you used to run a theme that styles posts as "Asides" based on categories, and then you switch to a theme that styles them based on the post format, a feature which was introduced in WordPress 3.1
Author: Martin Lormes
Author URI: http://ten-fingers-and-a-brain.com/
Text Domain: set-aside
*/
/*
Copyright (c) 2011 Martin Lormes

This program is free software; you can redistribute it and/or modify it under 
the terms of the GNU General Public License as published by the Free Software 
Foundation; either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT 
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program. If not, see <http://www.gnu.org/licenses/>.
*/
/** Set Aside (WordPress Plugin) */

// i18n/l10n
load_plugin_textdomain ( 'set-aside', '', basename ( dirname ( __FILE__ ) ) );

/** Set Aside (WordPress Plugin) functions wrapped in a class. (namespacing pre PHP 5.3) */
class set_aside
{
  
  /**
   * this big chunk of spaghetti code makes for the plugin itself
   */
  function management_page()
  {
    if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) )
      $post_formats = get_theme_support( 'post-formats' );
    else
      $post_formats = array( array(), );
    
    // user action
    if ( isset( $_POST['action'] ) AND 'set-aside' == $_POST['action'] AND isset( $_POST['cat'] ) AND isset( $_POST['post_format'] ) )
    {
      check_admin_referer ( 'set-aside' );
      
      /** @todo sanitize */
      $cat = $_POST['cat'];
      $post_format = $_POST['post_format'];
      
      $qry = new WP_Query( array ( 'cat' => $cat, 'post_type' => 'post', 'nopaging' => 1, ) );
      
      if ( ( 0 < $qry->post_count ) AND is_array( $qry->posts ) )
        foreach ( $qry->posts as $p )
          set_post_format( $p, $post_format );
      
      echo '<div id="message" class="updated fade"><p><strong>' . sprintf ( __( '%1$d Posts changed to Format "%2$s" &ndash; Thank you for using <em>%3$s</em>' , 'set-aside' ), $qry->post_count,  esc_html( get_post_format_string($post_format) ), __( 'Set Aside', 'set-aside' ) ) . '</strong></p></div>';
    } // user action
    
    ?>
    <div class="wrap">
      <div id="icon-tools" class="icon32"><br></div>
      <h2><?php _e( 'Set Aside', 'set-aside' ); ?></h2>
      
      <?php if ( !function_exists('get_post_format_slugs') ) : ?>
        <?php _e( '<p>This version of WordPress is too old. It doesn\'t support post formats.</p>', 'set-aside' ); ?>
      <?php else : ?>
        <form method="post" action="">
          <?php wp_nonce_field ( 'set-aside' ); ?>
          <input type="hidden" name="action" value="set-aside" />
          
          <?php echo sprintf ( __( '<p>When you click the button below, <em>%1$s</em> will change the post format on all posts in the category you selected.</p>', 'set-aside' ), __( 'Set Aside', 'set-aside' ) ); ?>
          
          <p><label for="cat"><?php _e('Categories'); ?>:</label>
            <?php wp_dropdown_categories( array( 'hierarchical' => 1, 'selected' => isset($cat)?$cat:0, ) ); ?>
          </p>
          <p><label for="post_format"><?php _e('Format'); ?>:</label>
            <select name="post_format" id="post_format">
              <optgroup label="Supported by your current theme">
                <option value="0"><?php _e('Standard'); // is this in the default domain or in the domain (or context!?) 'Post format' ?? ?></option>
                <?php foreach ( $post_formats[0] as $format ) : ?>
                  <option value="<?php echo esc_attr( $format ); ?>"<?php if (isset($post_format) AND ($post_format==$format)) echo ' selected="selected"'; ?>><?php echo esc_html( get_post_format_string( $format ) ); ?></option>
                <?php endforeach; ?>
              </optgroup>
              <optgroup label="Other post formats">
                <?php foreach ( array_diff ( get_post_format_slugs(), $post_formats[0], array ( 'standard' ) ) as $format ) : ?>
                  <option value="<?php echo esc_attr( $format ); ?>"><?php echo esc_html( get_post_format_string( $format ) ); ?></option>
                <?php endforeach; ?>
              </optgroup>
            </select>
          </p>
          <p class="submit"><input class="button-secondary" type="submit" value="<?php esc_attr_e( 'Change Post Format', 'set-aside' ); ?>" /></p>
        </form>
      <?php endif; // !function_exists('get_post_format_slugs') ?>
      
    </div>
    <?php
  }

  /**
   * hooked to {@link http://codex.wordpress.org/Plugin_API/Action_Reference WordPress action}: {@link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu admin_menu}
   */
  function admin_menu ()
  {
    $page = add_management_page ( __( 'Set Aside', 'set-aside' ), __( 'Set Aside', 'set-aside' ), 'edit_posts', 'set-aside', array ( 'set_aside', 'management_page' ) );
    /* translators: %1$s is the plugin name, %2$s is the Plugin URI, %3$s links to the WordPress Codex page on post formats, %4$s links to a blog post by Otto */
    $help = sprintf ( __( '<p><em>%1$s</em> lets you change the post format on all posts in a category. This should come in useful e.g. when you used to run a theme that styles posts as "Asides" based on categories, and then you switch to a theme that styles them based on the post format, a feature which was introduced in WordPress 3.1</p><p>If you have any questions you can contact the author of this plugin via his blog at <a href="%2$s">ten-fingers-and-a-brain.com</a></p><p>For more information on post formats consult <a href="%3$s">the WordPress Codex</a> or read <a href="%4$s">this great article by Otto</a></p>', 'set-aside' ), __( 'Set Aside', 'set-aside' ), __( 'http://ten-fingers-and-a-brain.com/wordpress-plugins/set-aside/', 'set-aside' ), __( 'http://codex.wordpress.org/Post_Formats', 'set-aside' ), __( 'http://ottopress.com/2010/post-types-and-formats-and-taxonomies-oh-my/', 'set-aside' ) );
    add_contextual_help ( $page, $help );
  }
  
} // class set_aside

// GO!
add_action ( 'admin_menu', array ( 'set_aside', 'admin_menu' ) );
