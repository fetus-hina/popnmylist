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
class Pml_Db {
    static public function get() {
        static $db = null;
        if(is_null($db)) {
            $db = Zend_Db::factory('Pdo_Sqlite', array('dbname' => __DIR__ . '/../../data/data.sqlite'));
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
        }
        return $db;
    }
}
