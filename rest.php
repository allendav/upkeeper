<?php

/* A simple RESTful endpoint for UpKeeper */

/******************************************************************************************************/
function upkeeper_debug_log( $message )
{
	// $logPath =  dirname(__FILE__) . '/upkeeper.log';
	// $fh = fopen($logPath, 'a') or die("can't open file");
	// $timestamp = strftime("%m-%d-%G %H:%M:%S");
	// fwrite($fh, "$timestamp $message\n");
	// fclose($fh);
}

include('../../../wp-config.php');

if ( ! current_user_can( 'manage_options' ) ) {
	http_response_code( 401 );
}
else {
	$method = strtoupper( $_SERVER['REQUEST_METHOD'] );
	$path_info = @$_SERVER['PATH_INFO'];
	upkeeper_debug_log( 'Method: ' . $method );

	$current_user = wp_get_current_user();
	$current_user_id = $current_user->ID;

	if ( !empty( $path_info ) ) {
		$path_info_array = split( "/", substr( @$_SERVER['PATH_INFO'], 1 ) );
		$id = $path_info_array[0];
		upkeeper_debug_log( 'id: ' . $id );
	}

	if ( $method === "GET" ) {
		// Return everything

		$data = array();

		$args = array(
			'numberposts'     => '-1',
			'post_type'       => 'upkeeperitem',
			'orderby'		  => 'ID',
			'order'           => 'ASC'
		); 

		$my_items = get_posts( $args );

		if ( count( $my_items ) > 0 ) {
			foreach ( $my_items as $my_item ) {
				$description = get_post_meta( $my_item->ID, 'description', true );
				$expires = get_post_meta( $my_item->ID, 'expires', true );
				$data[] = array(
					"id" => $my_item->ID,
					"description" => $description,
					"expires" => $expires
					);
			}
		}

		echo json_encode($data);

		upkeeper_debug_log( 'GET (read) all upkeeperitems');
	}

	if ( $method === "POST" ) {
		// Add a new model

		// TODO: NONCE
		// TODO: SANITIZE

		$contents = file_get_contents('php://input');
		$data = json_decode( $contents, true );
		$description = $data['description'];
		$expires = $data['expires'];

		// Create a new upkeeper item post
		
		$new_item = array(
			'post_title' => $description,
			'post_content' => '',
			'post_status' => 'publish',
			// 'post_date' => $dateTime,
			'post_author' => $current_user_id,
			'post_type' => 'upkeeperitem'
		);

		$new_item_id = wp_insert_post( $new_item );

		update_post_meta( $new_item_id, 'description', $description );
		update_post_meta( $new_item_id, 'expires', $expires );

		header( 'Location: /' . $new_item_id, true, 201 ); // Let the client know it was created and give its id

		// Send back the model so backbone can update its own copy (especially the new ID)
		$data = array(
			"id" => $new_item_id,
			"description" => $description,
			"expires" => $expires
		);

		echo json_encode($data);

		upkeeper_debug_log( 'POST (created) upkeeperitem with id ' . $new_item_id );
	}

	if ( $method === "PUT" ) {
		// Update an existing upkeeper post

		// TODO: NONCE
		// TODO: SANITIZE
		// TODO: For sanity, make sure the post we are updating is an upkeeperitem

		$contents = file_get_contents('php://input');
		$data = json_decode( $contents, true );
		$description = $data['description'];
		$expires = $data['expires'];

		update_post_meta( $id, 'description', $description );
		update_post_meta( $id, 'expires', $expires );

		upkeeper_debug_log( 'PUT (updated) upkeeperitem with id ' . $id );
	}

	if ( $method === "DELETE" ) {
		// Delete a model

		// TODO: NONCE
		// TODO: For sanity, make sure the post we are deleting is an upkeeperitem

		wp_delete_post( $id, true ); // true: force delete (no trash)

		// Delete the specified upkeeper item post
		upkeeper_debug_log( 'DELETE (deleted) upkeeperitem with id ' . $id );
	}	

}