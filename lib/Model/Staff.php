<?php

class Model_Staff extends Model_Table{
	public $table="staffs";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->mandatory(true);
		$this->addField('name')->mandatory(true);
		$this->addField('username')->mandatory(true);
		$this->addField('password')->type('password')->mandatory(true);

		 $this->add('dynamic_model/Controller_AutoCreator');


	}

	


	function createNew($name,$username,$password,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception("You can not use Loaded Model on createNewBranch ");
		
		$this['name']=$name;
		$this['username']=$username;
		$this['password']=$password;
		$this['branch_id']=$other_fields['branch_id'];
		$this->save();
			
	}


}