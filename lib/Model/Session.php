<?php


class Model_Session extends Model_Table{
public $table="sessions";
	function init(){
		parent::init();

		$this->addField('name')->mandatory("Name of a session is must like [2011-2012]");
        // $this->addField('is_current')->type('boolean')->defaultValue(false);
        $this->addField('start_date')->type('date')->caption("Session Start Date")->mandatory(true);
        $this->addField('end_date')->type('date')->caption("Session End Date")->mandatory(true);


        $this->hasMany('Student','session_id');       
        $this->hasMany('Stock_Transaction','session_id');       
        $this->hasMany('SubjectInAClass','session_id');
        $this->hasMany('FeesInAClass','session_id'); 
		$this->hasMany('ExamInAClass','session_id');       
		$this->hasMany('Staff_Attendance','session_id');       
		$this->hasMany('Student_Attendance','session_id');       

        $this->addHook('beforeDelete',$this);
        // $this->add('dynamic_model/Controller_AutoCreator');       
	}


	function createNew($name,$start_date, $end_date,$all_fields,$form){

		$this['name']=$name;
		$this['start_date']=$start_date;
		$this['end_date']=$end_date;
		$this->save();

		// $log=$this->add('Model_Log');
		// $log->createNew("Session created");
		// $log->save();

	}


	function deleteForced(){
		$st=$this->ref('Student');
		$fic=$this->ref('FeeClassMapping');
		$sic=$this->ref('SubjectInAClass');
		$eic=$this->ref('ExamInAClass');

		foreach ($st as $junk) {
			$st->delete($force);
		}

		foreach ($fic as $junk) {
			$fic->delete($force);
		}

		foreach ($sic as $junk) {
			$sic->delete($force);
		}

		foreach ($eic as $junk) {
			$eic->delete($force);
		}

		$this->delete();

		$log=$this->add('Model_Log');
		$log->createNew("session removed");
		$log->save();
		
	}

	function beforeDelete(){

		if($this->ref('Student')->count()->getOne()>0)
			throw $this->exception(' You can not delete, It contain student record');

		if($this->ref('FeesInAClass')->count()->getOne()>0)
			throw $this->exception(' You can not delete, It contain Fees record');


		if($this->ref('SubjectInAClass')->count()->getOne()>0)
			throw $this->exception(' You can not delete, It contain Subject record');


		if($this->ref('ExamInAClass')->count()->getOne()>0)
			throw $this->exception(' You can not delete, It contain Exam record');
    	
    	$this->api->forget('currentSession');
	}

	function getCurrent(){
		if($memorized_session = $this->api->recall('currentSession',false))
			return  $memorized_session;
		else{
			return $this->getLast();
		}
	}

	function getLast(){
		$this->setOrder('id','desc');
		$this->tryLoadAny();
		return $this;
	}

	function markCurrent(){
		if(!$this->loaded())
			throw $this->exception('Can not Mark Current unloaded Model');
		
			$this->api->memorize('currentSession',$this);
			$this->api->currentSession =  $this;
			// throw new \Exception($this->api->currentSession, 1);
			
			$log=$this->add('Model_Log');
			$log->createNew("change session");
			$log->save();
		}


	function deleteSession(){}
	// function editSession(){} ???
	
	function getGrade($received, $from){
		$g_m = $this->add('Model_Grade');
		$g_m->addCondition('session_id',$this->id);
		$g_m->setOrder('percentage','desc');

		foreach ($g_m as $junk) {
			if(($received/$from*100) >= $g_m['percentage'])
				return $g_m['name'];
		}

		return 'F';
	}
}	