<?php
/**
 * 模块小程序接口定义
 */

defined('IN_IA') or exit('Access Denied');
include 'model.php';
class We7_WmallModuleWxapp extends WeModuleWxapp{
	public function __construct()
	{
		global $_GPC;
		global $_W;
		global $openid;
		$this->iswxapp = true;
		$_W['account']['key'] = $_W['wxappkey'];
		$_W['openid'] = $_GPC['openid'];
		$_W['fans'] = mc_fansinfo($_W['openid']);
		$_W['member']['uid'] = $_W['fans']['uid'];
		//$_W['openid'] = 'o9NSQt-C6M33iau0Cz-cBpsGlBJI';
		//$_W['fans']['from_user'] = 'o9NSQt-C6M33iau0Cz-cBpsGlBJI';
		//$_W['member']['uid'] = '156819';
		$this->openid = $_W['openid'];
	}
	public function doPageindex() {
		//首页，读取门店列表
		global $_W,$_GPC;
		 mload()->model('store');
		/*$result=pdo_getall('tiny_wmall_store',array('uniacid'=>$_W['uniacid']),array('id','title','logo','address','send_price','delivery_price','is_in_business','business_hours','location_x','location_y'));
		if(!empty($result)){
			foreach($result as &$row){
				$row['logo']=tomedia($row['logo']);
				$row['is_rest'] = ($row['is_in_business'] && store_is_in_business_hours($row['business_hours']));
			}
		}*/
		$lat = trim($_GPC['lat']);
		$lng = trim($_GPC['lng']);
		isetcookie('__lat', $lat, 120);
		isetcookie('__lng', $lng, 120);
		$stores = pdo_fetchall('select id,score,title,logo,address,sailed,score,label,serve_radius,business_hours,is_in_business,delivery_fee_mode,delivery_price,delivery_free_price,send_price,delivery_time,delivery_mode,token_status,invoice_status,location_x,location_y,forward_mode,forward_url,displayorder,click from ' . tablename('tiny_wmall_store') . ' where uniacid = :uniacid and status = 1', array(':uniacid' => $_W['uniacid']));
		$min = 0;
		if(!empty($stores)) {
			$store_label = category_store_label();
			foreach($stores as $key => &$da) {
				$da['logo'] = tomedia($da['logo']);
				$da['business_hours'] = (array)iunserializer($da['business_hours']);
				$da['is_in_business_hours'] = ($da['is_in_business'] && store_is_in_business_hours($da['business_hours']));
				$da['hot_goods'] = pdo_fetchall('select title from ' . tablename('tiny_wmall_goods') . ' where uniacid = :uniacid and sid = :sid and is_hot = 1 limit 3', array(':uniacid' => $_W['uniacid'], ':sid' => $da['id']));
				$da['activity'] = store_fetch_activity($da['id']);
				$da['activity']['num'] += ($da['delivery_free_price'] > 0 ? 1 : 0);
				$da['score_cn'] = round($da['score'] / 5, 2) * 100;
				$da['url'] = store_forward_url($da['id'], $da['forward_mode'], $da['forward_url']);
				if($da['label'] > 0) {
					$da['label_color'] = $store_label[$da['label']]['color'];
					$da['label_cn'] = $store_label[$da['label']]['title'];
				}
				if($da['delivery_fee_mode'] == 2) {
					$da['delivery_price'] = iunserializer($da['delivery_price']);
					$da['delivery_price'] = $da['delivery_price']['start_fee'];
				}
				if(!empty($lng) && !empty($lat)) {
					$da['distance'] = distanceBetween($da['location_y'], $da['location_x'], $lng, $lat);
					$da['distance'] = round($da['distance'] / 1000, 2);
					if($config['store_overradius_display'] == 2 && $da['distance'] > $config_takeout['range']['serve_radius']) {
						unset($stores[$key]);
					}
				} else {
					$da['distance'] = 0;
				}
				if($da['is_in_business_hours'] == 1) {
					$da['is_in_business_hours_'] = 100000;
				}
				$da['displayorder_order'] = $da['displayorder'] + (($da['displayorder'] + 1) * $da['is_in_business_hours_']);
				$da['sailed_order'] = $da['sailed'] + (($da['sailed'] + 1) * $da['is_in_business_hours_']);
				$da['score_order'] = $da['score'] + (($da['score'] + 1) * $da['is_in_business_hours_']);
				$da['click_order'] = $da['click'] + (($da['click'] + 1) * $da['is_in_business_hours_']);
				$da['distance_order'] = $da['distance'] + ($da['distance'] * (($da['is_in_business_hours'] == 1 ? 0 : 100000)));
			}
			$order_by_type = $config['store_orderby_type'] ? $config['store_orderby_type'] : 'distance';
			if(empty($lat)) {
				$order_by_type = 'displayorder';
			}
			if(!empty($stores)) {
				$min = min(array_keys($stores));
				if(in_array($order_by_type, array('distance'))) {
					$stores = array_sort($stores, "{$order_by_type}_order", SORT_ASC);
				} else {
					$stores = array_sort($stores, "{$order_by_type}_order", SORT_DESC);
				}
			}
		}
		$stores = array_values($stores);
		return $this->result(0, '成功', $stores);
	}
	public function doPagelist() {
		//商品列表
		global $_W,$_GPC;
		$result=pdo_getall('tiny_wmall_goods_category',array('sid'=>$_GPC['storeid']),array('id','title'));
		if($result){
			foreach($result as &$row){
				$row['dishes']=pdo_getall('tiny_wmall_goods',array('sid'=>$_GPC['storeid'],'cid'=>$row['id'],'status'=>1),array('id','title','thumb','price'));
				foreach($row['dishes'] as &$d){
					$d['thumb']=tomedia($d['thumb']);
				}
			}
		}
		return $this->result(0, '成功', $result);
	}
	public function doPageslide() {
		//商品列表
		global $_W,$_GPC;
		$slide = pdo_fetchall('select * from' . tablename('tiny_wmall_slide') . 'where uniacid = :uniacid  and status = 1 order by displayorder desc', array(
        ':uniacid' => $_W['uniacid'],
		));
		return $this->result(0, '成功', $slide);
	}
	
