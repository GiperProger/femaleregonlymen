<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'controllers' . DS . 'join.php';

class FEMALEREGONLYMEN_CLASS_JoinForm extends JoinForm
{

    public function __construct( $controller )
    {
        
        
        $this->setId(UTIL_HtmlTag::generateAutoId('form'));
        $this->setMethod(self::METHOD_POST);
        $this->setAjaxResetOnSuccess(true);
        $this->setAjaxDataType(self::AJAX_DATA_TYPE_JSON);
        $this->bindedFunctions = array(self::BIND_SUBMIT => array(), self::BIND_SUCCESS => array());
        $this->setEmptyElementsErrorMessage(OW::getLanguage()->text('base', 'form_validate_common_error_message'));

        $formNameHidden = new HiddenField('form_name');
        $formNameHidden->setValue('joinForm');
        $this->addElement($formNameHidden);

        $this->setName('joinForm');

        $this->setId('joinForm');

        $stamp = OW::getSession()->get(self::SESSION_START_STAMP);

        if ( empty($stamp) )
        {
            OW::getSession()->set(self::SESSION_START_STAMP, time());
        }

        unset($stamp);

        $this->checkSession();

        $stepCount = 1;
        $joinSubmitLabel = "";

        // get available account types from DB
        $accounts = $this->getAccountTypes();

        $joinData = OW::getSession()->get(self::SESSION_JOIN_DATA);

        if ( !isset($joinData) || !is_array($joinData) )
        {
            $joinData = array();
        }

        $accountsKeys = array_keys($accounts);
        $this->accountType = $accountsKeys[0];

        if ( isset($joinData['accountType']) )
        {
            $this->accountType = trim($joinData['accountType']);
        }

        $step = $this->getStep();

        if ( count($accounts) > 1 )
        {
            $this->stepCount = 2;
            switch ( $step )
            {
                case 1:
                    $this->displayAccountType = true;
                    $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_continue');
                    break;

                case 2:
                    $this->isLastStep = true;
                    $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_join');
                    break;
            }
        }
        else
        {
            $this->isLastStep = true;
            $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_join');
        }

        $joinSubmit = new Submit('joinSubmit');
        $joinSubmit->addAttribute('class', 'ow_button ow_ic_submit');
        $joinSubmit->setValue($joinSubmitLabel);
        $this->addElement($joinSubmit);

        /* if ( $this->displayAccountType )
          {
          $questionValueList = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList(array('sex', 'match_sex'));

          $sex = new RadioField('sex');
          $sex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('sex'));
          $sex->setRequired();

          $this->setFieldOptions($sex, 'sex', $questionValueList['sex']);

          if ( !empty($joinData['sex']) )
          {
          $sex->setValue($joinData['sex']);
          }

          $this->addElement($sex);

          $matchSex = new RadioField('match_sex');
          $matchSex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('match_sex'));
          $matchSex->setRequired();

          $this->setFieldOptions($matchSex, 'match_sex', $questionValueList['match_sex']);

          if ( !empty($joinData['match_sex']) )
          {
          $matchSex->setValue($joinData['match_sex']);
          }

          $this->addElement($matchSex);
          } */

        $this->getQuestions();

        $section = null;
        //$this->questionListBySection = array();
        $questionNameList = array();
        $this->sortedQuestionsList = array();

        foreach ( $this->questions as $sort => $question )
        {
            if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
            {
                if ( $section !== $question['sectionName'] )
                {
                    $section = $question['sectionName'];
                }

                //$this->questionListBySection[$section][] = $this->questions[$sort];
                $questionNameList[] = $this->questions[$sort]['name'];
                $this->sortedQuestionsList[] = $this->questions[$sort];
            }
        }

        $this->questionValuesList = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addFakeQuestions();

        $this->addQuestions($this->sortedQuestionsList, $this->questionValuesList, $this->updateJoinData());

        $this->setQuestionsLabel();

        $this->addClassToBaseQuestions();

        if ( $this->isLastStep )
        {
            $this->addLastStepQuestions($controller);
        }

        $controller->assign('step', $step);
        $controller->assign('questionArray', $this->questionListBySection);
        $controller->assign('displayAccountType', $this->displayAccountType);
        $controller->assign('isLastStep', $this->isLastStep);
    }

