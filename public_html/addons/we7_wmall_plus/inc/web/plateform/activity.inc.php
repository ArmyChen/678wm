<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;

$_W['page']['title'] = '平台营销活动';
$do = 'activity';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';

if($op == 'list') {

}
include $this->template('plateform/activity');