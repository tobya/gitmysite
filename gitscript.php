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