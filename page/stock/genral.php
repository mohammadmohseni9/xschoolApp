<?php
class page_stock_genral extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form',null,null,array('form_horizontal'));
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');

		$from_date=$form->addField('DatePicker','from_date');
		$to_date=$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('Issue'=>'Issue','Submit'=>'Submit','Consume'=>'Consume'))->setEmptyText('Please Select');
		$form->addSubmit('GET LIST');

		$grid=$this->add('Grid');
		$transaction=$this->add('Model_Stock_Transaction');
		if($_GET['filter']){
			if($_GET['item'])
				$transaction->addCondition('item_id',$_GET['item']);
			if($_GET['type'])
				$transaction->addCondition('type',$_GET['type']);
			if($_GET['from_date'])
				$transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transaction->addCondition('created_at','<=',$_GET['to_date']);
		}else{
			$transaction->addCondition('id',-1);
		}

		$grid->setModel($transaction);

		$grid->removeColumn('session');
		$grid->removeColumn('branch');
		$grid->removeColumn('supplier');

		if($form->isSubmitted()){
			$grid->js()->reload(array('item'=>$form['item'],'type'=>$form['type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}
	}
}