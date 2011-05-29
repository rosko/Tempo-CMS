<?php

class FileManagerController extends Controller
{
    public function actionIndex()
    {
        $this->render('index');
    }

    public function getVolumes()
    {
        return array(
            'files'=>Yii::getPathOfAlias('webroot.files'),
            'templates'=>Yii::getPathOfAlias('local.templates'),
            'themes'=>Yii::getPathOfAlias('webroot.themes'),
            //'local'=>Yii::getPathOfAlias('webroot'),
        );
    }

    public function restrictedFileTypes()
    {
        return array(
            'php'
        );
    }

    protected function filterPath($path)
    {
        $path = str_replace(array('/..','../'),'/',$path);
        if ($path=='..') $path = '/';
        return $path;
    }

    protected function filterName($name)
    {
        $restrictedChars = array(
            '&', '?', '|', '*', '"', "'", ':', ';', '=', '\\', '/', '`', 
        );
        return str_replace($restrictedChars, '', $name);
    }

    public function actionFileList($volume='files',$path='/')
    {
        $volumes = $this->getVolumes();
        $path = $this->filterPath($path);
        if (!isset($volumes[$volume])) $volume = 'files';
        echo CJavaScript::jsonEncode(self::getFileList($volumes[$volume].DIRECTORY_SEPARATOR.$path, 0, array(
            'excludeHidden'=>true,
            'excludeFolders'=>array(
                'thmb',
            ),
            'excludeFileTypes'=>$this->restrictedFileTypes(),
        )));
    }

    public function actionCreate($volume, $path, $type)
    {
        $volumes = $this->getVolumes();
        $ret = false;
        $path = $this->filterPath($path);
        $breadcrumbs = explode(DIRECTORY_SEPARATOR, $path);
        $name = $this->filterName($breadcrumbs[count($breadcrumbs)-1]);
        if (in_array(pathinfo($name, PATHINFO_EXTENSION),$this->restrictedFileTypes())) {
            $name .= '_';
        }
        $filePath = $volumes[$volume].dirname($path).DIRECTORY_SEPARATOR.$name;
        if (!file_exists($filePath)) {
            if ($type == 'folder') {
                 if (mkdir($filePath, 0777)) {
                    chmod($filePath, 0777);
                    $ret = $name;
                }
            } elseif ($type == 'file') {
                if (file_put_contents($filePath, '', FILE_APPEND)!==false) {
                    chmod($filePath, 0666);
                    $ret = $name;
                }
            }
        }
        echo $ret;
    }

    public function actionDelete($volume, $path)
    {
        $volumes = $this->getVolumes();
        $ret = true;
        $path = $this->filterPath($path);
        $filePath = $volumes[$volume].$path;
        if (file_exists($filePath)) {
            if (is_dir($filePath)) {
                $ret = $ret && self::removeDir($filePath);
            } else {
                $ret = $ret && unlink($filePath);
            }
        }
        echo $ret ? 1 : 'error';
    }

    public function actionRename($volume, $path, $newName)
    {
        $volumes = $this->getVolumes();
        $path = $this->filterPath($path);
        $breadcrumbs = explode(DIRECTORY_SEPARATOR, $path);
        $name = $breadcrumbs[count($breadcrumbs)-1];
        $ret = $name;
        $newName = $this->filterName($newName);
        if (!in_array(pathinfo($newName, PATHINFO_EXTENSION),$this->restrictedFileTypes())) {
            $filePath = $volumes[$volume].$path;
            $newFilePath = dirname($filePath).DIRECTORY_SEPARATOR.$newName;
            if ($filePath == $newFilePath) {
                $ret = $newName;
            }
            elseif (file_exists($filePath)) {
                if (@rename($filePath, $newFilePath)) {
                    $ret = $newName;
                }
            }
        }
        echo $ret;
    }

    public static function getFileList($path, $level=-1, $options=array())
    {
        $found = file_exists($path) ? scandir($path) : array();
        $files = array();
        $folders = array();
        sort($found);
        foreach ($found as $i => $file) {
            if ($file == '.' ||
                $file == '..') continue;
            if ($options['excludeHidden'] && substr($file,0,1) == '.') continue;
            if (is_dir($path.DIRECTORY_SEPARATOR.$file)) {
                if (isset($options['excludeFolders']) && in_array($file, $options['excludeFolders'])) continue;
                // ToDO: добавить is_writable
                $tmp = array(
                    'attr'=>array(
                        'rel'=>'folder',
                        'rev'=>$file,
                    ),
                    'data'=>$file,
                );
                if ($level)
                    $tmp['children'] = self::getFileList($path.DIRECTORY_SEPARATOR.$file, $level-1, $options);
                else
                    $tmp['state'] = 'closed';
                if ($options['foldersFirst'])
                    $folders[] = $tmp;
                else
                    $files[] = $tmp;
            } else {
                $info = pathinfo($file);
                if ((isset($options['excludeFiles']) && in_array($file, $options['excludeFiles'])) ||
                    (isset($options['excludeFileTypes']) && in_array(strtolower($info['extension']),$options['excludeFileTypes']))) continue;
                if (is_array($options['fileTypes']) && 
                    !in_array(strtolower($info['extension']),$options['fileTypes'])) continue;
                // ToDO: добавить is_writable
                $files[] = array(
                    'attr'=>array(
                        'rel'=>'file',
                        'rev'=>$file,
                    ),
                    'metadata'=>(is_writable($path.DIRECTORY_SEPARATOR.$file)?'':'readonly'),
                    'data'=>$file,
                );
            }
        }
        foreach ($folders as $folder) {
            array_unshift($files, $folder);
        }
        return $files;
    }

    public static function removeDir($dir, $recursive=true)
    {
        $ret = true;
        if (is_dir($dir)) {
            if ($recursive) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir.DIRECTORY_SEPARATOR.$object))
                            $ret = $ret && self::removeDir($dir.DIRECTORY_SEPARATOR.$object, $recursive);
                        else
                            $ret = $ret && @unlink($dir.DIRECTORY_SEPARATOR.$object);
                    }
                }
            }
            $ret = $ret && @rmdir($dir);
        }
        return $ret;
    }


}