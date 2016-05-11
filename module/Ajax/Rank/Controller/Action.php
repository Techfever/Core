<?php

namespace Ajax\Rank\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;

class ActionController extends AbstractActionController {
	public function getRankPriceAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = false;
		$valid = false;
		$price_dl = 0;
		$price_pv = 0;
		$rank = $request->getPost ( 'user_rank' );
		if ($request->isXmlHttpRequest ()) {
			$success = true;
			$UserRank = $this->getUserRank ();
			if (isset ( $rank ) && $rank > 0 && $UserRank->verifyRank ( $rank )) {
				$valid = true;
				$price_dl = SYSTEM_DEFAULT_CURRENCY . " " . number_format ( $UserRank->getRankPriceDL ( $rank ), 2, '.', ',' );
				$price_pv = SYSTEM_DEFAULT_CURRENCY_POINT . " " . number_format ( $UserRank->getRankPricePV ( $rank ), 2, '.', ',' );
			}
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid,
				'price_dl' => $price_dl,
				'price_pv' => $price_pv 
		) ) );
		return $response;
	}
	public function getRankAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = false;
		$valid = false;
		$name = 0;
		$rank = $request->getPost ( 'rank' );
		if ($request->isXmlHttpRequest ()) {
			$success = true;
			$UserRank = $this->getUserRank ();
			if (isset ( $rank ) && $rank > 0 && $UserRank->verifyRank ( $rank )) {
				$valid = true;
				$name = $UserRank->getMessage ( $rank );
			}
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid,
				'name' => $name 
		) ) );
		return $response;
	}
	public function addPermissionRankAction() {
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$valid = false;
		$height = "250";
		$width = "300";
		$title = $this->getTranslate ( "text_dialog_add_permission_rank_title" );
		$content = "";
		$callback = "";
		$js = "";
		$rankPost = $request->getPost ( 'content_label_permission_rank' );
		if ($request->isXmlHttpRequest ()) {
			$rankData = null;
			$UserRank = $this->getUserRank ();
			$rank_all = $UserRank->getRankAll ();
			if (is_array ( $rank_all ) && count ( $rank_all ) > 0) {
				$valid = true;
				foreach ( $rank_all as $rank_key => $rank_value ) {
					$text = $UserRank->getMessage ( $rank_key );
					$key = $this->Encrypt ( $rank_value ['id'] );
					$status = true;
					if ($rank_value ['id'] != "1") {
						if (is_array ( $rankPost ) && in_array ( $key, $rankPost )) {
							$status = false;
						}
						if ($status) {
							$rawdata = $rank_value;
							
							$rawdata ['modify_id'] = $key;
							$rawdata ['text'] = $text;
							$rankData [$rank_key] = $rawdata;
						}
					}
				}
				$ViewModel = new ViewModel ();
				$ViewModel->setTerminal ( true );
				$ViewModel->setTemplate ( 'ajax/rank/controller/action/addpermissionrank' );
				$ViewModel->setVariables ( array (
						'rankData' => $rankData 
				) );
				$content = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
			}
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
}
