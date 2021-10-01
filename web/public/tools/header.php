<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Airwin tester</title>
	<!-- Bootstrap -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

	<style>
		.btn-submit {
			text-align: left;
		}

		.panel-heading a:after {
			font-family: 'Glyphicons Halflings';
			content: "\e114";
			float: right;
			color: grey;
		}
		.panel-heading a.collapsed:after {
			content: "\e080";
		}

		a.xml:visited {
			color: red;
		}

		a.action {
			text-decoration: none;
			border-bottom: 1px dotted #428bca;
		}
		a.action:hover {
			text-decoration: none;
			border-bottom: 1px dotted #3276b1;
		}

		.opt {
			font-family: 'Courier New', Monospace;
			font-size: 12px;
			font-weight: bold;
		}
		.no-margin {
			margin: 0;
		}
		.no-padding {
			padding: 0;
		}

		.row-selected {
			background-color: #faebcc !important;
		}

		.tbl-row {
			cursor: pointer;
		}
	</style>
</head>

<body>
<div class="panel  panel-default">

	<div class="panel-heading">
		<h2><?php if(isset($title)) {?><?=$title?><?php }else{ ?>Debugger<?php } ?></h2>
	</div>

	<div class="panel-body">
		<?php if(!$standalone_mode) {?>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"<?php if(_TAB=='web'){?> class="active"<?php } ?>><a href="index.php">WEB</a></li>
			<li role="presentation"><a href="index.php?getinfo" target="_blank">PHP info</a></li>
		</ul>
		<?php } ?>