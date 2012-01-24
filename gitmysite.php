<?php
//include("gitscript.php");
//if (isset($_REQUEST['action'] ))
{
$mySite = new gitmysite();
$mySite->exec(@$_REQUEST['action'], $_REQUEST);
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
	width: 600px;

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
	width: 600px;
}
-->
		</style>
		<script>
	$(function() {
	  var $tabs;
		$tabs = $( "#tabs" ).tabs();

	});
		</script>
	</head>
	<body>
		<div class="demo">
			<div class="header ui-widget-content ">
				<p>Git My Site <?php echo $_SERVER['SERVER_NAME']; ?></p>
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
					<div class="sectioncomments">Provide a username and password to secure your .git directory.  You can also use this username and password when you clone your repo to your local machine.  
					<strong>Note:</strong>  Clicking Submit will not overwrite any existing .htaccess and .htpasswd file you have in the .git folder. Please delete first or edit manually</div>
					<form name="form2" method="post" action="gitmysite.php#gitmysite_results">
						Username:
						<input type="text" name="apache_username"> <BR>
						Password:
						<input type="password" name="apache_pass"><BR>
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
				</div>
				<div  id="gitmysite_results">
					- <a href="gitmysite.php?action=status#gitmysite_results">Status</a> -  <a href="gitmysite.php?action=log#gitmysite_results">Log</a> -</p>
			
					<p>Command Results </p>
					 
							<div class="results">
				
					<?php
					if (isset($mySite) )
					{ 
					foreach ($mySite->gitOutput as $line)
					{
					echo "<BR>$line
					";
					}
					}
					?>	</div>
				
				</div>
			</div>
		</div>
		<!-- End demo -->
		<p>&nbsp;</p>
	</body>
</html>


<?php


class gitmysite
{

    private $allowed_actions = 'status add commit update-server-info creategitignore editgitignore showgitignore status init username log apache_secure diff';
    
    private $QueryVars = array();
    private $gitCommand = '';
    private $ignoreFileName = '.gitignore';
    public $gitOutput = array();
    public $file_gitignore = '';
    
    function gitmysite()
    {

    }
    
    function exec($usercmd, $QueryVars)
    {
		if ($usercmd == '') {return;}
		
		$this->QueryVars = $QueryVars;
		 // $this->getgitIgnoreFile();	
    	  $Require_Execute = true;
    	  $output = array();
    	//Check if gitcommand is allowed.  
    	if (strpos($this->allowed_actions, $usercmd) !== false)
    	{
    	  $git_action = $usercmd;
    		switch ($git_action)
    		{
    			case 'add':
    				$gitexec[] = ' add -A';
    				$gitexec[] = ' status'; //print out status after
    				break;
    			case 'commit':
    			
    				$Comment =  isset($QueryVars['commit_comment']) ? $QueryVars['commit_comment'] : 'an update';
    			
    				$gitexec[] = " commit -m \"$Comment\" ";
    				break;
    			case 'creategitignore':
    			  $this->create_gitIgnoreFile();
    			  $Require_Execute = false;
    			  break;
    			case 'showgitignore':
    		    $this->get_gitIgnoreFile();	
    		    
    		     $Require_Execute = false;
    			  break;
    			case 'editgitignore':
    		    $this->update_gitIgnoreFile($QueryVars['content_gitignore']);	
    		    
    		     $Require_Execute = false;
    			  break;    			  
    			case 'apache_secure':
            if ($this->gitDirectorySecured() == false)
            {
    				$this->createApacheSecurityFiles($this->QueryVars['apache_username'],$this->QueryVars['apache_pass']);		
   				  
   				  }
   				  else
   				  {
   				    $output[] = 'Directory already contains .htaccess file, please delete via ftp or modify manually';
   				  }
   				  	$Require_Execute = false;	
	    			break;	
    			case 'username':
            $gitexec[] = " config --local user.name \"$this->QueryVars[username]\" ";
            $gitexec[] = " config --local user.email $this->QueryVars[email] ";            
    			  break;    			  
    			default :
    				$gitexec[] = $git_action; 
    		}
    	
    
            if ($Require_Execute)
            {        
        		$output = array();
        		//$this->gitCommand = "git $gitexec";
            	$this->gitCommand = implode(';', $gitexec);
            	foreach ($gitexec as $cmd)
            	{
        		  exec("git " . $cmd, $output); 
        		 // echo "git " . $cmd;
        		 
        		}
            //because we are a dumb server, call serverupdate
        		 
        		exec("git update-server-info", $output);
        		
        	}
        	
        	$this->gitOutput = $output;
    	}  
    	else
    	{
    	  echo 'not an allowable command';
    	}  
    }
    

    function createApacheSecurityFiles($apacheuser, $apachepass)
    {
    
    	$rootpath = $this->ensure_finalslash($_SERVER['DOCUMENT_ROOT']);
    
    	
    
    	$file_htaccess = "AuthUserFile $rootpath.git/.htpasswd
AuthGroupFile /dev/null
AuthName \"Secured Directory\"
AuthType Basic
<Limit GET PUT POST>
require valid-user
</Limit>
";
		//print_r($file_htaccess);
				// md5(
				$realm = '';
				$md5pass = md5($apacheuser . ':' . $realm . ':' .$apachepass);
		//admin:cPwAL5yUQy6jw
		$file_htpass = "$apacheuser:$md5pass";
		//print_r($file_htpass);		
		$this->CreateFile('.git/.htaccess', $file_htaccess);
		$this->CreateFile('.git/.htpasswd', $file_htpass);		
    
    }
    
    
    function create_gitIgnoreFile($Content = 'default')
    {
    
      
      
      if ($Content == 'default')
      {
        $Content = "   
#Default .gitignore files for gitmysite.
#Specify your own .gitignore file 

*.exe
*.jpg
*.png
*.bmp
*.gif        
        ";
      
      }
      
      $this->CreateFile($this->ignoreFileName, $Content);
      
    
    }

  function CreateFile($FileName, $Content)
  {
    $myFile = $FileName;
    $fh = fopen($myFile, 'w') or die("can't open file");
    echo fwrite($fh, $Content);
    fclose($fh);  
  }
  
  function get_gitIgnoreFile()
  {
   //echo $this->ignoreFileName;
    $this->file_gitignore =  file_get_contents($this->ignoreFileName);
   // print_r($this->file_gitignore);
    return true;
  }
  
  function update_gitIgnoreFile($file_content)
  {
    $this->CreateFile($this->ignoreFileName, $file_content);	
    return true;
  }
  
  function ensure_finalslash($path)
  {
  	if (strrpos($path, "/") == (strlen($path) -1))
  	{
  		return $path;
  	}
  	else
  	{
  		return  $path . '/';
  	}
  	
  
  }
  
  function gitDirectorySecured()
  {
    if (file_exists('.git/.htaccess'))
    {
      //echo 'yes';
      return true;
    }
    else
    {
      //echo 'no';
      return false;
    }
  }
  
  function ToDo()
  {
  	$Issues[] = "Properly encode .htpasswd apache passwords";
  
  	return $Issues;
  }
}
  
?>