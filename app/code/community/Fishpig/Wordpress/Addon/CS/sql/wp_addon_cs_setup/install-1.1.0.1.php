<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 */

	$this->startSetup();
	
	$files = array(
		'/app/etc/modules/Fishpig_WpCustomerSynch.xml',
	);
	
	foreach($files as $file) {
		$file = Mage::getBaseDir() . $file;

		if (is_file($file)) {
			@unlink($file);
		}	
	}
	
	$this->endSetup();
	