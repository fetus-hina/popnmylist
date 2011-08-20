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
require_once(__DIR__ . '/bootstrap.php');
$config = new Pml_Config();

Pml_Konami::update($config);
$client = new Pml_Poplin_Client($config);
$client->getForm25()->update();
$client->getForm34()->update();
