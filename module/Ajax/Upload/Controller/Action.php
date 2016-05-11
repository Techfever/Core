<?php

namespace Ajax\Upload\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;
use Techfever\Form\Form as UploadForm;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var Upload Form
	 *     
	 */
	protected $uploadform = null;
	
	/**
	 * Photo Action
	 *
	 * @return Json
	 */
	public function PhotoAction() {
		$type = 1000;
		$success = false;
		$message = "";
		$file = "";
		
		$UploadForm = $this->UploadForm ();
		if ($this->isXmlHttpRequest ()) {
			if ($UploadForm->isPost ()) {
				if ($UploadForm->isValid ()) {
					$data = $UploadForm->getData ();
					$file = $data;
					$success = true;
				} else {
					$message = $UploadForm->getMessages ();
				}
			}
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		
		$UploadForm->getResponse ()->setContent ( Json::encode ( array (
				'success' => $success,
				'message' => $message,
				'file' => $file 
		) ) );
		return $UploadForm->getResponse ();
	}
	
	/**
	 * Video Action
	 *
	 * @return Json
	 */
	public function VideoAction() {
		$type = 2000;
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$valid = 0;
		if ($request->isXmlHttpRequest ()) {
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid 
		) ) );
		return $response;
	}
	
	/**
	 * Voice Action
	 *
	 * @return Json
	 */
	public function VoiceAction() {
		$type = 3000;
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$valid = 0;
		if ($request->isXmlHttpRequest ()) {
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid 
		) ) );
		return $response;
	}
	
	/**
	 * File Action
	 *
	 * @return Json
	 */
	public function FileAction() {
		$type = 4000;
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = 0;
		$valid = 0;
		if ($request->isXmlHttpRequest ()) {
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid 
		) ) );
		return $response;
	}
	
	/**
	 * Progress Action
	 *
	 * @return Json
	 */
	public function ProgressAction() {
		$id = $this->params ()->fromQuery ( 'query', null );
		$progress = new \Zend\ProgressBar\Upload\SessionProgress ();
		return new \Zend\View\Model\JsonModel ( $progress->getProgress ( $id ) );
	}
	
	/**
	 * Upload Form
	 *
	 * @return Form
	 */
	public function UploadForm() {
		if (! is_object ( $this->uploadform ) && empty ( $this->uploadform )) {
			$datetime = new \DateTime ();
			$timestamp = $datetime->getTimestamp ();
			$user_id = $this->getUserID ();
			$session_id = $this->getSession ()->getID ();
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'user' => $user_id,
					'variable' => array (
							'userid' => $user_id,
							'sessionid' => $session_id,
							'timestamp' => $timestamp,
							'path_to_photo_upload' => realpath ( 'data/document/photo/' ),
							'path_to_video_upload' => realpath ( 'data/document/video/' ),
							'path_to_voice_upload' => realpath ( 'data/document/voice/' ),
							'path_to_file_upload' => realpath ( 'data/document/file/' ) 
					) 
			);
			$this->uploadform = new UploadForm ( $options );
		}
		return $this->uploadform;
	}
}
