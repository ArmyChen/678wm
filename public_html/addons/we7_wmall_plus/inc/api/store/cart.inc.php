<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$this->icheckAuth();
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
$sid = intval($_GPC['sid']);
if(!$sid) {
	message(ierror(-1, '门店id不能为空'), '', 'ajax');
}
if($op == 'index') {
	$cart = order_fetch_member_cart($sid);
	message(ierror(0, '', $cart), '', 'ajax');
}

if($op == 'truncate') {
	pdo_delete('tiny_wmall_plus_order_cart', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'uid' => $_W['member']['uid']));
	message(ierror(0, '清空购物车成功'), '', 'ajax');
}


