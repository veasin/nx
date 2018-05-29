<?php
namespace nx\fs;

class file{
	public static function makeDir($path, $mod =777){
		$dir =dirname($path);
		if(!is_dir($dir)) self::makeDir($dir, $mod);
		return @mkdir($path, $mod);
	}

	public static function scanPath2Array($Path, $flat =false, $scanSub =true, $WithStats =false){
		static $_level =0;
		$_path =realpath($Path);
		if (!is_dir($_path)) return null;
		$_files =array();
		//$_list =array($Path=>0);
		$_list =array();
		$_handle =opendir($_path);
		while (false !== ($_file =readdir($_handle))){
			if ($_file == '.' || $_file == '..') continue;
			$__path =$_path . DIRECTORY_SEPARATOR . $_file;
			
			if (is_dir($__path)){
				if ($scanSub){
					$_level ++;
					$__files =self::scanPath2Array($__path, $flat, $scanSub, $WithStats);
					$_level --;
					if (is_array($__files)) {
						if ($flat) $_list +=$__files ;
							else $_files[$_file] =$__files;
					}
				} else if(!$flat) $_files['d'][] =$_file;
				continue;
			} else $_list[$__path] =0;
			if (!$flat) {
			if ($WithStats) {
				$s =array_slice(stat($__path), 15);
				unset($s['uid'], $s['gid'], $s['nlink'], $s['rdev'], $s['blksize'], $s['blocks'], $s['atime']);
				$_files['f'][$_file] =$s;
				clearstatcache();
			} else $_files['f'][] =$_file;
			}

		}
		closedir($_handle);
		return $flat ? ($_level ==0) ?array_keys($_list) : $_list :$_files;
	}
	public static function scanPathOld($Path, $Skeletonize='', $WithPath=false){
		$_path =realpath($Path);
		if (!$_path) return null;
		
		if ($Skeletonize =='') $Skeletonize =$_path;
		$_s =strlen($Skeletonize) +1;
		
		$_files =array();
		$_handle =opendir($_path);
		while (false !== ($_file =readdir($_handle))){
			if ($_file == '.' || $_file == '..') continue;
			$__path =$_path . DIRECTORY_SEPARATOR . $_file;
			if (is_dir($__path)){
				$__files =self::scanPath($__path, $Skeletonize, $WithPath);
				if (is_array($__files)) $_files =array_merge($_files, $__files);
				continue;
			}
			$_files[] =$WithPath ?$__path :substr($__path, $_s);
		}
		closedir($_handle);
		return $_files;
	}
	/**
	 * DIRECTORY_SEPARATOR -> / !!!!!
	 * Enter description here ...
	 * @param string $Path
	 * @param string $Skeletonize
	 * @param string $WithPath
	 * @param string $Callback
	 * @param string $CallbackArgs
	 * @return NULL|array:string Ambigous <unknown, string>
	 */
	public static function scanPath($Path, $Skeletonize ='', $WithPath=false, $Callback =null, $CallbackArgs =null){
		$_path =realpath($Path);
		if (!$_path) return null;
		$_path =str_replace(DIRECTORY_SEPARATOR, '/', $_path);
	
		if ($Skeletonize =='') $Skeletonize =$_path;
		$_s =strlen($Skeletonize) +1;
	
		$_files =array();
		$_handle =opendir($_path);
		while (false !==($_file =readdir($_handle))){
			if ($_file =='.' ||$_file =='..') continue;
			$__path =$_path .'/' .$_file;
			if (is_dir($__path)){
				$__files =self::scanPath($__path, $Skeletonize, $WithPath, $Callback, $CallbackArgs);
				if (is_array($__files)) $_files =array_merge($_files, $__files);
				continue;
			}
			$p =$WithPath ?$__path :substr($__path, $_s);
			if (is_callable($Callback)){
				$r =call_user_func($Callback, $_path, $_file, $CallbackArgs, $p);
				if ($r) $_files[] =$r;
			}else $_files[] =$p;
		}
		closedir($_handle);
		return $_files;
	}
	public static function zip($BasePath='./', $Files =array(), $zip2 ='', $Comment=null, $NoOverwrite =false){
		if (!$NoOverwrite && file_exists($zip2)) return false;
		//$_path =dirname($BasePath).'/';
		//$_path =realpath($_path).DIRECTORY_SEPARATOR;
		$_len =strlen($BasePath);
		
		$_files =array();
		if (is_array($Files)){
			foreach ($Files as $_file){
				if (file_exists($BasePath.$_file)) $_files[] =$BasePath.$_file;
			}
		}
		if (count($_files)){
			$zip =new \ZipArchive();
			if ($zip->open($zip2, $NoOverwrite ?ZipArchive::OVERWRITE :ZipArchive::CREATE) != true){
				return false;
			}
			foreach ($_files as $_file){
				$zip->addFile($_file, substr($_file, $_len));
			}
			if(!empty($Comment)) $zip->setArchiveComment($Comment);
			$zip->close();
			return file_exists($zip2);
		} else
			return false;
	}
	public static function zipComment($zip2, $Comment=null){
		$zip =new \ZipArchive();
		$res =$zip->open($zip2);
		if ($res !==true) return $res;
		if (is_null($Comment)) return $zip->getArchiveComment();
		else $zip->setArchiveComment($Comment);
		$zip->close();
	}
	public static function zipExtract($zip2, $Path){
		$zip =new \ZipArchive();
		if ($zip->open($zip2) !== true) return false;
		$ok =$zip->extractTo($Path);
		$zip->close();
		return $ok;
	}
	public static function download($FileName, $Dname =null){
		if (is_file($FileName) && file_exists($FileName)){
			header('Content-length: ' . filesize($FileName));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . (empty($Dname) ? basename($FileName) :$Dname) . '');
			readfile($FileName);
		} else{
			echo "Download Nothing!";
		}
	}
	/**
	 * 保存内容到文件
	 *
	 * @param string $FileName 文件名
	 * @param string $Content 文件内容
	 * @param boolean $UnlinkFile 文件存在时是否删除存在文件
	 * @param string $Mode 文件mod
	 * @return boolean 是否保存成功
	 */
	static public function saveContent($FileName, $Content ='', $UnlinkFile =false, $Mode ='wb'){
		if (is_file($FileName) && $UnlinkFile) unlink($FileName);
		$r_file =fopen($FileName, $Mode);
		if ($r_file){
			flock($r_file, LOCK_EX);
			fwrite($r_file, $Content);
			flock($r_file, LOCK_UN);
			fclose($r_file);
			return true;
		}
		return false;
	}
	/**
	 * 保存内容为php文件
	 *
	 * @param string $FileName 文件名
	 * @param string $Var 变量名
	 * @param string $Content 变量内容
	 * @param boolean $UnlinkFile 文件存在时是否删除存在文件
	 * @param string $Mode 文件mod
	 * @return boolean 是否保存成功
	 */
	static public function save2PHP($FileName, $Var, $Content, $UnlinkFile =false, $Mode ='wb'){
		$r =var_export($Content, True);
		$find =array("=>
", "
    ", "
  ),", " ");
		$replace =array("=>", "", "),", "");
		$r =str_replace($find, $replace, $r);
		return self::saveContent($FileName, "<?PHP\n\${$Var} = " . $r . ";", $UnlinkFile, $Mode);
	}
	static public function saveReturnFile($FileName, $Content){
		$r =var_export($Content, True);
		$find =array("=>
", "
    ", "
  ),");
		$replace =array("=>", "", "),");
		$r =str_replace($find, $replace, $r);
		return file_put_contents($FileName, "<?PHP\n return " . $r . ";");
	}
}
?>