<?php

namespace Techfever\Wallet\Form;

use Techfever\Form\Form as BaseForm;
use Techfever\Wallet\Wallet;
use Techfever\User\Management as UserManagement;

class Defined extends BaseForm {
	public function getVariables() {
		$request = $this->getRequest ();
		
		$wallet_type_from = null;
		$wallet_type_to = null;
		if ($this->hasOption ( 'wallet' )) {
			$options = $this->getOption ( 'wallet' );
			$useroptions = array (
					'servicelocator' => $this->getServiceLocator () 
			);
			$userManagement = new UserManagement ( $useroptions );
			
			$to_username = $request->getPost ( 'user_wallet_username_to' );
			$to_user = $userManagement->getID ( $to_username );
			$to_user_rank = $userManagement->getRankID ( $to_user );
			$from_wallet_type = $request->getPost ( 'user_wallet_type_from' );
			$to_wallet_type = $request->getPost ( 'user_wallet_type_to' );
			
			$walletoption = array (
					'action' => '',
					'from_user' => 0,
					'to_user' => $to_user,
					'from_wallet_type' => $from_wallet_type,
					'to_wallet_type' => $to_wallet_type,
					'from_user_rank' => 0,
					'to_user_rank' => $to_user_rank 
			);
			if (is_array ( $options ) && count ( $options ) > 0) {
				$rawoptions = array ();
				foreach ( $options as $option_key => $option_value ) {
					if (preg_match ( '/val\{(.*)\}$/', $option_value )) {
						$variable = $option_value;
						$variable = str_replace ( 'val{', '', $variable );
						$variable = str_replace ( '}', '', $variable );
						$option_value = $walletoption [$variable];
					}
					$rawoptions [$option_key] = $option_value;
				}
				$walletoption = array_merge ( $walletoption, $rawoptions );
			}
			$walletoption ['servicelocator'] = $this->getServiceLocator ();
			$Wallet = new Wallet ( $walletoption );
			$wallet_type_from = $Wallet->TypeFromToForm ();
			$wallet_type_to = $Wallet->TypeToToForm ();
		}
		return array (
				'wallet_type_from' => $wallet_type_from,
				'wallet_type_to' => $wallet_type_to 
		);
	}
}
