<?php
require_once('SmrSessionMySqlDatabase.class.inc');
if(!defined('USING_AJAX'))
	define('USING_AJAX',false);
class SmrSession {
	const ALWAYS_AVAILABLE = 999999;
	const TIME_BEFORE_EXPIRY = 3600;
	private static $URL_DEFAULT_REMAINING_PAGE_LOADS = array(
			'alliance_broadcast.php' => self::ALWAYS_AVAILABLE,
			'alliance_forces.php' => self::ALWAYS_AVAILABLE,
			'alliance_list.php' => self::ALWAYS_AVAILABLE,
			'alliance_message_view.php' => self::ALWAYS_AVAILABLE,
			'alliance_message.php' => self::ALWAYS_AVAILABLE,
			'alliance_mod.php' => self::ALWAYS_AVAILABLE,
			'alliance_option.php' => self::ALWAYS_AVAILABLE,
			'alliance_pick.php' => self::ALWAYS_AVAILABLE,
			'alliance_planets.php' => self::ALWAYS_AVAILABLE,
			'alliance_remove_member.php' => self::ALWAYS_AVAILABLE,
			'alliance_roster.php' => self::ALWAYS_AVAILABLE,
			'beta_functions.php' => self::ALWAYS_AVAILABLE,
			'bug_report.php' => self::ALWAYS_AVAILABLE,
			'cargo_dump.php' => self::ALWAYS_AVAILABLE,
			'council_list.php' => self::ALWAYS_AVAILABLE,
			'course_plot.php' => self::ALWAYS_AVAILABLE,
			'changelog_view.php' => self::ALWAYS_AVAILABLE,
			'chat_rules.php' => self::ALWAYS_AVAILABLE,
			'chess_play.php' => self::ALWAYS_AVAILABLE,
			'combat_log_viewer.php' => self::ALWAYS_AVAILABLE,
			'current_sector.php' => self::ALWAYS_AVAILABLE,
			'configure_hardware.php' => self::ALWAYS_AVAILABLE,
			'contact.php' => self::ALWAYS_AVAILABLE,
			'council_embassy.php' => self::ALWAYS_AVAILABLE,
			'council_list.php' => self::ALWAYS_AVAILABLE,
			'council_politics.php' => self::ALWAYS_AVAILABLE,
			'council_send_message.php' => self::ALWAYS_AVAILABLE,
			'council_vote.php' => self::ALWAYS_AVAILABLE,
			'current_players.php' => self::ALWAYS_AVAILABLE,
			'donation.php' => self::ALWAYS_AVAILABLE,
			'feature_request_comments.php' => self::ALWAYS_AVAILABLE,
			'feature_request.php' => self::ALWAYS_AVAILABLE,
			'forces_examine.php' => self::ALWAYS_AVAILABLE,
			'forces_list.php' => self::ALWAYS_AVAILABLE,
			'forces_mass_refresh.php' => self::ALWAYS_AVAILABLE,
			'government.php' => 1,
			'hall_of_fame_player_new.php' => self::ALWAYS_AVAILABLE,
			'hall_of_fame_player_detail.php' => self::ALWAYS_AVAILABLE,
			'leave_newbie.php' => self::ALWAYS_AVAILABLE,
			'logoff.php' => self::ALWAYS_AVAILABLE,
			'map_local.php' => self::ALWAYS_AVAILABLE,
			'message_view.php' => self::ALWAYS_AVAILABLE,
			'message_send.php' => self::ALWAYS_AVAILABLE,
			'news_read_advanced.php' => self::ALWAYS_AVAILABLE,
			'news_read_current.php' => 1,
			'news_read.php' => self::ALWAYS_AVAILABLE,
			'planet_construction.php' => self::ALWAYS_AVAILABLE,
			'planet_defense.php' => self::ALWAYS_AVAILABLE,
			'planet_financial.php' => self::ALWAYS_AVAILABLE,
			'planet_main.php' => self::ALWAYS_AVAILABLE,
			'planet_ownership.php' => self::ALWAYS_AVAILABLE,
			'planet_stockpile.php' => self::ALWAYS_AVAILABLE,
			'preferences.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_experience.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_vs_alliance.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_experience.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_profit.php' => self::ALWAYS_AVAILABLE,
			'rankings_race_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_race_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_race.php' => self::ALWAYS_AVAILABLE,
			'rankings_sector_kill.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_view.php' => self::ALWAYS_AVAILABLE,
			'sector_scan.php' => self::ALWAYS_AVAILABLE,
			'trader_bounties.php' => self::ALWAYS_AVAILABLE,
			'trader_planet.php' => self::ALWAYS_AVAILABLE,
			'trader_relations.php' => self::ALWAYS_AVAILABLE,
			'trader_savings.php' => self::ALWAYS_AVAILABLE,
			'trader_search_result.php' => self::ALWAYS_AVAILABLE,
			'trader_search.php' => self::ALWAYS_AVAILABLE,
			'trader_status.php' => self::ALWAYS_AVAILABLE,
			'weapon_reorder.php' => self::ALWAYS_AVAILABLE,
			//Processing pages
			'alliance_message_add_processing.php' => self::ALWAYS_AVAILABLE,
			'alliance_message_delete_processing.php' => self::ALWAYS_AVAILABLE,
			'alliance_pick_processing.php' => self::ALWAYS_AVAILABLE,
			'chess_move_processing.php' => self::ALWAYS_AVAILABLE,
			'toggle_processing.php' => self::ALWAYS_AVAILABLE,
			//Admin pages
			'account_edit.php' => self::ALWAYS_AVAILABLE,
			'account_ip_view_result.php' => self::ALWAYS_AVAILABLE,
			'album_moderate.php' => self::ALWAYS_AVAILABLE,
			'box_view.php' => self::ALWAYS_AVAILABLE,
			'changelog.php' => self::ALWAYS_AVAILABLE,
			'comp_share.php' => self::ALWAYS_AVAILABLE,
			'form_open.php' => self::ALWAYS_AVAILABLE,
			'ip_view_results.php' => self::ALWAYS_AVAILABLE,
			'ip_view.php' => self::ALWAYS_AVAILABLE,
			'password_check.php' => self::ALWAYS_AVAILABLE,
			'permission_manage.php' => self::ALWAYS_AVAILABLE,
			'word_filter.php' => self::ALWAYS_AVAILABLE,
			//Uni gen
			'universe_create_locations.php' => self::ALWAYS_AVAILABLE,
			'universe_create_planets.php' => self::ALWAYS_AVAILABLE,
			'universe_create_ports.php' => self::ALWAYS_AVAILABLE,
			'universe_create_sector_details.php' => self::ALWAYS_AVAILABLE,
			'universe_create_sectors.php' => self::ALWAYS_AVAILABLE,
			'universe_create_warps.php' => self::ALWAYS_AVAILABLE
		);

