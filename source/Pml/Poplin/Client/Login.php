<?php
class Pml_Poplin_Client_Login {
    const LOGIN_START_URL           = 'http://poplin.jp/mylist/index.html';

    // return: 次に Referrer とする URI
    static public function login(Pml_Config $config) {
        $login_data = self::parseLoginForm();
        $login_data['params']['ID']         = $config->mylist->id;
        $login_data['params']['PASSWORD']   = $config->mylist->password;
        return self::doLogin($login_data);
    }

    static private function parseLoginForm() {
        echo "Requesting Poplin Login form\n";
        list($uri, $document) =
            Pml_Http_Client::requestHtml(
                Zend_Uri::factory(self::LOGIN_START_URL));
        echo "Parsing Poplin Login form\n";
        $xpath = new DOMXpath($document);
        $forms = $xpath->query('//form[@method="POST"]');
        foreach($forms as $form) {
            if($result = self::parseLoginFormNodeForm($uri, $form)) {
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
        if(isset($result['params']['ID']) && isset($result['params']['PASSWORD'])) {
            return $result;
        }
        return false;
    }

    static private function parseLoginFormNodeFormChild(DOMNode $node) {
        if($node->nodeType !== XML_ELEMENT_NODE) {
            return array();
        }
        $result = array();
        switch(strtolower($node->nodeName)) {
        case 'input':
            if($node->hasAttribute('name') && $node->getAttribute('name') !== '') {
                $type = strtolower($node->getAttribute('type'));
                if($type === 'text' || $type === 'password') {
                    $result[ $node->getAttribute('name') ] = $node->getAttribute('value');
                }
            }
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
