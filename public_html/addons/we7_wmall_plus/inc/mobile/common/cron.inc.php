<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
mload()->model('cron');
global $_W, $_GPC;
$do = 'cron';
if($_W['isajax']) {
	set_time_limit(0);
	cron_order();
	exit('success');
}












