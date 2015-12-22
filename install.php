<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 OW::getPluginManager()->addPluginSettingsRouteName('femaleregonlymen', 'femaleregonlymen_admin');
 $path = OW::getPluginManager()->getPlugin('femaleregonlymen')->getRootDir() . 'langs.zip';
 OW::getLanguage()->importPluginLangs($path, 'femaleregonlymen');
 OW::getConfig()->addConfig('femaleregonlymen', 'new_login', '');
