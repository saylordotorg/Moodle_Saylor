<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * This is a class containing functions for managing/checking licenses
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class licensemanager {

    const FILTER_POODLL_VAL_BY_REGCODE = 1;
    const FILTER_POODLL_VAL_BY_APICREDS = 2;
    const FILTER_POODLL_IS_REGISTERED = 1;
    const FILTER_POODLL_IS_UNREGISTERED = 0;
    const FILTER_POODLL_IS_EXPIRED = 2;

    const FILTER_POODLL_LICENSE_ENTERPRISE = 4135;
    const FILTER_POODLL_LICENSE_BIGDOG = 4134;
    const FILTER_POODLL_LICENSE_STANDARD = 4132;
    const FILTER_POODLL_LICENSE_TINY = 4141;

    //Old subscription levels
    const FILTER_POODLL_LICENSE_INSTITUTION = 2511;
    const FILTER_POODLL_LICENSE_INDIVIDUAL = 2512;
    const FILTER_POODLL_LICENSE_FREETRIAL = 2583;

    //are we validated
    private $validated = false;
    private $validation_method = false;

    //data parsed from registration code
    private $registered_url = '';
    private $cloud_access_key = '';
    private $cloud_access_secret = '';
    private $expire_date = '';
    private $license_type = 0;

    //data parsed from api tokenobject
    private $api_registered_url = '';
    private $api_cloud_access_key = '';
    private $api_cloud_access_secret = '';
    private $api_expire_date = '';
    private $api_license_type = 0;

    /**
     * Check the registration key is valid
     *
     *
     */
    public function get_cloud_access_key($regkey) {
        //if we are using API keys
        $apiuser = get_config(constants::MOD_FRANKY, 'cpapiuser');
        $apisecret = get_config(constants::MOD_FRANKY, 'cpapisecret');
        if ($this->validation_method == self::FILTER_POODLL_VAL_BY_APICREDS && $this->validated) {
            return $this->api_cloud_access_key;
        } else if (!empty($apiuser) && !empty($apisecret)) {
            $tokenobject = $this->fetch_token($apiuser, $apisecret);
            if ($tokenobject) {
                return $tokenobject->awsaccessid;
            }
        }

        //Else we are using reg code
        if (empty($this->cloud_access_key)) {
            if (empty($regkey)) {
                return false;
            }
            $decrypted = $this->decrypt_registration_key($regkey);
            $this->parse_decrypted_data($decrypted);
        }
        return $this->cloud_access_key;

    }

    /**
     * Check the registration key is valid
     *
     *
     */
    public function get_cloud_access_secret($regkey) {
        //if we are using API keys
        $apiuser = get_config(constants::MOD_FRANKY, 'cpapiuser');
        $apisecret = get_config(constants::MOD_FRANKY, 'cpapisecret');
        if ($this->validation_method == self::FILTER_POODLL_VAL_BY_APICREDS && $this->validated) {
            return $this->api_cloud_access_secret;
        } else if (!empty($apiuser) && !empty($apisecret)) {
            $tokenobject = $this->fetch_token($apiuser, $apisecret);
            if ($tokenobject) {
                return $tokenobject->awsaccesssecret;
            }
        }
        //Else we are using reg code
        if (empty($this->cloud_access_secret)) {
            if (empty($regkey)) {
                return false;
            }
            $decrypted = $this->decrypt_registration_key($regkey);
            $this->parse_decrypted_data($decrypted);
        }
        return $this->cloud_access_secret;
    }

    /**
     * Fetch license details in display form
     *
     *
     */
    public function fetch_license_details() {
        $details = new \stdClass();
        $details->expire_date = $this->expire_date;
        switch ($this->license_type) {
            case self::FILTER_POODLL_LICENSE_FREETRIAL:
                $details->license_type = 'Free Trial';
                break;
            case self::FILTER_POODLL_LICENSE_INDIVIDUAL:
                $details->license_type = 'Individual Teacher';
                break;
            case self::FILTER_POODLL_LICENSE_INSTITUTION:
                $details->license_type = 'Institution';
                break;
            default:
                $details->license_type = "Other: " . $this->license_type;
        }
        $details->registered_url = $this->registered_url;
        return $details;
    }

    public function validate_license() {
        global $CFG;
        $apiuser = get_config(constants::MOD_FRANKY, 'cpapiuser');
        $apisecret = get_config(constants::MOD_FRANKY, 'cpapisecret');
        $regkey = $CFG->filter_poodll_registrationkey;
        if (empty($apiuser) || empty($apisecret)) {
            $regstatus = $this->validate_registrationkey($regkey);
            $this->validation_method = self::FILTER_POODLL_VAL_BY_REGCODE;
        } else {
            $regstatus = $this->validate_api_creds($apiuser, $apisecret);
            $this->validation_method = self::FILTER_POODLL_VAL_BY_APICREDS;
        }
        if ($regstatus == self::FILTER_POODLL_IS_REGISTERED) {
            $this->validated = true;
        }
        return $regstatus;
    }

    /**
     * Check the registration key is valid
     *
     *
     */
    public function validate_registrationkey($regkey) {
        global $CFG;

        if ($this->validated) {
            return self::FILTER_POODLL_IS_REGISTERED;
        }
        if (empty($regkey)) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
        if (empty($this->registered_url)) {
            $decrypted = $this->decrypt_registration_key($regkey);
            $this->parse_decrypted_data($decrypted);
        }
        //if we still have no url return false
        if (empty($this->registered_url)) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }

        //if we are expired or have no expiry, return false
        if (empty($this->expire_date)) {
            return self::FILTER_POODLL_IS_EXPIRED;
        }
        $expire_time = strtotime($this->expire_date);
        $diff = $expire_time - time();

        if ($diff < 0) {
            return self::FILTER_POODLL_IS_EXPIRED;
        }

        return $this->check_registered_url($this->registered_url, true);

    }

    protected function check_registered_url($theurl, $wildcardok = true) {
        global $CFG;

        //get arrays of the wwwroot and registered url
        //just in case, lowercase'ify them
        $thewwwroot = strtolower($CFG->wwwroot);
        $theregisteredurl = strtolower($theurl);
        $theregisteredurl = trim($theregisteredurl);

        //add http:// or https:// to URLs that do not have it
        if (strpos($theregisteredurl, 'https://') !== 0 &&
                strpos($theregisteredurl, 'http://') !== 0) {
            $theregisteredurl = 'https://' . $theregisteredurl;
        }

        //if neither parsed successfully, that a no straight up
        $wwwroot_bits = parse_url($thewwwroot);
        $registered_bits = parse_url($theregisteredurl);
        if (!$wwwroot_bits || !$registered_bits) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }

        //get the subdomain widlcard address, ie *.a.b.c.d.com
        $wildcard_subdomain_wwwroot = '';
        if (array_key_exists('host', $wwwroot_bits)) {
            $wildcardparts = explode('.', $wwwroot_bits['host']);
            $wildcardparts[0] = '*';
            $wildcard_subdomain_wwwroot = implode('.', $wildcardparts);
        } else {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }

        //match either the exact domain or the wildcard domain or fail
        if (array_key_exists('host', $registered_bits)) {
            //this will cover exact matches and path matches
            if ($registered_bits['host'] === $wwwroot_bits['host']) {
                $this->validated = true;
                return self::FILTER_POODLL_IS_REGISTERED;
                //this will cover subdomain matches but only for institution bigdog and enterprise license
            } else if (($registered_bits['host'] === $wildcard_subdomain_wwwroot) && $wildcardok) {
                //yay we are registered!!!!
                return self::FILTER_POODLL_IS_REGISTERED;
            } else {
                return self::FILTER_POODLL_IS_UNREGISTERED;
            }
        } else {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
    }

    protected function parse_decrypted_data($decrypted_data) {
        // print_r($decrypted_data);
        // die;
        $delim = '+@@@@@@+';
        $parts = explode($delim, $decrypted_data);
        if (count($parts) > 4) {
            $this->registered_url = $parts[0];
            $this->cloud_access_key = $parts[1];
            $this->cloud_access_secret = $parts[2];
            $this->license_type = $parts[3];
            $this->expire_date = $parts[4];
        }
    }

    public function fetch_unregistered_content($registration_status) {
        switch ($registration_status) {
            case self::FILTER_POODLL_IS_EXPIRED:
                $thereason = get_string('expired', 'filter_poodll');
                break;
            case self::FILTER_POODLL_IS_UNREGISTERED:
            default:
                $thereason = get_string('unregistered', 'filter_poodll');
        }
        return \html_writer::div($thereason, 'filter_poodll_unregistered');

    }

    /* PoodLL URL + data decryption */
    public function decrypt_registration_key($encrypted) {
        $decrypted =
                ""; // holds text which was decrypted by the public key after being encrypted with the private key, should be same as $tocrypt
        $pubkey = self::fetch_public_key();
        $base64decrypted = base64_decode($encrypted);
        openssl_public_decrypt($base64decrypted, $decrypted, $pubkey);
        return $decrypted;
    }

    /* PoodLL public key */
    function fetch_public_key() {
        $pubcert = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArqaQv3yajo7dUbvxCqgA
qcb7ZBp+oUZ5PbCE36q8Fm4dI6VYd6ihuAZmQKfMJqkD6f6ZupxW7mIl6YUW6Hjf
vIQb9c+ZRQ4p5L1foQ/MB9oFaJJvZE0tb70taXO5sQzvA+3odvqjWtqZ7fS06ILC
qlaT3jAOvzOYs0B6dqE8XBPJxagGB2/OGvxtN3yAMCHQ3tNIOS85I9dkCK6tbHyK
R/WfJ67egRWgeJ83JbEEuCXUOIKXYFu5HQf0FJEQWZiwHN5h9fSS7POIhM2P9y/F
YtSPP2ag4FsnLMCzC6bt0bxEnCmHoJcr3JmX1lspnqw2OGnPUjX8JeP7+yon2Bpo
gQIDAQAB
-----END PUBLIC KEY-----
";
        $pubkey = openssl_get_publickey($pubcert);
        return $pubkey;
    }

    /* API Key stuff from here */

    /**
     * Check the api key and secret validate this site and app
     *
     *
     */
    public function validate_api_creds($apiuser, $apisecret) {
        //fetch token or bust
        $tokenobject = $this->fetch_token($apiuser, $apisecret);
        if (!$tokenobject) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }

        //check sites and apps and subs exist
        if (!$tokenobject->apps) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
        if (!$tokenobject->sites) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
        if (!$tokenobject->subs) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }

        //check at least one subscription is current
        $havecurrentsub = false;
        foreach ($tokenobject->subs as $sub) {
            if ($sub->expiredate > time()) {
                $havecurrentsub = true;
                break;
            }
        }
        if (!$havecurrentsub) {
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }

        //if the app (filter_poodll) is authorised, and the site URL is ok, return true
        //else its false.
        foreach ($tokenobject->apps as $app) {
            if ($app == constants::MOD_FRANKY) {
                foreach ($tokenobject->sites as $site) {
                    $reg_status = $this->check_registered_url($site, true);
                    if ($reg_status == self::FILTER_POODLL_IS_REGISTERED) {
                        //update our reg info for later
                        $this->api_registered_url = $site;
                        $this->api_cloud_access_key = $tokenobject->awsaccessid;
                        $this->api_cloud_access_secret = $tokenobject->awsaccesssecret;
                        $this->api_license_type = $sub->subscriptionid;
                        $this->api_expire_date = $sub->expiredate;
                        //return validated flag
                        return self::FILTER_POODLL_IS_REGISTERED;
                    }
                }
            }
        }
        return self::FILTER_POODLL_IS_UNREGISTERED;
    }

    //we use curl to fetch Tokens from cloudpoodll
    //this is our helper
    protected function curl_fetch($url, $postdata = false) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();

        $result = $curl->get($url, $postdata);
        return $result;
    }

    //This is called from the settings page and we do not want to make calls out to cloud.poodll.com on settings
    //page load, for performance and stability issues. So if the cache is empty and/or no token, we just show a
    //"refresh token" link
    public function fetch_token_for_display($apiuser, $apisecret) {
        global $CFG;

        //First check that we have an API id and secret
        //refresh token
        $refresh = \html_writer::link($CFG->wwwroot . '/filter/poodll/refreshtoken.php',
                        get_string('refreshtoken', constants::MOD_FRANKY)) . '<br>';

        $message = '';
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        if (empty($apiuser)) {
            $message .= get_string('noapiuser', constants::MOD_FRANKY) . '<br>';
        }
        if (empty($apisecret)) {
            $message .= get_string('noapisecret', constants::MOD_FRANKY);
        }

        if (!empty($message)) {
            return $refresh . $message;
        }

        //Fetch from cache and process the results and display
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::MOD_FRANKY, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        //if we have no token object the creds were wrong ... or something
        if (!($tokenobject)) {
            $message = get_string('notokenincache', constants::MOD_FRANKY);
            //if we have an object but its no good, creds werer wrong ..or something
        } else if (!property_exists($tokenobject, 'token') || empty($tokenobject->token)) {
            $message = get_string('credentialsinvalid', constants::MOD_FRANKY);
            //if we do not have subs, then we are on a very old token or something is wrong, just get out of here.
        } else if (!property_exists($tokenobject, 'subs')) {
            $message = 'No subscriptions found at all';
        }
        if (!empty($message)) {
            return $refresh . $message;
        }

        //we have enough info to display a report. Lets go.
        foreach ($tokenobject->subs as $sub) {
            $sub->expiredate = date('d/m/Y', $sub->expiredate);
            $message .= get_string('displaysubs', constants::MOD_FRANKY, $sub) . '<br>';
        }
        //Is site authorised
        $haveauthsite = false;
        foreach ($tokenobject->sites as $site) {
            if ($this->check_registered_url($site) == self::FILTER_POODLL_IS_REGISTERED) {
                $haveauthsite = true;
                break;
            }
        }
        if (!$haveauthsite) {
            $message .= get_string('appnotauthorised', constants::MOD_FRANKY) . '<br>';
        } else {

            //Is app authorised
            if (in_array(constants::MOD_FRANKY, $tokenobject->apps)) {
                $message .= get_string('appauthorised', constants::MOD_FRANKY) . '<br>';
            } else {
                $message .= get_string('appnotauthorised', constants::MOD_FRANKY) . '<br>';
            }
        }

        return $refresh . $message;

    }

    //We need a Poodll token to make all this recording and transcripts happen
    public function fetch_token($apiuser, $apisecret, $force = false) {

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::MOD_FRANKY, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');
        $tokenuser = $cache->get('recentpoodlluser');
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);

        //if we got a token and its less than expiry time
        // use the cached one
        if ($tokenobject && $tokenuser && $tokenuser == $apiuser && !$force) {
            if ($tokenobject->validuntil == 0 || $tokenobject->validuntil > time()) {
                return $tokenobject;
            }
        }

        // Send the request & save response to $resp
        $token_url = constants::CLOUDPOODLL . "/local/cpapi/poodlltoken.php";
        $postdata = array(
                'username' => $apiuser,
                'password' => $apisecret,
                'service' => 'cloud_poodll'
        );
        $token_response = $this->curl_fetch($token_url, $postdata);
