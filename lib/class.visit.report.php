<?php
	class VisitReport extends DashReport {
		public function __construct($date, $profile_id, $options=array()) {
			$this->title = "Visits";
			$this->description = "number of unique users";
			parent::__construct($date, "ga:visits", $profile_id, array());
		}		
	}
?>