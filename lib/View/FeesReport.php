<?php
class View_FeesReport extends View {
	public $from_date;
	public $to_date;
	public $branch_id;
	public $exclude_cheque;

	function recursiveRender(){

		if(!$this->from_date)
			$this->from_date = date('Y-m-01',strtotime($this->api->today));
		if(!$this->to_date)
			$this->to_date = date('Y-m-t',strtotime($this->api->today));
		

		$fees_transaction  = $this->add('Model_FeesTransaction');

		$fees_transaction->addExpression('mode')->set(function($m,$q){
			return $m->refSQL('fees_receipt_id')->fieldQuery('mode');
		});
		$fees_transaction->addCondition('submitted_on','>=',$this->from_date);
		$fees_transaction->addCondition('submitted_on','<',$this->api->nextDate($this->to_date));
		
		// $student_join = $fees_transaction->leftJoin('students','student_id',null,'_st');
		// $class_join = $student_join->leftJoin('classes','class_id');
		// $class_join->addField('b_id','branch_id');

		$student_applied_fees_join = $fees_transaction->leftJoin('student_fees_applied','student_applied_fees_id');
		$student_applied_fees_join->addField('fees_id');

		if($this->branch_id){
			$fees_transaction->addCondition('branch_id',$this->branch_id);
		}

		if($this->exclude_cheque == 'true'){
			$fees_transaction->addCondition('mode','<>','Cheque');
		}else{
			
		}
		

		// $fees_join = $student_applied_fees_join->join('fees','fees_id');

		$fees_transaction->_dsql()->del('fields')
			->field('submitted_on')
			->field($student_applied_fees_join->table_alias.'.fees_id')
			->field('by_consession')
			->field(
				$this->api->db->dsql()->expr(
					'SUM(
						case
							WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 10 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 11 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 12 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 13 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 16 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 17 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 18 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 19 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 22 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 23 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 26 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 28 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 29 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 43 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 44 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 45 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 49 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 57 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 58 AND fees_transactions.session_id=2 THEN 0 
							ELSE fees_transactions.amount
						END
					) as total_amount'
					)
				);

		$fees_transaction->addCondition('by_consession',false);

		$fees_transaction->_dsql()->group('submitted_on,_s.fees_id');
		
		// $fees_transaction->_dsql()->where('submitted_on','2014-07-07');

		// echo "<pre>";
		// print_r($fees_transaction->_dsql()->get());
		// echo "</pre>";

		$result_array = array();
		$grid=$this->add('Grid');
		
		$columns_added=array();
		$consession_stored_4_date=array();



		$grid->addColumn('text','date');

			$fees = $this->add('Model_Fees');
			// $fees->addCondition('name','Caution Money');
		foreach ($fees_transaction->_dsql()->get() as $data) {

			$fees->unload();
			$fees->load($data['fees_id']);

            if(!isset($result_array[$data['submitted_on']])) $result_array[$data['submitted_on']] = array();
                        
			$result_array[$data['submitted_on']]['date'] = $data['submitted_on'];
			$result_array[$data['submitted_on']][trim($fees['name'])] = $data['total_amount'];
			$result_array[$data['submitted_on']]['row_total'] = ($result_array[$data['submitted_on']]['row_total']+$data['total_amount']);

			$fees_names = $this->add('Model_Fees');

			foreach ($fees_names as $junk) {
				if(!isset($result_array[$data['submitted_on']][$fees_names['name']])){
					$result_array[$data['submitted_on']][$fees_names['name']]=0;
				}
			}

			// echo "<pre>";
			// print_r($result_array[$data['submitted_on']]['row_total']);
			// echo "</pre>";

			if(!in_array($fees['name'], $columns_added)){
				$grid->addColumn('text',$fees['name']);
				$columns_added[] = $fees['name'];
			}
			// echo "<pre>";
			// print_r($columns_added);

			// consession time
			if(!in_array($data['submitted_on'], $consession_stored_4_date)){
				$fee_trans = $this->add('Model_FeesTransaction');
				$student_applied_fees_join = $fee_trans->leftJoin('student_fees_applied','student_applied_fees_id');
				$student_applied_fees_join->addField('fees_id');
				$fee_trans
					->_dsql()
					->del('fields')
					->field(
						$this->api->db->dsql()->expr(
							'SUM(
							case 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 10 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 11 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 12 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 13 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 16 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 17 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 18 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 19 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 22 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 23 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 26 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 28 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 29 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 43 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 44 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 45 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 49 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 57 AND fees_transactions.session_id=2 THEN 0 
								WHEN '.$student_applied_fees_join->table_alias.'.fees_id = 58 AND fees_transactions.session_id=2 THEN 0 
								ELSE fees_transactions.amount
							END
							)'
						)
					)
					->where('submitted_on',$data['submitted_on'])
					->where('by_consession',1);

				$result_array[$data['submitted_on']] += array(
					'consession'=> $fee_trans->_dsql()->getOne(),
					);
				
				// $result_array[$data['submitted_on']]['row_total'] = ($result_array[$data['submitted_on']]['row_total']+$result_array[$data['submitted_on']]['consession']);
				// $consession_stored_4_date[] = $data['submitted_on'];
			}

		}

		$fees_totals=array();
		
		$fees_key_value_array=array();

		foreach ($result_array as $date => $row) {
			foreach ($row as $key => $value) {
				if($key=='date') continue;
				if(!isset($fees_totals[$key])) $fees_totals[$key]=0;
				$fees_totals[$key] += $value;
				$fees_key_value_array[$key]=$key;
			}
		}


		$fees_totals['date']='Total';


		$result_array['totals'] = $fees_totals;

		$grid->addColumn('text','consession');
		$grid->addColumn('money','row_total');
		$grid->setStyle('font-size','15px');
		$grid->setSource($result_array);
		
		$grid->addHook('formatRow',function($g)use($fees_key_value_array){
			foreach ($fees_key_value_array as $value) {
				if($g->model[$value] > 0)
					$g->current_row_html[$value] = $g->model[$value];
				else
					$g->current_row_html[$value] = "0";
			}
		});
                // echo "<pre>";
                //     print_r($result_array);
                // echo "</pre>";
		
		$js=array(
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);
		// echo "<pre>";
		// print_r($result_array);
		// echo "</pre>";
		parent::recursiveRender();

	}
}