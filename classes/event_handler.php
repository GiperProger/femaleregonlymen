<?php

class FEMALEREGONLYMEN_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FEMALEREGONLYMEN_CLASS_EventHandler
     */
    private static $classInstance;
    /**
     * @var FEMALEREGONLYMEN_BOL_Service
     */
    private $service;
    private $organiztion;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CHARITYSTAT_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        
    }
    
    public function init()
    {
        OW::getEventManager()->bind('base.questions_field_add_fake_questions', array($this, 'addFakeQuestions'));
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, "onAfterRout"));
        OW::getEventManager()->bind("class.get_instance.JoinForm", array($this, "onAfterGetSKADATE_CLASS_JoinFormInstance"), 999);
        OW::getEventManager()->bind("class.get_instance.USEARCH_CLASS_MainSearchForm", array($this, "onAfterGetUSEARCH_CTRL_SearchInstance"));
        OW::getEventManager()->bind("class.get_instance.USEARCH_CMP_QuickSearch", array($this, "onAfterGetUSEARCH_CMP_QuickSearchInstance"));
        OW::getEventManager()->bind('base.query.content_filter', array($this, 'photoGetPhotoList'));
        
    }
    
    public function onAfterGetUSEARCH_CTRL_SearchInstance(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !empty($params['className']))
        {
            $event->setData(new FEMALEREGONLYMEN_CLASS_MainSearchForm($params['arguments'][0]));
        }
    }
    
    public function addFakeQuestions( OW_Event $event )
    { 
         $params = $event->getParams();
        

        if ( !empty($params['name']) && ($params['name'] == 'sex' || $params['name'] == 'match_sex') )
        {

            $event->setData(false);
        }
        
    }
    
    public function onAfterRout(OW_Event $event)
    {
       $handlerAtr = OW::getRequestHandler()->getHandlerAttributes();
       
       if($handlerAtr['controller'] == 'BASE_CTRL_ComponentPanel' && $handlerAtr['action'] == 'profile')
       {
           $profileUserName = empty($handlerAtr['params']['username']) ? false : $handlerAtr['params']['username'];
           $profileUserId = BOL_UserService::getInstance()->findByUsername($profileUserName)->id;
           $currentId = OW::getUser()->getId();
           
           if($profileUserId == $currentId)
           {
               return;
           }
           
           $total = BOL_QuestionService::getInstance()->getQuestionData(array($profileUserId, $currentId), array('sex'));
           if($total[$profileUserId]['sex'] == $total[$currentId]['sex'])
           {
              OW::getApplication()->redirect((OW::getRouter()->urlForRoute('not-for-your-sex', array('type' => $_POST['type']))));
           } 
        }         
    }
    
    public function onAfterGetSKADATE_CLASS_JoinFormInstance(OW_Event $event)
    {
        $params = $event->getParams();
        
        if ( !empty($params['className']))
        {
            $event->setData(new FEMALEREGONLYMEN_CLASS_JoinForm($params['arguments'][0]));
        
            return $event->getData();
        }        
        
    }
    
    public function onAfterGetUSEARCH_CMP_QuickSearchInstance(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !empty($params['className']))
        {
           $event->setData(new FEMALEREGONLYMEN_CMP_QuickSearch());
        }
    }
    
    public function photoGetPhotoList( BASE_CLASS_QueryBuilderEvent $event )
    {
        if(!OW::getUser()->isAuthenticated())
        {
            return;
        }
        
        $params = $event->getParams();
        $aliases = $params['tables'];
        $currentId = OW::getUser()->getId();     
           
        $sex = BOL_QuestionService::getInstance()->getQuestionData(array($currentId), array('sex'));     
        
        $join = ' INNER JOIN `' . BOL_QuestionDataDao::getInstance()->getTableName() . '` AS `bqdt` ON(`' . $aliases['content'] . '`.`userId` = `bqdt`.`userId`
            AND `bqdt`.`questionName` = \'sex\' 
            AND (`bqdt`.`intValue` != '.$sex[$currentId]['sex'].' OR `' . $aliases['content'] . '`.`userId` = '.$currentId.' )) ';
        $params = array(
            'sex' => $sex[$currentId]['sex'],
            'currentId' => $currentId
        );

        $event->addJoin($join);
    }
    
}