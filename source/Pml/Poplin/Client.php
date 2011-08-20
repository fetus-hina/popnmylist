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
class Pml_Poplin_Client {
    private
        $logged_in  = false,
        $config     = null;

    public function __construct(Pml_Config $config) {
        $this->config = $config;
    }

    //TODO: 真面目に実装
    public function login() {
        if($this->logged_in) {
            return true;
        }
        Pml_Poplin_Client_Login::login($this->config);
        $this->logged_in = true;
        return true;
    }

    public function getForm25() {
        $this->login();
        return new Pml_Poplin_Client_Form($this->config, 25);
    }

    public function getForm34() {
        $this->login();
        return new Pml_Poplin_Client_Form($this->config, 34);
    }
}
