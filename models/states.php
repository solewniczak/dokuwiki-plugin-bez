<?php

class States {
	public function get($anytask = true) {
		global $bezlang;
		$a = array($bezlang['state_opened']);
		if ($anytask)
			$a[] = $bezlang['state_closed'];
		else
			$a[] = $bezlang['state_rejected'];
		return $a;
	}
	public function get_list() {
			global $bezlang;
			$ret['-proposal'] = $bezlang['state_proposal'];
			$ret = array_merge($ret ,$this->get());
			$ret[] = $bezlang['state_rejected'];
			return $ret;
	}
	public function get_all($anytask = true) {
			global $bezlang;
			$ret = $this->get($anytask);
			$ret['-proposal'] = $bezlang['state_proposal'];
			return $ret;
	}
	public function id($name) {
		switch ($name) {
			case 'opened': return 0;
			case 'closed': return 1;
			default: return -1;
		}
	}
	public function closed($name) {
		$states = $this->get();
		$key = array_search($name, $states);
		return $key == 1;
	}
	public function rejected($name) {
		global $bezlang;
		return $bezlang['state_rejected'] == $name;
	}
	/*pobierz nazwę stanu, uwzględniając -proposal i -rejected*/
	public function name($id, $coordinator, $anytask=true) {
		global $bezlang;
		$a = $this->get_all($anytask);
		if ($id == 1 && !$anytask)
			return $bezlang['state_rejected'];
		else if (strstr($coordinator, '-') === 0)
			return $a[$coordinator];
		else
			return $a[$id];
	}

	public function open() {
		return 0;
	}
	
	public function proposal() {
		return '-proposal';
	}
}

