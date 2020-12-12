<?php


namespace PrintMyBlog\services;


use WP_Error;

class PmbCentral
{
    /**
     * Asks PMBCentral for info about this license' info.
     * @param int $license_id
     * @return array|WP_Error
     */
    public function getCreditsInfo($license_id){
        $transient_name = 'pmb_license_credits';
        $credit_data = get_transient($transient_name);
        if($credit_data === false){
            $response = wp_remote_get(
                    'https://printmy.blog/wp-json/pmb/v1/accounts/' . $license_id . '/credits'
            );
            if(! $response instanceof WP_Error){
                $body = wp_remote_retrieve_body($response);
                $credit_data = json_decode($body, true);
                if(is_array($credit_data) && isset($credit_data['expiry_date'])){
                    $time_to_expire = rest_parse_date($credit_data['expiry_date']) - current_time('timestamp');
                    set_transient($transient_name, $credit_data, $time_to_expire);
                }
            }
        }
        return $credit_data;
    }


}