<?php
	set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/google-api-php-client/src/');
	
	require_once dirname(__FILE__).'/google-api-php-client/src/Google/Client.php';
	require_once dirname(__FILE__).'/google-api-php-client/src/Google/Service/Analytics.php';

	session_start();

	//set initial session
	if(!isset($_SESSION["from"])) {
		$_SESSION["from"] = date("Y-m-d");
		$_SESSION["to"] = date("Y-m-d");
	}

	$scriptUri = "http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF'];

	$client = new Google_Client();
	$client->setApplicationName('VRM');

	// Visit https://cloud.google.com/console to generate your
	// client id, client secret, and to register your redirect uri.
	$client->setClientId('98442211070-873rc4ag7nmoluf2l1q96i12470upou2.apps.googleusercontent.com');
	$client->setClientSecret('d4oVBGPtaOgzoMrazL1cUmwj');
	$client->setRedirectUri($scriptUri);
	//$client->setDeveloperKey('AIzaSyD2CwqRvK9hGy6aplKMeFH_gbug1AuhodU');
	$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));

	// Magic. Returns objects from the Analytics Service instead of associative arrays.
	//$client->setUseObjects(true);

	if (isset($_GET['code'])) {
	  $client->authenticate($_GET['code']);
	  $_SESSION['token'] = $client->getAccessToken();
	  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}

	if (isset($_SESSION['token'])) {
	  $client->setAccessToken($_SESSION['token']);
	}
	?>
<html>
<title>Dashboard</title>
	<head>
		<script src="bower_components/jquery/dist/jquery.js"></script>
		<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>		
		<link rel="stylesheet" type="text/css" href="bower_components/bootstrap/dist/css/bootstrap.css">
		
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
			$.get("set_date.php", { from: {day:d, month: m, year: y}});
		}
		</script>
		
	    <div class="container-fluid">
			<?php echo $_SESSION["from"]; ?>
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
					<input type="submit" value="Set date"/>
				</form>
			</div>

			   		<h1 class="page-header">Dashboard</h1>
					<!-- content -->
					<?php
					if (!$client->getAccessToken() || $client->isAccessTokenExpired()) {
					  $authUrl = $client->createAuthUrl();
					  print "<a class='login' href='$authUrl'>Connect Me!</a>";

					} else {
					  // Create analytics service object. 
					  $analytics = new Google_Service_Analytics($client);
					  if(isset($_GET["profile_id"])) {
						  $_SESSION["profile_id"] = $_GET["profile_id"];
					  }	  
						calculateResults($analytics);	
						printAccounts($analytics);	
					    //runMainDemo($analytics);
					}
					?>	
		 </div>

	<?php  
	
	function calculateResults(&$analytics) {
  	  if(isset($_GET["property_id"])) {
  		$webproperties = $analytics->management_webproperties->listManagementWebproperties($_GET["account_id"]);		  
  		  foreach($webproperties as $wp) {
  			  if($_GET["property_id"] == $wp["id"]) {				    				 
  				  $profiles = $analytics->management_profiles->listManagementProfiles($_GET["account_id"], $_GET["property_id"]);
  				  $profile = $profiles["items"][0];
  				  $_SESSION["profile_id"] = $profile["id"];
  				  $results = getResults($analytics, $_SESSION["profile_id"]);
  				  printResults($results);
  			  }
  		  }	      		  
  	  }
	}
	
	function printAccounts(&$analytics) {
  	  $accounts = $analytics->management_accounts->listManagementAccounts();
	  $items = $accounts->getItems();

	  foreach($items as $item) {		  
		  echo "<h2>".$item["name"]."</h2>";		  
   		  $webproperties = $analytics->management_webproperties->listManagementWebproperties($item["id"]);		  		 
		  echo "<ul>";
		  foreach($webproperties as $wp) {
			  echo '<li>';
			  echo '<a href="index.php?property_id='.$wp["id"].'&account_id='.$item->getId().'">';
			  echo $wp["name"];
			  echo '</a>';
			  echo '</li>';
		  }
		  echo "</ul>";
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
	
	function getFirstprofileId(&$analytics) {
	  $accounts = $analytics->management_accounts->listManagementAccounts();

	  if (count($accounts->getItems()) > 0) {
	    $items = $accounts->getItems();
	    $firstAccountId = $items[0]->getId();

	    $webproperties = $analytics->management_webproperties->listManagementWebproperties($firstAccountId);

	    if (count($webproperties->getItems()) > 0) {
	      $items = $webproperties->getItems();
	      $firstWebpropertyId = $items[0]->getId();

	      $profiles = $analytics->management_profiles
	          ->listManagementProfiles($firstAccountId, $firstWebpropertyId);

	      if (count($profiles->getItems()) > 0) {
	        $items = $profiles->getItems();
	        return $items[0]->getId();

	      } else {
	        throw new Exception('No views (profiles) found for this user.');
	      }
	    } else {
	      throw new Exception('No webproperties found for this user.');
	    }
	  } else {
	    throw new Exception('No accounts found for this user.');
	  }
	}
	
	function getResults(&$analytics, $profileId) {
	   return $analytics->data_ga->get(
	       'ga:' . $profileId,
	       $_SESSION["from"],
	       $_SESSION["to"],
	       'ga:visits');
	}
	
	function printResults(&$results) {
	  if (count($results->getRows()) > 0) {
	    $profileName = $results->getProfileInfo()->getProfileName();
	    $rows = $results->getRows();
	    $visits = $rows[0][0];

	    print "<p>First view (profile) found: $profileName</p>";
	    print "<p>Total visits: $visits</p>";

	  } else {
	    print '<p>No results found.</p>';
	  }
	}
?>
</body>
</html>