	private static $URL_LOAD_DELAY = array(
		'configure_hardware.php' => .4,
		'forces_drop.php' => .4,
		'forces_drop_processing.php' => .5,
		'forces_refresh_processing.php' => .4,
		'sector_jump_processing.php' => .4,
		'sector_move_processing.php' => .4,
		'sector_scan.php' => .4,
		'shop_goods_processing.php' => .4,
		'trader_attack_processing.php' => .75,
		'trader_examine.php' => .75
	);

	protected static $db;

	public static $session_id;
	public static $account_id;
	public static $old_account_id;
	public static $game_id;
	public static $last_accessed;
	public static $fast_forward;
	public static $var;
	public static $commonIDs;
	public static $generate;
	public static $SN;
	public static $lastSN;

	protected static $previousAjaxReturns;
	protected static $ajaxReturns=array();

	public static function init() {
		self::$db = new SmrSessionMySqlDatabase();
		// now try the cookie
		if (isset($_COOKIE['session_id'])) {
			self::$session_id = $_COOKIE['session_id'];
		}
		else {
			// create a new session id
			do {
				self::$session_id = md5 (uniqid (mt_rand()) );
				self::$db->query('SELECT 1 FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
			} while(self::$db->nextRecord()); //Make sure we haven't somehow clashed with someone else's session.
			setcookie('session_id', self::$session_id);
		}

		// try to get current session
		self::$db->query('DELETE FROM active_session WHERE last_accessed < ' . self::$db->escapeNumber(time() - self::TIME_BEFORE_EXPIRY));
		self::fetchVarInfo();

		if(!USING_AJAX && isset($_REQUEST['sn']) && isset(self::$var[$_REQUEST['sn']]) && !empty(self::$var[$_REQUEST['sn']])) {
			$var = self::$var[$_REQUEST['sn']];
			$currentPage = $var['url'] == 'skeleton.php' ? $var['body'] : $var['url'];
			$loadDelay = isset(self::$URL_LOAD_DELAY[$currentPage]) ? self::$URL_LOAD_DELAY[$currentPage] : 0;
			$initialTimeBetweenLoads = microtimeDiff(microtime(), $var['PreviousRequestTime']);
			while(($timeBetweenLoads = microtimeDiff(microtime(), $var['PreviousRequestTime'])) < $loadDelay) {
				$sleepTime = round(($loadDelay-$timeBetweenLoads)*1000000);
			//	echo 'Sleeping for: ' . $sleepTime . 'us';
				usleep($sleepTime);
			}
			self::$db->query('INSERT INTO debug VALUES (' . self::$db->escapeString('Delay: ' . $currentPage) . ',' . self::$db->escapeNumber(self::$account_id) . ',' . self::$db->escapeNumber($initialTimeBetweenLoads) . ',' . self::$db->escapeNumber($timeBetweenLoads) . ')');
		}

		define('MICRO_TIME', microtime());
		define('TIME', (int)microtimeSec(MICRO_TIME));

		if(class_exists('SmrMySqlDatabase',false))
			$db = new SmrMySqlDatabase();
	}

	public static function fetchVarInfo() {
		self::$db->query('SELECT * FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		if (self::$db->nextRecord()) {
			self::$generate = false;
			self::$session_id		= self::$db->getField('session_id');
			self::$account_id		= self::$db->getInt('account_id');
			self::$old_account_id	= self::$db->getInt('old_account_id');
			self::$game_id			= self::$db->getInt('game_id');
			self::$last_accessed	= self::$db->getInt('last_accessed');
			self::$var = @unserialize(@gzuncompress(self::$db->getField('session_var')));
			self::$commonIDs	= array();
			self::$lastSN = self::$db->getField('last_sn');
			self::$previousAjaxReturns = @unserialize(@gzuncompress(self::$db->getField('ajax_returns')));
			if(!is_array(self::$var)) {
				self::$account_id	= 0;
				self::$old_account_id	= 0;
				self::$game_id		= 0;
				self::$var			= array();
			}
			else {
				foreach(self::$var as $key => &$value) {
					if ($value['Expires'] > 0 && $value['Expires'] <= TIME) { // Use 0 for infinity
						//This link is no longer valid
						unset(self::$var[$key]);
					}
					else if ($value['RemainingPageLoads'] < 0) {
						//This link is no longer valid
						unset(self::$var[$key]);
					}
					else {
						--$value['RemainingPageLoads'];
						if(isset($value['CommonID'])) {
							self::$commonIDs[$value['CommonID']] = $key;
						}
					}
				} unset($value);
			}
		}
		else {
			self::$generate = true;
			self::$account_id	= 0;
			self::$old_account_id	= 0;
			self::$game_id		= 0;
			self::$var			= array();
			self::$game_id		= 0;
			self::$commonIDs	= array();
		}
	}

	public static function update() {
		self::$db = new SmrSessionMySqlDatabase();
		foreach(self::$var as $key => &$value) {
			if ($value['RemainingPageLoads'] <= 0) {
				//This link was valid this load but will not be in the future, removing it now saves database space and data transfer.
				unset(self::$var[$key]);
			}
		} unset($value);
		$compressed = gzcompress(serialize(self::$var));
		if(!self::$generate) {
			self::$db->query('UPDATE active_session SET account_id=' . self::$db->escapeNumber(self::$account_id) . ',old_account_id=' . self::$db->escapeNumber(is_numeric(self::$old_account_id)?self::$old_account_id:0) . ',game_id=' . self::$db->escapeNumber(self::$game_id) . (!USING_AJAX ? ',last_accessed=' . self::$db->escapeNumber(TIME) : '') . ',session_var=' . self::$db->escapeBinary($compressed) .
					',last_sn='.self::$db->escapeString(self::$SN).
					' WHERE session_id=' . self::$db->escapeString(self::$session_id) . (USING_AJAX ? ' AND last_sn='.self::$db->escapeString(self::$lastSN) : '') . ' LIMIT 1');
		}
		else {
			self::$db->query('DELETE FROM active_session WHERE account_id = ' . self::$db->escapeNumber(self::$account_id) . ' LIMIT 1');
			self::$db->query('INSERT INTO active_session (session_id, account_id, old_account_id, game_id, last_accessed, session_var) VALUES(' . self::$db->escapeString(self::$session_id) . ',' . self::$db->escapeNumber(self::$account_id) . ',' . self::$db->escapeNumber(is_numeric(self::$old_account_id)?self::$old_account_id:0) . ',' . self::$db->escapeNumber(self::$game_id) . ',' . self::$db->escapeNumber(TIME) . ',' . self::$db->escapeBinary($compressed) . ')');
			self::$generate = false;
		}
		// This takes us back to the standard database if we had to change it.
		$db = new SmrMySqlDatabase();
	}

	public static function updateSN() {
		self::$db = new SmrSessionMySqlDatabase();
		if(!USING_AJAX)
			self::$db->query('UPDATE active_session SET last_sn='.self::$db->escapeString(self::$SN).
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		new SmrMySqlDatabase();
	}

	public static function destroy() {
		self::$db = new SmrSessionMySqlDatabase();
		self::$db->query('UPDATE active_session SET account_id=0,old_account_id=0,game_id=0,session_var=\'\',ajax_returns=\'\' WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		self::$session_id = '';
		self::$account_id = 0;
		self::$old_account_id = 0;
		self::$game_id = 0;
		$db = new SmrMySqlDatabase();
	}

	public static function retrieveVar($sn) {
		if(!isset(self::$var[$sn]) || empty(self::$var[$sn]))
			return false;
		self::$SN = $sn;
		SmrSession::updateSN();
		if(isset(self::$var[$sn]['body']) && isset(self::$var[$sn]['CommonID'])) {
//			if(preg_match('/processing/',self::$var[$sn]['body']))
			unset(self::$commonIDs[self::$var[$sn]['CommonID']]); //Do not store common id for current page
			unset(self::$var[$sn]['CommonID']);
		}

		self::$var[$sn]['RemainingPageLoads'] += 1; // Allow refreshing
		self::$var[$sn]['Expires'] = 0; // Allow refreshing forever
		return self::$var[$sn];
	}

	public static function resetLink($container, $sn) { //Do not allow sharing SN, useful for forwarding.
		global $lock;
		if(isset(self::$var[$sn]['CommonID'])) {
			unset(self::$commonIDs[self::$var[$sn]['CommonID']]); //Do not store common id for reset page, to allow refreshing to always give the same page in response
		}
		self::$SN = $sn;
		if(!isset($container['Expires'])) {
			$container['Expires'] = 0; // Lasts forever
		}
		if(!isset($container['RemainingPageLoads'])) {
			$container['RemainingPageLoads'] = 1; // Allow refreshing
		}
		if(!isset($container['PreviousRequestTime'])) {
			$container['PreviousRequestTime'] = self::$var[$sn]['PreviousRequestTime']; // Copy across the previous request time if not explicitly set.
		}

		self::$var[$sn] = $container;
		if(!$lock && !USING_AJAX)
			self::update();
		return $sn;
	}

	public static function updateVar($key,$value) {
		global $var;
		if($value===null) {
			unset($var[$key]);
			unset(self::$var[self::$SN][$key]);
		}
		else {
			$var[$key] = $value;
			self::$var[self::$SN][$key] = $value;
		}
	}

	public static function clearLinks() {
		self::$var = array(self::$SN => self::$var[self::$SN]);
		self::$commonIDs = array();
	}

	public static function addLink($container, $sn = false) { // Container['ID'] MUST be unique to a specific action, if there will be two different outcomes from containers given the same ID then problems will likely arise.
		if(!isset($container['Expires'])) {
			$container['Expires'] = 0; // Lasts forever
		}
		if(!isset($container['RemainingPageLoads'])) {
			$pageURL = $container['url'] == 'skeleton.php' ? $container['body'] : $container['url'];
			$container['RemainingPageLoads'] = isset(self::$URL_DEFAULT_REMAINING_PAGE_LOADS[$pageURL]) ? self::$URL_DEFAULT_REMAINING_PAGE_LOADS[$pageURL] : 1; // Allow refreshing
		}

		if($sn === false) {
			$sn = self::generateSN($container);
		}
		else {
			// If we've been provided an SN to use then copy over the existing 'PreviousRequestTime'
			$container['PreviousRequestTime'] = self::$var[$sn]['PreviousRequestTime'];
		}
		self::$var[$sn] = $container;
		return $sn;
	}

	protected static function generateSN(&$container) {
		$container['CommonID'] = self::getCommonID($container);
		if(isset(self::$commonIDs[$container['CommonID']])) {
			$sn = self::$commonIDs[$container['CommonID']];
			$container['PreviousRequestTime'] = isset(self::$var[$sn]) ? self::$var[$sn]['PreviousRequestTime'] : MICRO_TIME;
		}
		else {
			do {
				$sn = substr(md5(mt_rand()),0,8);
			} while(isset(self::$var[$sn]));
			$container['PreviousRequestTime'] = MICRO_TIME;
		}
		self::$commonIDs[$container['CommonID']] = $sn;
		return $sn;
	}

	protected static function getCommonID($commonContainer) {
		unset($commonContainer['Expires']);
		unset($commonContainer['RemainingPageLoads']);
		unset($commonContainer['PreviousRequestTime']);
		unset($commonContainer['CommonID']);
		return md5(serialize($commonContainer));
	}

	public static function getNewHREF($container, $forceFullURL=false) {
		$sn = self::addLink($container) . (isset($container['AJAX']) && $container['AJAX'] == true ? '&ajax=1':'');
		if($forceFullURL===true||stripos($_SERVER['REQUEST_URI'],'loader.php')===false)
			return URL.'/loader.php?sn=' . $sn;
		else
			return '?sn=' . $sn;
	}

	public static function addAjaxReturns($element,$contents) {
		self::$ajaxReturns[$element] = $contents;
		return isset(self::$previousAjaxReturns[$element])&&self::$previousAjaxReturns[$element]==$contents;
	}

	public static function saveAjaxReturns() {
		if(empty(self::$ajaxReturns))
			return;
		$compressed = gzcompress(serialize(self::$ajaxReturns));
		self::$db->query('UPDATE active_session SET ajax_returns=' . self::$db->escapeBinary($compressed) .
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
	}
}

SmrSession::init();
?>