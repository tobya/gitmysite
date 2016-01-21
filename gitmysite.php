<?php
/*************************************************************
Copyright � 2012 Toby Allen (http://github.com/tobya)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the �Software�), to deal in the Software without restriction, 
including without limitation the rights to use, copy, modify, merge, publish, distribute, sub-license, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice, and every other copyright notice found in this software, and all the attributions in every file, and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED �AS IS�, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. 
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************/

$PASS = 'password'; //PLEASE CHANGE THIS PASSWORD BEFORE UPLOADING
$IsLoggedIn = false;
$mySite = new gitmysite();

CheckLogin($mySite);
$mySite->exec(@$_REQUEST['action'], $_REQUEST);


class gitmysite
{

    private $allowed_actions = 'status add add-preview commit addcommit update-server-info creategitignore editgitignore showgitignore status init username log apache_secure diff sitelogin';
    
    private $QueryVars = array();
    private $gitCommand = '';
    private $ignoreFileName = '.gitignore';
    public $gitOutput = array();
    public $file_gitignore = '';
    public $Errors = array();
    
    function gitmysite()
    {
      //Does anything need to be done on creation?
    }
    
    function exec($usercmd, $QueryVars)
    {
      if ($usercmd == '') {return;}
    
      $this->QueryVars = $QueryVars;
     // $this->getgitIgnoreFile();  
     
        //Defaults
        $Require_Execute = true;      
        $this->gitOutput = array();
        
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
          case 'add-preview' :
          
            $gitexec[] = ' add -A --dry-run';           
            break;
          case 'commit':
          
            $Comment =  isset($QueryVars['commit_comment']) ? $QueryVars['commit_comment'] : 'an update';
          
            $gitexec[] = " commit -m \"$Comment\" ";
            break;
          
          case 'addcommit':
          
            $Comment =  isset($QueryVars['commit_comment']) ? $QueryVars['commit_comment'] : 'an update';
          
            $gitexec[] = " add -A "; //Add all files modified and new files first.  git commit -a wont do as it does not add new files.
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
                $this->Errors[] = 'Directory already contains .htaccess file, please delete via ftp or modify manually';
            }
            $Require_Execute = false; 
            break;  
            
          case 'username':
              
                $ConfigInfo = json_encode(array('config' => array('user.name' => $this->QueryVars['username'])));
                $this->CreateFile('.git/gitmysite.config',$ConfigInfo);
                
          
                $gitexec[] = " config --local user.name \"$this->QueryVars[username]\" ";
                $gitexec[] = " config --local user.email $this->QueryVars[email] ";            
              break;            
          default :
            $gitexec[] = $git_action; 
        }
      
    
            if ($Require_Execute)
            {        
                $output = array();
              $this->gitCommand = implode(';', $gitexec);
      
      
              foreach ($gitexec as $cmd)
              {
                  exec("git " . $cmd,  $this->gitOutput); 
              }
              
              //because we are a dumb server, call serverupdate after each time we execute commands.           
              exec("git update-server-info",  $this->gitOutput);
            
            }
          
          
      }  
      else
      {
       $this->Errors[] =   $usercmd . ' is not an allowable command';
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

    $cryptpass = crypt($apachepass);

    $file_htpass = "$apacheuser:$cryptpass";
    $this->CreateFile('.git/.htaccess', $file_htaccess);
    $this->CreateFile('.git/.htpasswd', $file_htpass);    
    
    }
    
    
    function create_gitIgnoreFile($Content = 'default')
    {
    
      if ($this->exists_gitIgnoreFile()){
        $this->Errors[] = '.gitignore file already exists, please edit or delete to create a new one.';
      } else {
      
        if ($Content == 'default')
        {
          $Content = "
             
#Default .gitignore files for gitmysite.
#Created by gitmysite.php https://github.com/tobya/gitmysite

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
    
    }

  function CreateFile($FileName, $Content)
  {
    $myFile = $FileName;
    $fh = fopen($myFile, 'w') or die("can't open file");
    $size = fwrite($fh, $Content);
    fclose($fh);  
    return $size;
  }
  
  function get_gitIgnoreFile()
  {

    if (!$this->exists_gitIgnoreFile()) {
      $this->Errors[] = '.gitignore file does not exist.';
      return false; 
    }

    $this->file_gitignore =  file_get_contents($this->ignoreFileName);
    return true;

  }
  
  function exists_gitIgnoreFile()
  {
  return  file_exists(  $this->ignoreFileName);

  }  
  
  function exists_gitmysiteConfig() {
   return file_exists('.git/gitmysite.config');
  }
  
  function update_gitIgnoreFile($file_content)
  {
    $this->CreateFile($this->ignoreFileName, $file_content);  
    return true;
  }
  
  /*
    I'm sure there is a better way to do this, just can see it now.
  */
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
    return file_exists('.git/.htaccess');
  }
  
  function ToDo()
  {
    $Issues[] = "";
  
    return $Issues;
  }
  
  function Version()
  {
    return '0.4.8';
  }
  
  function GitRepoExists()
  {
    
    return file_exists('.git');
  
  }
  
  function GitRepoRoot()
  {
    $ScriptFile = $_SERVER['PHP_SELF'];
    $info  = pathinfo($ScriptFile);
    
    return $_SERVER['SERVER_NAME'] . $info['dirname'];
  
  }  
  
  function InSetupMode(){
    $Result = true;
    if ($this->exists_gitIgnoreFile()) {
      if ($this->GitRepoExists()){
        $Result = false;
      }
    }
    
    return $Result;
  }
}

