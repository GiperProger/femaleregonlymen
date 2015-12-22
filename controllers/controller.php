<?php

class FEMALEREGONLYMEN_CTRL_Controller extends OW_ActionController
{
    public function newRedirect()
    {         
        $language = OW::getLanguage();
        
        $this->assign('upgradeToView', $language->text( "femaleregonlymen", "access_denided_text" )); 
    }
    
    public function toSignin()
    {
        throw new AuthenticateException();
    }
    
    
    
    
}