    public function getRealValues()
    {
        $list = $this->sortedQuestionsList;

        $values = $this->getValues();
        $result = array();

        if ( !empty($list) )
        {
            foreach ( $values as $fakeName => $value )
            {
                if ( !empty($list[$fakeName]) && isset($list[$fakeName]['fake']) && $list[$fakeName]['fake'] == false )
                {
                    $result[$list[$fakeName]['realName']] = $value;
                }

                if ( $fakeName == 'accountType' )
                {
                    $result[$fakeName] = $value;
                }
            }

            if ( !empty($result['sex']) )
            {

                $gender2accountType = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

                if ( !empty($gender2accountType) )
                {
                    /* @var $dto SKADATE_BOL_AccountTypeToGender */
                    foreach ( $gender2accountType as $dto )
                    {
                        if ( $dto->genderValue == $result['sex'] )
                        {
                            $result['accountType'] = $dto->accountType;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getQuestions()
    {
        $this->questions = array();

        if ( $this->isLastStep )
        {
            $this->questions = BOL_QuestionService::getInstance()->findSignUpQuestionsForAccountType($this->accountType);

            foreach ( $this->questions as $key => $question )
            {
                if ( in_array($question['name'], array('sex', 'match_sex')) )
                {
                    unset($this->questions[$key]);
                }
            }
        }
        else
        {
            $this->questions = BOL_QuestionService::getInstance()->findBaseSignUpQuestions();

            $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList(array('sex', 'match_sex'));

            if ( !empty($questionDtoList['sex']) )
            {
                $sex = get_object_vars($questionDtoList['sex']);
                array_push($this->questions, $sex);
            }
            
            if ( !empty($questionDtoList['match_sex']) )
            {
                $matchSex = get_object_vars($questionDtoList['match_sex']);
                array_push($this->questions, $matchSex);
            }
        }
        
        $js = 'var form = $("form[name=joinForm]");
            
        var gender = $("select[name=sex]",form);
        gender.closest("tr").hide();
        $("[for=" + gender.attr("id") + "]").hide();
        $("[value = 1]", gender).attr("selected", "selected");
        
        
        $gender = $("input[value=2][name=\'match_sex[]\'] ");
                $gender.attr("checked",true);
                
        var gender = $(" input[value=1][name=\'match_sex[]\'] ");
                gender.hide();
                $("[for=" + gender.attr("id") + "]").hide();
             
                var gender = $(" input[value=2][name=\'match_sex[]\'] ");
                gender.hide();
                $("[for=" + gender.attr("id") + "]").hide();
          gender.closest("tr").hide();
            
        ';
        
        OW::getDocument()->addOnloadScript($js);
    }
}


/*var gender = $("li input[value=1][name=\'match_sex[]\'] ");
                gender.hide();
                $("[for=" + gender.attr("id") + "]").hide();
             
               
                
                var gender = $("li input[value=2][name=\'match_sex[]\'] ");
                gender.hide();
                $("[for=" + gender.attr("id") + "]").hide();
             
            

$("select[name=sex]", form).change(  
           function() {
            
             $gender = $("select[name=sex]",form).val();
             
            
             if($gender == 1)
           {
                $gender = $("input[value=2][name=\'match_sex[]\'] ");
                $gender.attr("checked",true);
                $gender = $("input[value=1][name=\'match_sex[]\'] ");
                $gender.attr("checked",false);
                

                var gender = $("li input[value=1][name=\'match_sex[]\'] ");
                gender.hide();
                $("[for=" + gender.attr("id") + "]").hide();
             
                var gender = $("li input[value=2][name=\'match_sex[]\'] ");
                gender.show();
                $("[for=" + gender.attr("id") + "]").show();

           }
           else
           {
                $gender = $("input[value=1][name=\'match_sex[]\'] ");
                $gender.attr("checked",true);
                $gender = $("input[value=2][name=\'match_sex[]\'] ");
                $gender.attr("checked",false);
                
                var gender = $("li input[value=2][name=\'match_sex[]\'] ");
                gender.hide();
                $("[for=" + gender.attr("id") + "]").hide();
             
             var gender = $("li input[value=1][name=\'match_sex[]\'] ");
                gender.show();
                $("[for=" + gender.attr("id") + "]").show();
 
           }
               
           
       } )*/