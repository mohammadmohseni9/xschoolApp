	<?php

class page_master_fees_main extends Page {

	function initMainPage(){	
		$crud=$this->add('xCRUD');

		$fees=$this->add('Model_Fees');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$fees_model = $crud->add('Model_Fees');
			// CreatNew Function call
			$fees_model->createNew($form['name'],$form['default_amount'],$form['distribution'],$form->getAllFields(),$form);
			return true; // Always required
		});

		if($crud->isEditing()){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('add')){
			
		}
		
		$crud->setModel($fees);		
		

		if($grid = $crud->grid){

			$grid->addColumn('expander','setamounts','Set Amounts');

			$grid->addPaginator(10);
			$grid->addQuickSearch(array('name'));
		}

		if($crud->isEditing()){
			$crud->form->getElement('distribution')->setEmptyText('Please select');
			$o->now();
		}
		
	}

	function page_setamounts(){
		$this->api->stickyGET('fees_id');
		$fee = $this->add('Model_Fees');
		$fee->load($_GET['fees_id']);

		$grid=$this->add('Grid');
		$grid->setModel($fee->amountForStudentTypes());

	}

}