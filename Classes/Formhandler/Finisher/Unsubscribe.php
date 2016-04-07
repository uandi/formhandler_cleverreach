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
class Unsubscribe extends CleverReach {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		$this->removeReceiver();

		return $this->gp;
	}

	/**
	 *
	 * @return void
	 */
	protected function removeReceiver() {

		$soap = new \SoapClient($this->settings['wsdlUrl']);
		$userdata = $this->parseFields('fields.');
		
		
		if ($this->settings['directUnsubscription'] == "1") {
			
			if ($this->settings['unsubscribemethod'] == "delete") {
				$return = $soap->receiverDelete($this->settings['apiKey'],$this->settings['listId'],$userdata['email']);
			} else {
				$return = $soap->receiverSetInactive($this->settings['apiKey'],$this->settings['listId'],$userdata['email']);
			}
			
			if ($return->status == Tx_Formhandler_Finisher_CleverReach::STATUS_SUCCESS) {
				$this->utilityFuncs->debugMessage("User removed successfully");
			} else {
				$this->utilityFuncs->debugMessage("Error at removing \"".$userdata['email']."\": ". $return->message);
			}
			
		} else {
			
			$return = $soap->formsUnsubscribeMail($this->settings['apiKey'],$this->settings['formId'],$userdata['email']);
			
			if ($return->status == Tx_Formhandler_Finisher_CleverReach::STATUS_SUCCESS) {
				$this->utilityFuncs->debugMessage("Unsubscribe mail sent");
			} else {
				$this->utilityFuncs->debugMessage("Unsubscription error for \"".$userdata['email']."\": ". $return->message);
			}
			
		}


	}



}
