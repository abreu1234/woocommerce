<?php
/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * Class use db for PagSeguro
 */
class WP_WC_Modal_Pagseguro {
    
    /**
     * Array with PagSeguro status
     * 
     * @var array 
     */
    private static $array_order_status = array(
	0 => 'pending',
        1 => 'on-hold',
        2 => 'processing',
        3 => 'completed',
        4 => 'completed',
        5 => 'em disputa',
        6 => 'refunded',
        7 => 'cancelled');
    
    /**
     * Use for get $array_order_status
     * 
     * @return Array 
     */
    public function getOrderStatus(){
        return self::$array_order_status;
    }
    
    /**
     * 
     * Return Key from Order Status for Name
     * 
     * @global type data variable
     * @param type String
     * @return type Integer
     */
    public function getKeyOrderStatusByName($name){
        global $wpdb;
        $term_id = NULL;
        
        if(isset($name) && !empty($name)){
            $term_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE name LIKE '".trim($name)."'"));
            return !empty($term_id) ? $term_id : NULL;
        }
        
        return $term_id;
    }
    
    /**
     * 
     * Return Name from Order Status for Key
     * 
     * @global type type data variable
     * @param type String
     * @return type Integer
     */
    public function getNameOrderStatusByKey($key){
        global $wpdb;
        $term_name = NULL;
        
        if(isset($key) && !empty($key)){
            $term_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $wpdb->terms WHERE term_id = $key"));
            return !empty($term_name) ? $term_name : NULL;
        }
        
        return $term_name;
    }
    
    /**
     * Update status in table wp_term_relationships
     * 
     * @param type $order_id, reference order
     * @param type $term_id, id status
     */
    public function updateOrder($order_id, $term_id){
        global $wpdb;
        $term_taxonomy_id = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE term_id = $term_id"));
        $term_taxonomy_id_relation = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id = $order_id"));
        if($term_taxonomy_id == $term_taxonomy_id_relation){
            return false;
        }else{
            $wpdb->query("UPDATE $wpdb->term_relationships SET term_taxonomy_id = $term_taxonomy_id WHERE object_id = $order_id;");
            return true;
        }
    }
    
	     /**
     * Translate status for PagSeguro
     * 
     * @param type $status
     */
    public function translateStatus($status){
    	switch($status){
		case 'pending':
			return 'pendente';
			break;
        case 'on-hold':
			return 'aguardando';
			break;
        case 'processing':
			return 'processando';
			break;
        case 'completed':
			return 'concluido';
			break;
        case 'em disputa':
			return 'em disputa';
			break;
        case 'refunded':
			return 'reembolsado';
			break;
        case 'cancelled':
			return 'cancelado';
			break;
		default:
			return '';
			break;
		}
    }
	
    /**
     * Save Historic when change PagSeguro status
     * 
     * @global type $wpdb
     * @param type $order_id
     * @param type $name_order_status
     * @param type $update
     */
    public function saveHistoric($order_id,$name_order_status,$update){
        if($update){
            global $wpdb;

            $comment_post_ID 		= $order_id;
            $comment_author 		= 'PagSeguro';
            $comment_author_url 	= '';
            $comment_content 		= (get_locale() == "pt_BR")? 'Status atualizado para '.$this->translateStatus($name_order_status):'Status updated to '.$name_order_status;
            $comment_agent              = 'WooCommerce';
            $comment_type		= 'order_note';
            $comment_parent		= 0;
            $comment_approved 		= 1;
            $commentdata 			= compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' );

            wp_insert_comment( $commentdata );
        }
    }
}
?>