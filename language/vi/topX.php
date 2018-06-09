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
	'TFORUM_STATS'	=> 'Thống kê diễn đàn',
	'TNEWEST_MEM'	=> 'Thành viên mới',
	'TTOP_THANKED'	=> 'Được cảm ơn nhiều',
	'TTOP_POSTER'	=> 'Thành viên tích cực',
	'TNEW_THREAD'	=> 'Bài mới',
	'TMOST_VIEW'	=> 'Được xem nhiều',
	'THOT_TOPIC'	=> 'Chủ đề nóng',
));

?>
