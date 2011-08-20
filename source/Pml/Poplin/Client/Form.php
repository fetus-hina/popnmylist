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
class Pml_Poplin_Client_Form {
    const URL_25    = 'http://park8.wakwak.com/~poplin/cgi-bin/mylist25/edit.cgi';
    const URL_34    = 'http://park8.wakwak.com/~poplin/cgi-bin/mylist/edit.cgi';

    private
        $config         = null,
        $type           = 25,
        $request_uri    = null,
        $post_uri       = null,
        $parameters     = array();

    public function __construct(Pml_Config $config, $type) {
        $this->config = $config;
        $this->type = (int)$type;
        $this->request_uri = Zend_Uri::factory($type == 25 ? self::URL_25 : self::URL_34);
        $this->load();
    }

    public function update() {
        $this->updateFormData();
        $this->doUpdate();
    }

    private function load() {
        $document = $this->request();
        $xpath = new DOMXpath($document);
        $forms = $xpath->query('//form[@action="entry.cgi"]');
        if($forms->length !== 1) {
            throw new Pml_Exception('Loaded HTML File May Broken');
        }
        $this->parseFormData($forms->item(0));
    }

    private function request() {
        list($uri, $document) =
            Pml_Http_Client::requestHtml(
                $this->request_uri,
                null,
                'POST',
                array(
                    'ID'        => $this->config->mylist->id,
                    'PASSWORD'  => $this->config->mylist->password),
                'CP932');
        $this->request_uri = $uri;
        return $document;
    }

    private function parseFormData(DOMElement $form) {
        $this->post_uri = Pml_Uri_Helper::resolveEx($this->request_uri, $form->getAttribute('action'));
        for($child = $form->firstChild; $child; $child = $child->nextSibling) {
            $this->parseFormDataNode($child);
        }
    }

    private function parseFormDataNode(DOMNode $node) {
        if($node->nodeType === XML_ELEMENT_NODE) {
            switch(strtolower($node->nodeName)) {
            case 'input':
                $type = strtolower($node->getAttribute('type'));
                $name = $node->getAttribute('name');
                if($type === 'text' || $type === 'password' || $type === 'hidden' || $type === '') {
                    $this->parameters[$name] = $node->getAttribute('value');
                } elseif($type === 'radio' || $type === 'checkbox') {
                    if($node->hasAttribute('checked')) {
                        $this->parameters[$name] = $node->getAttribute('value');
                    }
                }
                break;
            }
        }
        for($child = $node->firstChild; $child; $child = $child->nextSibling) {
            $this->parseFormDataNode($child);
        }
    }

    private function updateFormData() {
        printf("%s()\n", __METHOD__);
        $value_map =
            array(
                1   => array(2 => 'space',      3 => 'space',       4 => 'space'),
                2   => array(2 => 'bad',        3 => 'bad',         4 => 'bad'),
                3   => array(2 => 'clear_nr',   3 => 'clear_hy',    4 => 'clear_ex'),
                4   => array(2 => 'clear_nr_f', 3 => 'clear_hy_f',  4 => 'clear_ex_f'),
                5   => array(2 => 'clear_nr_p', 3 => 'clear_hy_p',  4 => 'clear_ex_p'));
        $db = Pml_Db::get();
        $select_ =
            $db->select()
                ->from('mylist_map', array())
                ->joinLeft(
                    'score_medals',
                    'score_medals.song_id = mylist_map.song_id AND score_medals.difficulty_id = mylist_map.difficulty_id',
                    array('difficulty_id', 'medal_id'));
        foreach(array_keys($this->parameters) as $key) {
            if(!is_int($key)) {
                continue;
            }
            $select = clone $select_;
            $select->where('mylist_map.mylist_id = ?', sprintf('%d_%d', $this->type, $key));
            if(!$row = $db->fetchRow($select)) {
                echo "WARNING: mylist-id {$this->type}_{$key} is not found\n";
                continue;
            }
            if(is_null($row['difficulty_id']) || is_null($row['medal_id'])) {
                $key1 = 1;
                $key2 = 2;
            } else {
                $key1 = (int)$row['medal_id'];
                $key2 = (int)$row['difficulty_id'];
            }
            if(!isset($value_map[$key1][$key2])) {
                throw new Pml_Exception('invalid value in database: medal ' . $key1 . ' / diff ' . $key2);
            }
            $this->parameters[$key] = $value_map[$key1][$key2];
        }
    }

    private function doUpdate() {
        list($uri, $document) =
            Pml_Http_Client::requestHtml(
                $this->post_uri,
                $this->request_uri,
                'POST',
                $this->prepareSendParameters($this->parameters),
                'CP932');
    }

    private function prepareSendParameters(array $parameters) {
        foreach(array_keys($parameters) as $key) {
            $parameters[$key] = mb_convert_encoding($parameters[$key], 'CP932', 'UTF-8');
        }
        return $parameters;
    }
}
