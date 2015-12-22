<?php
/**
 * Copyright (c) 2009, Skalfa LLC
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow_plugins.smsverification.classes
 * @since 1.7.6
 */

class FEMALEREGONLYMEN_CLASS_SettingsForm extends BASE_CLASS_UserQuestionForm
{
    public function __construct( OW_ActionController $controller )
    {
        parent::__construct('settings-form');

        $lang = OW::getLanguage();
        
        $newLogin = new CheckboxField('newLogin');
        $newLogin->setLabel($lang->text('femaleregonlymen', 'login_as_user'));
        $this->addElement($newLogin);

        
        $questionNameList = array('username',  'email', 'password', 'sex');
        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);
        $orderedQuestionList = array();
        $questionService = BOL_QuestionService::getInstance();
        
        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);
       
        foreach ( $questionNameList as $questionName )
        {
            if ( !empty($questionDtoList[$questionName]) )
            {
                $orderedQuestionList[] = get_object_vars($questionDtoList[$questionName]);
            }
        }
        
        $this->addQuestions($orderedQuestionList, $questionValueList);
        $controller->assign('questions', $orderedQuestionList);

        $submit = new Submit('reg_fake');
        $submit->setValue($lang->text('femaleregonlymen', 'reg_fake'));
        $this->addElement($submit);
        
    }
    
    protected function addFieldValidator( $formField, $question )
    {
        if ( (string) $question['base'] === '1' )
        {
            if ( $question['name'] === 'email' )
            {
                $formField->addValidator(new joinEmailValidator());
            }

            if ( $question['name'] === 'username' )
            {
                
                $formField->addValidator(new UserNameValidator());
            }

            if ( $question['name'] === 'password' )
            {
                $passwordRepeat = BOL_QuestionService::getInstance()->getPresentationClass($question['presentation'], 'repeatPassword');
                $passwordRepeat->setLabel(OW::getLanguage()->text('base', 'questions_question_repeat_password_label'));
                $passwordRepeat->setRequired((string) $question['required'] === '1');
                $this->addElement($passwordRepeat);

                $formField->addValidator(new PasswordValidator());
            }
        }
    }
    
    
     protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        parent::setFieldOptions($formField, $questionName, $questionValues);

        if ( $questionName == 'match_sex' )
        {
            $options = array_reverse($formField->getOptions(), true);
            $formField->setOptions($options);
        }

        $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $questionName . '_label'));
    }
    
    public function index()
    {
        
        
    }
    
    
    
}