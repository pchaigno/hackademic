<?php
/**
 *
 * Hackademic-CMS/controller/class.CongratulationPageController.php
 *
 * Hackademic Congratulation Page Controller
 * Class for the Congratulation page in Frontend
 *
 * Copyright (c) 2012 OWASP
 *
 * LICENSE:
 *
 * This file is part of Hackademic CMS (https://www.owasp.org/index.php/OWASP_Hackademic_Challenges_Project).
 *
 * Hackademic CMS is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * Hackademic CMS is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with Hackademic CMS.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * @author Paul Chaignon <paul.chaignon@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 OWASP
 *
 */
require_once(HACKADEMIC_PATH."model/common/class.Challenge.php");
require_once(HACKADEMIC_PATH."controller/class.HackademicController.php");
require_once(HACKADEMIC_PATH."model/common/class.ChallengeAttempts.php");
require_once(HACKADEMIC_PATH."model/common/class.Session.php");

class CongratulationPageController extends HackademicController {

	private static $action_type = 'congratulation_page';

	public function go() {
		if (isset($_GET['id'])) {
			$this->setViewTemplate('congratulation.tpl');
			$challenge_id = $_GET['id'];
			$user_id = Session::getLoggedInUserId();
			if (ChallengeAttempts::isChallengeCleared($user_id, $challenge_id)) {
				$challenge = Challenge::getChallenge($challenge_id);
				$this->addToView('challenge', $challenge);
			} else {
				$this->addErrorMessage("You must have completed the challenge to see this page");
			}
			$this->generateView(self::$action_type);
		}
	}
}
