<?php
/*
 * MyBB: Last User's Threads in Profile
 *
 * File: mybbirlastthreadsprofile.php
 *
 * Authors: AliReza_Tofighi & updated by Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.2
 *
 */

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("member_profile_end", "mybbirlastthreadsprofile");


function mybbirlastthreadsprofile_info()
    {
    global $lang;

    $lang->load("mybbirlastthreadsprofile");

    $lang->mybbirlastthreadsprofile_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' .
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->mybbirlastthreadsprofile_Desc;

    return Array(
        'name' => $lang->mybbirlastthreadsprofile_Name,
        'description' => $lang->mybbirlastthreadsprofile_Desc,
        'website' => $lang->mybbirlastthreadsprofile_Web,
        'author' => $lang->mybbirlastthreadsprofile_Auth,
        'authorsite' => $lang->mybbirlastthreadsprofile_AuthSite,
        'version' => $lang->mybbirlastthreadsprofile_Ver,
        'compatibility' => $lang->mybbirlastthreadsprofile_Compat
    );
    }


function mybbirlastthreadsprofile_activate(){
	global $mybb, $db;
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("member_profile", "#".preg_quote('{$mybbirlastthreadsprofile}')."#i", '', 0);
	find_replace_templatesets("member_profile", "#".preg_quote('{$signature}')."#i", '{$mybbirlastthreadsprofile}{$signature}');
}

function mybbirlastthreadsprofile_deactivate(){
	global $mybb, $db;
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("member_profile", "#".preg_quote('{$mybbirlastthreadsprofile}')."#i", '', 0);

}

function mybbirlastthreadsprofile()
{
	global $db, $mybb, $memprofile, $theme, $lang, $mybbirlastthreadsprofile;

    $lang->load("mybbirlastthreadsprofile");

	$threadlimit = 10;
	$query = $db->query("
		SELECT t.*, t.subject AS threadsubject, u.username, u.usergroup, u.displaygroup, i.*, i.name AS iconname,
		t.dateline AS threaddate, t.lastpost AS threadlastpost
		FROM ".TABLE_PREFIX."threads t
		LEFT JOIN ".TABLE_PREFIX."icons i ON (i.iid=t.icon)
		LEFT JOIN ".TABLE_PREFIX."users u ON (t.lastposter=u.username)
		WHERE t.visible = '1' And t.uid = '{$memprofile['uid']}'
		GROUP BY t.tid
		ORDER BY threadlastpost DESC
		LIMIT 0, {$threadlimit}
	");

	while($threads = $db->fetch_array($query))
	{

		if($threads['icon'] > 0)
		{
			$icon = "<img src=\"{$threads['path']}\" alt=\"{$threads['iconname']}\" title=\"{$threads['iconname']}\" />";
		}
		else
		{
			$icon = "&nbsp;";
		}

        $threads['threadsubject'] = htmlspecialchars_uni($threads['threadsubject']);

		if(strlen($threads['threadsubject']) > "40")
		{
			$threadsthreadsubject = my_substr($threads['threadsubject'],0,40)."...";
		}
		else
		{
			$threadsthreadsubject = $threads['threadsubject'];
		}

		if(strlen($threads['forumname']) > "20")
		{
			$threadsforumname = my_substr($threads['forumname'],0,20)."...";
		}
		else
		{
			$threadsforumname = $threads['forumname'];
		}

		$threadlink = get_thread_link($threads['tid']);
		$forumlink = get_forum_link($threads['fid']);
		$replies = my_number_format($threads['replies']);
		$views = my_number_format($threads['views']);
		$lastpostdate = my_date($mybb->settings['dateformat'], $threads['threadlastpost']);
		$lastposttime = my_date($mybb->settings['timeformat'], $threads['threadlastpost']);
		$lastposter = format_name($threads['username'], $threads['usergroup'], $threads['displaygroup']);
		$lastposter = build_profile_link($lastposter, $threads['lastposteruid']);

		$last_thread .= "<tr>
			<td class=\"trow1\" align=\"center\" height=\"24\">$icon</td>
			<td class=\"trow2\"><a href=\"$threadlink\" title=\"$threads[threadsubject]\">$threadsthreadsubject</a></td>
			<td class=\"trow1\" align=\"center\">$replies</td>
			<td class=\"trow2\" align=\"center\">$views</td>
			<td class=\"trow1\"><span class=\"smalltext\">$lastpostdate $lastposttime<br />".$lang->mybbirlastthreadsprofile_lastthread_By.": $lastposter</span></td>
	</tr>";

	}
	if(!$last_thread){
    global $lang;

    $lang->load("mybbirlastthreadsprofile");

		$last_thread = "<tr><td class=\"trow1\" colspan=\"5\">".$lang->mybbirlastthreadsprofile_lastthread_no_thread."</td></tr>";

	}

	global $lang;

    $lang->load("mybbirlastthreadsprofile");

	$mybbirlastthreadsprofile = "<table border=\"0\" cellspacing=\"".$theme['borderwidth']."\" cellpadding=\"".$theme['tablespace']."\" class=\"tborder\">
			<tr>
				<td class=\"thead\" colspan=\"6\"><strong>".$lang->mybbirlastthreadsprofile_lastthread_title."</strong></td>
			</tr>
				<tr>
					<td class=\"tcat tcat_menu\" width=\"5%\" height=\"24\">&nbsp;</td>
					<td class=\"tcat tcat_menu\" width=\"50%\"><span class=\"smalltext\"><strong>".$lang->mybbirlastthreadsprofile_lastthread_subject."</strong></span></td>
					<td class=\"tcat tcat_menu\" width=\"10%\" align=\"center\"><span class=\"smalltext\"><strong>".$lang->mybbirlastthreadsprofile_lastthread_replies."</strong></span></td>
					<td class=\"tcat tcat_menu\" width=\"10%\" align=\"center\"><span class=\"smalltext\"><strong>".$lang->mybbirlastthreadsprofile_lastthread_views."</strong></span></td>
					<td class=\"tcat tcat_menu\" width=\"25%\" align=\"center\"><span class=\"smalltext\"><strong>".$lang->mybbirlastthreadsprofile_lastthread_last_post."</strong></span></td>
				</tr>
			<tbody>
			{$last_thread}
			</tbody>
		</table><br />";
}

?>
