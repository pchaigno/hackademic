<?php
/**
 *
 * Hackademic-CMS/model/common/class.ChallengeScoring.php
 *
 * Hackademic ScoringRulesBackend Class
 * Class for Hackademic's ScoringRule Object
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
 * @author Spyros Gasteratos
 * @author Konstantinos Papapanagiotou <conpap[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 OWASP
 *
 */

class ScoringRuleBackend extends ScoringRule{
/*TODO na pros8esw ola ta parakatw san arguments
 * 	public $id;
	public $challenge_id;
	public $class_id;
	public $attempt_cap;
	public $attempt_cap_penalty;
	public $time_between_first_and_last_attempt;
	public $time_penalty;
	public $time_reset_limit_seconds;
	public $request_frequency;
	public $request_frequency_penalty;
	public $experimentation_bonus;
	public $multiple_solution_bonus;
	public $banned_user_agents;
	public $base_score;
	public $banned_user_agents_penalty;
	public $first_try_solves;
	public $penalty_for_many_first_try_solves;
*/
	/**
	 * Adds a new scoring rule
	 */
	public static function add_scoring_rule(	$challenge_id,
																						$class_id,
																						$attempt_cap,
																						$attempt_cap_penalty,
																						$time_between_first_and_last_attempt,
																						$time_penalty,
																						$time_reset_limit_seconds,
																						$request_frequency,
																						$request_frequency_penalty,
																						$experimentation_bonus,
																						$multiple_solution_bonus,
																						$banned_user_agents,
																						$base_score,
																						$banned_user_agents_penalty,
																						$first_try_solves,
																						$penalty_for_many_first_try_solves){


	}
	/**
	 * Updates an existing rule
	 */
	public static function update_scoring_rule($id, $challenge_id, $class_id,
											   $attempt_cap, $attempt_cap_penalty,
											   $time_between_first_and_last_attempt,
											   $time_penalty, $time_reset_limit_seconds,
											   $request_frequency,	$request_frequency_penalty,
											   $experimentation_bonus,	$multiple_solution_bonus,
											   $banned_user_agents, $base_score,$banned_user_agents_penalty,
												$first_try_solves, $penalty_for_many_first_try_solves){


	}
	/**
	 * Deletes the default scoring rule
	 */
	public static function delete_scoring_rule($id){

	}
}
 ?>
