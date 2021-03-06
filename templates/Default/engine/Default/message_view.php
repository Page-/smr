<?php
if (isset($MessageBoxes)) { ?>
	<p>Please choose your Message folder!</p>

	<table id="folders" class="standard">
		<thead>
			<tr>
				<th class="sort" data-sort="name">Folder</th>
				<th class="sort" data-sort="messages">Messages</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach ($MessageBoxes as $MessageBox) { ?>
				<tr id="<?php echo str_replace(' ','-',$MessageBox['Name']);?>" class="ajax<?php if($MessageBox['HasUnread']) { ?> bold<?php } ?>">
					<td class="name">
						<a href="<?php echo $MessageBox['ViewHref']; ?>"><?php echo $MessageBox['Name']; ?></a>
					</td>
					<td class="messages center yellow"><?php echo $MessageBox['MessageCount']; ?></td>
					<td><a href="<?php echo $MessageBox['DeleteHref']; ?>">Empty Read Messages</a></td>
				</tr><?php
			} ?>
		</tbody>
	</table>
	<p><a href="<?php echo $ManageBlacklistLink; ?>">Manage Player Blacklist</a></p>
	<script type="text/javascript" src="js/list.1.0.0.custom.min.js"></script>
	<script>
	var list = new List('folders', {
		valueNames: ['name', 'messages'],
		sortFunction: function(a, b) {
			return list.sort.naturalSort(a.values()[this.valueName].replace(/<.*?>|,/g,''), b.values()[this.valueName].replace(/<.*?>|,/g,''), this);
		}
	});
	</script><?php
}
else {
	if ($MessageBox['Type'] == MSG_GLOBAL) { ?>
		<form name="IgnoreGlobalsForm" method="POST" action="<?php echo $IgnoreGlobalsFormHref; ?>">
			<div align="center">Ignore global messages?&nbsp;&nbsp;
				<input type="submit" name="action" value="Yes" id="InputFields"<?php if ($ThisPlayer->isIgnoreGlobals()) { ?> style="background-color:green;"<?php } ?> />&nbsp;<input type="submit" name="action" value="No" id="InputFields"<?php if (!$ThisPlayer->isIgnoreGlobals()) { ?> style="background-color:green;"<?php } ?> />
			</div>
		</form><?php
	} ?>
	<br />
	<form name="MessageDeleteForm" method="POST" action="<?php echo $MessageBox['DeleteFormHref']; ?>">
		<table class="fullwidth center">
			<tr>
				<td style="width: 30%" valign="middle"><?php
					if(isset($PreviousPageHREF)) {
						?><a href="<?php echo $PreviousPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
					} ?>
				</td>
				<td>
					<input type="submit" name="action" value="Delete" id="InputFields" />&nbsp;<select name="action" size="1" id="InputFields">
																						<option>Marked Messages</option>
																						<option>All Messages</option>
																					</select>
					<p>You have <span class="yellow"><?php echo $MessageBox['TotalMessages']; ?></span> <?php echo $this->pluralise('message', $MessageBox['TotalMessages']); if($MessageBox['TotalMessages']!=$MessageBox['NumberMessages']){ ?> of which <span class="yellow"><?php echo $MessageBox['NumberMessages']; ?></span> <?php echo $this->pluralise('is', $MessageBox['NumberMessages']); ?> being displayed<?php } ?>.</p>
				</td>
				<td style="width: 30%" valign="middle"><?php
					if(isset($NextPageHREF)) {
						?><a href="<?php echo $NextPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
					} ?>
				</td>
			</tr>
		</table><?php
		
		if (isset($MessageBox['ShowAllHref'])) {
			?><div class="buttonA"><a class="buttonA" href="<?php echo $MessageBox['ShowAllHref'] ?>">&nbsp;Show all Messages&nbsp;</a></div><br /><br /><?php
		} ?>
		<table class="standard fullwidth"><?php
			if(isset($MessageBox['Messages'])) {
				foreach($MessageBox['Messages'] as &$Message) { ?>
					<tr>
						<td width="10"><input type="checkbox" name="message_id[]" value="<?php echo $Message['ID']; ?>" /><?php if($Message['Unread']) { ?>*<?php } ?></td>
						<td class="noWrap" width="100%"><?php
							if(isset($Message['ReceiverDisplayName'])) {
								?>To: <?php echo $Message['ReceiverDisplayName'];
							}
							else {
								?>From: <?php echo $Message['SenderDisplayName'];
							} ?>
						</td>
						<td class="noWrap"<?php if(!isset($Message['Sender'])) { ?> colspan="3"<?php } ?>>Date: <?php echo date(DATE_FULL_SHORT, $Message['SendTime']); ?></td>
						<td>
							<a href="<?php echo $Message['ReportHref']; ?>"><img src="images/notify.gif" width="14" height="11" border="0" align="right" title="Report this message to an admin" /></a>
						</td><?php
						if (isset($Message['Sender'])) { ?>
							<td>
								<a href="<?php echo $Message['BlacklistHref']; ?>">Blacklist Player</a>
							</td>
							<td>
								<a href="<?php echo $Message['ReplyHref']; ?>">Reply</a>
							</td><?php
						} ?>
					</tr>
					<tr>
						<td colspan="6"><?php echo bbifyMessage($Message['Text']); ?></td>
					</tr>
					<?php
				} unset($Message);
			}
			if(isset($MessageBox['GroupedMessages'])) {
				foreach($MessageBox['GroupedMessages'] as &$Message) { ?>
					<tr>
						<td width="10"><input type="checkbox" name="message_id[]" value="<?php echo $Message['ID'] ?>" /><?php if($Message['Unread']) { ?>*<?php } ?></td>
						<td class="noWrap" width="100%">From: <?php echo $Message['SenderDisplayName']; ?></td>
						<td class="noWrap" colspan="4">Date: <?php echo date(DATE_FULL_SHORT, $Message['FirstSendTime']); ?> - <?php echo date(DATE_FULL_SHORT, $Message['LastSendTime']); ?></td>
					</tr>
					<tr>
						<td colspan="6"><?php echo bbifyMessage($Message['Text']); ?></td>
					</tr>
					<?php
				} unset($Message);
			} ?>
		</table>
		<table class="fullwidth center">
			<tr>
				<td style="width: 30%" valign="middle"><?php
					if(isset($PreviousPageHREF)) {
						?><a href="<?php echo $PreviousPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
					} ?>
				</td>
				<td>
				</td>
				<td style="width: 30%" valign="middle"><?php
					if(isset($NextPageHREF)) {
						?><a href="<?php echo $NextPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
					} ?>
				</td>
			</tr>
		</table>
	</form><?php
} ?>