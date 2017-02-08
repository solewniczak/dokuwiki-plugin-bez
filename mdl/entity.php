<?php
 
if(!defined('DOKU_INC')) die();

/*
 * Task coordinator is taken from tasktypes
 */

class BEZ_mdl_Entity {	
	protected $auth, $validator, $model, $helper;
	
	protected $parse_int = array();
	
	public function get_level() {
		return $this->auth->get_level();
	}
	
	public function get_user() {
		return $this->auth->get_user();
	}
	
	public function get_columns() {
		return array();
	}
	
	public function get_virtual_columns() {
		return array();
	}
	
	public function get_assoc() {
		$assoc = array();
		$columns = array_merge($this->get_columns(), $this->get_virtual_columns());
		foreach ($columns as $col) {
			$assoc[$col] = $this->$col;
		}
		return $assoc;
	}
	
	public function sqlite_date($time=NULL) {
		//SQLITE format: https://www.sqlite.org/lang_datefunc.html
		if ($time === NULL) {
			return date('Y-m-d H:i:s');
		} else {
			return date('Y-m-d H:i:s', $time);
		}
	}
	
	public function __get($property) {
		$columns = array_merge($this->get_columns(), $this->get_virtual_columns());
		if (property_exists($this, $property) && in_array($property, $columns)) {
			if (in_array($property, $this->parse_int)) {
				return (int)$this->$property;
			} else {
				return $this->$property;
			}
		}
	}
	
	public function any_errors() {
		return count($this->validator->get_errors()) > 0;
	}
	
	public function get_errors() {	
		return $this->validator->get_errors();
	}
	
	public function __construct($model) {
		$this->model = $model;
		$this->auth = new BEZ_mdl_Auth($model->dw_auth, $model->user_nick);
		$this->validator = new BEZ_mdl_Validator($this->model);
		$this->helper = plugin_load('helper', 'bez');
	}
	
	/*Function protected to prevent accidential calling on child class */
	protected function remove() {
		$sth = $this->model->db->prepare('DELETE FROM '.$this->get_table_name().' WHERE id = ?');
		$sth->execute(array($this->id));
	}
}
