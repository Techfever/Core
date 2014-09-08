<?php

namespace Techfever\User;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Techfever\User\Permission as UserPermission;

/**
 * User Access
 */
class PermissionServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$UserAccess = $serviceLocator->get ( 'UserAccess' );
		$user_id = $UserAccess->getID ();
		$user_id = ($user_id === False ? 0 : $user_id);
		$user_rank_id = $UserAccess->getRankID ();
		$user_rank_id = ($user_rank_id === False ? 1 : $user_rank_id);
		$is_login = $UserAccess->isLogin ();
		$is_permission_as = $UserAccess->isPermissionAs ();
		
		$UserPermission = new UserPermission ( array (
				'servicelocator' => $serviceLocator,
				'user_id' => $user_id,
				'user_rank_id' => $user_rank_id,
				'permission_as' => $is_permission_as 
		) );
		return $UserPermission;
	}
}
