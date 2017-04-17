<?php
include 'tpl.php';
$base_dir =getcwd();
$traits=[
	'mvc'=>'\nx\control\mvc',
	'ca'=>'\nx\router\ca',
	'route'=>'\nx\router\route',
	'files'=>'\nx\config\files',
	'ini'=>'\nx\config\ini',
	'memcache'=>'\nx\cache\memcache',
	'redis'=>'\nx\cache\redis',
	'mongo'=>'\nx\cache\mongo',
	'dump'=>'\nx\log\dump',
	'head'=>'\nx\log\header',
	'file'=>'\nx\log\file',
	'view'=>'\nx\response\view',
	'web'=>'\nx\response\web',
	'api'=>'\nx\response\api',
];

/*
      \e[0m 关闭所有属性
      \e[1m 设置高亮度
      \e[4m 下划线
      \e[5m 闪烁
      \e[7m 反显
      \e[8m 消隐
      \e[30m 至 \33[37m 设置前景色
      \e[40m 至 \33[47m 设置背景色
      \e[nA 光标上移n行
      \e[nB 光标下移n行
      \e[nC 光标右移n行
      \e[nD 光标左移n行
      \e[y;xH设置光标位置
      \e[2J 清屏
      \e[K 清除从光标到行尾的内容
      \e[s 保存光标位置
      \e[u 恢复光标位置
      \e[?25l 隐藏光标
      \e[?25h 显示光标

      字背景颜色范围:40----49
      40:黑
      41:深红
      42:绿
      43:黄色
      44:蓝色
      45:紫色
      46:深绿
      47:白色

      字颜色:30-----------39
      30:黑
      31:红
      32:绿
      33:黄
      34:蓝色
      35:紫色
      36:深绿
      37:白色
 */

$color =true;
function color($msg, $fcolor=false, $bcolor=false){
	global $color;
	if(!$color) return $msg;
	if(!$fcolor) return str_replace([
		'[ENTER]',
		'=',
		':',
		'(',
		')',
		'PASS',
	], [
		"\e[1;33m[ENTER]\e[0m",
		"\e[1;30m=\e[0m",
		"\e[1;36m:\e[0m",
		"\e[1;30m(\e[0m",
		"\e[1;30m)\e[0m",
		"\e[1;31mPASS\e[0m",
	], $msg);

	$c =[
		'black'=>'0;30',
		'gray'=>'1;30',
		'red'=>'0;31',
		'light_red'=>'1;31',
		'green'=>'0;32',
		'light_green'=>'1;32',
		'brown'=>'0;33',
		'yellow'=>'1;33',
		'blue'=>'0;34',
		'light_blue'=>'1;34',
		'purple'=>'0;35',
		'light_purple'=>'1;35',
		'cyan'=>'0;36',
		'light_cyan'=>'1;36',
		'light_gray'=>'0;37',
		'white'=>'1;37',
	];
	$_c =isset($c[$fcolor]) ?$c[$fcolor] :'1;37';
	return "\e[{$_c}m".$msg."\e[0m";
}

function l($msg='', $color=false, $eol =PHP_EOL){
	echo color($msg, $color).$eol;
}
function c($msg='', $color=false, $eol =' '){
	echo color($msg, $color).$eol;
}
function ask($msg='', $default='', $color =false){
	c($msg, $color);
	$input =trim(fgets(STDIN));
	return $input==='' ?$default :$input;
}

function askTraits(){
	l(' control  : mvc', 'gray');
	l(' router   : ca|route', 'gray');
	l(' config   : files|ini', 'gray');
	l(' cache    : memcache|redis|mongo', 'gray');
	l(' db       : pdo', 'gray');
	l(' log      : dump|header|file', 'gray');
	l(' response : view|web|api', 'gray');
	c('use traits ([ENTER]=mvc route files pdo file api) :');

	//l("[router]route\t[config]files\t[cache]redis\t[log]file\t[response]view");
	//l("[router]ca\t[config]ini\t[cache]memcache\t[log]header\t[response]web");
	//l("[control]mvc\t[db]pdo\t\t[cache]mongo\t[log]dump\t[response]api");
	return ask('', 'mvc route files pdo file api');
}

