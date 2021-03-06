<?php
namespace Menu\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General
{

    public function InitialAction()
    {
        $this->setControllerName(__NAMESPACE__);
        $status = false;
        $content = "";
        $id = "";
        $user_id = $this->getUserID();
        if ($this->getUserManagement()->verifyID($user_id)) {
            $title = 'text_widget_menu';
            $id = 'widget_menu';
            $status = true;
        }
        
        $this->setContent(array(
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'success' => $status
        ));
        $this->setSuccess(True);
        
        return $this->getWidgetModel($this->getOptions());
    }
}
