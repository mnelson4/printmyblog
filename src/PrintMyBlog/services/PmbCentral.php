<?php

namespace PrintMyBlog\services;

use Exception;
use WP_Error;

class PmbCentral
{
    /**
     * Asks PMBCentral for info about this license' info.
     * @param boolean $refresh
     * @return array|WP_Error
     * @throws Exception
     */
    public function getCreditsInfo($refresh = false)
    {
        $license_id = pmb_fs()->_get_license()->id;
        if (! $license_id) {
            throw new Exception(__('No license ID available', 'print-my-blog'));
        }
        $install_id = pmb_fs()->get_site()->id;
        $transient_name = $this->getCreditsTransientName();
        $credit_data = false;
        if ($refresh) {
            delete_transient($transient_name);
        } else {
            $credit_data = get_transient($transient_name);
        }

        if ($credit_data === false) {
            $url = $this->getCentralUrl() . 'licenses/' . $license_id . '/installs/' . $install_id . '/credits';
            if ($refresh) {
                $url = add_query_arg(
                    [
                        'refresh' => true
                    ],
                    $url
                );
            }

            $response = wp_remote_get(
                $url,
                [
                    'headers' => [
                        'Authorization' => $this->getSiteAuthorizationHeader()
                    ],
                    'timeout' => 20
                ]
            );
            if ($response instanceof WP_Error) {
                throw new Exception($response->get_error_message(), (int)$response->get_error_code());
            } else {
                $body = wp_remote_retrieve_body($response);
                $credit_data = json_decode($body, true);
                if (is_array($credit_data) && isset($credit_data['expiry_date'])) {
                    $time_to_expire = rest_parse_date($credit_data['expiry_date']) - current_time('timestamp');
                    set_transient($transient_name, $credit_data, $time_to_expire);
                }
            }
        }
        return $credit_data;
    }

    /**
     * Gets the special "site signature", derived from the install's private key, to send to PMB central.
     * @return string
     */
    public function getSiteSignature()
    {
        $site = pmb_fs()->get_site();
        if(! $site){
            throw new Exception(__('There is no site registered with Freemius', 'print-my-blog'));
        }
        $site_private_key = pmb_fs()->get_site()->secret_key;
        // create the signature that verifies we own this license and install.
        $nonce = date('Y-m-d');
        $pk_hash = hash('sha512', $site_private_key . '|' . $nonce);
        return base64_encode($pk_hash . '|' . $nonce);
    }

    /**
     * Gets the header value for the site authorization
     * @return string
     */
    public function getSiteAuthorizationHeader()
    {
        return 'PMB ' . $this->getSiteSignature();
    }

    public function getCreditsTransientName()
    {
        return 'pmb_license_credits';
    }

    /**
     * Returns the current credits info, just like PmbCentral::getCreditsInfo()
     * @return int
     */
    public function reduceCredits()
    {
        $license_id = pmb_fs()->_get_license()->id;
        $transient_name = $this->getCreditsTransientName();
        $credit_data = get_transient($transient_name);
        if ($credit_data === false) {
            // ok it wasn't cached anyway, just update to whatever is correct
            // no need to modify anything, that's already up-to-date
            $credit_data = $this->getCreditsInfo();
        } else {
            // we already have it cached, just modify it then.
            $credit_data['remaining_credits']--;
            set_transient($transient_name, $credit_data, rest_parse_date($credit_data['expiry_date']) - current_time('timestamp'));
        }
        return $credit_data;
    }

    public function getCentralUrl()
    {
        if (defined('PMB_CENTRAL_URL')) {
            $central_base_url = PMB_CENTRAL_URL;
        } else {
            $central_base_url = 'https://printmy.blog/wp-json/pmb/v1/';
        }
        return $central_base_url;
    }
}
