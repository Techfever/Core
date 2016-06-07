<?php
namespace Account\Dashboard\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General
{

    public function InitialAction()
    {
        $this->setControllerName(__NAMESPACE__);
        $status = false;
        $content = "";
        $title = "";
        $id = "widget_quick_link";
        $user_id = $this->getUserID();
        if ($this->getUserManagement()->verifyID($user_id)) {
            $content = array();
            $content['services'] = array();
            $content['services'][] = array(
                'key' => "news_feed",
                'title' => 'text_quick_link_news_feed'
            );
            $content['services'][] = array(
                'key' => "friend_n_family",
                'title' => 'text_quick_link_friend_n_family'
            );
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
