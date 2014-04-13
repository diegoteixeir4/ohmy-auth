<?php namespace ohmy;

/*
 * Copyright (c) 2014, Yahoo! Inc. All rights reserved.
 * Copyrights licensed under the New BSD License.
 * See the accompanying LICENSE file for terms.
 */

use ohmy\Auth1\Flow\TwoLegged,
    ohmy\Auth1\Flow\ThreeLegged,
    ohmy\Components\Http\Curl\Request,
    ohmy\Components\Session\PHPSession;

class Auth1 {

    public static function legs($number) {
        return Auth1::init($number);
    }

    public static function init($type) {

        $client = new Request;
        $oauth = array(
            'oauth_callback'           => '',
            'oauth_consumer_key'       => '',
            'oauth_consumer_secret'    => '',
            'oauth_nonce'              => md5(mt_rand()),
            'oauth_signature_method'   => 'HMAC-SHA1',
            'oauth_timestamp'          => time(),
            'oauth_version'            => '1.0'
        );

        # encode all params
        foreach($oauth as $key => $val) $oauth[$key] = rawurlencode($val);

        switch($type) {
            case 2:
                return new TwoLegged(function($resolve) use($oauth) {
                    $resolve($oauth);
                }, $client);
                break;
            case 3:
                $session = new PHPSession;
                $oauth['oauth_token'] = $session->read('oauth_token');
                $oauth['oauth_token_secret'] = $session->read('oauth_token_secret');
                $oauth['oauth_verifier'] = $session['oauth_verifier'];
                return new ThreeLegged(function($resolve) use($oauth) {
                    $resolve($oauth);
                }, $client, $session);
                break;
            default:
        }
    }
}
