<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC, $_POST;
mload()->model('store');
mload()->model('order');
load()->func('logging');
$_W['we7_wmall_plus']['config'] = sys_config();

