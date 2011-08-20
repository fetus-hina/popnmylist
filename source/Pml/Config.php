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
class Pml_Config {
    private
        $konami,
        $mylist;

    public function __construct() {
        $ini_path = __DIR__ . '/../../config/config.ini';
        $this->konami = new Zend_Config_Ini($ini_path, 'konami');
        $this->mylist = new Zend_Config_Ini($ini_path, 'mylist');
    }

    public function __get($key) {
        switch($key) {
        case 'konami':  return $this->konami;
        case 'mylist':  return $this->mylist;
        }
    }
}
