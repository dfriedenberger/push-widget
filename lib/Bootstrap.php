<?php
    
require_once 'Template.class.php';
require_once 'Guid.class.php';
require_once 'JsonEncoder.class.php';
require_once 'JsonDecoder.class.php';

class Bootstrap {



    public static function start($publicPath) {
		
	   $url = strtok($_SERVER["REQUEST_URI"],'?');	
		
	   $configPath = realPath(dirname(__FILE__) . "/../config/") . "/";
	   $dataPath = realPath(dirname(__FILE__) . "/../data/") . "/";

		
	   //Content ausliefern
	   $templatePath = realPath(dirname(__FILE__) . "/../templates/") . "/";
	   $languagePath = realPath(dirname(__FILE__) . "/../language/") . "/";
	   
	   //static resources
	   self::staticResources($url,$publicPath,$templatePath);
	    
	   //install if config files not exists
	   if(!file_exists($configPath .'bearer.inc.php'))
       {
		    $error = null;
            $token = new Guid();
			$deliverForm = true;
			if ($_SERVER['REQUEST_METHOD'] == 'POST') 
			{	  
				$token  = $_POST['token'];
				
				//check if php version
				if (version_compare(PHP_VERSION, '5.5.0') < 0) {
					$error = "{L_ERRORPHPVERSION55} (" . PHP_VERSION.")";
				}
				
				//check if data path is writeable
				if($error == null && !is_writable ( $dataPath ))
				{
					$error = "{L_ERRORWRITEDATA}";
				}
				
		        //check if configfile is writeable
				if($error == null && !is_writable ( $configPath ))
				{
					$error = "{L_ERRORWRITECONFIG}";
				}
				
			
				//check token
				if($error == null)
				{
					//create bearer
					$fh = fopen($configPath .'bearer.inc.php','w');
				    if(!$fh) 
					   $error == "{L_ERRORCREATEBEARER}";
					else
					{
					   $salt = password_hash($token, PASSWORD_BCRYPT);
					   fwrite($fh, "<?php \r\n",1024);
					   fwrite($fh, "/** Bearer Token */\r\n",1024);
					   fwrite($fh, '$bearer = \''.$salt.'\';' . "\r\n",1024);
					   fwrite($fh, "?>",1024);
					   fclose($fh);
					}
					$deliverForm = false;
				}
			}
			
			//send Form
			if($deliverForm)
			{
				$template = new Template($templatePath,$languagePath);
				$template->load("install.htm");
				$template->assign("token",$token);
				$template->assign("message" , $error != null ? '<div class="alert alert-danger" role="alert">'. $error .'</div>' : '');
				// Die Sprachdatei laden
				$langs[] = "install.de.php";
				$lang = $template->loadLanguage($langs);
				echo $template->html();
				exit;
			}
			
	   }
		  
		  
	   //api
	   if($url == "/api/data")
	   {

			if ($_SERVER['REQUEST_METHOD'] == 'POST') 
			{
				self::validateBearerToken($configPath);

				//create
				$json = file_get_contents('php://input',true);
				if($json == null)
					self::sendErrorJson('HTTP/1.0 400 Bad Request', 'no json data received');
		  
			  $decoder = new JsonDecoder($json);
			  if($decoder->HasError())
				   self::sendErrorJson('HTTP/1.0 400 Bad Request', $decoder->GetErrorMessage());
		   
			  $data = $decoder->GetContent();
			  if($data == null)
				 self::sendErrorJson('HTTP/1.0 400 Bad Request', 'empty json data received');
			
			
			   $encoder = new JsonEncoder($data,true);
			  //save data to file
			  $fh = fopen($dataPath .'data.json','w');
			  if(!$fh) 
				self::sendErrorJson('HTTP/1.0 500 Internal Server Error', 'could not write data');
			  
			  fwrite($fh, $encoder->GetContent() ,1024);
			  fclose($fh);
			  self::sendOkJson('HTTP/1.0 201 Created','data successfuly created', array());

			}
			
			if ($_SERVER['REQUEST_METHOD'] == 'GET') 
			{
				//get
				$content = file_get_contents($dataPath .'data.json');
				$decoder = new JsonDecoder($content);
				if($decoder->HasError())
				   self::sendErrorJson('HTTP/1.0 500 Internal Server Error', $decoder->GetErrorMessage());
		         
				self::sendOkJson('HTTP/1.0 200 Ok','data successfuly read', array("data" => $decoder->GetContent()));
			}
			
			self::sendErrorJson('HTTP/1.0 501 Not Implemented', 'Unknown Request Method ' . $_SERVER['REQUEST_METHOD']);
	   }
		  
       //Deliver widgets
       $template = new Template($templatePath,$languagePath);
	   $template->load("frame.htm");
	   
	   
	   //Content
	   $content = new Template($templatePath,$languagePath);
	   $content->load("weather.htm");
	   $template->assign("content",$content->html());
	   
	   //global
	   $template->assign("title","Weather Widget");
	   
	   //Analytis
	   if(file_exists($configPath . "analytics.txt"))
	   {
		   $analytics = "";
		   $analytics = file_get_contents($configPath . "analytics.txt");
		   $template->assign("analytics",$analytics);
	   }
	   
       echo $template->html();
	   exit;
		
	}
	
	
	 public static function staticResources($url,$publicPath,$templatePath) {
		 		 
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		
		switch ($ext) {
			case "css":
				self::sendFile($url,$publicPath,"text/css"); //public path overwrites
				self::sendFile($url,$templatePath,"text/css");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "js":
				self::sendFile($url,$publicPath,"application/javascript"); //public path overwrites
				self::sendFile($url,$templatePath,"application/javascript");
				header("HTTP/1.0 404 Not Found");
				exit;
	        case "jpg":
				self::sendFile($url,$publicPath,"image/jpeg"); //public path overwrites
				self::sendFile($url,$templatePath,"image/jpeg");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "png":
				self::sendFile($url,$publicPath,"image/png"); //public path overwrites
				self::sendFile($url,$templatePath,"image/png");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "ico":
			    self::sendFile($url,$publicPath,"image/x-icon");
				self::sendFile($url,$templatePath,"image/x-icon");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "woff2":
			    self::sendFile($url,$templatePath,"font/woff2");
				header("HTTP/1.0 404 Not Found");
				exit;
			default:
			    if(strlen($ext) > 0)
				{
					header("HTTP/1.0 404 Not Found");
					exit;
				}
				break;
			
		}
		 
	 }
	 
