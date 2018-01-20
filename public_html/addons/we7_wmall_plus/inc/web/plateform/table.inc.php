<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '列表-';
mload()->model('store');

$sid = $store['id'];
$do = 'store';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';



include $this->template('plateform/table');