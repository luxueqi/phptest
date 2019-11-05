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
}

?>