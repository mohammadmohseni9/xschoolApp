<?php

class page_library_main extends Page{
	function init(){
		parent::init();


		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('library_category','Subjects');
		$tab1=$tabs->addTabURL('library_title','Title');
		$tab1=$tabs->addTabURL('library_item','Item');
		$tab1=$tabs->addTabURL('library_stocktransaction','Stock transaction');
		$tab1=$tabs->addTabURL('library_transaction','Library Actions');
	}
}