<?php
	session_start();
	
	$_GET["from"]["day"] = $_GET["from"]["day"] < 10 ? "0".$_GET["from"]["day"] : $_GET["from"]["day"];	
	$_GET["from"]["month"] = $_GET["from"]["month"] < 10 ? "0".$_GET["from"]["month"] : $_GET["from"]["month"];
	$_GET["from"]["year"] = $_GET["from"]["year"] < 10 ? "0".$_GET["from"]["year"] : $_GET["from"]["year"];
	
	$_SESSION["from"] = $_GET["from"]["year"]."-".$_GET["from"]["month"]."-".$_GET["from"]["day"];
	$_SESSION["to"] = $_SESSION["from"];	
?>