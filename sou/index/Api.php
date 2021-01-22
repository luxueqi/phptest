<?php
/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends Base {

	public function vue() {
		$this->view();
	}

	public function index() {
		//Request::ReDirectUrl();
		$this->view();
	}

	public function cha() {
		if (isGetPostAjax('post')) {
			$params = $this->checkParams(['txt' => 'regex:^[0-9a-zA-Z-_@.]{5,20}$', 'type' => 'regex:^[012]$'], ['txt' => '输入正确的参数']);

			try {
				$chastr = ['name', 'phone', 'idcard'];

				$data = Db::getInstance()->exec('select name,password,idcard,apasspord,phone from 12306_1 where ' . $chastr[$params['type']] . '=?', [$params['txt']])->getAll();
				if (!Session('uid')) {
					foreach ($data as $key => $value) {
						$data[$key]['name'] = strReplaceStart($value['name']);
						$data[$key]['password'] = strReplaceStart($value['password']);
						$data[$key]['apasspord'] = strReplaceStart($value['apasspord']);
						$data[$key]['phone'] = strReplaceStart($value['phone']);
						$data[$key]['idcard'] = strReplaceStart($value['idcard']);
					}
				}
				exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok', $data);

			} catch (Exception $e) {

				exitMsg(ErrorConst::API_CATCH_ERRNO, 'catch');

			}
		} else {
			exitMsg(-1, 'method error');
		}

	}

}

?>