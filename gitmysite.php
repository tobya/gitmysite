<?php
include("gitscript.php");
if (isset($_GET['action'] ))
{
$mySite = new gitmysite();
$mySite->exec($_GET['action'], $_GET);
}
/*
Some interesting git resources
http://www.draconianoverlord.com/2010/03/04/git-config.html
*/
?>
<html>
	<head>
		<title>Git my Site</title>
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/le-frog/jquery-ui.css" rel="stylesheet" type="text/css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js" type="text/javascript"></script>
		<style type="text/css">
<!--
.sectionheader {
	font-size: large;
	font-weight: bold;
	color: #000000;
}
.sectioncomments {
	font-size: small;
	font-weight: italic;
	color: #ffffff;
	width: 400px;

}
.header {
	font-size: x-large;
	font-weight: bold;
	color: #CCCCCC;
	
}
.results {
	font-size: small;
	color: #0099CC;
	background-color: #CCCCCC;
	border: 1px #660066;
}
-->
		</style>
		<script>
	$(function() {
		$( "#tabs" ).tabs();
	});
		</script>
	</head>
	<body>
		<div class="demo">
					<div class="header ui-widget-content ">
						<p>Git My Site <? echo $_SERVER['SERVER_NAME']; ?></p>
					</div>		
			<div id="tabs">
				<ul>
					<li><a href="#gitmysite_setup">Setup</a></li>
					<li><a href="#gitmysite_commit">Add &amp; Commit</a></li>
					<li><a href="#gitmysite_status">Status</a></li>
					<li><a href="#gitmysite_results">Results</a></li>
				</ul>
				<div id="gitmysite_setup">
					<div class="demo-description" style="display: none; ">
						<p>Click tabs to swap between content that is broken into logical sections.</p>
					</div>
					<!-- End demo-description -->

					<p class="sectionheader">Create a New Repository in this Directory </p>
					<p><a href="gitmysite.php?action=init">Create git Repository</a> 
					- </p>
					<p class="sectionheader">Secure the Directory</p>
					<div class="sectioncomments">Provide a username and password to secure your .git directory.  You can also use this username and password when you clone your repo to your local machine.  <strong>Note:</strong>  Clicking Submit will overwrite any existing .htaccess and .htpasswd file you have in the .git folder, so if you have changed it do not edit from here.</div>
					<form name="form2" method="get" action="gitmysite.php">
						Username:
						<input type="text" name="apache_username"> <BR>
						Password:
						<input type="text" name="apache_pass"><BR>
						<input type=hidden value="apache_secure" name="action" >
						<input type="submit" name="Submit2" value="Submit">
					</form>
					<form name="form1" method="get" action="gitmysite.php">
						<p class="sectionheader">Set User Config Details</p>
						<p>User Details:</p>
						<p> Username:
						<input type="text" name="username">
						</p>
						<p> Email:
						<input type="text" name="email">
						<input type=hidden value="username" name="action" >
						<input type="submit" name="Submit" value="Submit">
						</p>
					</form>
	<p class="sectionheader">Create and Edit Git Ignore File </p>
				  <?php if ($mySite->file_gitignore > '') {  ?>
					<form action=gitmysite.php method=post>
					
						<textarea name='content_gitignore' cols="40" rows="12"><?php echo $mySite->file_gitignore ; ?></textarea>
					
						<input type=hidden  name="action" value="editgitignore">
						<input type="submit" name="Submit" value="Submit">
					</form>
					<?php } 
					else
					{
					
					?>
					<p><a href="gitmysite.php?action=creategitignore">Create .gitignore</a> </p>
					<p><a href="gitmysite.php?action=showgitignore">Edit .gitignore</a> </p>
					
					<?php } ?>
				</div>
				<div id="gitmysite_commit">
					<p>&nbsp; </p>

					<p><a href="gitmysite.php?action=add">Add New &amp; Updated Files</a> - 
					Commit Files
					<form name="form1" method="get" action="gitmysite.php">
						<p class="sectionheader">Commit Comment</p>
						<textarea name="commit_comment" cols="80" rows="6">An Update
						</textarea>
						<input type=hidden  name="action" value="commit">
						<input type="submit" name="Submit" value="Submit">
						</p>
					</form>
				</div>
				<div id="gitmysite_status">
					- <a href="gitmysite.php?action=status#gitmysite_results">Status</a> - <a href="gitmysite.php?action=diff#gitmysite_results">Diff of Changes</a>- <a href="gitmysite.php?action=log#gitmysite_results">Log</a> -</p>
					<div class="results">
					</div>
				</div>
				<div  id="gitmysite_results">
			
			<p>Command Results </p>
			<p>  
			<?php
			if (isset($mySite) )
			{ 
			foreach ($mySite->gitOutput as $line)
			{
			echo "<BR>$line
			";
			}
			}
			?>
			</p>
				
				</div>
			</div>
			</div>
			<!-- End demo -->

		<p>&nbsp;</p>
	</body>
</html>
