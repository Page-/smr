<?php

$account_id = $var['account_id'];
$curr_account = SmrAccount::getAccount($account_id);

// request
$donation = $_REQUEST['donation'];
$smr_credit = $_REQUEST['smr_credit'];
$choise = $_REQUEST['choise'];
$reason_pre_select = $_REQUEST['reason_pre_select'];
$reason_msg = $_REQUEST['reason_msg'];
$veteran_status = $_REQUEST['veteran_status']=='TRUE';
$logging_status = $_REQUEST['logging_status']=='TRUE';
$except = $_REQUEST['exception_add'];
$names = $_REQUEST['player_name'];
$points = intval($_REQUEST['points']);
$delete = $_REQUEST['delete'];

$actions = [];

if (!empty($donation)) {
	// add entry to account donated table
	$db->query('INSERT INTO account_donated (account_id, time, amount) VALUES ('.$db->escapeNumber($account_id).', ' . $db->escapeNumber(TIME) . ' , '.$db->escapeNumber($donation).')');

	// add the credits to the players account - if requested
	if (!empty($smr_credit)) {
		$curr_account->increaseSmrCredits($donation * CREDITS_PER_DOLLAR);
	}

	$actions[] = 'added $'.$donation;
}

if(!empty($_REQUEST['grant_credits'])&&is_numeric($_REQUEST['grant_credits'])) {
	$curr_account->increaseSmrRewardCredits($_REQUEST['grant_credits']);
	$actions[] = 'added ' . $_REQUEST['grant_credits'] . ' reward credits';
}

if (!empty($_POST['special_close'])) {
	$specialClose = $_POST['special_close'];
	// Make sure the special closing reason exists
	$db->query('SELECT reason_id FROM closing_reason WHERE reason='.$db->escapeString($specialClose));
	if ($db->nextRecord()) {
		$reasonID = $db->getInt('reason_id');
	} else {
		$db->query('INSERT INTO closing_reason (reason) VALUES(' . $db->escapeString($specialClose).')');
		$reasonID = $db->getInsertID();
	}

	$closeByRequestNote = $_REQUEST['close_by_request_note'];
	if (empty($closeByRequestNote)) {
		$closeByRequestNote = $specialClose;
	}

	$curr_account->banAccount(0, $account, $reasonID, $closeByRequestNote);
	$actions[] = 'added ' . $specialClose . ' ban';
}

if ($choise == 'reopen') {
	//do we have points
	$curr_account->removePoints($points);
	$curr_account->unbanAccount($account);
	$actions[] = 'reopened account and removed '.$points.' points';
}
else if ($points > 0) {
	if ($choise == 'individual') {
		$db->query('INSERT INTO closing_reason (reason) VALUES(' . $db->escape_string($reason_msg) . ')');
		$reason_id = $db->getInsertID();
	}
	else {
		$reason_id = $reason_pre_select;
	}

	$bannedDays = $curr_account->addPoints($points,$account,$reason_id,$_REQUEST['suspicion']);
	$actions[] = 'added '.$points.' ban points';

	if ($bannedDays !== false) {
		if ($bannedDays > 0) {
			$expire_msg = 'for '.$bannedDays.' days';
		} else {
			$expire_msg = 'indefinitely';
		}
		$actions[] = 'closed ' . $expire_msg;
	}
}

if (!empty($_POST['mailban'])) {
	$mailban = $_POST['mailban'];
	if ($mailban == 'remove') {
		$curr_account->setMailBanned(TIME);
		$actions[] = 'removed mailban';
	} elseif ($mailban == 'add_days') {
		$days = $_POST['mailban_days'];
		$curr_account->increaseMailBanned($days*86400);
		$actions[] = 'mail banned for '.$days.' days';
	}
}

if ($veteran_status != $curr_account->isVeteranForced()) {
	$db->query('UPDATE account SET veteran = '.$db->escapeString($veteran_status).' WHERE account_id = '.$db->escapeNumber($account_id));
	$actions[] = 'set the veteran status to '.$db->escapeString($veteran_status);
}

