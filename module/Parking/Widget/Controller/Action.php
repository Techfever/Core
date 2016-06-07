<?php

namespace Parking\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	public function InitialAction() {
        $this->setControllerName(__NAMESPACE__);
        $status = false;
        $content = "";
        $id = "";
        $user_id = $this->getUserID();
        if ($this->getUserManagement()->verifyID($user_id)) {
            $SystemService = $this->getSystemService();
            $group = $SystemService->getGroupIDbyKey("service_parking");
            if ($SystemService->isGroupActivated($group)) {
                $SystemService->setOption("group", $group);
                $content = $SystemService->getGroupData();
                $content[$group]['services'] = $SystemService->getServiceData();
                $content = $content[$group];
                
                $title = $content['title'];
                $id = 'widget_' . $content['key'];
                $status = true;
            }
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
