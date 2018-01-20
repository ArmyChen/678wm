<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'guide';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';

$url = $this->createMobileUrl('index');
if($_W['we7_wmall_plus']['config']['version'] == 2) {
	$url = $this->createMobileUrl('goods', array('sid' => $_W['we7_wmall_plus']['config']['default_sid']));
}
if($_GPC['__guide'] == 1) {
	header('location:' . $url);
	die;
}
if($op == 'index') {
	$slides = sys_fetch_slide();
	if(empty($slides)) {
		header('location:' . $url);
		die;
	}
	isetcookie('__guide', 1, 10800);
}
include $this->template('guide');