if ($logging_status != $curr_account->isLoggingEnabled()) {
	$curr_account->setLoggingEnabled($logging_status);
	$actions[] = 'set the logging status to '.$logging_status;
}

if ($except != 'Add An Exception' && $except != '') {
	$db->query('INSERT INTO account_exceptions (account_id, reason) VALUES ('.$db->escapeNumber($account_id).', '.$db->escapeString($except).')');
	$actions[] = 'added the exception '.$except;
}

if (!empty($names))
	foreach ($names as $game_id => $new_name) {
		if(!empty($new_name)) {
			$db->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($game_id).' AND player_name = ' . $db->escape_string($new_name, FALSE));
			if (!$db->nextRecord()) {
				$db->query('SELECT player_name, player_id FROM player WHERE game_id='.$db->escapeNumber($game_id).' AND account_id = '.$db->escapeNumber($account_id).' LIMIT 1');
				$db->nextRecord();
				$old_name = $db->getField('player_name');
				$player_id = $db->getInt('player_id');

				$db->query('UPDATE player SET player_name = ' . $db->escape_string($new_name, FALSE) . ' WHERE game_id = '.$db->escapeNumber($game_id).' AND account_id = '.$db->escapeNumber($account_id));
				$actions[] = 'changed players name to '.$new_name;

				//insert news message
				$news = '<span class="blue">ADMIN</span> Please be advised that <span class="yellow">' . $old_name . '(' . $player_id . ')</span> has had their name changed to <span class="yellow">' . $new_name . '(' . $player_id . ')</span>';

				$db->query('INSERT INTO news (time, news_message, game_id) VALUES (' . $db->escapeNumber(TIME) . ',' . $db->escape_string($news, FALSE) . ','.$db->escapeNumber($game_id).')');
			}
		}

	}

if (!empty($delete)) {
	foreach ($delete as $game_id => $value) {
		if($value == 'TRUE') {
			// Check for bank transactions into the alliance account
			$db->query('SELECT * FROM alliance_bank_transactions WHERE payee_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id) . ' LIMIT 1');
			if($db->getNumRows() != 0){
				// Can't delete
				$actions[] = 'player has made alliance transaction';
				continue;
			}
			// Check anon accounts for transactions
			$db->query('SELECT * FROM anon_bank_transactions WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id) . ' LIMIT 1');
			if($db->getNumRows() != 0){
				// Can't delete
				$actions[] = 'player has made anonymous transaction';
				continue;
			}

			$db->query('DELETE FROM alliance_thread
						WHERE sender_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM blackjack
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM bounty
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM galactic_post_applications
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM galactic_post_article
						WHERE writer_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM galactic_post_writer
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM message
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM message_notify
						WHERE (from_id=' . $db->escapeNumber($account_id) . ' OR to_id=' . $db->escapeNumber($account_id) .') AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM message
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('UPDATE planet SET owner_id=0,planet_name=\'\',password=\'\',shields=0,drones=0,credits=0,bonds=0
						WHERE owner_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_attacks_planet
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_attacks_port
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_cache
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_has_alliance_role
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_has_drinks
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_has_relation
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_has_ticker
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_has_ticket
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_has_unread_messages
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_plotted_course
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_read_thread
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_visited_port
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_visited_sector
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_votes_pact
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_votes_relation
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM ship_has_cargo
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM ship_has_hardware
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM ship_has_illusion
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM ship_has_weapon
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM ship_is_cloaked
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));

			$db->query('UPDATE active_session SET game_id=0
						WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id) .' LIMIT 1');

			$actions[] = 'deleted player from game '.$game_id;
		}
	}

}

//get his login name
$container = create_container('skeleton.php', 'account_edit.php');
$container['msg'] = 'You '.join(' and ', $actions).' for the account of '.$curr_account->getLogin().'.';

$curr_account->update();
forward($container);
