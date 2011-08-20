<?php
/*
 * pop'n My List updater
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
class Pml_Konami_Client_Login {
    const LOGIN_URL_BASE            = 'https://www.ea-pass.konami.net/mypage/login.do';
    const LOGIN_PARAM_RETURN_URL    = '/contents/popn/music19/index.do';

    static public function login(Pml_Config $config) {
        $login_data = self::parseLoginForm();
        $login_data['params']['kid']        = $config->konami->konami_id;
        $login_data['params']['password']   = $config->konami->password;
        return self::doLogin($login_data);
    }

    static private function parseLoginForm() {
        echo "Requesting ea-Pass Login form\n";
        list($uri, $document) =
            Pml_Http_Client::requestHtml(
                Zend_Uri::factory(
                    self::LOGIN_URL_BASE . '?' .
                    http_build_query(array('return_url' => self::LOGIN_PARAM_RETURN_URL))),
                null);
        echo "Parsing ea-Pass Login form\n";
        $xpath = new DOMXpath($document);
        $nodes = $xpath->query('//form[@name="LoginForm"]');
        foreach($nodes as $node) {
            if($result = self::parseLoginFormNodeForm($uri, $node)) {
                return $result;
            }
        }
        throw new Pml_Exception('Cannot parse HTML form');
    }

    static private function parseLoginFormNodeForm(Zend_Uri $uri, DOMElement $form) {
        $result =
            array(
                'uri'       => Pml_Uri_Helper::resolveEx($uri, $form->getAttribute('action')),
                'referrer'  => $uri,
                'method'    => trim(strtolower($form->getAttribute('method'))) === 'post' ? 'POST' : 'GET',
                'params'    => array());
        for($node = $form->firstChild; $node; $node = $node->nextSibling) {
            $result['params'] = array_merge($result['params'], self::parseLoginFormNodeFormChild($node));
        }
        if(!isset($result['params']['password'])) {
            return false;
        }
        return $result;
    }

    static private function parseLoginFormNodeFormChild(DOMNode $node) {
        if($node->nodeType !== XML_ELEMENT_NODE) {
            return array();
        }
        $result = array();
        switch(strtolower($node->nodeName)) {
        case 'input':
            $result[ $node->getAttribute('name') ] = $node->getAttribute('value');
            break;
        }

        for($child = $node->firstChild; $child; $child = $child->nextSibling) {
            $result = array_merge($result, self::parseLoginFormNodeFormChild($child));
        }
        return $result;
    }

    static private function doLogin(array $data) {
        echo "Login request sending...\n";
        $client = Pml_Http_Client::getInstance($data['uri'], isset($data['referer']) ? $data['referer'] : null);
        if($data['method'] === 'POST') {
            $client->setMethod('POST');
            $client->setParameterPost($data['params']);
        } else {
            $client->setMethod('GET');
            $client->setParameterGet($data['params']);
        }
        if(!$resp = $client->request()) {
            throw new Pml_Excepion('Login Request Failed');
        }
        if(!$resp instanceof Zend_Http_Response || !$resp->isSuccessful()) {
            throw new Pml_Excepion('Login Request Error');
        }
        echo "OK. Logged in\n";
        echo "Current URL: " . $client->getUri()->__toString() . "\n";
        return $client->getUri();
    }
}
