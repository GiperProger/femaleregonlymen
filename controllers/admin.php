<?php

/**
 * Copyright (c) 2009, Skalfa LLC
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */



/**
 * femaleregonlymen admin controller
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.femaleregonlymen.controllers
 * @since 1.7.6
 */
class FEMALEREGONLYMEN_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $language = OW::getLanguage();

        $form = new FEMALEREGONLYMEN_CLASS_SettingsForm($this);
        $form->setId($form->getName());
        $this->addForm($form);
        
        $form->getElement('newLogin')->setValue(OW::getConfig()->getValue('femaleregonlymen', 'new_login'));


        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();
            $userName = $values['username'];
            $email = $values['email'];
            $password = $values['password'];
            $sex = $values['sex'];
            $newLogin = $values['newLogin'];
            OW::getConfig()->saveConfig ('femaleregonlymen', 'new_login', $values['newLogin']);
            $accountType =  SKADATE_BOL_AccountTypeToGenderService::getInstance()->getAccountType($sex);
            $userData = BOL_UserService::getInstance()->createUser($userName, $password, $email, $accountType, true);
            
            if($newLogin == true)
            {
                OW::getUser()->login($userData->id);
            }
            else
            {
                OW::getFeedback()->info($language->text('femaleregonlymen', 'reg_fake_success'));
                $this->redirect();
            }        

            
        }
        
        
        $language->addKeyForJs('base', 'join_error_username_not_valid');
        $language->addKeyForJs('base', 'join_error_username_already_exist');
        $language->addKeyForJs('base', 'join_error_email_not_valid');
        $language->addKeyForJs('base', 'join_error_email_already_exist');
        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');

         $onLoadJs = " window.join = new OW_BaseFieldValidators( " .
            json_encode(array(
                'formName' => $form->getName(),
                'responderUrl' => OW::getRouter()->urlFor("BASE_CTRL_Join", "ajaxResponder"),
                'passwordMaxLength' => UTIL_Validator::PASSWORD_MAX_LENGTH,
                'passwordMinLength' => UTIL_Validator::PASSWORD_MIN_LENGTH)) . ",
                " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . " ); ";

        OW::getDocument()->addOnloadScript($onLoadJs);
        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "base_field_validators.js");
        
        
        
        $this->setPageHeading(OW::getLanguage()->text('femaleregonlymen', 'config_page_heading'));
        $this->assign('mandatory_description', $language->text('femaleregonlymen', 'mandatory_description'));
       
    }
    
    
    
}



