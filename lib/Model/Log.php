<?php

class Model_Log extends Model_Table {
	var $table= "Log";
	function init(){
		parent::init();

		$this->addField('branch_id');
		$this->addField('staff_id');
		$this->addField('session_id');
		$this->addField('activity');
		$this->addField('on_date')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		$this->add('dynamic_model/Controller_AutoCreator');
	}


	function createNew($activity){
		$this['branch_id']=$this->api->currentBranch->id;
		$this['staff_id']=$this->api->auth->model->id;
		$this['session_id']=$this->api->currentSession->id;
		$this['activity']=$activity;
		$this->save();

	}
}