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
	'TFORUM_STATS'	=> 'Forum Stats',
	'TNEWEST_MEM'	=> 'Newest Member',
	'TTOP_THANKED'	=> 'Top Thanked',
	'TTOP_POSTER'	=> 'Top Poster',
	'TNEW_THREAD'	=> 'Latest Post',
	'TMOST_VIEW'	=> 'Most Viewed',
	'THOT_TOPIC'	=> 'Hotest Thread',
));

?>