//error_log( $token_response);
        if ($token_response) {
            $resp_object = json_decode($token_response);
            if ($resp_object && property_exists($resp_object, 'token')) {
                //store the expiry timestamp and adjust it for diffs between our server times
                if ($resp_object->validuntil) {
                    $validuntil = $resp_object->validuntil - ($resp_object->poodlltime - time());
                    //we refresh one hour out, to prevent any overlap
                    $validuntil = $validuntil - (1 * HOURSECS);
                } else {
                    $validuntil = 0;
                }

                //make sure the token has all the bits in it we expect before caching it
                $tokenobject = $resp_object;//new \stdClass();
                $tokenobject->validuntil = $validuntil;
                if (!property_exists($tokenobject, 'subs')) {
                    $tokenobject->subs = false;
                }
                if (!property_exists($tokenobject, 'apps')) {
                    $tokenobject->apps = false;
                }
                if (!property_exists($tokenobject, 'sites')) {
                    $tokenobject->sites = false;
                }
                $cache->set('recentpoodlltoken', $tokenobject);
                $cache->set('recentpoodlluser', $apiuser);

            } else {
                $tokenobject = false;
                if ($resp_object && property_exists($resp_object, 'error')) {
                    //ERROR = $resp_object->error
                }
            }
        } else {
            $tokenobject = false;
        }
        return $tokenobject;
    }
}
