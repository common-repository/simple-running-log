<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

 	global $wpdb;
    $table = $wpdb->prefix."running";
    $wpdb->query("DROP TABLE IF EXISTS $table");  //delete custom **_running table
    
    delete_option('log_db_version'); //delete option from the  options table
    
?>