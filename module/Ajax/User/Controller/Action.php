<?php

namespace Ajax\User\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;
use Techfever\User\Form\Defined as UserForm;

class ActionController extends AbstractActionController {
	public function checkUsernameAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$valid = 0;
		$username = $request->getPost ( 'username' );
		if ($request->isXmlHttpRequest ()) {
			if (isset ( $username ) && strlen ( $username ) > 0) {
				$valid = 1;
				$success = 1;
			}
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid,
				'username' => $username 
		) ) );
		return $response;
	}
	public function addPermissionUserAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$valid = false;
		$height = "400";
		$width = "600";
		$title = $this->getTranslate ( "text_dialog_add_permission_user_title" );
		$content = "";
		$callback = "";
		$js = "";
		$userPost = $request->getPost ( 'content_label_permission_user' );
		if ($request->isXmlHttpRequest ()) {
			$valid = true;
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'addPermissionUserSearch' 
			);
			$search = new UserForm ( $options );
			$ViewModel = new ViewModel ();
			$ViewModel->setTerminal ( true );
			$ViewModel->setTemplate ( 'ajax/user/controller/action/addpermissionuser' );
			$ViewModel->setVariables ( array (
					'search' => $search 
			) );
			$content = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'valid' => $valid,
				'height' => $height,
				'width' => $width,
				'title' => $title,
				'content' => $content,
				'callback' => $callback,
				'js' => $js 
		) ) );
		return $response;
	}
	public function addPermissionUserSearchAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$valid = false;
		$callback = "";
		$js = "";
		$search_username = $request->getPost ( 'search_username' );
		if ($request->isXmlHttpRequest ()) {
			$userData = array ();
			
			$UserRank = $this->getUserRank ();
			$RankAll = $UserRank->getRankIDAll ();
			
			$QUser = $this->getDatabase ();
			$QUser->select ();
			$QUser->columns ( array (
					'user_access_id',
					'user_access_username',
					'user_rank_id' 
			) );
			$QUser->from ( array (
					'ua' => 'user_access' 
			) );
			$QUser->where ( array (
					'ua.user_access_delete_status = 0',
					'ua.user_rank_id in (' . implode ( ', ', $RankAll ) . ')',
					'ua.user_access_username like "' . $search_username . '%"' 
			) );
			$QUser->order ( array (
					'ua.user_rank_id',
					'ua.user_access_username' 
			) );
			$QUser->execute ();
			if ($QUser->hasResult ()) {
				$count = 1;
				$valid = true;
				while ( $QUser->valid () ) {
					$rawdata = $QUser->current ();
					
					$cryptID = $this->Encrypt ( $rawdata ['user_access_id'] );
					$rawdata ['modify_id'] = $cryptID;
					
					$rawdata ['user_rank_text'] = $rawdata ['user_rank_id'];
					$valueRank = $UserRank->getMessage ( $rawdata ['user_rank_id'] );
					if (strlen ( $valueRank ) > 0) {
						$rawdata ['user_rank_text'] = $valueRank;
					}
					$rawdata ['text'] = $valueRank . ' - ' . $rawdata ['user_access_username'];
					$QUser->next ();
					ksort ( $rawdata );
					$userData [] = $rawdata;
					$count ++;
				}
			}
			$ViewModel = new ViewModel ();
			$ViewModel->setTerminal ( true );
			$ViewModel->setTemplate ( 'ajax/user/controller/action/addpermissionusersearch' );
			$ViewModel->setVariables ( array (
					'userData' => $userData 
			) );
			$content = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'valid' => $valid,
				'content' => $content,
				'callback' => $callback,
				'js' => $js 
		) ) );
		return $response;
	}
}
