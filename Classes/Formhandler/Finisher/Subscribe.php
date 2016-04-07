<?php
namespace Typoheads\Formhandler\Finisher;
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Sven Wappler <typo3@wapplersystems.de>, WapplerSystems
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 *
 * @author	Sven Wappler <typo3@wapplersystems.de>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Subscribe extends CleverReach {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		
		$this->addReceiver();

		return $this->gp;
	}

	/**
	 *
	 * @return void
	 */
	protected function addReceiver() {

		$soap = new \SoapClient($this->settings['wsdlUrl']);
		
		$userdata = array();
		
		$userdata['source'] = $this->settings['source'];
		$userdata['registered'] = time();
		
		$attributes = array_merge($this->parseFields('fields.'),$this->parseFields('additionalfields.'));
		
		$userdata['email'] = $attributes['email'];
		
		$userdata['attributes'] = $this->convertAttributes($attributes);
		
		$this->utilityFuncs->debugMessage("Attributes: \"".print_r($userdata['attributes'],true)."\"");
		
		// überprüfen, ob schon im System ist
		$return = $soap->receiverGetByEmail($this->settings['apiKey'],$this->settings['listId'], $userdata['email'],0);
		
		$subscriber_found = !($return->statuscode == 20);

		if (!$subscriber_found) {
			$return = $soap->receiverAdd($this->settings['apiKey'],$this->settings['listId'],$userdata);
	
			if ($return->status == CleverReach::STATUS_SUCCESS) {
				$this->utilityFuncs->debugMessage("Subscriber \"".$userdata['email']."\" accepted");
			} else {
				$this->utilityFuncs->debugMessage("A problem with the new subscriber: ".(string)$return->message);
			}
		}
		
		if ($this->settings['directSubscription'] == "1") {
			// sofort aktivieren
			$return = $soap->receiverSetActive($this->settings['apiKey'],$this->settings['listId'],$userdata['email']);
			
		} else {
			
			$return = $soap->formsActivationMail($this->settings['apiKey'],$this->settings['formId'],$userdata['email']);
			
			if ($return->status == CleverReach::STATUS_SUCCESS) {
				$this->utilityFuncs->debugMessage("Activation mail sent");
			} else {
				$this->utilityFuncs->debugMessage("Activation mail error for \"".$userdata['email']."\": ". $return->message);
			}
			
			
		}


	}



}
