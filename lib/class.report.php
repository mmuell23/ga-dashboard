<?php
	class DashReport {
		protected $date;
		protected $key;
		protected $options;
		protected $profile_id;
		protected static $analytics;
		protected $results;
		protected $title = "No title";
		protected $description = "No description";
		
		public static function setAnalytics(&$analytics) {
			DashReport::$analytics = $analytics;
		}
		
		public function __construct($date, $key, $profile_id, $options=array()) {
			$this->date = $date;
			$this->key = $key;
			$this->options = $options;
			$this->profile_id = $profile_id;
		}
		
		public function getResults() {
			
			$s = md5(serialize($this->options).$this->key.$this->date.$this->profile_id);
			
			$sql = "SELECT * FROM log WHERE `key`='$s'";
			$qy = mysql_query($sql) or die($sql);
			
			if(!mysql_num_rows($qy)) {
				//MySQL: date, profile_id, key, value
			   $this->results = DashReport::$analytics->data_ga->get(
			       'ga:' . $this->profile_id,
			       $this->date,
			       $this->date,
			       $this->key, 
				   $this->options);
			} else {
				
			}
			
			   
		    return $this->results;
		}
		
		public function render() {
			$rows = $this->results->getRows();
			echo '<h1>'.$this->title.'</h1>';
			echo '<div class="result">'.$this->description.' <span class="badge">'.$rows[0][0].'</span></div>';
		}
		
		private function storeRecord() {
			
		}
		
		private function getRecord() {
			
		}
	}
?>