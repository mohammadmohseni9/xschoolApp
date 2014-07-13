<?php

class Model_Staff extends Model_Table{
	public $table="staffs";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->mandatory(true);
		$this->addField('name')->mandatory(true);
		$this->addField('username')->mandatory(true);
		$this->addField('password')->type('password')->mandatory(true);
		$this->addField('is_application_user')->type('boolean')->defaultValue(false);
		$this->hasMany('Library_transaction','staff_id');
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

	function filterByBranch($branch){
		$this->addCondition('branch_id',$branch->id);
		return $this;
	}

	function issue($item){
		if(!$this->loaded())
			throw $this->exception("You can not use Loaded Model on issue ");
		if(!$item->loaded() or !$item instanceof Model_Library_Item)
			throw $this->exception("Please pass loaded object of Library_item ");
		$transaction=$this->add('Model_Library_Transaction');
		$transaction->issue($item,null,$this);
	}


}