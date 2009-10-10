<?php
/*
Plugin Name: Now Playing
Plugin URI: http://github.com/jaredmoody/wp_now_playing
Description: Show the music you're playhing in wordpress
Version: 0.1
Author: Jared Moody
Author URI: http://jaredmoody.com

Copyright 2009  Jared Moody  (email : jared@jaredmoody.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

$now_playing_version = "0.1";


function now_playing_install()
{
  global $wpdb;
  global $now_playing_version;
  
  $table_name = $wpdb->prefix . "songs";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
  {
      
      $sql = "CREATE TABLE " . $table_name . " (
	            `id` int(11) NOT NULL auto_increment,
              `img` varchar(100) default NULL,
              `title` varchar(75) default NULL,
              `album` varchar(50) default NULL,
              `artist` varchar(50) default NULL,
              `url` varchar(255) default NULL,
              `user_id` int(11) default NULL,
              PRIMARY KEY  (`id`)
	            );";
      
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
  
      add_option("now_playing_version", $now_playing_version);
   }
}


// shows the current album
function now_playing($username)
{
	global $wpdb;
	
  $users_table = $wpdb->prefix . "users";
  $songs_table = $wpdb->prefix . "songs";
  
  $result =  $wpdb->get_results("SELECT $songs_table.* FROM $songs_table INNER JOIN $users_table ON $songs_table.user_id = $users_table.id WHERE user_login = '$username' ORDER BY $songs_table.id DESC LIMIT 1");
    
  return $result[0];
}

function now_playing_config_page()
{
  //add_menu_page("Now Playing", "Now Playing", "Author", __FILE__, "now_playing_admin_menu"); 
  // Add a new submenu under Options:
      //add_options_page('Test Options', 'Test Options', 8, 'testoptions', 'mt_options_page');

      // Add a new submenu under Manage:
      //add_management_page('Test Manage', 'Test Manage', 8, 'testmanage', 'mt_manage_page');

      // Add a new top-level menu (ill-advised):
      //add_menu_page("Now Playing", "Now Playing", 8, __FILE__, 'now_playing_admin_menu');

      // Add a submenu to the custom top-level menu:
      add_submenu_page('options-general.php', 'Now Playing', 'Now Playing', 8, "now_playing_configuration", "now_playing_configuration" );
      //add_submenu_page(parent, page_title, menu_title, access_level/capability, file, [function]); 
      // Add a second submenu to the custom top-level menu:
      //add_submenu_page(__FILE__, 'Test Sublevel 2', 'Test Sublevel 2', 8, 'sub-page2', 'mt_sublevel_page2');
  
}


function now_playing_admin_menu()
{
  global $wpdb;
  
  
  $users_table = $wpdb->prefix . "users";
  
  $users = $wpdb->get_results("SELECT display_name FROM $users_table");
  print_r($users);

  echo "<div class='wrap'>";  
  echo "<h2>Now Playing</h2> Update now playing for: <select>";
  
  
  
  foreach($users as $user)
  {
    
    echo("<option>".$user->display_name."<option>");
  }
  echo "</select>";
  
}

function now_playing_configuration()
{
	global $wpdb;
	$users_table = $wpdb->prefix . "users";
?>
<div class="wrap">
<h2>Now Playing Configuration</h2>

<form method="post" action="options.php">
<table class="form-table">
<tr>
  <td colspan="2">
    <h3>Amazon API Keys</h3>
    The Now Playing plugin uses the Amazon API to retrieve album art.  You will need an amazon web services account
    which you can get at <a href="http://aws.amazon.com/">http://aws.amazon.com/</a> 
  </td>
</tr>
<tr valign="top">
<th scope="row">Amazon API Public Key</th>
<td><input type="text" name="now_playing_amzn_public" value="<?php echo get_option('now_playing_amzn_public'); ?>" /></td>
</tr>
 
<tr valign="top">
  <th scope="row">Amazon API Private Key</th>
  <td><input type="text" name="now_playing_amzn_private" value="<?php echo get_option('now_playing_amzn_private'); ?>" /></td>
</tr>
<tr>
  <td colspan="2">
    <h3>Amazon Associates Tag</h3>
    If you have an amazon affiliate account, you can enter the tag below.  This will be used in the links to the album on amazon and any purchases made will credit to your affiliates account.
    You can support this plugin by leaving this field blank, which defaults to a tag of the author.
    You can sign up for an amazon affiliate account at <a href="http://affiliate-program.amazon.com/">http://affiliate-program.amazon.com/</a> 
  </td>
</tr>
<tr valign="top">
  <th scope="row">Amazon Associates Tag (optional)</th>
  <td><input type="text" name="now_playing_amzn_tag" value="<?php echo get_option('now_playing_amzn_tag'); ?>" /></td>
</tr>
<tr valign="top">
  <td><h3>Script API Keys</h3></td>
</tr>
<?php
$result =  $wpdb->get_results("SELECT display_name, user_pass FROM $users_table");
foreach($result as $user )
{
?>
<tr valign="top">
  <th scope="row"><?php echo $user->display_name ?></th>
  <td><?php echo md5($user->user_pass); ?></td>
</tr>
<?php 
}
?>
</table>

<?php settings_fields( 'now_playing' ); ?>

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>

<?php
}

function register_settings()
{
  register_setting( 'now_playing', 'now_playing_amzn_public' );
  register_setting( 'now_playing', 'now_playing_amzn_private' );
  register_setting( 'now_playing', 'now_playing_amzn_tag' ); 
}

//
// HOOKS
//

register_activation_hook(__FILE__,'now_playing_install');
add_action('admin_menu', 'now_playing_config_page');
add_action('admin_init', 'register_settings' );
?>
