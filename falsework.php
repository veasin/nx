<?php
$base_dir =getcwd().DIRECTORY_SEPARATOR;
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
				$project['entry'] =ask('create entry script ([ENTER]=./'.$project['name'].'.php) :', ''.$project['name'].'.php');
				if(strlen($project['entry'])>0){
					$project['composer'] =ask('use composer autoload ([ENTER]=no) [yes|y|no|n] ?', 'no');
					$project['composer'] =($project['composer'] =='yes' || $project['composer'] =='y');
				}

				//$project['traits'] =ask('use traits (split by [SPACE]) :');
				$project['traits'] =askTraits();

				$setup=[];

				$controllers=[];
				$models=[];
				$views=[];

				$_traits =explode(' ', $project['traits']);
				$project['traits'] =[];
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
					isset($traits[$trait]) && $project['traits'][]=$traits[$trait];
				}
				l();
				l('create files :');
				l(' ./'.$project['name'].'/app.php', 'gray');
				if(count($setup)>0) l(' ./'.$project['name'].'/setup.php', 'gray');
				if(strlen($project['entry'])>0){
					if(substr($project['entry'], -4)==='.php') l(' ./'.$project['entry'], 'gray');
					elseif(substr($project['entry'], -1)==='/') l(' ./'.$project['entry'].$project['name'].'.php', 'gray');
					else l(' ./'.$project['entry'].'.php', 'gray');
				} else l(' ./'.$project['name'].'.php', 'gray');
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
					//l('make file:', 'green');
					saveProject($project, $setup);
					foreach($controllers as $controller){
						saveController($controller, $project);
					}
					foreach($models as $model){
						saveModel($model, $project);
					}
					foreach($views as $view){
						saveView($view, $project);
					}
					l();
					l('all done. good bye !', 'green');
					l();
					exit();
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


	default:
		l("不支持的命令，支持的命令如下：");
		l();
		l("  create [project|controller|model|view]");
		l("  select [project]");
		break;
}
function makeDir($filename){
	global $base_dir;
	$filename =$base_dir.$filename;
	is_dir(dirname($filename)) || mkdir(dirname($filename), 0777, true);
}
function saveFile($filename, $content, $replace =[]){
	global $base_dir;
	makeDir($filename);
	if(!empty($replace)) $content =str_replace(array_keys($replace), array_values($replace), $content);
	c($filename, 'gray');
	$ok =file_put_contents($base_dir.$filename, $content);
	l($ok ?'done' :'faild', $ok ?'green':'red');
	return $ok;
}
function relativePath($from, $to){
	$arr1 = explode(DIRECTORY_SEPARATOR, realpath($from));
	$arr2 = explode(DIRECTORY_SEPARATOR, realpath($to));
	$intersection = array_intersect_assoc($arr1, $arr2);
	$depth =count($intersection);
	return implode('/', array_merge(array_fill(0, count($arr1)-$depth, '..'), array_slice($arr2, $depth)));
}

function saveProject($project, $setup){
	$path='./'.$project['name'].'/';
	//app
	$app_tpl="<?php\nnamespace {name};\n\nclass app extends \\nx\\app{\n	{uses}\n	public \$path =__DIR__;\n}";
	saveFile($path.'app.php', $app_tpl, [
		'{name}'=>$project['name'],
		'{uses}'=>(count($project['traits'])>0) ?"use ".implode(",\n\t\t", $project['traits']).';' :'',
	]);
	//setup
	$r =var_export($setup, True);
	$find =array(" => ", "
    ", "),", "array (", "\n         ", "\n        ", "],\n      ]");
	$replace =array("=>", "", "],", "[", "", "", "]]");
	$r =str_replace($find, $replace, $r);
	$r =preg_replace('/=>\s+\[/m', '=>[', $r);
	$r =preg_replace('/\[\s+\]/m', '[]', $r);
	$r =preg_replace('/\d+=>/m', '', $r);
	$r[strlen($r)-1] =']';
	saveFile($path.'setup.php', "<?php\nreturn {$r};");
	//entry
	$file =$project['name'].'.php';
	if(strlen($project['entry'])>0){
		if(substr($project['entry'], -4) ==='.php'){
			$file =$project['entry'];
		}elseif(substr($project['entry'], -1) ==='/'){
			$file =$project['entry'].$project['name'].'.php';
		}else{
			$file =$project['entry'].'.php';
		}
	}
	$entry_tpl=<<<PHP
<?php
define('AGREE_LICENSE', true);//同意框架协议
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区

{loader}

\$setup =include("{appdir}/setup.php");//加载配置
\{name}\app::factory(\$setup)->run();//生成app并执行
PHP;

	$nxload =<<<PHP
require '{nxdir}/autoload.php';//框架自动加载路径，可使用composer替换
\\nx\autoload::register([
	'{name}'=>['{appdir}'],//声明命名空间为当前目录
]);//自动加载注册，可在其中指定命名空间第一段指向目录
PHP;
	$composer=<<<PHP
\$loader = require '{crdir}/autoload.php';
\$loader->addPsr4('{name}\\\\', '{appdir}');
PHP;

	$entry_tpl =str_replace('{loader}', $project['composer'] ?$composer :$nxload, $entry_tpl);

	makeDir('./'.$file);
	saveFile('./'.$file, $entry_tpl, [
		'{name}'=>$project['name'],
		'{appdir}'=>relativePath(dirname($file), $path),
		'{nxdir}'=>relativePath(dirname($file), dirname($_SERVER['PHP_SELF'])),
		'{crdir}'=>relativePath(dirname($file), dirname(dirname(dirname($_SERVER['PHP_SELF'])))),
	]);
}
function saveController($controller, $project){
	$tpl="<?php\nnamespace {name}\\controllers;\n\nclass {control} extends \\nx\\mvc\\controller{\n	public function onIndex(){\n		echo 'default index';\n	}\n}";
	saveFile('./'.$project['name'].'/controllers/'.$controller.'.php', $tpl, [
		'{name}'=>$project['name'],
		'{control}'=>$controller,
	]);
}
function saveModel($model, $project){
	$tpl="<?php\nnamespace {name}\\models;\n\nclass {model} extends \\nx\\mvc\\model{\n	use \\nx\\db\\pdo, \\nx\\db\\table;\n\n	public function isOk(){\n		return 'ok';\n	}\n}";
	saveFile('./'.$project['name'].'/models/'.$model.'.php', $tpl, [
		'{name}'=>$project['name'],
		'{model}'=>$model,
	]);
}
function saveView($view, $project){
	$tpl="<!doctype html>\n<html lang=\"zh_cn\">\n<head>\n	<meta charset=\"UTF-8\">\n	<title>View</title>\n</head>\n<body>\n	view template ~\n</body>\n</html>";
	saveFile('./'.$project['name'].'/views/'.$view.'.php', $tpl);
}