function CheckLogin(&$GitMySite)
{
  global $IsLoggedIn;
  global $PASS;
  global $PasswordIsDefault;
  $DefaultPassword = 'password'; //dont change;
  $PasswordIsDefault = false;
  session_start();
  
  if (@$_POST['Password'] == $PASS)
  {
    $IsLoggedIn = True;
    $_SESSION['pass'] = $_POST['Password'];
    $IsLoggedIn = True;
  }
  else if (@$_SESSION['pass']  == $PASS)
  {
     $IsLoggedIn = True;
  }
  else
  {
    
    $IsLoggedIn = false;
    $GitMySite->Errors[] = 'Login Failed';
  }
  
  //for security check whether password has been updated.
  if ($DefaultPassword == $PASS)
  {
    $PasswordIsDefault = true;
    
  }
  
  
}
  
  
?>

<html>
  <head>
    <title>Git my Site</title>
    <link   href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/le-frog/jquery-ui.css" rel="stylesheet" type="text/css">
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
  color: #ffffff;
  
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
  <body >
    <div class="demo">    
      <div class="header ui-widget-content ui-corner-all ">
        <p>Git My Site  <?php echo $mySite->Version() .': ' ; echo $mySite->GitRepoRoot(); ?> </p>
      </div>
      <?php  



            foreach ($mySite->Errors as $Error)
            {
                echo "<div class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 

                      <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                      $Error </p></div>";
            }


      ?>
      
      
      <?php if (!$IsLoggedIn) //not logged in show the login form.
      {
          //Display a note to indicate to the user that they should change the default password.
          //Nothing like showing the password in plaintext to get people to act.
          if ($PasswordIsDefault) {
              echo "<div class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
              <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
              Please change Default Password: '$PASS' in gitmysite.php script </p>   </div>" ;
              }
      ?>
        <div id="tabs">
        <ul>
          <li><a href="#gitmysite_login">Login</a></li>
        </ul>
        <div id="gitmysite_ligin">
         <form action="" method="Post">
         <fieldset Name"Login Details">
         <input type=password name=Password>
         <input type=Submit Value="Login">
         <input type=hidden name="action" value="sitelogin"
         </fieldset>
         </form>
        </div>
        </div>    
      <?php 
      
      } 
          else //User is Logged in, show the main sections. 
      { 
      
      ?>
      <div id="tabs">
        <ul>
          
          <li><a href="#gitmysite_setup">Setup</a></li>
          
          <li><a href="#gitmysite_commit">Add &amp; Commit</a></li>
          <li><a href="#gitmysite_status">Status</a></li>
                    
        </ul>
        <div id="gitmysite_setup">
          <div class="demo-description" style="display: none; ">
            <p>Click tabs to swap between content that is broken into logical sections.</p>
          </div>
          <?php if (!$mySite->GitRepoExists()){ ?>
          <p class="sectionheader">Create a New Repository in this Directory </p>
          <p><a href="gitmysite.php?action=init">Create git Repository</a> 
          - </p>
          <?php }; 
          
          if ($mySite->gitDirectorySecured()) {
          ?>
            <p class="sectionheader">Directory Security</p>
          <div class="sectioncomments">Your .git directory is secured with basic Auth.  If you need to update the password, please delete the .htaccess and .htpasswd files from                                       the .git directory first. </div>
          <?php } else { ?>
          <p class="sectionheader">Secure the Directory</p>
          <div class="sectioncomments">Provide a username and password to secure your .git directory.  You can also use this username and password when 
          you clone your repo to your local machine.  
          
          <strong>Note:</strong>  
          Clicking Submit will not overwrite any existing .htaccess and .htpasswd file you have in the .git folder. 
          Please delete first or edit manually</div>
          <form name="form2" method="post" action="gitmysite.php##gitmysite_status">
            Username:
            <input type="text" name="apache_username"> <BR>
            Password:
            <input type="password" name="apache_pass"><BR>
            <input type=hidden value="apache_secure" name="action" >
            <input type="submit" name="Submit2" value="Submit">
          </form>
          
          <?php } 
          
          if (!$mySite->exists_gitmysiteConfig()){?>
          <form name="form1" method="get" action="gitmysite.php">
            <p class="sectionheader">Set User Config Details</p>
            <p>User Details:</p>
            <p> Username:
            <input type="text" name="username">
            </p>
            <p> Email:
            <input type="text" name="email">
            <input type=hidden  name="action value="username"" >
            <input type="submit" name="Submit" value="Submit">
            </p>
          </form>
          <?php } ?>
          <p class="sectionheader">Create and Edit Git Ignore File </p>
          
          <?php if ($mySite->file_gitignore > '') {  ?>
          
                <form action=gitmysite.php method=post>
                  <textarea name='content_gitignore' cols="80" rows="12"><?php echo $mySite->file_gitignore ; ?></textarea>
                  <input type=hidden  name="action" value="editgitignore">
                  <BR><input type="submit" name="Submit" value="Save .gitignore">
                </form>
                
          <?php } 
          else  { 
          
            if (!$mySite->exists_gitIgnoreFile()) {?>
          
                <p><a href="gitmysite.php?action=creategitignore">Create .gitignore</a> </p>
                
                <?php } else{ ?>
                
                <p>
                  <div class="sectioncomments">A .gitignore file exists.  Please click below to edit it.</div> <BR>
                <a href="gitmysite.php?action=showgitignore">Edit .gitignore</a> </p>
        
          <?php  }
           } ?>
        
        </div>
        <div id="gitmysite_commit">
        
          <a href=''>Add New Files if Any</A> <a href="gitmysite.php?action=add-preview">(Preview)</a>
                  <p>Command Results </p>
           
          <div class="results">
        
          <?php
          if (isset($mySite) )
          { 
            foreach ($mySite->gitOutput as $line)
            {
              echo "<BR>$line ";
            }
          }
          ?>  
          </div>
          Add Modified Files and Commit
          <form name="form1" method="post" action="gitmysite.php?#gitmysite_status">
            <p class="sectionheader">Commit Comment</p>
            <textarea name="commit_comment" cols="80" rows="6">An Update
            </textarea>
            <input type=hidden  name="action" value="addcommit">
            <input type="submit" name="Submit" value="Submit">
            </p>
          </form>
        </div>
        <div id="gitmysite_status">
          - <a href="gitmysite.php?action=status#gitmysite_status">Status</a> -  <a href="gitmysite.php?action=log#gitmysite_status">Log</a> -</p>
      
          <p>Command Results </p>
           
          <div class="results">
        
          <?php
          if (isset($mySite) )
          { 
            foreach ($mySite->gitOutput as $line)
            {
              echo "<BR>$line ";
            }
          }
          ?>  
          </div>
        
        </div>
      </div>
    </div>
    <?php } //if Logged in end. ?>
    <p>&nbsp;</p>
  </body>
</html>
