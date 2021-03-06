<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}
$alliance_id = $var['alliance_id'];
define('WITHDRAW',0);
define('DEPOSIT',1);

$template->assign('PageTopic','Alliance Bank Report');

require_once(get_file_loc('menu.inc'));
create_bank_menu();

//get all transactions
$db->query('SELECT * FROM alliance_bank_transactions WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
if (!$db->getNumRows()) {
	create_error('Your alliance has no recorded transactions.');
}
while ($db->nextRecord()) {
	if ($db->getField('transaction') == 'Payment') {
		if (!$db->getField('exempt')) {
			$trans[$db->getInt('payee_id')][WITHDRAW] += $db->getInt('amount');
		}
		else {
			$trans[0][WITHDRAW] += $db->getField('amount');
		}
	}
	else {
		if (!$db->getField('exempt')) {
			$trans[$db->getInt('payee_id')][DEPOSIT] += $db->getInt('amount');
		}
		else {
			$trans[0][DEPOSIT] += $db->getInt('amount');
		}
	}
}

//ordering
$playerIDs = array_keys($trans);
foreach ($trans as $accId => $transArray) {
	$totals[$accId] = $transArray[DEPOSIT] - $transArray[WITHDRAW];
}
arsort($totals, SORT_NUMERIC);
$db->query('SELECT * FROM player WHERE account_id IN (' . $db->escapeArray($playerIDs) . ') AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY player_name');
$players[0] = 'Alliance Funds';
while ($db->nextRecord()) {
	$players[$db->getField('account_id')] = $db->getField('player_name');
}
//format it this way so its easy to send to the alliance MB if requested.
$text = '<table class="nobord" cellspacing="0" align="center">';
foreach ($totals as $accId => $total) {
	$text .= '<tr><td>';
	if (empty($trans[$accId][DEPOSIT])) {
		$trans[$accId][DEPOSIT] = 0;
	}
	if (empty($trans[$accId][WITHDRAW])) {
		$trans[$accId][WITHDRAW] = 0;
	}
	$text .= '<table class="nobord" cellspacing="0">';
	$text .= '<tr><td colspan="2"><span class="yellow">' . $players[$accId] . ':</span></td></tr>';
	$text .= '<tr><td>Deposits</td><td>' . number_format($trans[$accId][DEPOSIT]) . '</td></tr>';
	$text .= '<tr><td>Withdrawals</td><td> -' . number_format($trans[$accId][WITHDRAW]) . '</td></tr>';
	$text .= '<tr><td><span class="bold">Total</td><td><span class="';
	if ($total < 0) {
		$text .= 'red bold';
	}
	else {
		$text .= 'bold';
	}
	$text .= '">' . number_format($total) . '</span></td></tr></table><br />';
	$text .= '</td></tr>';
	$balance += $total;
}
if (empty($balance)) {
	$balance = 0;
}
$text .= '</table>';
$text = '<div align="center"><br />Ending Balance: ' . number_format($balance) . '</div><br />' . $text;
$container=create_container('skeleton.php', 'bank_report.php');
$container['alliance_id'] = $alliance_id;
$container['text'] = $text;
if (isset($var['text'])) {
	$thread_id = 0;
	$db->query('SELECT thread_id FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND topic = \'Bank Statement\' LIMIT 1');
	if ($db->nextRecord()) {
		$thread_id = $db->getInt('thread_id');
	}
	if ($thread_id == 0) {
		$db->query('SELECT thread_id FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id) . ' ORDER BY thread_id DESC LIMIT 1');
		if ($db->nextRecord()) {
			$thread_id = $db->getField('thread_id') + 1;
		}
		else {
			$thread_id = 1;
		}
		$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic)
					VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($thread_id) . ', \'Bank Statement\')');
		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time)
					VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($thread_id) . ', 1, ' . $db->escapeString($text) . ', ' . $db->escapeNumber(ACCOUNT_ID_BANK_REPORTER) . ', ' . $db->escapeNumber(TIME) . ')');
	}
	else {
		$db->query('UPDATE alliance_thread SET time = ' . $db->escapeNumber(TIME) . ', text = ' . $db->escapeString($text) . ' WHERE thread_id = ' . $db->escapeNumber($thread_id) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND reply_id = 1');
		$db->query('DELETE FROM player_read_thread WHERE thread_id = ' . $db->escapeNumber($thread_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id));
	}

	$PHP_OUTPUT.=('<div align="center">A statement has been sent to the alliance.</div><br />');
}
else {
	$PHP_OUTPUT.=('<div align="center">');
	$PHP_OUTPUT.=create_button($container,'Send Report to Alliance');
	$PHP_OUTPUT.=('</div>');
}
$PHP_OUTPUT.=($text);

?>