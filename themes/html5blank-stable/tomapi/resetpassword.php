<?php
add_action('rest_api_init', function () {
  	register_rest_route( 'tomapi/v1', 'resetpassword',array(
		'methods'  => 'POST',
        'callback' => function ($request){
        	$base_url = site_url();
        	$email = $request->get_json_params()['email'];
			$data = array();
			$data['user_login'] = $email;
			$response = wp_remote_post( $base_url . '/wp-json/wp/v2/users/lost-password', array(
			    'method'      => 'POST',
			    'timeout'     => 45,
			    'redirection' => 5,
			    'httpversion' => '1.0',
			    'blocking'    => true,
			    'headers'     => array('Content-Type'=>'application/json'),
			    'data_format' => 'body',
			    'body'        => json_encode($data),
			    'cookies'     => array()
			    )
			);
			 
			if ( is_wp_error( $response ) ) {
			    $error_message = $response->get_error_message();
			    return "Something went wrong: $error_message";
			} else {
			    return $response;
			}
        },
        'args'     => array()
    ));
});