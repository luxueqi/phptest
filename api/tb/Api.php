<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api {

	public function block() {
		$bduss = G('bduss');
		$v = G('v');
		$type = G('type');
		$tname = G('tname');
		if (empty($bduss) || empty($v) || !in_array($type, ['uid', 'portrait', 'un']) || empty($tname)) {
			exitMsg(ErrorConst::API_PARAMS_ERRNO, '参数错误');
		}
		$tb = new Tieba($bduss);
		try {
			echo $tb->block($tname, $v, $type);

		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}
}

?>