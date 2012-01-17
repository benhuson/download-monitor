<?php

class WP_DLM_Logs {

	var $wp_dlm_db;
	var $wp_dlm_db_log;
	
	function WP_DLM_Logs() {
		global $wpdb;
		$this->wp_dlm_db = $wpdb->prefix . 'download_monitor_files';
		$this->wp_dlm_db_log = $wpdb->prefix . 'download_monitor_log';
	}
	
	function get_log_date( $download_id, $args = null ) {
		global $wpdb;
		$args = wp_parse_args( $args, array(
			'ip_address' => ''
		) );
		$ip_address_sql = !empty( $args['ip_address'] ) ? "ip_address = '" . $args['ip_address'] . "' AND " : '';
		return $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$this->wp_dlm_db_log} WHERE " . $ip_address_sql . "download_id = " . $download_id . " ORDER BY date DESC limit 1;" ) );
	}
	
	function log_download( $download_id, $args = null ) {
		global $wpdb, $user_ID;
		$args = wp_parse_args( $args, array(
			'ip_address' => '',
			'timestamp'  => current_time( 'timestamp', 1 ),
			'user'       => $user_ID
		) );
		if ( empty( $args['user'] ) )
			$args['user'] = '0';
		$wpdb->query( $wpdb->prepare( "INSERT INTO {$this->wp_dlm_db_log} (download_id, user_id, date, ip_address) VALUES (%s, %s, %s, %s);", $download_id, $args['user'], date( "Y-m-d H:i:s", $args['timestamp'] ), $args['ip_address'] ) );
	}
	
	function delete_log( $download_id ) {
		global $wpdb;
		$query_delete = "DELETE FROM {$this->wp_dlm_db_log} WHERE download_id=" . $wpdb->escape( $download_id ) . ";";
		$wpdb->query( $query_delete );
	}
	
	function clear_logs() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$this->wp_dlm_db_log};" );
	}
	
	function get_logs( $args = null ) {
		global $wpdb;
		$args = wp_parse_args( $args, array(
			'order_by' => $this->wp_dlm_db_log . '.date',
			'order'    => 'DESC',
			'offset'   => 0,
			'limit'    => 0
		) );
		$limit_sql = $args['limit'] > 0 ? 'LIMIT ' . $wpdb->escape( $args['offset'] ) . ', ' . $args['limit'] : '';
		$logs = $wpdb->get_results( "
			SELECT {$this->wp_dlm_db}.*, {$this->wp_dlm_db_log}.ip_address, {$this->wp_dlm_db_log}.date, {$this->wp_dlm_db_log}.user_id
			FROM {$this->wp_dlm_db_log}  
			INNER JOIN {$this->wp_dlm_db} ON {$this->wp_dlm_db_log}.download_id = {$this->wp_dlm_db}.id 
			ORDER BY " . $args['order_by'] . " " . $args['order'] . " " . $limit_sql . ";
		" );
		return $logs;
	}
	
	function count_logs() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->wp_dlm_db_log} INNER JOIN {$this->wp_dlm_db} ON {$this->wp_dlm_db_log}.download_id = {$this->wp_dlm_db}.id;" );
	}
	
}
	
?>