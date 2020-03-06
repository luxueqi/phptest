<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends Base {

	public function block() {

		$params = $this->checkParams(['bduss' => 'noempty', 'v' => 'noempty', 'tname' => 'noempty', 'type' => 'regex:^(uid|portrait|un)$'], ['type' => '取值只能为uid,portrait,un']);
		$tb = new Tieba($params['bduss']);
		try {
			echo $tb->block($params['tname'], $params['v'], $params['type']);

		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}

	public function like() {

		$tb = new Tieba(G('bduss'));
		$uid = G('u');
		if ($uid == '') {
			if (G('p') != '') {
				$uid = $tb->portrait2uid(G('p'));
			} elseif (G('n') != '') {
				$uid = $tb->name2uid(G('n'));

			}
		}

		$_REQUEST['psize'] = G('psize', 60);
		$_REQUEST['u'] = $uid;

		$params = $this->checkParams(['u' => 'int', 'psize' => 'int', 'type' => 'regex:^[01]$', 'pn' => 'int'], ['u' => '支持n=用户名，或u=uid,p=portrait三种模式']);

		try {
			$list = $tb->like($params['pn'], $params['u'], $params['psize']);
			if ($params['type'] == 0) {
				$ss = '';
				for ($i = 0, $len = count($list); $i < $len; $i++) {
					$ss .= ($i + 1) . "." . $list[$i]['name'] . '-' . $list[$i]['level_id'] . '<br>';
				}

				echo $ss . '<a href="/api/tb/like?type=0&pn=' . ($params['pn'] + 1) . '&u=' . $params['u'] . '">下一页</a>' . '<a href="/api/tb/like?type=0&pn=' . (($params['pn'] == 1 ? 2 : $params['pn']) - 1) . '&u=' . $params['u'] . '">上一页</a>';

			} else {
				exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok', $list);

			}
		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}
	}
}

?>