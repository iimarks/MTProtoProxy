<?php
/***************************************/
/***************************************/
// گروه نرم افزاری وی کن
// WeCan-Co.ir | @WeCanGP
/*************Copy Right**************************/
// سو استفاده از این فایل و تغییر به نام خود و نقض حق سازنده شرعا حرام و غیرقانونی و عملی غیرانسانی است
/***************************************/

@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);
@ini_set("log_errors", 1);
@ini_set("error_log", getcwd()."/php-error.log");
@session_start();

	/*********** Functions *************/
	function execInBackground($cmd,$debug=false){
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			if($debug){
				exec($cmd,$res);
				//print_r($res);
				echo "<b>Debug:</b> <br>\n";
				echo implode('<br>',$res);
				echo "\n<hr>\n";
			}else{
				exec($cmd . " > /dev/null &",$res);
			}
			//exec($cmd . " > ".time().".txt &");
		}
		
	}
	function GetProcessLinux($UserBotF=""){
		$count = 0;
		$psRes = [];
		if($UserBotF==""){
			$UserBotF = getcwd().'/'.__FILE__;
			$UserBotF = __FILE__;
		}
		try{
			exec("ps aux", $psRes);
			//$psResS = implode("\n",$psRes);
			$UserBotF = explode("/",$UserBotF);
			$UserBotF = end($UserBotF);
			foreach($psRes as $key => $processLine){
				if((strpos($processLine, $UserBotF) !== false)){
					$count++;
				}else{
					unset($psRes[$key]);
				}
			}
		} catch (Exception $e) { 
			$psRes = ['error' => $e->getMessage()];
		}
		
		$psRes['count'] = $count;
		return $psRes;
	}
	
	
	
	/*********** Main *************/
	
	if(!function_exists('exec')){
		echo "exec function not enabled on your host!";
		exit();
	}
	
	$msg = '';
	
	start:
	$allowedCount = 1;
	$RunnedCount = GetProcessLinux()['count'];
	
	$wsock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	socket_connect($wsock, "8.8.8.8", 53);
	socket_getsockname($wsock, $name); // $name passed by reference
	$localAddr = $name;
					
	$RunInTerminal = false;
	if( (isset($_SERVER['SESSIONNAME']) && strpos(strtolower($_SERVER['SESSIONNAME']), 'console') !== false) || 
		isset($_SERVER['SHELL']) || 
		(isset($_SERVER['SHLVL']) && trim($_SERVER['SHLVL']) == '1' ) ||
		(isset($_SERVER['_']) && trim($_SERVER['_']) == '/bin/php' ) ||
		(isset($_SERVER['_']) &&  strpos(strtolower($_SERVER['_']), 'php') !== false ) || 
		(isset($_SERVER['ComSpec']) && strpos(strtolower($_SERVER['ComSpec']), 'cmd.exe') !== false ) ){
		$RunInTerminal = true;
	}
	
	if($RunInTerminal){
		$port = 8080;
		$secret = md5('WeCanGP');
		$tag = md5('WeCanCo');
		$RunnedFrom = "terminal";
		
		if(file_exists('.conf')){
		    $conf = json_decode(file_get_contents('.conf'),true);
		    $port = $conf['port'];
		    $secret = $conf['secret'];
		    $tag = $conf['tag'];
		}
		
		if(isset($argv[1]) && trim($argv[1]) != ""){
			$port = trim($argv[1]);
		}
		
		if(isset($argv[2]) && trim($argv[2]) != ""){
			$secret = trim($argv[2]);
		}
		if(isset($argv[3]) && trim($argv[3]) != ""){
			$tag = trim($argv[3]);
		}
		
		
		if(isset($argv[4]) && trim($argv[4]) != ""){
			$RunnedFrom = trim($argv[4]);
		}
		
		
		if(strtolower($RunnedFrom) == 'web' || file_exists('.runbyweb')){
			$allowedCount++;
		}
		
		if(file_exists('.runbyweb')){
		    unlink('.runbyweb');
		}
		
		if( $RunnedCount > $allowedCount){
			echo "\nProxy is Runnig!\n";
			exit();
		}
		
		if(!file_exists('madeline.phar')){
			$phar = file_get_contents('http://madeline.wecan-co.ir/files/madeline.phar?v=new');
			file_put_contents('madeline.phar', $phar);
			unset($phar);
		}
		require_once 'madeline.phar';
		
		
		
		echo "Loading MTProxy..."."\n";
		
		file_put_contents('.lastrun',time());
		
		$settings = 
		[
			'logger' => [
				'logger' => 0
			],
			'app_info' => [
				'device_model' => 'WeCan MTProtoProxy',
				'system_version' => ''.rand(1,10),
				'app_version' => 'MTProxy',
				'lang_code' => 'fa',
				'api_id' => 6,
				'api_hash' => 'eb06d4abfb49dc3eeb1aeb98ae0f581e'
			],

		];
	
		$MadelineProto = new \danog\MadelineProto\API('.WeMTProxy.sec',$settings);
		$MadelineProto->parse_dc_options($MadelineProto->help->getConfig()['dc_options']);
		$handler = new \danog\MadelineProto\Server(
		[
			'type' => AF_INET,
			'protocol' => 0,
			//'address' => '0.0.0.0',
			'address' => $localAddr,
			'port' => $port,
			'handler' => '\danog\MadelineProto\Server\Proxy',
			'extra' => [
					'madeline' => $MadelineProto->API->datacenter->sockets, 
					'proxy-tag' => $tag, 
					'secret' => hex2bin($secret), 
					'timeout' => 10
				]
		]
		);
		$handler->start();

	
	}else{
		if(!isset($_SESSION['pass'])){
			if(isset($_POST['pass']) && $_POST['pass']=='WeCanCoMT'){
				$_SESSION['pass'] = $_POST['pass'];
			}else{
			?>
				<form action="" method="post">
					<input name="pass" type="text" placeholder="Password..." value="" />
					<input type="submit" value="Login" />
				</form><br>
			<?php
				exit();
			}
		}
		if(isset($_POST['mksecret'])){
			$w = $_POST['w'];
			if($w == ""){
				$w = "WeCan".rand(100,1000).rand(777,999)."GP";
			}
			echo "Secret Key: <b>".md5($w)."</b><br>\n";
			?>
			<form action="" method="post">
				<input name="back" type="submit" value="Back" />
			</form>
			<?php
		}if(isset($_POST['runproxy'])){
			$file = explode("/",__FILE__);
			//$comm = "cd ".getcwd()." && php ". end($file) ." ".$_POST['port']." ".$_POST['secret']." ".$_POST['tag']." web";
			if($_POST['tag'] == ""){
				$_POST['tag'] = md5('WeCanCo');
			}
			
			$debug = false;
			if(isset($_POST['debug']) && $_POST['debug']=='yes'){
				$debug = true;
			}
			
			$conf['port'] = $_POST['port'];
    		$conf['secret'] = $_POST['secret'];
    		$conf['tag'] = $_POST['tag'];
    		file_put_contents('.conf',json_encode($conf));
    		file_put_contents('.runbyweb','');

			$comm = "php ". __FILE__ ." ".$_POST['port']." ".$_POST['secret']." ".$_POST['tag']." web";
			execInBackground($comm,$debug);
			unset($_POST);
			$msg = "check";
			sleep(5);
			goto start;
			
		}if(isset($_POST['fstop'])){
			$file = explode("/",__FILE__);
			execInBackground("ps aux | grep '". end($file) ."' | awk '{print $2}' | xargs kill");
		}else{
			?>
			<h2>WeCan MTProtoProxy</h2>
			<span style="color:blue;"><?php
			if($msg == 'check'){
			    if( $RunnedCount > $allowedCount){ 
			         $msg ='';
			    }else{
			        $msg ='Can\'t Run Proxy! Check php-error.log file.';
			    }
			}
			echo $msg; 
			?></span><br>
			<?php if( $RunnedCount > $allowedCount){ 
					$conf = json_decode(file_get_contents('.conf'),true);
					$lastrun = file_get_contents('.lastrun');
				?>
				<form action="" method="post">
					<span>
					MTProtoProxy is Runned at <b><?php echo date('Y-m-d H:i:s',$lastrun); ?></b> on <b><?php echo $localAddr; ?></b> with port <b><?php echo $conf['port'] ?></b> . <br>
					secret key is <code><?php echo $conf['secret']; ?></code> 
					</span>
					<input name="fstop" type="submit" value="Force Stop" />
				</form><br>
			<?php }else{ ?>
			        <span>MTProtoProxy is Offline. </span>
			<?php }?>
			
			<hr>
			<form action="" method="post">
				<input name="w" type="text" placeholder="type any word..." value="" />
				<input name="mksecret" type="submit" value="Generate Secret Key" />
			</form><br>
			
			<hr>
			<form action="" method="post">
				<input name="port" type="number" placeholder="Proxy Port..." value="8080" /><br>
				<input name="secret" type="text" placeholder="Secret..." value="<?php echo md5('WeCanCo'); ?>" /><br>
				<input name="tag" type="text" placeholder="Proxy Tag..." value="" /><br>
				Debug: <input name="debug" type="checkbox" value="yes" /><br>
				<input name="runproxy" type="submit" value="Run Proxy" />
			</form><br>
			
			Ready By: <a target="_new" href='http://WeCan-Co.ir'>WeCanCo</a>
			<?php
		}
	
	
	}
	
	
