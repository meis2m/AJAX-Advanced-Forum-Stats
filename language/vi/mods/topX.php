<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    'NEWEST_TOPICS'		=> 'Bài mới',
	'ACTIVE_MEM'	=> 'Thành viên tích cực nhất',
    'NEWEST_MEM'	=> 'Thành viên mới nhất',
	'BY'                => 'bởi',
	'VIEWS'		=> 'Đã xem',
	'REPLY'		=> 'Trả lời',
	'THANKED'		=> 'Được cảm ơn',
	'TOPICS'		=> 'Số bài viết',
	'REGDATE'		=> 'Ngày đăng kí',
	'POSTER'		=> 'Người gởi',
	'LATEST_POSTER'		=> 'Trả lời cuối',
	'LATEST_POST_TIME'	=> 'Lần trả lời cuối',
	'POST_TIME'		=> 'Ngày gởi',
	'TIMES'			=> 'lần',
));

?>
