<?php

namespace Framework\Library\Process;

/**
 * 系统扩展器
 * Class Extend
 * @package Framework\Library\Process
 */
class Extend
{

    /**
     * 包路径
     * @var string
     */
    public $PackagePath;

    /**
     * 类路径
     * @var string
     */
    public $ClassPath;

    /**
     * 已加载的扩展容器
     * @var string
     */
    public $Extendbox;

    /**
     * 初始化相关路径
     * Extend constructor.
     */
    public function __construct()
    {
        $this->PackagePath = Running::$framworkPath . 'Extend/Package/';
        $this->ClassPath = Running::$framworkPath . 'Extend/Class/';
        if(file_exists(Running::$framworkPath . 'vendor/autoload.php')) require_once Running::$framworkPath . 'vendor/autoload.php';
    }

    /**
     * 加入新的扩展包
     * @param string $PackageName
     */
    public function addPackage($PackageName='')
    {
        if(!empty($PackageName) && file_exists($this->PackagePath . $PackageName)){
            $PackageName = $this->PackagePath . $PackageName;
            $extension = self::get_extension($PackageName);
            if(strtolower($extension)  == 'php'){
                include_once $PackageName;
                return true;
            }
            if(in_array($extension,['zip','tar'])){
                $Packagezip = $this->getPackageName($PackageName);
                $this->releasePackage($PackageName,$this->PackagePath.'Cache',$Packagezip);
            }
        }
        return false;
    }

    /**
     * 加入新的扩展类
     * @param string $ClassName
     */
    public function addClass($ClassName='')
    {
        if(!empty($ClassName) && file_exists($this->ClassPath . $ClassName)){
            include_once $this->ClassPath . $ClassName;
        }
        return false;
    }

    /**
     * 获取扩展信息
     * @param $file
     * @return mixed
     */
    public static function get_extension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * 获取zip包信息
     */
    private function getPackageInfo($infoPath='')
    {
        if(file_exists($infoPath)){
            return include $infoPath;
        }
        return false;
    }

    /**
     * 释放压缩文件
     * @param string $zipfile
     * @param string $folder
     */
    private function releasePackage($zipfile='',$folder='',$Packagezip)
    {
        if($this->iszipload($folder,$Packagezip)){
            return true;
        }
        if(class_exists('ZipArchive',false)){
            $zip = new \ZipArchive;
            $res = $zip->open($zipfile);
            if ($res === TRUE) {
                $zip->extractTo($folder);
                $zip->close();
                $this->iszipload($folder,$Packagezip);
            } else {
                $error = [
                    'file' => $zipfile,
                    'message' => "'{$zipfile}' 读取文件失败!"
                ];
                \Framework\App::$app->get('LogicExceptions')->readErrorFile($error);
            }
        }else{
            $error = [
                'file' => $zipfile,
                'message' => "你需要先启动 PHP-ZipArchive 扩展!"
            ];
            \Framework\App::$app->get('LogicExceptions')->readErrorFile($error);
        }
        return true;
    }

    /**
     * 加载扩展文件
     * @param $folder
     * @param $Packagezip
     */
    private function iszipload($folder,$Packagezip)
    {
        $autoload = $folder . '/' . $Packagezip . '/autoload.php';
        if(file_exists($autoload)){
            include_once $autoload;
            $this->Extendbox[$Packagezip] = $this->getPackageInfo($folder.$Packagezip.'/info.php');
            file_put_contents($folder . '/' . $Packagezip.'/marked.txt','This is an automatically unpacked package. Please do not manually modify or delete it!  - PHP300Framework2.0');
            return true;
        }
        return false;
    }

    /**
     * 返回包名
     * @param $Package
     * @return bool|mixed
     */
    private function getPackageName($Package)
    {
        $extension = self::get_extension($Package);
        $path = explode('Package/',$Package);
        if(isset($path[1])){
            return str_replace(array('.',$extension),'',$path[1]);
        }
        return false;
    }

    /**
     * 返回已加载的包信息
     * @return string
     */
    public function getPackagebox()
    {
        return $this->Extendbox;
    }
}