	public function doPagedetail() {
		//商品详情
		global $_W,$_GPC;
		$good_id=intval($_GPC['good_id']);
		$result=pdo_get('tiny_wmall_goods',array('id'=>$good_id));
		$result['thumb']=tomedia($result['thumb']);
		return $this->result(0, '成功', $result);
	}
	public function doPagecartList() {
		//购物车
		global $_W,$_GPC;
		$result=pdo_getcolumn('tiny_wmall_order_cart',array('uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']),'data');
		$data=array();
		if($result){
			$result=json_decode($result,true);
			
			foreach($result as $key=>$row){
				$goods=pdo_get('tiny_wmall_goods',array('id'=>$key),array('id','title','thumb','price'));
				$goods['total']=$row;
				$data[]=$goods;
			}
		}
		return $this->result(0, '成功', $data);
	}
	public function doPageDelteCart() {
		//删除购物车商品
		//global $_W,$_GPC;
		//$id=intval($_GPC['id']);
		//if($id){
		//	pdo_delete('weisrc_dish_cart',array('uniacid'=>$_W['uniacid'],'from_user'=>$_W['openid'],'id'=>$id));
		//}
		return $this->result(0, '成功', array());
	}
	public function doPagesubmitOrder() {
		//提交订单
		global $_W,$_GPC;
		//print_r($_GPC['order']);exit;
		$data=json_decode(htmlspecialchars_decode($_GPC['order']),true);
		$order_goods=array();
		$total_price=0.00;
		$total_num=0;
		if(!is_array($data)){
			return $this->result(1, '参数错误');
		}
		
		foreach($data as $row){
			$goods=pdo_get('tiny_wmall_goods',array('id'=>intval($row['goodsid'])),array('id','price'));
			$num=intval($row['nums']);
			$order_goods[]=array(
				'uniacid'=>$_W['uniacid'],
				'sid'=>intval($_GPC['storeid']),
				'goods_id'=>$goods['id'],
				'goods_price'=>$goods['price'],
				'goods_num'=>$num
			);
			$total_price+=$goods['price']*$num;
			$total_num+=$num;
		}
		$order=array(
			'uniacid'=>$_W['uniacid'],
			'sid'=>intval($_GPC['storeid']),
			'uid'=>$_W['member']['uid'],
			'openid'=>$_W['openid'],
			'num'=>$total_num,
			'price'=>$total_price,
			'pay_type'=>'wechat',
			'ordersn'=>date('ymdHis').random(5,true),
			'addtime'=>TIMESTAMP
		);
		//print_r($order);exit;
		pdo_insert('tiny_wmall_order',$order);
		$order_id=pdo_insertid();
		pdo_update('tiny_wmall_order_cart',array('data'=>''),array('uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']));
		foreach($order_goods as $o){
			$o['oid']=$order_id;
			pdo_insert('tiny_wmall_order_stat',$o);
		}
		return $this->result(0, '成功', $order_id);
		
	}
	public function doPageorder() {
		global $_W,$_GPC;
		$orderid=intval($_GPC['orderid']);
		$order=pdo_get('tiny_wmall_order',array('id'=>$orderid),array('id','ordersn','price'));
		//print_r($order);exit;
		$order_goods=pdo_getall('tiny_wmall_order_stat',array('oid'=>$orderid),array('goods_num','goods_id'));
		foreach($order_goods as &$row){
			$goods=pdo_get('tiny_wmall_goods',array('id'=>$row['goods_id']),array('title','thumb','price'));
			$goods['thumb']=tomedia($goods['thumb']);
			$row=array_merge($row,$goods);
		}
		$order['goods']=$order_goods;
		return $this->result(0, '成功', $order);
	}
	public function doPagepay() {
		global $_W,$_GPC;
		$order=array(
			'username'=>trim($_GPC['username']),
			'mobile'=>trim($_GPC['mobile']),
			'note'=>trim($_GPC['remark']),
			'address'=>trim($_GPC['address'])
		);
		$id=intval($_GPC['id']);
		pdo_update('tiny_wmall_order',$order,array('id'=>$id));
		$order=pdo_get('tiny_wmall_order',array('id'=>$id),array('id','price'));
		$data=array(
			'title'=>'餐费',
			'fee'=>$order['price'],
			'tid'=>$order['id']
		);
		$params['tid'] = $order['id'];
		$params['user'] = $_W['fans']['from_user'];
		$params['title'] = '餐费';
		$params['ordersn'] = $order['ordersn'];
		$params['virtual'] = $order['goodstype'] == 2 ? true : false;
		$params['fee'] = $order['price'];
		$res = $this->wxpay($params, 14);
		if (!is_error($res)) {
				$wechat = array('success' => true, 'payinfo' => $res);
		}else {
				$wechat['payinfo'] = $res;
		}
		return $this->result(0, '成功', $wechat);
		
	}
	public function doPagepaysuccess() {
		global $_W,$_GPC;
		$id=intval($_GPC['id']);
		if($id){
			pdo_update('tiny_wmall_order',array('is_pay'=>1,'paytime'=>TIMESTAMP),array('id'=>$id));
		}
		return $this->result(0, '成功',array());
	}
	public function doPageAddToCart() {
		//添加购物车
		global $_W,$_GPC;
		$goodsid=intval($_GPC['goodsid']);
		//$num=intval($_GPC['num']);
		$cart=pdo_get('tiny_wmall_order_cart',array('uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']),array('data','id'));
		if($cart){
			if($cart['data']){
				$data=json_decode($cart['data'],true);
				if($data[$goodsid]){
					$data[$goodsid]++;
				}else{
					$data[$goodsid]=1;
				}
			}else{
				$data=array($goodsid=>1);
			}
			$data=json_encode($data);
			pdo_update('tiny_wmall_order_cart',array('data'=>$data),array('id'=>$cart['id']));
		}else{
			$data=array($goodsid=>1);
			$data=json_encode($data);
			pdo_insert('tiny_wmall_order_cart',array('data'=>$data,'uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']));
		}
		return $this->result(0, '成功', array());
	}
	
	public function doPageorderList() {
		//我的订单
		global $_W,$_GPC;
		$result=pdo_getall('tiny_wmall_order',array('uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']),array('is_pay','id','price','addtime'));
		if($result){
			foreach($result as &$order){
				$order_goods=pdo_getall('tiny_wmall_order_stat',array('oid'=>$order['id']),array('goods_num','goods_id'));
				foreach($order_goods as &$row){
					$goods=pdo_get('tiny_wmall_goods',array('id'=>$row['goods_id']),array('title','thumb','price'));
					$goods['thumb']=tomedia($goods['thumb']);
					$row=array_merge($row,$goods);
				}
				$order['goods']=$order_goods;
				$order['addtime']=date('20y/m/d H:i:s',$order['addtime']);
			}
		}
		return $this->result(0, '成功', $result);
	}
	public function wxpay($params, $type = 0)
	{
			global $_W;
			$setting = uni_setting($_W['wx_uniacid']);
			$wxpay = $setting['payment']['wechat'];
			$openid = (empty($params['openid']) ? $_W['openid'] : $params['openid']);
			$package = array();
			$package['appid'] = $_W['wxappkey'];
			$package['mch_id'] = $wxpay['mchid'];
			$package['nonce_str'] = random(32);
			$package['body'] = $params['title'];
			$package['device_info'] = 'wdl_shopping';
			$package['attach'] = $_W['uniacid'] . ':' . $type;
			$package['out_trade_no'] = $params['tid'];
			$package['total_fee'] = $params['fee'] * 100;
			$package['spbill_create_ip'] = CLIENT_IP;

			if (!empty($params['goods_tag'])) {
				$package['goods_tag'] = $params['goods_tag'];
			}

			$package['notify_url'] = $_W['siteroot'] . 'payment/wechat/notify.php';
			$package['trade_type'] = 'JSAPI';
			$package['openid'] = $openid;
			ksort($package, SORT_STRING);
			$string1 = '';

			foreach ($package as $key => $v) {
				if (empty($v)) {
					continue;
				}

				$string1 .= $key . '=' . $v . '&';
			}

			$string1 .= 'key=' . $wxpay['signkey'];
			$package['sign'] = strtoupper(md5($string1));
			$dat = array2xml($package);
			//file_put_contents(IA_ROOT.'/data/cs.txt',$string1.$_W['wx_uniacid']);
			load()->func('communication');
			$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);

			if (is_error($response)) {
				return error(-1, $response['message']);
			}

			$xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);

			if (strval($xml->return_code) == 'FAIL') {
				return error(-2, strval($xml->return_msg));
			}

			if (strval($xml->result_code) == 'FAIL') {
				return error(-3, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
			}

			$prepayid = $xml->prepay_id;
			$wOpt['appId'] = $_W['wxappkey'];
			$wOpt['timeStamp'] = TIMESTAMP . '';
			$wOpt['nonceStr'] = random(32);
			$wOpt['package'] = 'prepay_id=' . $prepayid;
			$wOpt['signType'] = 'MD5';
			ksort($wOpt, SORT_STRING);
			$string = '';

			foreach ($wOpt as $key => $v) {
				$string .= $key . '=' . $v . '&';
			}

			$string .= 'key=' . $wxpay['signkey'];
			$wOpt['paySign'] = strtoupper(md5($string));
			unset($wOpt['appId']);
			return $wOpt;
	}

	public function isWeixinPay($out_trade_no, $money = 0)
		{
			global $_W;
			global $_GPC;
			$setting = uni_setting($_W['wx_uniacid']);
			$wxpay = $setting['payment']['wechat'];
			$url = 'https://api.mch.weixin.qq.com/pay/orderquery';
			$pars = array();
			$pars['appid'] = $_W['wxappkey'];
			$pars['mch_id'] = $wxpay['mchid'];
			$pars['nonce_str'] = random(32);
			$pars['out_trade_no'] = $out_trade_no;
			ksort($pars, SORT_STRING);
			$string1 = '';

			foreach ($pars as $k => $v) {
				$string1 .= $k . '=' . $v . '&';
			}

			$string1 .= 'key=' . $wxpay['signkey'];
			$pars['sign'] = strtoupper(md5($string1));
			$xml = array2xml($pars);
			load()->func('communication');
			$resp = ihttp_post($url, $xml);

			if (is_error($resp)) {
				return error(-2, $resp['message']);
			}

			if (empty($resp['content'])) {
				return error(-2, '网络错误');
			}

			$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
			$dom = new DOMDocument();

			if ($dom->loadXML($xml)) {
				$xpath = new DOMXPath($dom);
				$code = $xpath->evaluate('string(//xml/return_code)');
				$ret = $xpath->evaluate('string(//xml/result_code)');
				$trade_state = $xpath->evaluate('string(//xml/trade_state)');
				if ((strtolower($code) == 'success') && (strtolower($ret) == 'success') && (strtolower($trade_state) == 'success')) {
					$total_fee = intval($xpath->evaluate('string(//xml/total_fee)')) / 100;

					if ($total_fee != $money) {
						return error(-1, '金额出错');
					}

					return true;
				}

				if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
					$error = $xpath->evaluate('string(//xml/return_msg)');
				}
				else {
					$error = $xpath->evaluate('string(//xml/return_msg)') . ' | ' . $xpath->evaluate('string(//xml/err_code_des)');
				}

				return error(-2, $error);
			}

			return error(-1, '未知错误');
	}
}
