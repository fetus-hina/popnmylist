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
class Pml_Konami_Client_Page_Music {
    const URL_BASE      = 'https://www.ea-pass.konami.net/contents/popn/music19/music/detail.do';
    const URL_PARAM_KEY = 'strData1';

    const MEDAL_NO_PLAY     = 'NO PLAY';
    const MEDAL_NO_CLEAR    = 'NO CLEAR';
    const MEDAL_CLEAR       = 'CLEAR';
    const MEDAL_NO_BAD      = 'NO BAD';
    const MEDAL_PERFECT     = 'PERFECT';

    private
        $song_id = null,
        $scores =
            array(
                'N'  => array('great' => null, 'cool' => null, 'medal' => null),
                'H'  => array('great' => null, 'cool' => null, 'medal' => null),
                'EX' => array('great' => null, 'cool' => null, 'medal' => null),
                '5'  => array('great' => null, 'cool' => null, 'medal' => null));

    public function __construct($song_id) {
        $this->song_id = (string)$song_id;
        $this->load();
    }

    public function getScore($diff, $type) {
        return $this->scores[$diff][$type];
    }

    public function getMedal($diff) {
        return $this->getScore($diff, 'medal');
    }

    private function load() {
        $document = $this->request();
        $xpath = new DOMXpath($document);
        $trs = $xpath->query('//table[@id="popun_tablecolor_type3"]/tr');
        if($trs->length !== 4) {
            throw new Pml_Exception('Loaded HTML File May Broken');
        }

        // スコア
        foreach(array(1 => 'great', 2 => 'cool') as $tr_number => $score_type_key) {
            $tds = $xpath->query('./td', $trs->item($tr_number));
            if($tds->length !== 4) {
                throw new Pml_Exception('Loaded HTML File May Broken');
            }
            foreach(array(0 => 'N', 1 => 'H', 2 => 'EX', 3 => '5') as $td_number => $diff_key) {
                $score = trim($tds->item($td_number)->textContent);
                if($score === '-') {
                    $this->scores[$diff_key][$score_type_key] = null;
                } elseif(preg_match('/^[[:digit:]]+$/', $score)) {
                    $this->scores[$diff_key][$score_type_key] = (int)$score;
                } else {
                    throw new Pml_Exception('Score value is invalid: ' . $score);
                }
            }
        }

        // メダル
        $tds = $xpath->query('./td', $trs->item(3));
        if($tds->length !== 4) {
            throw new Pml_Exception('Loaded HTML File May Broken');
        }
        foreach(array(0 => 'N', 1 => 'H', 2 => 'EX', 3 => '5') as $td_number => $diff_key) {
            $value = trim($tds->item($td_number)->textContent);
            switch($value) {
            case 'PERFECT':     $value2 = self::MEDAL_PERFECT;  break;
            case 'NO BAD':      $value2 = self::MEDAL_NO_BAD;   break;
            case 'CLEAR':       $value2 = self::MEDAL_CLEAR;    break;
            case 'NO CLEAR':
            case '':
            case "\xC2\xA0":    // nbsp
                if(is_null($this->scores[$diff_key]['great']) && is_null($this->scores[$diff_key]['cool'])) {
                    $value2 = null;
                } elseif($this->scores[$diff_key]['great'] > 0 || $this->scores[$diff_key]['cool'] > 0) {
                    $value2 = self::MEDAL_NO_CLEAR;
                } else {
                    $value2 = self::MEDAL_NO_PLAY;
                }
                break;
            default:
                throw new Pml_Exception('Medal value is invalid: ' . $value);
            }
            $this->scores[$diff_key]['medal'] = $value2;
        }
    }

    private function request() {
        $uri = Zend_Uri::factory(self::URL_BASE);
        $uri->setQuery(array(self::URL_PARAM_KEY => $this->song_id));
        list($uri, $document) = Pml_Http_Client::requestHtml($uri);
        return $document;
    }
}
