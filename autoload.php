<?php


/**
 * OCDLA autoloader.
 *
 * Load classes in the OCDLA namespace.
 */
$ocdlaAutoloader=createAutoloader(array(
		'mediawiki/lib',
		'pdo/lib',
		'saml/lib',
		'session/lib',
		'sso/lib',
		'http/lib',
		'user/lib',
		'auth/lib',
		'member/lib'
	),DRUPAL_ROOT .'/core/vendor/ocdla');
	
	
spl_autoload_register($ocdlaAutoloader,true,false);