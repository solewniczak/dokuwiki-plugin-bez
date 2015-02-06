<?php
include_once DOKU_PLUGIN."bez/models/connect.php";
include_once DOKU_PLUGIN."bez/models/users.php";
include_once DOKU_PLUGIN."bez/models/rootcauses.php";
include_once DOKU_PLUGIN."bez/models/event.php";

class Causes extends Event {
	public function __construct() {
		global $errors;
		parent::__construct();
		$q = "CREATE TABLE IF NOT EXISTS causes (
				id INTEGER PRIMARY KEY,
				cause TEXT NOT NULL,
				rootcause INTEGER NOT NULL,
				reporter INTEGER NOT NULL,
				date INTEGER NOT NULL,
				issue INTEGER NOT NULL)";
		$this->errquery($q);
	}
	public function can_modify($cause_id) {
		$cause = $this->getone($cause_id);

		if ($cause && $this->issue->opened($cause['issue']))
			if ($this->helper->user_coordinator($cause['issue']) || $this->helper->user_admin()) 
				return true;

		return false;
	}
	public function validate($post) {
		global $bezlang, $errors;

		$cause_max = 65000;

		$post['cause'] = trim($post['cause']);
		if (strlen($post['cause']) == 0) 
			$errors['cause'] = $bezlang['vald_content_required'];
		else if (strlen($post['cause']) > $cause_max)
			$errors['cause'] = str_replace('%d', $cause_max, $bezlang['vald_content_too_long']);

		$data['cause'] = $post['cause'];

		$rootco = new Rootcauses();
		if ( ! array_key_exists((int)$post['rootcause'], $rootco->get()))
			$errors['type'] = $bezlang['vald_root_cause'];
		 
		$data['rootcause'] = (int)$post['rootcause'];

		return $data;
	}
	public function add($post, $data=array())
	{
		if ($this->helper->user_coordinator($data['issue']) && $this->issue->opened($data['issue'])) {
			$from_user = $this->validate($post);
			$data = array_merge($data, $from_user);

			$this->errinsert($data, 'causes');
			$this->issue->update_last_mod($data['issue']);
		}
	}
	public function update($post, $data, $id) {

		if ($this->can_modify($id)) {
			$from_user = $this->validate($post);
			$data = array_merge($data, $from_user);

			$this->errupdate($data, 'causes', $id);

			$cause = $this->getone($id);
			$this->issue->update_last_mod($cause['issue']);
		}
	}
	public function delete($cause_id) {
		if ($this->can_modify($cause_id)) {
			$data = $this->getone($cause_id);
			$this->errdelete('causes', $cause_id);
			$this->issue->update_last_mod($data['issue']);
		}
	}

	public function join($a) {
		$usro = new Users();
		$rootco = new Rootcauses();
		$a['reporter'] = $usro->name($a['reporter']);
		$a['rootcause'] = $rootco->name($a['rootcause']);
		return $a;
	}

	public function getone($id) {
		$id = (int) $id;
		$cause = $this->fetch_assoc("SELECT * FROM causes WHERE id=$id");

		return $cause[0];
	}
	public function get($issue) {
		$issue = (int) $issue;

		$a = $this->fetch_assoc("SELECT * FROM causes WHERE issue=$issue");
		return $this->join_all($a);
	}

	public function get_by_rootcause($issue) {
		$a = $this->get($issue);
		$b = array();
		foreach ($a as $row) {
			$k = $row['rootcause'];
			if ( !isset($b[$k]) )
				$b[$k] = array();
			$b[$k][] = $row;
		}
		return $b;
	}
}

