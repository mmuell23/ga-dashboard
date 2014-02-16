<?php
	set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/google-api-php-client/src/');
	
	require_once dirname(__FILE__).'/google-api-php-client/src/Google/Client.php';
	require_once dirname(__FILE__).'/google-api-php-client/src/Google/Service/Analytics.php';

	session_start();

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
	
	echo "<pre>";
	
	if (!$client->getAccessToken()) {
	  $authUrl = $client->createAuthUrl();
	  print "<a class='login' href='$authUrl'>Connect Me!</a>";

	} else {
	  // Create analytics service object. See next step below.

	  $analytics = new Google_Service_Analytics($client);

	  if(isset($_GET["profile_id"])) {
		  $_SESSION["profile_id"] = $_GET["profile_id"];
	  }
	  
	  if(isset($_GET["property_id"])) {
		  
		  $webproperties = $analytics->management_webproperties->listManagementWebproperties($_GET["account_id"]);		  
		  foreach($webproperties as $wp) {
			  echo $wp->getId();
			  echo "<br>";
			  if($_GET["property_id"] == $wp["id"]) {				  
				  echo $_GET["account_id"]."-".$_GET["property_id"];
				  $profiles = $analytics->management_profiles->listManagementProfiles($_GET["account_id"], $_GET["property_id"]);
				  $profile = $profiles["items"][0];
				  $_SESSION["profile_id"] = $profile["id"];
				  $results = getResults($analytics, $_SESSION["profile_id"]);
				  printResults($results);
			  }
		  }	      		  
		  $results = getResults($analytics, $_SESSION["profile_id"]);
		  //printResults($results);
	  }

	  
	}
	printAccounts($analytics);	  
	
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
			echo "items";
			print_r($items);
			
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
	       '2013-03-03',
	       '2013-03-03',
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