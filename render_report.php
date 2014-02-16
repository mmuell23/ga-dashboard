<?php
	require_once("lib/config.php");
	$report = false;
	
	$options = isset($_GET["options"]) ? $_GET["options"] : array();
	
	switch ($_GET["type"]) {
	    case 'visits':			
	        $report = new VisitReport($_SESSION["from"], $_SESSION["profile_id"], $options);
	        break;
	}
	
	if($report) {
		?>
<div class="report-widget thumbnail">		
		<?php
		$report->getResults();
		$report->render();
		?>
</div>	
		<?php
	}
?>