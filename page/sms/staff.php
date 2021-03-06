<?php

class page_sms_staff extends Page {
	function init(){
		parent::init();

		$cols=$this->add('Columns');
		$col1=$cols->addColumn(6);
		$form=$col1->add('Form');
		$branch_field=$form->addField('dropdown','branch')->setEmptyText('Please Select')->validateNotNull();
		$branch_field->setModel('Branch');
		$form->addField('text','message')->validateNotNull();
		$form->addSubmit('Send');

		if($form->isSubmitted()){
			$numbers=array();

			$branch=$this->add('Model_Branch')->load($form['branch']);
			$st=$branch->staffs();
			$st->addCondition('is_active',true);
			// $st->addCondition('is_application_user',false);

			foreach ($st as $key => $value) {
				if(!$st['mobile_no']) continue;
				$numbers[]=$st['mobile_no'];

			}
		
			// print_r($numbers);
			$sms=$this->add('Model_Sms');
			try{
				$this->api->db->beginTransaction();
				$sms->sendMessage($form['message'],$numbers,null);
				$this->api->db->commit();
			}catch(Exception $e){
				$this->api->db->rollBack();
				throw $e;
				
			}
			$form->js()->reload(null,$form->js()->univ()->successMessage('Message Send Successfully'))->execute();
		}
		
	}
}