	  public static function sendFile($url,$path,$contentType) {
		
		
		$file = realpath($path . substr($url,1));
		if(!file_exists($file)) return;

		$rPath = realpath($path);
		$sPath = substr(realpath($file), 0, strlen($rPath));
		
		//Check if no relative path attack
		if(strcmp($sPath,$rPath) == 1)
		{
			header('HTTP/1.0 403 Forbidden');
			exit;
		}
		
		$size = filesize($file);
		header("Content-type: $contentType",true);
		header("Content-length: $size");
		readfile($file);
		exit;
		 
	 }
	 
	public static function validateBearerToken($configPath)
	{
		$token = null;
		$headers = apache_request_headers();
	    if(isset($headers['Authorization'])){
			$matches = array();
			preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
			if(isset($matches[1])){
			  $token = $matches[1];
			}
		}
	    
		if($token == null)
		{
			self::sendErrorJson('HTTP/1.0 401 Unauthorized', 'use Authorization Header');
		}
		
		//validate token
		require_once $configPath.'bearer.inc.php';
		
		if(!password_verify($token,$bearer))
        {
		   self::sendErrorJson('HTTP/1.0 401 Unauthorized', 'Unauthorized Access');
        }
		
	}
	
	public static function sendErrorJson($header, $message)
	{  
       $data = array('state' => 'error' ,  'message' => $message);
	   
	   $encoder = new JsonEncoder($data);

   	   header($header);
   	   header('Content-Type: application/json');
	   header("Content-Length: ". $encoder->GetLength());
	   echo $encoder->GetContent();
	   exit;
	}
	
	function sendOkJson($header, $message, $jsondata)
	{
       $data = array('state' => 'ok' ,  'message' => $message);
	   foreach ( $jsondata as $key => $value )
	   {
		   $data[$key] = $value;
	   }
	   
	   $encoder = new JsonEncoder($data);

	   if($encoder->HasError())
	   {
		   $this->error($header, $encoder->GetErrorMessage());
	   }
	   
   	   header($header);
   	   header('Content-Type: application/json');
	   header("Content-Length: ".$encoder->GetLength());
	   echo $encoder->GetContent();
	   exit;
	}

}

?>