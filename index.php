<?php
	require_once("lib/config.php");
?>
<html>
<title>Dashboard</title>
	<head>
		<script src="bower_components/jquery/dist/jquery.js"></script>
		<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>		
		<link rel="stylesheet" type="text/css" href="bower_components/bootstrap/dist/css/bootstrap.css">
		<style type="text/css">
		.report-widget { width: 400px; height: 300px; float: left; }
		</style>
		<script type="text/javascript">
		var reports = {
			items: new Array(),
			register: function(data) {
				reports.items.push(data);
			},
			init: function() {
				$('#widgets').empty();
				for(var i=0; i<reports.items.length; i++) {
					reports.render(reports.items[i]);
				}
			},
			
			render: function (data) {
				 var d = {date: data.date, type: data.type};
				 $.get('render_report.php', d, function(data) {
					$('#widgets').append(data);
			 	});
			}
		};	
		
		$(document).ready(function() {
			reports.init();
		});	
		</script>
	</head>
	<body>
	
		<nav class="navbar navbar-default" role="navigation">
		  <div class="container-fluid">
		    <!-- Brand and toggle get grouped for better mobile display -->
		    <div class="navbar-header">
		      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
		        <span class="sr-only">Toggle navigation</span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		      </button>
		      <a class="navbar-brand" href="#">Brand</a>
		    </div>

		    <!-- Collect the nav links, forms, and other content for toggling -->
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		      <ul class="nav navbar-nav">
		        <li class="active"><a href="#">Link</a></li>
		        <li><a href="#">Link</a></li>
		        <li class="dropdown">
		          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
		          <ul class="dropdown-menu">
		            <li><a href="#">Action</a></li>
		            <li><a href="#">Another action</a></li>
		            <li><a href="#">Something else here</a></li>
		            <li class="divider"></li>
		            <li><a href="#">Separated link</a></li>
		            <li class="divider"></li>
		            <li><a href="#">One more separated link</a></li>
		          </ul>
		        </li>
		      </ul>
		      <form class="navbar-form navbar-left" role="search">
		        <div class="form-group">
		          <input type="text" class="form-control" placeholder="Search">
		        </div>
		        <button type="submit" class="btn btn-default">Submit</button>
		      </form>
		      <ul class="nav navbar-nav navbar-right">
		        <li><a href="#">Link</a></li>
		        <li class="dropdown">
		          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
		          <ul class="dropdown-menu">
		            <li><a href="#">Action</a></li>
		            <li><a href="#">Another action</a></li>
		            <li><a href="#">Something else here</a></li>
		            <li class="divider"></li>
		            <li><a href="#">Separated link</a></li>
		          </ul>
		        </li>
		      </ul>
		    </div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		
		<script type="text/javascript">
		function setDate(f) {
			var d = f.find("select[name=from_day]").val();
			var m = f.find("select[name=from_month]").val();
			var y = f.find("select[name=from_year]").val();
			$.get("set_date.php", { from: {day:d, month: m, year: y}}, function(data) { reports.init(); $('#date').text("Dashboard for "+data)});
		}
		</script>
		
	    <div class="container-fluid">
			<?php 
				echo $_SESSION["from"]; 
				list($year, $month, $day) = explode("-", $_SESSION["from"]);
			?>
			<div class="date-setter">
				<form action="set_date.php" onsubmit="setDate($(this)); return false;">
					<fieldset>
						<select name="from_day">
							<?php for($i=1;$i<=32;$i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor;?>
						</select>.						
						<select name="from_month">
							<?php for($i=1;$i<=12;$i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor;?>
						</select>.						
						<select name="from_year">
							<?php for($i=date("Y")-3;$i<=date("Y");$i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor;?>
						</select>
					</fieldset>
					<p></p>
					<p><input type="submit" value="Set date" class="btn btn-primary"/></p>
				</form>
			</div>

			   		<h1 class="page-header" id="date">Dashboard</h1>
					<!-- content -->
					<?php
					if (!$client->getAccessToken() || $client->isAccessTokenExpired()) {
					  $authUrl = $client->createAuthUrl();
					  print "<a class='login' href='$authUrl'>Connect Me!</a>";

					} else {					
					  if(isset($_GET["profile_id"])) {
						  $_SESSION["profile_id"] = $_GET["profile_id"];
					  }	  
					}
					?>	
		 </div>
		 <div class="col-md-4">
			 <?php if($_SESSION["ga_connected"]): ?>
				 <?php printAccounts($analytics);?>
			 <?php endif;?>
		 </div>
		 <div class="col-md-8">
		 	<div id="widgets">
			</div> 
		 </div>
		 <script type="text/javascript">

		 reports.register({type:'visits', date:'<?php echo $_SESSION["from"]; ?>'});
		 </script>
	<?php  
		
	function printAccounts(&$analytics) {
  	  $accounts = $analytics->management_accounts->listManagementAccounts();
	  $items = $accounts->getItems();

	  foreach($items as $item) {		  		  
   		  $webproperties = $analytics->management_webproperties->listManagementWebproperties($item["id"]);		  		 
		  echo '<ul class="nav nav-pills">';
		  echo '<li class="dropdown">';
		  echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
		  echo $item["name"];
		  echo '<span class="caret"></span></a>';
		  echo '<ul class="dropdown-menu">';
		  foreach($webproperties as $wp) {
			  echo '<li>';
			  echo '<a href="index.php?property_id='.$wp["id"].'&account_id='.$item->getId().'">';
			  echo $wp["name"];
			  echo '</a>';
			  echo '</li>';
		  }
		  echo "</ul></li></ul>";
	  }

	}
	
	function runMainDemo(&$analytics) {
	  try {

	    // Step 2. Get the user's first view (profile) ID.
	    $profileId = getFirstProfileId($analytics);
		echo $profileId;
	    if (isset($profileId)) {

	      // Step 3. Query the Core Reporting API.
	      $results = getResults($analytics, $profileId);

	      // Step 4. Output the results.
	      printResults($results);
	    }

	  } catch (apiServiceException $e) {
	    // Error from the API.
	    print 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage();

	  } catch (Exception $e) {
	    print 'There wan a general error : ' . $e->getMessage();
	  }
	}
	
	
	
	/**
	*
	* ga:pageviews, ga:visits
	* filters=ga:pagePath=~/[^/]+/view/
	*
	**/
	function getResults(&$analytics, $profileId, $key='ga:visits') {
		$options = array('filters' => 'ga:pagePath=~.*[0-9]+\.htm');

	   return $analytics->data_ga->get(
	       'ga:' . $profileId,
		   $_SESSION["from"],
	       $_SESSION["to"],
	       $key, $options);
	}
	
	function printResults(&$results) {
		print_r($results);
	  if (count($results->getRows()) > 0) {
	    $profileName = $results->getProfileInfo()->getProfileName();
	    $rows = $results->getRows();
		echo "<pre>";
		print_r($results);
		echo "</pre>";
	    $visits = $rows[0][0];

	    print "<p>First view (profile) found: $profileName</p>";
	    print "<p>Total visits: $visits</p>";

	  } else {
	    print '<p>No results found.</p>';
	  }
	}
?>Done.
</body>
</html>