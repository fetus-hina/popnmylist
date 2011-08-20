<?php
/*
 * pop'n My List updator
 *
 * Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 *
 * LICENSE
 *
 * This source file is subject to the 2-cause BSD License(Simplified
 * BSD License) that is bundled with this package in the file LICENSE.
 * The license is also available at this URL:
 * https://github.com/fetus-hina/popnmylist/blob/master/LICENSE
 */
class Pml_Http_Client {
    static public function getInstance(Zend_Uri $uri = null, Zend_Uri $referrer = null) {
        static $instance = null;
        if(is_null($instance)) {
            $instance = new Zend_Http_Client();
            $instance->setConfig(
                array(
                    'maxredirects'      => 10,
                    'strictredirects'   => false,
                    'useragent'         => 'Mozilla/5.0 (compatible)',
                    'timeout'           => 30,
                    'httpversion'       => '1.1',
                    'keepalive'         => true,
                    'storeresponse'     => true));
            $instance->setCookieJar();
        }
        $instance->resetParameters(true);
        $instance->setMethod(Zend_Http_Client::GET);
        if($uri) {
            $instance->setUri($uri);
            $instance->setHeaders('Referer', $referrer ? $referrer->__toString() : null);
        }
        return $instance;
    }

    static public function request(
        Zend_Uri $uri,
        Zend_Uri $referrer = null,
        $method = 'GET',
        array $post_params = array())
    {
        $client = self::getInstance($uri, $referrer);
        if($method === 'POST') {
            $client->setMethod(Zend_Http_Client::POST);
            if($post_params) {
                $client->setParameterPost($post_params);
            }
        }
        if(!$resp = $client->request()) {
            throw new Pml_Exception(__METHOD__ . ': request failed');
        }
        if(!$resp instanceof Zend_Http_Response || !$resp->isSuccessful()) {
            throw new Pml_Exception(__METHOD__ . ': response error');
        }
        return array($client->getUri(), $resp);
    }

    static public function requestHtml(
        Zend_Uri $uri,
        Zend_Uri $referrer = null,
        $method = 'GET',
        array $post_params = array(),
        $encoding = null)
    {
        $resp = self::request($uri, $referrer, $method, $post_params);
        $document = new DOMDocument();
        $document->preserveWhitespace = false;
        $document->recover = true;
        $body = $resp[1]->getBody();
        if($encoding) {
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', $encoding);
        }
        if(!@$document->loadHTML($body)) {
            throw new Pml_Exception('Cannot parse HTML');
        }
        return array($resp[0], $document);
    }
}
