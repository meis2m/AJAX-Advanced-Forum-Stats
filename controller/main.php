<?php
/**
*
* @package phpBB Extension - AJAX Advanced Forum Stats
* @copyright (c) 2018 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace meis2m\aafs\controller;

class main
{
    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;

    /**
     * Constructor
     *
     * @param \phpbb\config\config      $config
     * @param \phpbb\controller\helper  $helper
     * @param \phpbb\template\template  $template
     * @param \phpbb\user               $user
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
    {
        $this->config   = $config;
        $this->helper   = $helper;
        $this->template = $template;
        $this->user     = $user;
		$this->request	= $request;
    }
    public function handle($mode = '')
    {
        //Config
        $col_num=15; //Number of rows that will be displayed on index

        $cache_time=10; //Time for update from forum table when user request (when have too many connections, it's usefull)
 
        $char_limit=50; //Max char of topic title that will display on forum stats

        $char_username_limit=11; //Max length (char)of username display in forum stats
		
        global $auth, $cache, $config, $user, $db, $phpbb_root_path, $phpEx;
 
		
        $forum_array = array_unique(array_keys($auth->acl_getf('!f_read', true)));
        $sql_and = '';
		
        if (sizeof($forum_array))
        {
                $sql_and = ' AND ' . $db->sql_in_set('p.forum_id', $forum_array, true);
        }
		
        $ignore_users = array(USER_IGNORE, USER_INACTIVE);
		
        $tiep=true;
		
        $sql_order='';
		
        if($mode=='topx_newpost'){
        $tiep=false;
        $sql_select='MAX(p.post_time) as post_time, u.user_id, u.username, u.user_colour, t.topic_title, t.forum_id, t.topic_last_post_id, t.topic_views, t.topic_replies, f.forum_name, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_poster_colour,t.topic_time';
        $sql_order='post_time DESC';
        }else if($mode=='topx_mostview'){
        $sql_select='MAX(t.topic_views) as topic_views, u.user_id, u.username, u.user_colour, t.topic_title, t.forum_id, t.topic_last_post_id, p.post_time, t.topic_replies, f.forum_name, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_poster_colour,t.topic_time';
        $sql_order='topic_views DESC';
        $tiep=false;
        }else if($mode=='topx_hotesttopic'){
        $sql_select='MAX(t.topic_replies) as topic_replies, u.user_id, u.username, u.user_colour, t.topic_title, t.forum_id, t.topic_last_post_id, t.topic_views, p.post_time, f.forum_name, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_poster_colour,t.topic_time';
        $sql_order='topic_replies DESC';
        $tiep=false;
        }
		
        if(!$tiep){
     $sql_ary = array(
                'SELECT'	=> $sql_select,
                'FROM'		=> array(TOPICS_TABLE => 't', POSTS_TABLE => 'p', FORUMS_TABLE => 'f'),
                'LEFT_JOIN'	=> array(
                        array(
                                'FROM'	=> array(USERS_TABLE => 'u'),
                                'ON'	=> 'p.poster_id = u.user_id',
                        ),
                ),
                'WHERE'		=> 'p.topic_id = t.topic_id
                           AND t.forum_id = f.forum_id
                           AND p.post_approved = 1
                           AND t.topic_moved_id = 0' . $sql_and,
                'GROUP_BY'	=> 't.topic_id',
                'ORDER_BY'	=> $sql_order,
        );

        $result = $db->sql_query_limit($db->sql_build_query('SELECT', $sql_ary), $col_num);
                        
        while( $row = $db->sql_fetchrow($result) )
        {
                $shortusername=utf8_strlen($row['topic_last_poster_name'])>$char_username_limit?utf8_substr($row['topic_last_poster_name'],0,$char_username_limit-3).'...':$row['topic_last_poster_name'];
                $username_string = get_username_string('full', $row['topic_last_poster_id'], $shortusername, $row['topic_last_poster_colour']);
                $view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;p=' . $row['topic_last_post_id'] . '#p' . $row['topic_last_post_id']);
                $topic_title = censor_text($row['topic_title']);
                $latest_poster=get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']);
                $this->template->assign_block_vars('topic_rows',array(
                        'LATEST_POSTER'	=> $latest_poster,
                        'LAST_POSTER_S'	=> $username_string,
                        'POST_TIME'	=> $user->format_date($row['topic_time']),
                        'FORUM_NAME'	=> $row['forum_name'],
                        'LATEST_POST_TIME'	=> $user->format_date($row['post_time']),
                        'VIEWS'			=> $row['topic_views'],
                        'REPLIES'		=> $row['topic_replies'],
                        'U_TOPIC' 		=> $view_topic_url,
                        'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
                        'TOPIC_TITLE' 	=> utf8_strlen($topic_title)>$char_limit?utf8_substr($topic_title,0,$char_limit-3).'...':$topic_title,
                        'TIP_TOPIC_TITLE' => $topic_title,
                        'NEWPOST_MODE'	=> $mode=='topx_newpost' ? true:false,
                        'MOSTVIEW_MODE'	=> $mode=='topx_mostview' ? true:false,
                        'HOTEST_MODE'	=> $mode=='topx_hotesttopic' ? true:false,
                )); //$user->lang['IN'] .
        }

                        $db->sql_freeresult($result);
        }else{
                switch($mode)
                {

                        case 'newest':
                                if (($newest_users = $cache->get('_topx_newest_users')) === false)
                                {
                                        $newest_users = array();

                                        $sql = 'SELECT user_id, username, user_colour, user_regdate
                                                FROM ' . USERS_TABLE . '
                                                WHERE ' . $db->sql_in_set('user_type', $ignore_users, true) . '
                                                        AND user_inactive_reason = 0
                                                ORDER BY user_regdate DESC';
                                        $result = $db->sql_query_limit($sql, $col_num);

                                        while ($row = $db->sql_fetchrow($result))
                                        {
                                                $newest_users[$row['user_id']] = array(
                                                        'user_id'				=> $row['user_id'],
                                                        'username'				=> $row['username'],
                                                        'user_colour'			=> $row['user_colour'],
                                                        'user_regdate'			=> $row['user_regdate'],
                                                );
                                        }
                                        $db->sql_freeresult($result);

                                        // cache this data for 5 minutes, this improves performance
                                        $cache->put('_topx_newest_users', $newest_users, $cache_time);
                                 }
                                 foreach ($newest_users as $row)
                                 {
                                        $shortusername=utf8_strlen($row['username'])>$char_username_limit?utf8_substr($row['username'],0,$char_username_limit-3).'...':$row['username'];
                                        $username_string = get_username_string('full', $row['user_id'], $shortusername, $row['user_colour']);

                                        $this->template->assign_block_vars('topx_newest',array(
                                                'REG_DATE'			=> date('d/m/y',$row['user_regdate']),
                                                'USERNAME_FULL'		=> $username_string
                                                ));
                                }
                        break;
                        case 'topposter':
                                if (($user_posts = $cache->get('_topx_posters')) === false)
                                {
                                        $user_posts = array();

                                        $sql = 'SELECT user_id, username, user_colour, user_posts
                                                FROM ' . USERS_TABLE . '
                                                WHERE ' . $db->sql_in_set('user_type', $ignore_users, true) . '
                                                        AND user_posts <> 0
                                           ORDER BY user_posts DESC';
                                        $result = $db->sql_query_limit($sql, $col_num);

                                        while ($row = $db->sql_fetchrow($result))
                                        {
                                                $user_posts[$row['user_id']] = array(
                                                        'user_id'		=> $row['user_id'],
                                                        'username'		=> $row['username'],
                                                        'user_colour'	=> $row['user_colour'],
                                                        'user_posts'    => $row['user_posts'],
                                                );
                                        }
                                        $db->sql_freeresult($result);

                                        $cache->put('_topx_posters', $user_posts, $cache_time);
                                 }

                                 foreach ($user_posts as $row)
                                 {
                                        $shortusername=utf8_strlen($row['username'])>$char_username_limit?utf8_substr($row['username'],0,$char_username_limit-3).'...':$row['username'];
                                        $username_string = get_username_string('full', $row['user_id'], $shortusername, $row['user_colour']);

                                        $this->template->assign_block_vars('topx_active',array(
                                                'S_SEARCH_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx", 'author_id=' . $row['user_id'] . '&amp;sr=posts'),
                                                'POSTS' 			=> $row['user_posts'],
                                                'USERNAME_FULL'		=> $username_string
                                        ));
                                }
                        break;
                        case 'topthanked':
                                if (($user_thanked = $cache->get('_topx_thanked')) === false)
                                {
                                        $user_thanked = array();

                                        $sql = 'SELECT user_id, username, user_colour, user_thanked
                                                FROM ' . USERS_TABLE . '
                                                WHERE ' . $db->sql_in_set('user_type', $ignore_users, true) . '
                                                        AND user_thanked <> 0
                                           ORDER BY user_thanked DESC';
                                        $result = $db->sql_query_limit($sql, $col_num);

                                        while ($row = $db->sql_fetchrow($result))
                                        {
                                                $user_thanked[$row['user_id']] = array(
                                                        'user_id'		=> $row['user_id'],
                                                        'username'		=> $row['username'],
                                                        'user_colour'	=> $row['user_colour'],
                                                        'user_thanked'    => $row['user_thanked'],
                                                );
                                        }
                                        $db->sql_freeresult($result);

                                        $cache->put('_topx_thanked', $user_thanked, $cache_time);
                                 }

                                 foreach ($user_thanked as $row)
                                 {
                                        $shortusername=utf8_strlen($row['username'])>$char_username_limit?utf8_substr($row['username'],0,$char_username_limit-3).'...':$row['username'];
                                        $username_string = get_username_string('full', $row['user_id'], $shortusername, $row['user_colour']);

                                        $this->template->assign_block_vars('topx_thanked',array(
                                                'S_SEARCH_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx", 'author_id=' . $row['user_id'] . '&amp;sr=posts'),
                                                'THANKED' 			=> $row['user_thanked'],
                                                'USERNAME_FULL'		=> $username_string
                                        ));
                                }
                        break;
                }
        }

		return $this->helper->render('advanced-forum-stats_rep.html', 'page_title');
    }
}
    
