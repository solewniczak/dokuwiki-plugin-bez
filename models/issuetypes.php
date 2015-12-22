<?php

include_once "connect.php";

class Issuetypes extends Connect {
	public function __construct() {
		global $errors;
		parent::__construct();
		$q = "CREATE TABLE IF NOT EXISTS issuetypes (
				id INTEGER PRIMARY KEY,
				pl VARCHAR(100) NOT NULL,
				en VARCHAR(100) NOT NULL)";
		$this->errquery($q);
	}
	public function can_modify() {
		if ($this->helper->user_admin()) 
			return true;

		return false;
	}
	public function validate($post) {
		global $bezlang, $errors;

		$post['en'] = trim($post['en']);
		$post['pl'] = trim($post['pl']);

		$regex = '/^[[:alnum:] ]*$/ui';
		if( ! preg_match($regex, $post['en']) || ! preg_match($regex, $post['pl'])) {
			$errors['type'] = $bezlang['vald_type_wrong_chars'];
		} 

		return $post;
	}
	public function get() {
		global $conf;

		$lang = $conf['lang'];
		$result = $this->fetch_assoc("SELECT * FROM issuetypes");

		$data = array();
		foreach ($result as $r) {
			$data[$r['id']] = $r[$lang];
		}

		return $data;
	}

	public function get_clean() {
		$result = $this->fetch_assoc("SELECT issuetypes.id, pl, en,
							(SELECT COUNT(*) FROM issues WHERE type = issuetypes.id) as refs 
							FROM issuetypes");
		return $result;
	}

	public function get_one($id) {
		$result = $this->fetch_assoc("SELECT * FROM issuetypes WHERE id=$id");
		return $result[0];
	}

	public function add($post) {
		global $bezlang, $errors;
		if ( ! $this->can_modify()) 
			return false;

		$data = $this->validate($post);
		if (strlen($data['pl']) == 0 || strlen($data['en']) == 0) {
			$errors['type'] = $bezlang['vald_type_required'];
			return false;
		}
		$this->errinsert($post, 'issuetypes');
		return $data;
	}
	public function update($post, $id) {
		global $INFO;
		if ( ! $this->can_modify()) 
			return false;

		$data = $this->validate($post);
		if (strlen($data['pl']) == 0 && strlen($data['en']) == 0) {
			$this->errquery("DELETE FROM issuetypes WHERE id=$id");
		} else {
			$this->errupdate($data, 'issuetypes', $id);
		}
		return $post;
	}
	public function clean_empty() {
		$res = $this->get_clean();
		foreach ($res as $r) {
			if ((int)$r['refs'] == 0) {
				$id = $r['id'];
				$this->errquery("DELETE FROM issuetypes WHERE id=$id");
			}
		}
	}
}


