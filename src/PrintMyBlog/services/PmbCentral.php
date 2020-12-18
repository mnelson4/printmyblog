<?php


namespace PrintMyBlog\services;


use WP_Error;

class PmbCentral
{
    /**
     * Asks PMBCentral for info about this license' info.
     * @param int $license_id
     * @param boolean $refresh
     * @return array|WP_Error
     */
    public function getCreditsInfo($license_id, $refresh = false){
        $transient_name = $this->getCreditsTransientName();
        $credit_data = false;
        if( ! $refresh){
            $credit_data = get_transient($transient_name);
        }

        if($credit_data === false){
            $url = 'https://printmy.blog/wp-json/pmb/v1/accounts/' . $license_id . '/credits';
            if($refresh){
                $url = add_query_arg(
                    [
                        'refresh' => true
                    ],
                    $url
                );
            }
            $response = wp_remote_get($url);
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
    public function getCreditsTransientName(){
        return 'pmb_license_credits';
    }

    /**
     * Returns the current credits info, just like PmbCentral::getCreditsInfo()
     * @param $license_id
     * @return int
     */
    public function reduceCredits($license_id){
        $transient_name = $this->getCreditsTransientName();
        $credit_data = get_transient($transient_name);
        if($credit_data === false){
            // ok it wasn't cached anyway, just update to whatever is correct
            // no need to modify anything, that's already up-to-date
            $credit_data = $this->getCreditsInfo($license_id);
        } else {
            // we already have it cached, just modify it then.
            $credit_data['credits_remaining']--;
            set_transient($transient_name, $credit_data, rest_parse_date($credit_data['expiry_date']) - current_time('timestamp'));
        }
        return $credit_data;
    }

}