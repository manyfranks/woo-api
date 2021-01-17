<?php

add_action( 'woocommerce_payment_complete', 'create_order_to_api', 10, 1 );

function create_order_to_api($order_id){
    $api_url = 'API_URL'; /*ADD API URL*/
    $api_key = 'API_KEY'; /*ADD API KEY*/
    $comment_meta_key_text = 'Comment_Meta'; /*Use the title of your TextArea as key here*/
    $field_meta_key_text = 'Field_Meta';/*Use the title of your Textfield as key here*/
    
    $wc_order = wc_get_order($order_id); /* Get order */
    
    if($wc_order){
       
        $items = $wc_order->get_items();
        foreach($items as $item_id => $item){
            $api_order_id_meta_text = 'api_order_id_'.$item_id; /*order meta key used to store API order id */
                        
            $parent_product_id  = $item->get_product_id();
            
            
            if (has_term( 'API', 'product_cat', $parent_product_id ) ) { /*Check product has category "API"*/
                $api_order_id = $wc_order->get_meta($api_order_id_meta_text, true); /*Create order in API if it is not created*/
                if(empty($api_order_id)){
                    $product = $item->get_product();
                    
                    $field = $item->get_meta( $field_meta_key_text, true); /*Gets textfield data*/
                    $comments = $item->get_meta($comment_meta_key_text, true); /*Gets textarea data*/
                    $weight = $product->get_weight(); /*Random parameter for payload. Could be any standard woocommerce data*/
                    $service = $product->get_sku(); /*Use this as product identifier for API*/
                    
                    /*Payload to send, format per instructions from API*/
                    $body = array( 
                        'key' => $api_key, 
                        'action' => 'add',
                        'service' => $service,                        
                        'field'  => $field,
                        'weight' => $weight,
                        'comments' => $comments
                    );
                    /*post on API */
                    $response = wp_remote_post( $api_url, array(
                            'method' => 'POST',
                            'body' => $body
                        )
                    );
                    /*Test and log errors on order page and in woocommerce logs*/
                    $error_message = '';
                    if( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();
                    }
                    else if(isset($response['body'])){
                       $response_body = json_decode($response['body'],true);
                       if(isset($response_body['order']) && !empty($response_body['order'])){
                           $api_order_id = $response_body['order'];
                       }
                       else{
                           $error_message = $response_body['error'];
                       }
                    }
                    else{
                        $error_message = "something went wrong";
                    }
                    
                    if(!empty($error_message)){
                        /*Add in error logging file if any error*/
                        /*Admin can see error log at WooCommerce -> Status -> Logs -> api-log(from drop down)*/
                        $log = new WC_Logger();
                        $log_entry = "\n URL : ".$api_url;
                        $log_entry .= "\n Error: ".$error_message;
                        $log_entry .= "\n Request: ".print_r( $body, true );
                        $log_entry .= "\n Response: ". print_r( $response , true );
                        $log->add( 'api-log', $log_entry );
                    }
                    else{
                        /*Add api order id if success*/
                        update_post_meta( $order_id, $api_order_id_meta_text, $api_order_id);
                    }
                }
            }
            

        }
        
        
    }
}
?>