<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'notice';
$id = intval($_GPC['id']);
$notice = pdo_get('tiny_wmall_plus_notice', array('id' => $id, 'uniacid' => $_W['uniacid']));
include $this->template('notice');
