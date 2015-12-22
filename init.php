<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
FEMALEREGONLYMEN_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('not-for-your-sex', '/users/denided', 'FEMALEREGONLYMEN_CTRL_Controller', 'newRedirect'));
OW::getRouter()->addRoute(new OW_Route('femaleregonlymen_admin', 'admin/femaleregonlymen', 'FEMALEREGONLYMEN_CTRL_Admin', 'index'));
