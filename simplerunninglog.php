<?php
/*
Plugin Name:  Simple Running Log
Plugin URI:   http://wordpress.org/support/plugin/simple-running-log
Description:  Add a simple training log to your website and update it from the WordPress admin dashboard
Version:      1.7
Author:       Kevin Marsden
Author URI:   http://kmarsden.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpdb;

function simple_running_log_install () {
	global $wpdb;
	
	$current_log_version = get_option( 'log_db_version' );
	$log_db_version = '2.0';
	
	$running_table = $wpdb->prefix . 'running';

//Create the "**_running" table in the WP database to store running log data
				
	$create_sql = "CREATE TABLE {$running_table} (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		srl_year VARCHAR(4),
		srl_month VARCHAR(2),
		srl_day VARCHAR(2),
		srl_time VARCHAR(5),
		distance VARCHAR(5) NOT NULL,
		the_date DATE NOT NULL,
		UNIQUE KEY id (id)
		);";

	require_once ABSPATH.'wp-admin/includes/upgrade.php';
	dbDelta( $create_sql );
	
	//Check if the table has been updated to the new column names.  If not, the names are updated
	$updated_columns = $wpdb->query( "SHOW COLUMNS FROM {$running_table} LIKE 'srl_year'" );
	if ( ( ( $current_log_version == "1.0" ) OR ( empty( $current_log_version ) ) ) AND ( $updated_columns == FALSE ) ) {
		$alter1 = "ALTER TABLE {$running_table} CHANGE year srl_year VARCHAR(4)";
		$alter2 = "ALTER TABLE {$running_table} CHANGE month srl_month VARCHAR(2)";
		$alter3 = "ALTER TABLE {$running_table} CHANGE day srl_day VARCHAR(2)";
		$alter4 = "ALTER TABLE {$running_table} CHANGE time srl_time VARCHAR(5)";
		$wpdb->query( $alter1 );
		$wpdb->query( $alter2 );
		$wpdb->query( $alter3 );
		$wpdb->query( $alter4 );
	}
	
	//Check if this is an db upgrade.  If so, migrate old data to the new column "the_date" 
	if ( ( $current_log_version == "1.0" ) || ( empty( $current_log_version ) ) ) {	
		$sql_update = "UPDATE {$running_table} SET the_date = CONCAT_WS( '-' , srl_year, srl_month, srl_day );";
		$wpdb->query( $sql_update );
	}
	
	//Update the db version in the options table
	if ( isset( $log_db_version ) ) {
	update_option( 'log_db_version', $log_db_version );
	}
}

//Registration hook to create the table when the plugin is activated

register_activation_hook( __FILE__, 'simple_running_log_install' );


//Registers and enqueues admin-specific JavaScript and CSS
function register_admin_scripts() {
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'wp-jquery-date-picker', plugins_url( 'simple-running-log/js/admin.js' ) );
	wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );
}

//Enqueue jquery UI and CSS
add_action( 'admin_enqueue_scripts', 'register_admin_scripts' );

//Registers and enqueues other scripts and CSS
function register_other_scripts() {
	wp_enqueue_style( 'srl', plugins_url( 'simple-running-log/css/style.css' ) );
}

//Enqueue jquery UI and CSS
add_action( 'wp_enqueue_scripts', 'register_other_scripts' );

// Create the function to output the admin dashboard widget for user to enter log data

function running_widget_function() {

    //Display Running Log Input Form
	echo '<form method="POST" action="" >';
	wp_nonce_field('running-log-input');
	echo '<table> 
		
		<tr>
		<td>Date:</td>
		<td><input id="datepicker" type="text" name="date_selected" value="Select a date"></td>
		</tr>
		
		<tr>
		<td>Distance:</td>
		<td><input type="text" name="distance_data"></td>
		</tr> 
		
		<tr> 
		<td><input type="submit" value="Submit" name="Submit"></td>
		<td><input type="reset" value="Reset" name="Reset"></td>  
		</tr> 

	</table> 
	</form>';


    //Submits data to the database table **_running
	if ( isset ( $_POST['Submit'] ) ) {
		check_admin_referer( 'running-log-input' );  //check nonces
		global $wpdb;
		$srl_table = $wpdb->prefix . "running"; 
		$distance = sanitize_text_field( $_POST['distance_data'] ); 
		$entry_date = sanitize_text_field( $_POST['date_selected'] );
		$insert_sql = "INSERT INTO {$srl_table} (the_date,distance) VALUES (%s, %d)";
		$prepare_sql = $wpdb->prepare( $insert_sql, $entry_date, $distance ); 
	
		if ( $wpdb->query( $prepare_sql ) == FALSE ) {
			echo "There was an error adding your mileage";
		} 
		else {
			echo "Your entry was successfully added to the log";
		}	
	}
}

// Create the widget action hook function which activates the admin dashboard widget

function add_running_widgets() {
	wp_add_dashboard_widget( 'admin_widget', 'Training Log Mileage', 'running_widget_function' );
} 

// Hook into the 'wp_dashboard_setup' action to register our other functions

add_action( 'wp_dashboard_setup', 'add_running_widgets' );

//Create Sidebar Widget

function simple_running_log_widget_init() {

	function simple_running_log_widget( $args ){
		extract( $args );
		global $wpdb;	
		$m = date( 'm' );
		$y = date( 'Y' );
		$srl_table = $wpdb->prefix . "running";
		
		$wptime = current_time( 'mysql' ); //based on the WP timezone setting
		$unixtime = strtotime( $wptime ); 
		$offset = date( 'w', $unixtime ); //get day of week
		$offsetunix = $offset * 86400; //convert offset to seconds
		$wpstartwk = get_option( 'start_of_week' ); //get the WordPress start of week setting
		$wpstartwkunix = $wpstartwk * 86400;  //convert setting to seconds
		if ( $offset >= $wpstartwk ) {
			$week_begin = $unixtime - $offsetunix + $wpstartwkunix;
			}	
		else {
			$week_begin = $unixtime - $offsetunix - ( 7*86400 ) + $wpstartwkunix;
			}
		$begin_week_date = date( 'Y-m-d', $week_begin );  //Convert to the needed date format	
	

		$var1 = $wpdb->get_var( "SELECT SUM(distance) FROM {$srl_table} WHERE the_date >= CAST('$begin_week_date' as DATE)" );
		$var2 = $wpdb->get_var( "SELECT SUM(distance) FROM {$srl_table} WHERE month(the_date) = '$m' and year(the_date) = '$y'" );
		$var3 = $wpdb->get_var( "SELECT SUM(distance) FROM {$srl_table} WHERE year(the_date) = '$y'" );
		$var4 = $wpdb->get_var( "SELECT SUM(distance) FROM {$srl_table}" );
		$week = number_format( $var1 );
		$month = number_format( $var2 );
		$year = number_format( $var3 );
		$grand_total = number_format( $var4 );

		// Set Sidebar Widget Name 
		$title = 'Training Log Mileage';
		
		//Before Widget (defined by theme) 
		echo $before_widget;

		echo $before_title
		 . esc_html( $title )
		 . $after_title
		 . "<table class='simple-running-log'>"
		 . "<tr><td>This Week:</td> "
		 . "<td  style='text-align:right;'>"
		 . esc_html( $week )
		 . "</td></tr>"
		 . "<tr><td>This Month:</td>"
		 . "<td  style='text-align:right;'>"
		 . esc_html( $month )
		 . "</td></tr>"
		 . "<tr><td>This Year:</td>"
		 . "<td  style='text-align:right;'>"
		 .  esc_html( $year )
		 . "</td></tr>"
		 . "<tr><td>Total Mileage:</td>"
		 . "<td  style='text-align:right;'>"
		 . esc_html( $grand_total )
		 . "</td></tr>"
		 . "</table>";
		
		// After Widget (defined by theme)
		echo $after_widget;
		}

	wp_register_sidebar_widget( 'simple_running_log_widget','Training Log', 'simple_running_log_widget' );

}

add_action( 'plugins_loaded', 'simple_running_log_widget_init' );