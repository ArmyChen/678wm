<?php
/**
 * 外送系统
 * @author 灯火阑珊
 * @QQ 2471240272
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '商品分类-' . $_W['we7_wmall_plus']['config']['title'];
mload()->model('store');

$store = store_check();
$sid = $store['id'];$slid=$store['slid'];
$do = 'category';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';

if($op == 'post') {
	if(checksubmit('submit')) {
		if(!empty($_GPC['title'])) {
			foreach($_GPC['title'] as $k => $v) {
				$v = trim($v);
				if(empty($v)) continue;
				$data['sid'] = $sid;
				$data['uniacid'] = $_W['uniacid'];
				$data['title'] = $v;
				$data['min_fee'] = intval($_GPC['min_fee'][$k]);
				$data['displayorder'] = intval($_GPC['displayorder'][$k]);
				pdo_insert('tiny_wmall_plus_goods_category', $data);
			}
		}
		message('添加商品分类成功', $this->createWebUrl('category'), 'success');
	}
}

if($op == 'list') {
	
	$url="http://www.678sh.com/biz.php?ctl=dc&act=dc_menu_index&id=".$slid;
	echo '<script>window.history.go(-1);</script>';		
	//header('location: ' . $url);
	/*
	$condition = 'location_id = :sid and is_effect=1';
	$params[':sid'] = $slid;
	$pindex = max(1, intval($_GPC['page']));
	$psize = 200;
	
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM fanwe_dc_supplier_menu_cate WHERE ' . $condition, $params);
	$lists = pdo_fetchall('SELECT * FROM fanwe_dc_supplier_menu_cate WHERE ' . $condition . ' ORDER BY sort ASC,id DESC LIMIT '.($pindex - 1) * $psize.','.$psize, $params, 'id');
	if(!empty($lists)) {
		$ids = implode(',', array_keys($lists));
		$nums = pdo_fetchall("SELECT count(*) AS num,cate_id as cid FROM fanwe_dc_menu WHERE ".$condition." and cate_id IN ({$ids}) GROUP BY cate_id", $params, 'cid');
	}	 
	$pager = pagination($total, $pindex, $psize);
	if(checksubmit('submit')) {
		if(!empty($_GPC['ids'])) {
			foreach($_GPC['ids'] as $k => $v) {
				$data = array(
					'title' => trim($_GPC['title'][$k]),
					'min_fee' => trim($_GPC['min_fee'][$k]),
					'displayorder' => intval($_GPC['displayorder'][$k])
				);
				pdo_update('tiny_wmall_plus_goods_category', $data, array('uniacid' => $_W['uniacid'], 'id' => intval($v)));
			}
			message('编辑成功', $this->createWebUrl('category'), 'success');
		}
	}
	*/
}

if($op == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('tiny_wmall_plus_goods_category', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
	pdo_delete('tiny_wmall_plus_goods', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'cid' => $id));
	message('删除商品分类成功', $this->createWebUrl('category'), 'success');
}

if($op == 'export') {
	if(checksubmit()) {
		$file = upload_file($_FILES['file'], 'excel');
		if(is_error($file)) {
			message($file['message'], $this->createWebUrl('category'), 'error');
		}
		$data = read_excel($file);
		if(is_error($data)) {
			message($data['message'], $this->createWebUrl('category'), 'error');
		}
		unset($data[0]);
		if(empty($data)) {
			message('没有要导入的数据', $this->createWebUrl('category'), 'error');
		}
		foreach($data as $da) {
			$insert = array(
				'uniacid' => $_W['uniacid'],
				'sid' => $sid,
				'title' => trim($da[0]),
				'displayorder' => intval($da[1]),
				'status' =>  intval($da[2]),
			);
			pdo_insert('tiny_wmall_plus_goods_category', $insert);
		}
		message('导入商品分类成功', $this->createWebUrl('category'), 'success');
	}
}

if($op == 'status') {
	if($_W['isajax']) {
		$id = intval($_GPC['id']);
		$status = intval($_GPC['status']);
		pdo_update('tiny_wmall_plus_goods_category', array('status' => $status), array('uniacid' => $_W['uniacid'], 'sid' => $sid , 'id' => $id));
		message(error(0, ''), '', 'ajax');
	}
}
include $this->template('store/category');