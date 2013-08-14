<?php
/**
 *
 * Hackademic-CMS/controller/class.ChallengeMonitorController.php
 *
 * Hackademic User Menu Controller
 * Class for creating the frontend Main Menu
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
 * @author Pragya Gupta <pragya18nsit[at]gmail[dot]com>
 * @author Konstantinos Papapanagiotou <conpap[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 OWASP
 *
 */
require_once(HACKADEMIC_PATH."model/common/class.Challenge.php");
require_once(HACKADEMIC_PATH."model/common/class.User.php");
require_once(HACKADEMIC_PATH."model/common/class.Session.php");
require_once(HACKADEMIC_PATH."model/common/class.ChallengeAttempts.php");
require_once(HACKADEMIC_PATH."admin/model/class.ClassMemberships.php");
require_once(HACKADEMIC_PATH."admin/model/class.ClassChallenges.php");
require_once(HACKADEMIC_PATH."model/common/class.UserHasChallengeToken.php");
require_once(HACKADEMIC_PATH."controller/class.HackademicController.php");

class ChallengeMonitorController {

    public function go() {
        // Check Permissions
    }

    public function get_pkg_name(){
		$url = $_SERVER['REQUEST_URI'];
        $url_components = explode("/", $url);
        $count_url_components = count($url_components);
        for ($i=0; $url_components[$i] != "challenges"; $i++);
			$pkg_name = $url_components[$i+1];
		return $pkg_name;
	}
    public function start($userid = null, $chid = null, $token = null,
						  $status = 'CHECK'){
		if(!isset($_SESSION))
			session_start();

		if($status == CHALLENGE_INIT && !isset($_SESSION['init'])){
			$_SESSION['chid'] = $chid;
			$_SESSION['token'] = $token;
			$_SESSION['userid'] = $userid;
			$_SESSION['pkg_name'] = $this->get_pkg_name();
			$_SESSION['init'] = true;
			//var_dump($_SESSION);
			return;
		}
		$pkg_name = $this->get_pkg_name();
		//echo"<p>";var_dump($token);echo "</p>";
		//echo"<p>";var_dump($_SESSION['token']);echo "</p>";
		if(!isset($_SESSION['chid']))
			$_SESSION['chid'] = $chid;
		if(!isset($_SESSION['token']))
			$_SESSION['token'] = $token;
		if(!isset($_SESSION['userid']))
			$_SESSION['userid'] = $userid;
		if(!isset($_SESSION['pkg_name']))
			$_SESSION['pkg_name'] = $pkg_name;

		$pair = UserHasChallengeToken::findByPair($userid,$chid,$token);

		/*If token is the one in the session then challenge must be the same*/
		if($_SESSION['token'] == $token)
		if($pkg_name != $_SESSION['pkg_name']  || $_SESSION['chid'] != $chid){
			error_log("HACKADEMIC::ChallengeMonitorController::RIGHT token WRONG CHALLENGE it's ".$pkg_name.' it should be '.$_SESSION['pkg_name']);
			header("Location: ".SITE_ROOT_PATH);
		}
		/* If token changed AND the challenge changed AND its a valid token
		 * for that challenge then we are in a new challenge
		 */
		if($_SESSION['token'] != $token && $token!=null)
			if($pkg_name != $_SESSION['pkg_name']  || $_SESSION['chid'] != $chid || $_SESSION['userid'] != $userid){
				if($pair->token == $token){
					$_SESSION['chid'] = $chid;
					$_SESSION['token'] = $token;
					$_SESSION['pkg_name'] = $pkg_name;
					$_SESSION['userid'] = $userid;
				}
			}else{
				//var_dump($_SESSION);//die();
				error_log("HACKADEMIC::ChallengeMonitorController::Hijacking attempt? ".$_SESSION['pkg_name']);
				header("Location: ".SITE_ROOT_PATH);
			}

		/*echo"<p>";var_dump($pair);echo "</p>";
		echo"<p>";var_dump($token);echo "</p>";
		echo"<p>";var_dump($_SESSION['token']);echo "</p>";
		*/
		if($pair && $pair->token != $token){
			error_log("HACKADEMIC::ChallengeMonitorController::pair->token != $token".$pair->token);
			header("Location: ".SITE_ROOT_PATH);

		}
	}
    public function update($status, $userid = null , $chid = null,
						   $class_id = null, $token = null) {

		$this->start($userid,$chid,$token,$status);
		/*if status == init we only need to update the SESSION var*/
		if($status == CHALLENGE_INIT){
			calc_score(-1, $user_id, $chid, $class_id);
			return;
		}
		if ($userid == null)
			$userid = $_SESSION['userid'];
		if ($chid == null)
			$chid = $_SESSION['chid'];
		if ($token == null)
			$token = $_SESSION['token'];
		if ($class_id == null)
			$class_id = $_SESSION['class_id'];

		$this->calc_score($status, $user_id, $chid, $class_id);

        $username = $userid;
        $url = $_SERVER['REQUEST_URI'];
        $url_components = explode("/", $url);
        $count_url_components = count($url_components);
        for ($i=0; $url_components[$i] != "challenges"; $i++);
		$pkg_name = $url_components[$i+1];
        $user = User::findByUserName($username);
        $challenge = Challenge::getChallengeByPkgName($pkg_name);
        if($user)
           $user_id = $user->id;
         $challenge_id = $challenge->id;
         if (!ChallengeAttempts::isChallengeCleared($user_id, $challenge_id)) {
			ChallengeAttempts::addChallengeAttempt($user_id, $challenge_id, $status);
          }
   }
	/**
	 * Called for unsuccesful attempt, updates the current score for the user
	 * Called on success calculates the total score for the user
	 */
	public function calc_score($status = 0, $user_id, $challenge_id, $class_id){
		if (!isset($_SESSION['rules']) || !is_array($_SESSION['rules'])|| $_SESSION['rules'] == ""){
			$rule = ScoringRule::get_scoring_rule_by_challenge_class_id($challenge_id, $class_id);

			var_dump($rule);

			/* if challenge has not scoring rules load up the default ones*/
			if( $rule == false){
				$rule = ScoringRule::get_scoring_rule(1);
			}

			/* Add the rules to the session */
			$_SESSION['rules'] =  (array)$rule;

		}else{
			/* load the rules and the current score*/
			$attempt_cap = $_SESSION['rules']['attempt_cap'];
			$attempt_cap_penalty = $_SESSION['rules']['attempt_cap_penalty'];

			$t_limit = $_SESSION['rules']['time_between_first_and_last_attempt'];
			$reset_time = $_SESSION['rules']['time_reset_limit_seconds'];
			$time_penalty = $_SESSION['rules']['time_penalty'];

			$rps_limit = $_SESSION['rules']['request_frequency'];
			$rps_penalty = $_SESSION['rules']['request_frequency_penalty'];

			$exp_bonus = $_SESSION['rules']['experimentation_bonus'];
			$mult_sol_bonus = $_SESSION['rules']['multiple_solution_bonus'];

			$banned_user_agents = $_SESSION['rules']['banned_user_agents'];
			$banned_ua_penalty =  $_SESSION['rules']['banned_user_agents_penalty'];

			$base_score = $_SESSION['rules']['base_score'];

			$first_try_limit = $_SESSION['rules']['first_try_solves'];
			$fts_penalty = $_SESSION['rules']['penalty_for_many_first_try_solves'];

			$current_score = UserScore::get_scores_for_user_class_challenge($user_id, $challenge_id, $class_id);
			if( $current_score->points == 0)
				$current_score->points = $base_score;

			$_SESSION['current_score'] = (array)$current_score;
		}
		if ($status == -1){
			$_SESSION['f_atempt'] = date();
			$_SESSION['last_attempt'] = date();
			$_SESSION['total_attempt_count'] = 1

			$_SESSION['rps_attempt_count'] = 1;
			$_SESSION['rps_sec_start'] = $_SERVER["REQUEST_TIME_FLOAT"];

			$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];

			return;

		}elseif ($status == 0){
/* TODO MUST CHECK IF PENALTIES APPLY!!!!*/
			if (ChallengeAttempts::isChallengeCleared($user_id, $challenge_id, $class_id)){
				if (strpos($current_score,EXPERIMENTATION_BONUS_ID) == false){
					/* apply experimentation bonus*/
					$current_score->poins += $exp_bonus;
					$current_score->penalties_bonuses .= EXPERIMENTATION_BONUS_ID .= ",";
				}
			}

			if ($_SESSION['total_attempt_count'] > $attempt_cap){
				/* apply total attempt penalty*/
				if(strpos($current_score->penalties_bonuses,TOTAL_ATTEMPT_PENALTY_ID) == false){
					$current_score->poins -= $attempt_cap_penalty;
					$current_score->penalties_bonuses .= TOTAL_ATTEMPT_PENALTY_ID .= ",";
				}
			}
			$_SESSION['total_attempt_count']++;

			$t_since_first = strtotime(date()) - strtotime($_SESSION['f_atempt']);
			if ($t_since_first >= $reset_time)
				$t_since_first = 0;
			if ($t_since_first >= $t_limit){
				/* apply total time penalty */
				if(strpos($current_score,TIME_LIMIT_PENALTY_ID) == false){
					$current_score->poins -= $time_penalty;
					$current_score->penalties_bonuses .= TIME_LIMIT_PENALTY_ID .= ",";
				}
			}
			$diff = $_SERVER["REQUEST_TIME_FLOAT"] - $_SESSION['rps_sec_start'];
			if ($idff == 1000000){
				if ($_SESSION['rps_attempt_count'] >= $rps_limit){
					/* apply requests per second penalty*/
					if(strpos($current_score,RPS_PENALTY_ID) == false){
						$current_score->poins -= $rps_penalty;
						$current_score->penalties_bonuses .= RPS_PENALTY_ID .= ",";
					}
				}
				$_SESSION['rps_sec_start'] = $_SERVER["REQUEST_TIME_FLOAT"];
				$_SESSION['rps_attempt_count'] = 0;
			}else{
				$_SESSION['rps_attempt_count']++;
			}

			$ua_check = strpos($banned_user_agents, $_SERVER['HTTP_USER_AGENT']);
			if ($ua_check != false){
				/* apply user agent penalty*/
				if(strpos($current_score,UA_PENALTY_ID) == false){
					$current_score->poins -= $banned_ua_penalty;
					$current_score->penalties_bonuses .= UA_PENALTY_ID .= ",";
				}

			}
		}elseif ($status == 1){

			if (ChallengeAttempts::isChallengeCleared($user_id, $challenge_id, $class_id)){
				/* apply multiple solutions bonus*/
				if(strpos($current_score,MULT_SOL_BONUS_ID) == false){
					$current_score->poins += $mult_sol_bonus;
					$current_score->penalties_bonuses .= MULT_SOL_BONUS_ID .= ",";
				}
			}else{
				/* 	get the tries from the database */
				$first = ChallengeAttempts::getUserFirstChallengeAttemptO($user_id, $challenge_id, $class_id);
				$last_db = ChallengeAttempts::getUserLastChallengeAttemptO($user_id, $challenge_id, $class_id);
				$last = date();
				$total_count = ChallengeAttempts::getUserTriesForChallenge($user_id, $challenge_id, $class_id);

				$t_since_first = strtotime(date()) - strtotime($last_db);

				if ($t_since_first >= $t_limit){
					/* apply time limit penalty */
					if(strpos($current_score,TIME_LIMIT_PENALTY_ID) == false){
						$current_score->poins -= $time_penalty;
						$current_score->penalties_bonuses .= TIME_LIMIT_PENALTY_ID .= ",";
					}
				}
				if ( 1 + $total_count >= $attempt_cap){
					/* apply total attempt penalty*/
					if(strpos($current_score->penalties_bonuses,TOTAL_ATTEMPT_PENALTY_ID) == false){
						$current_score->poins -= $attempt_cap_penalty;
						$current_score->penalties_bonuses .= TOTAL_ATTEMPT_PENALTY_ID .= ",";
					}
				}
				if(ChallengeAttempts::getCountOfFirstTrySolves($user_id, $class_id) > $first_try_limit){
					/* apply cheater penalty */
					if(strpos($current_score->penalties_bonuses,FTS_PENALTY_ID) == false){
						$current_score->poins -= $fts_penalty;
						$current_score->penalties_bonuses .= FTS_PENALTY_ID .= ",";
					}
				}
			}
		}
	UserScore::update_user_score( $current_score->id, $user_id,
								  $challenge_id, $class_id,
								  $current_score->points,
								  $current_score->penalties_bonuses);
	}

}