l();
c(' nx', 'light_green');
c('falsework');
l('(path='.$base_dir.')', 'gray');
l();
//$agree =ask('are u agree nx license ([ENTER]=yes) [yes|y|no|n] ?');
//if($agree =='' || $agree =='yes' || $agree =='y'){
//	l();
//} else {
//	l();
//	l('good bye !', 'light_red');
//	exit();
//}

switch($argv[1]){
	case 'create':
		$type =isset($argv[2]) ?$argv[2] :'';
		switch($type){
			case '':
			case 'p':
			case 'project':
				$project =[];
				do{
					$project['name'] =ask('project name :');
				} while (strlen($project['name'])===0);
				$project['entry'] =ask('create entry script ([ENTER]=PASS) :');
				//$project['traits'] =ask('use traits (split by [SPACE]) :');
				$project['traits'] =askTraits();

				$setup=[];

				$controllers=[];
				$models=[];
				$views=[];

				$_traits =explode(' ', $project['traits']);
				//筛选出需要use
				$uses=[];
				foreach($_traits as $trait){
					//l($trait, 'light_red');
					switch($trait){
						case 'mvc':
							l('control/mvc', 'light_cyan');
							$loop =1;
							do{
								$name=ask($loop==1 ?'create controller ([ENTER]=PASS) :' :'create other controller ([ENTER]=PASS) :');
								if($name!=='') $controllers[]=$name;
								$loop+=1;
							} while($name!=='');
							$loop =1;
							do{
								$name=ask($loop==1 ?'create model ([ENTER]=PASS) :' :'create other model ([ENTER]=PASS) :');
								if($name!=='') $models[]=$name;
								$loop+=1;
							} while($name!=='');
							$loop =1;
							do{
								$name=ask($loop==1 ?'create view ([ENTER]=PASS) :' :'create other view ([ENTER]=PASS) :');
								if($name!=='') $views[]=$name;
								$loop+=1;
							} while($name!=='');
							break;
						case 'route':
							$setup['router/route']=[
								'rules'=>[
									['get', '', false, ['index', 'index']],
								],
							];
							break;
						//config
						case 'files':
						case 'ini':
							break;
						//cache
						case 'memcache':
							l('cache/memcache', 'light_cyan');
							$host =ask('memcache host ([ENTER]=localhost) ?', 'localhost');
							$port =ask('memcache port ([ENTER]=11211) ?', 11211);
							$setup['cache/memcache']=[
								'default'=>[
									'host'=>$host,
									'port'=>$port,
									'timeout'=>1,
								],
							];
							break;
						case 'redis':
							l('cache/redis', 'light_cyan');
							$host =ask('redis host ([ENTER]=localhost) ?', 'localhost');
							$port =ask('redis port ([ENTER]=6379) ?', 6379);
							$sel =ask('redis select db ([ENTER]=0) ?', 0);
							$setup['cache/redis']=[
								'default'=>[
									'host' => $host,
									'port' => $port,
									'select' => $sel,
									'timeout' =>3,
								],
							];
							break;
						case 'mongo':
							break;
						//db
						case 'pdo':
							l('db/pdo', 'light_cyan');
							$type =ask('db type ([ENTER]=mysql) ?', 'mysql');
							$host =ask('db host ([ENTER]=localhost) ?', 'localhost');
							$port =ask('db port ([ENTER]=3306) ?', 3306);
							$db =ask('db name ([ENTER]=demo) ?', 'demo');
							//$charset =ask('db charset ([ENTER]=utf8) ?');
							$username =ask('db username ([ENTER]=root) ?', 'root');
							$password =ask('db password ?');
							$setup['db/pdo']=[
								'default'=>[
									'dsn'=>"{$type}:dbname={$db};host={$host};port={$port};charset=utf8",//'mysql:dbname=demo;host=localhost;charset=utf8',
									'username'=>$username,
									'password'=>$password,
									'options'=>[],
								],
							];
							break;
						//log
						case 'file':
							l('log/file', 'light_cyan');
							$line =ask('line format ([ENTER]="{create} {var}") ?', "{create} {var}");
							$path =ask('line format ([ENTER]="./logs/") ?', "./logs/");
							$setup['log/file']=[
								'line'=>$line,
								'path'=>$path,
							];
							break;
						case 'dump':
						case 'header':
						case 'view':
						case 'web':
						case 'api':
							break;
						case 'ca':
						default:
							break;
					}
					isset($traits[$trait]) and $uses[]=$traits[$trait];
				}
				l();
				l('create files :');
				l(' ./'.$project['name'].'/app.php', 'gray');
				if(count($setup)>0) l(' ./'.$project['name'].'/setup.php', 'gray');
				if(strlen($project['entry'])>0) l(' ./'.$project['entry'], 'gray');
				$controls=[];
				foreach($controllers as $controller){
					$controls[]=['file'=>'./'.$project['name'].'/controllers/'.$controller.'.php', 'filename'=>$controller];
					l(' ./'.$project['name'].'/controllers/'.$controller.'.php', 'gray');
				}
				$mods=[];
				foreach($models as $model){
					$mods[]=['file'=>'./'.$project['name'].'/models/'.$model.'.php', 'filename'=>$model];
					l(' ./'.$project['name'].'/models/'.$model.'.php', 'gray');
				}
				$views2=[];
				foreach($views as $view){
					$views2[]=['file'=>'./'.$project['name'].'/views/'.$view.'.php', 'filename'=>$view];
					l(' ./'.$project['name'].'/views/'.$view.'.php', 'gray');
				}

				l();
				$create =ask('are u sure ([ENTER]=yes) [yes|y|no|n] ?');
				if($create =='' || $create =='yes' || $create =='y'){
					l();
					l('todo: make file', 'green');
					$project_path='./'.$project['name'].'/';
					saveFile($project_path.'setup.php', $setup);
					//有web入口
					$tpl2=false;
					if(!empty($project['entry']) && strrpos($project['entry'], '.php')){
						saveFile('./'.$project['entry'], str_replace(['{name}'], [$project['name']], $web_tpl), false);
						$tpl2=true;
					}
					if(count($uses)>0) $uses_str='use '.implode(",\n", $uses).';';
					saveFile($project_path.'app.php', str_replace(['{name}', '{uses}'], [$project['name'], $uses_str], $tpl2 ? $app_tpl2 : $app_tpl), false);
					//生成控制器
					foreach($controls as $item){
						saveFile($item['file'], str_replace(['{name}', '{control}'], [$project['name'], $item['filename']], $controller_tpl), false);
					}
					//生成models
					foreach($mods as $item){
						saveFile($item['file'], str_replace(['{name}', '{model}'], [$project['name'], $item['filename']], $model_tpl), false);
					}
					//生成views
					foreach($views2 as $item){
						saveFile($item['file'], $view_tpl, false);
					}
				} else {
					l();
					l('good bye !', 'light_red');
					exit();
				}
				break;
			default:
				l("无法确认你要创建的是什么……");
				break;
		}


	case 'select':
		break;
	default:
		l("不支持的命令，支持的命令如下：");
		l();
		l("  create [project|controller|model|view]");
		l("  select [project]");
		break;
}
function saveFile($filename, $content, $isarr=true){
	is_dir(dirname($filename)) or mkdir(dirname($filename), 0777, true);
	if($isarr) $content ="<?PHP\n return " . var_export($content, True) . ";";
	return file_put_contents($filename, $content);
}


