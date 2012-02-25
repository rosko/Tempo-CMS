<?php
/**
 * FilesystemWrapper class file.
 *
 * @author Alexey Volkov <a@insvit.com>
 * @copyright Copyright &copy; 2012 Alexey Volkov
 */

/**
 * Класс, который осуществляет доступ к файловой системе. С помощью него 
 * осуществляется высокоуровненое управление файлами и директориями.
 * 
 * @property array $sort ассоциативный массив, который указывает столбцы и тип сортировки
 * в стиле "название атрибута файла/директории" => "asc|desc"
 * Плюс есть еще один ключ:
 * - directoriesFirst boolean отображать ли в списке директории сгруппировано перед файлами
 * @property array $filter ассоциативный массив с описанием фильтров. Допустимы следующие ключи массива:
 * - excludeHidden boolean исключать ли скрытые файлы
 * - excludeDirectories array список имен директорий, которые подлежат исключению
 * - excludeFiles array список имен файлов, которые подлежат исключению
 * - excludeReadonlyFiles boolean исключать ли фалы, которые доступны в режиме "только для чтения"
 * - excludeReadonly boolean исключать ли файлы и директории, которые доступны в режиме "только для чтения"
 * - onlyFileTypes array список расширений файлов, которые исключительно должны быть в списке
 * - excludeFileTypes array список расширений файлоы, которые должны быть исключены
 * - checkDoubleExtension boolean проверять ли "второе расширение" файла для исключения нежелательных расширений файлов
 * @property string $baseUrl базовая ссылка
 */
class FilesystemWrapper extends CComponent
{
    private $_connectUri;
    private $_connectParams;
    private $_connected;
    private $_filterParams;
    private $_sortParams;
    private $_baseUrl;
    
    /**
     * @param string $uri uniform resource identifier 
     * @param array $params parameters
     * - sort array 
     * - filter array
     * - baseUrl string
     * @return FilesystemWrapper 
     */
    public function __construct($uri, array $params=array()) 
    {
        $uri = $this->_fixPath($uri);
        if (is_dir($uri)) {
            $this->_connected = true;
            $this->_connectUri = $uri;
            $this->_connectParams = $params;
            foreach ($params as $k => $v) {
                $this->$k = $v;
            }
            return $this;
        } else
            return false;
    }
    
    /**
     * Проверяет удачно ли осуществлено подключение.
     *
     * @return boolean удачно ли осуществлено подключение
     */
    public function isConnected()
    {
        return $this->_connected;
    }
    
    /**
     * Получает информацию о файле или директории.
     * 
     * @param string $path путь к файлу/директории
     * @return array список атрибутов файла/директории
     */
    public function getInfo($path)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        if ($this->isConnected() && file_exists($fullPath)) {
            return $this->_getInfo($fullPath);
        } else 
            return false;        
    }
    
    /**
     * Делает файл или директорию доступной для публичного доступа по ссылке.
     * 
     * @param string $path путь к файлу/директории
     * @return mixed ссылка на файл/директорию или false, если публикация невозможна 
     */
    public function publish($path)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        if ($this->isConnected() && file_exists($fullPath)) {
            return !empty($this->_baseUrl) ? $this->_baseUrl.$path : false;
        } else 
            return false;
    }
    
    /**
     * Переименовывает файл или директорию.
     * 
     * @param string $path путь к файлу/директории
     * @param string $newName новое имя файла/директории
     * @return boolean удачно ли прошло переименование 
     */
    public function rename($path, $newName)
    {
        $newName = $this->_fixName($newName);
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        if ($this->isConnected() && file_exists($fullPath)) {
            $dirName = dirname($fullPath);
            $newFullPath = $this->_fixPath($dirName.DIRECTORY_SEPARATOR.$newName);
            return @rename($fullPath, $newFullPath);
        } else
            return false;
    }
    
    /**
     * Перемещает файл или директорию.
     * 
     * @param string $path путь к файлу/директории
     * @param string $newName новое имя файла/директории
     * @param boolean $overwrite позволять ли перезаписывать существующие файлы/директории
     * @return boolean удачно ли прошло перемещение
     */
    public function move($path, $newPath, $overwrite=false)
    {
        return $this->copy($path, $newPath, true, $overwrite);
    }
    
    /**
     * Копирует файл или директорию.
     * 
     * @param string $path путь к файлу/директории
     * @param string $newName новое имя файла/директории
     * @param boolean $move производится ли операция перемещения (true) или копирования (false)
     * @param boolean $overwrite позволять ли перезаписывать существующие файлы/директории
     * @return boolean удачно ли прошло копирование
     */
    public function copy($path, $newPath, $move=false, $overwrite=false)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        if ($this->isConnected() && file_exists($fullPath)) {
            $baseName = pathinfo($fullPath, PATHINFO_BASENAME);
            $newFullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$newPath.DIRECTORY_SEPARATOR.$baseName);
            if (is_file($fullPath)) {
                if (!is_file($newFullPath) || ($overwrite && unlink($newFullPath))) {
                    if ($move) {
                        return @rename($fullPath, $newFullPath);
                    } else {
                        return @copy($fullPath, $newFullPath) && @chmod($newFullPath, 0666);
                    }
                } else
                    return false;
            } elseif (is_dir($fullPath)) {
                $ret = true;
                if (!file_exists($newFullPath)) {
                    $ret = $ret && @mkdir($newFullPath, 0777) && @chmod($newFullPath, 0777);;
                }
                $list = scandir($fullPath);
                foreach ($list as $file) {
                    if ($file=='.' || $file=='..') continue;
                    $ret = $ret && $this->copy($this->_fixPath($path.DIRECTORY_SEPARATOR.$file), $this->_fixPath($newPath.DIRECTORY_SEPARATOR.$baseName), $move, $overwrite);
                }
                if ($move) {
                    $ret = $ret && @rmdir($fullPath);
                }
                return $ret;
            } else {
                return false;
            }
        } else
            return false;
    }
    
    /**
     * Удаляет файл или директорию.
     * 
     * @param string $path путь к файлу/директории
     * @return boolean удачно ли прошло удаление
     */
    public function delete($path)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        if ($this->isConnected() && file_exists($fullPath)) {
            if (is_file($fullPath)) {
                return @unlink($fullPath);
            } elseif (is_dir($fullPath)) {
                $ret = true;                
                $list = scandir($fullPath);
                foreach ($list as $file) {
                    if ($file=='.' || $file=='..') continue;
                    $ret = $ret && $this->delete($this->_fixPath($path.DIRECTORY_SEPARATOR.$file));
                }
                $ret = $ret && @rmdir($fullPath);
                return $ret;
            } else
                return false;
        } else
            return false;
    }
    
    /**
     * Создает новую директорию
     * 
     * @param string $path путь к директории в которой будет создаваться новая директория
     * @param string $name имя создаваемой директории
     * @param int $mode права с которыми будет создаваться новая директория
     * @return array список атрибутов созданной директории
     */
    public function createDirectory($path, $name, $mode=0777)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        $name = $this->_fixPath($name);
        $newFullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$name);
        if ($this->isConnected() && is_dir($fullPath) && !file_exists($newFullPath))
        {
            if (@mkdir($newFullPath, $mode)) {
                @chmod($newFullPath, $mode);
                return $this->_getInfo($this->_fixPath($path.DIRECTORY_SEPARATOR.$name));
            } else
                return false;
        } else
            return false;
    }
    
    /**
     * Создает новый файл
     * 
     * @param string $path путь к директории в которой будет создаваться новый файл
     * @param string $name имя создаваемого файла
     * @param string $content содержимое нового файла
     * @param int $mode права с которыми будет создаваться новый файл
     * @return array список атрибутов созданного файла
     */
    public function createFile($path, $name, $content='', $mode=0666)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        $name = $this->_fixPath($name);
        $newFullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$name);
        if ($this->isConnected() && is_dir($fullPath) && !file_exists($newFullPath))
        {
            if (@file_put_contents($newFullPath, $content, FILE_APPEND)) {
                @chmod($newFullPath, $mode);
                return $this->_getInfo($this->_fixPath($path.DIRECTORY_SEPARATOR.$name));
            } else
                return false;
        } else
            return false;
    }
    
    /**
     * Закачивает файл. Точнее, обрабатывает закачанный файл и переносит его в нужное место.
     * 
     * @param string $path путь к директории в которую будет закачиваться новый файл
     * @param string $fileInputName имя поля ввода файла
     * @param boolean $overwrite перезаписывать ли файл, если он уже существует
     * @param string $name имя создаваемого файла (если пусто, будет использовано имя закачиваемого файла)
     * @param int $mode права с которыми будет создаваться новый файл
     * @return array список атрибутов закачанного файла
     */
    public function uploadFile($path, $fileInputName, $overwrite=false, $name='', $mode=0777)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        $name = $this->_fixPath($name);
        $file = CUploadedFile::getInstanceByName($fileInputName);
        if ($this->isConnected() && $file && is_object($file)) 
        {
            if (!$name) $name = $file->getName();
            $newFullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$name);
            if (!is_file($newFullPath) || $overwrite) {
                if (is_file($newFullPath)) @unlink($newFullPath);
                return $file->saveAs($newFullPath) && $this->_getInfo($this->_fixPath($path.DIRECTORY_SEPARATOR.$name));
            } else
                return false;
        } else
            return false;
    }
    
    /**
     * Получает список файлов и директорий в указанной директории. К каждому 
     * файлу/директории прилагается список атрибутов.
     *
     * @param string $path путь к файлу/директории
     * @return array список файлов с их атрибутами
     */
    public function getDirectory($path)
    {
        $fullPath = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        if ($this->isConnected() && is_dir($fullPath)) {
            $list = scandir($fullPath);
            $files = array();
            sort($list);
            foreach ($list as $file) 
            {                
                if ($file == '.' ||
                    $file == '..') continue;
                
                if (!empty($this->_filterParams['excludeHidden']) && substr($file,0,1) == '.') continue;                

                $filename = $this->_fixPath($path.DIRECTORY_SEPARATOR.$file);
                $tmp = $this->_getInfo($filename);                

                if (!empty($this->_filterParams['excludeDirectories']) && $tmp['type']=='dir' && in_array($file, $this->_filterParams['excludeDirectories'])) continue;
                if (!empty($this->_filterParams['excludeFiles']) && $tmp['type']=='file' && in_array($file, $this->_filterParams['excludeFiles'])) continue;
                if (!empty($this->_filterParams['excludeReadonlyFiles']) && $tmp['type']=='file' && !$tmp['writable']) continue;
                if (!empty($this->_filterParams['excludeReadonly']) && !$tmp['writable']) continue;
                if (!empty($this->_filterParams['onlyFileTypes']) && $tmp['type']=='file' && !in_array(strtolower($tmp['extension']),$this->_filterParams['onlyFileTypes'])) continue;
                if (!empty($this->_filterParams['excludeFileTypes']) && $tmp['type']=='file' && in_array(strtolower($tmp['extension']),$this->_filterParams['excludeFileTypes'])) continue;
                if (!empty($this->_filterParams['checkDoubleExtension']) && !empty($this->_filterParams['excludeFileTypes']) && $tmp['type']=='file' && in_array(strtolower($tmp['extension2']),$this->_filterParams['excludeFileTypes'])) continue;
                $files[] = $tmp;
            }
            unset($tmp);
            // Sorting
            $func_params = array();
            if (!empty($this->_sortParams))
                foreach ($this->_sortParams as $name => $direction) {
                    if (isset($files[0][$name])) {
                        foreach ($files as $i => $file) {
                            $tmp[$i] = $file[$name];
                        }
                        $func_params[] = $tmp;
                        $func_params[] = (strtolower($direction)=='desc' ? SORT_DESC : SORT_ASC);
                        $func_params[] = is_string($files[0][$name]) ? SORT_STRING : SORT_REGULAR;
                    }
                }
            if (!empty($func_params)) {
                $func_params[] = &$files;
                call_user_func_array('array_multisort', $func_params);
            }
            // Directories first
            if (!empty($this->_sortParams['directoriesFirst'])) {
                $_folders = array();
                $_files = array();
                foreach ($files as $file) {
                    if ($file['type']=='dir') {
                        $_folders[] = $file;
                    } else {
                        $_files[] = $file;
                    }
                }
                $files = array_merge($_folders, $_files);
            }
            return $files;
        } else
            return false;
    }
    
    /**
     * Устанавливает фильтры для отображения списков файлов/директорий.
     * 
     * @param array $params ассоциативные массив с описанием фильтров. Допустимы следующие ключи массива:
     * - excludeHidden boolean исключать ли скрытые файлы
     * - excludeDirectories array список имен директорий, которые подлежат исключению
     * - excludeFiles array список имен файлов, которые подлежат исключению
     * - excludeReadonlyFiles boolean исключать ли фалы, которые доступны в режиме "только для чтения"
     * - excludeReadonly boolean исключать ли файлы и директории, которые доступны в режиме "только для чтения"
     * - onlyFileTypes array список расширений файлов, которые исключительно должны быть в списке
     * - excludeFileTypes array список расширений файлоы, которые должны быть исключены
     * - checkDoubleExtension boolean проверять ли "второе расширение" файла для исключения нежелательных расширений файлов
     */
    public function setFilter($params)
    {
        $this->_filterParams = $params;
    }
    
    /**
     * Устанавливает настройки сортивроки списков файлов/директорий.
     * 
     * @param array $params ассоциативный массив, который указывает столбцы и тип сортировки
     * в стиле "название атрибута файла/директории" => "asc|desc"
     * Плюс есть еще один ключ:
     * - directoriesFirst boolean отображать ли в списке директории сгруппировано перед файлами
     */
    public function setSort($params)
    {
        $this->_sortParams = $params;
    }
    
    /**
     * Устанвливает базовую ссылку, то есть ссылку по которой доступен корень файловой системы.
     * 
     * @param string $baseUrl базовая ссылка
     */
    public function setBaseUrl($baseUrl)
    {
        if (substr($baseUrl,-1,1)=='/') $baseUrl=substr($baseUrl,0,-1);
        $this->_baseUrl = $baseUrl;
    }
    
    /**
     * Исправляет предполагаемые ошибки или уязвимости в пути к файлу/директории
     * 
     * @param string $path путь к файлу/директории
     * @return string исправленный путь
     */
    protected function _fixPath($path)
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $path = str_replace(array(DIRECTORY_SEPARATOR.'..','..'.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR),DIRECTORY_SEPARATOR,$path);
        if ($path=='..') $path = DIRECTORY_SEPARATOR;
        if (substr($path,0,1)!=DIRECTORY_SEPARATOR) $path = DIRECTORY_SEPARATOR.$path;
        if (substr($path,-1,1)==DIRECTORY_SEPARATOR) $path = substr($path,0,-1);
        return $path;
    }

    /**
     * Исправляет предполагаемые ошибки или уязвимости в названии файла или директории
     * 
     * @param string $name имя файла/директории
     * @return string исправленное имя
     */
    protected function _fixName($name)
    {
        $restrictedChars = array(
            '&', '?', '|', '*', '"', "'", ':', ';', '=', '\\', '/', '`', 
        );
        return str_replace($restrictedChars, '', $name);
    }    
    
    /**
     * Получает информацию о файле или директории.
     * 
     * @param string $path путь к файлу/директории
     * @return array список атрибутов файла/директории
     */
    protected function _getInfo($path)
    {
        $fullFilename = $this->_fixPath($this->_connectUri.DIRECTORY_SEPARATOR.$path);
        $pathinfo = pathinfo($fullFilename);
        $pathinfo2 = pathinfo($pathinfo['filename']);
        $stat = stat($fullFilename);
        return array(
            'path' => $path,
            'filename' => $pathinfo['basename'],
            'shortname' => $pathinfo['filename'],
            'extension' => $pathinfo['extension'],
            'extension2' => $pathinfo2['extension'],
            'size' => $stat['size'],
            'readable' => is_readable($fullFilename),
            'writable' => is_writable($fullFilename),
            'type' => filetype($fullFilename),
            'modified' => $stat['mtime'],
            'uid' => $stat['uid'],
            'gid' => $stat['gid'],
            'url' => !empty($this->_baseUrl) ? $this->_baseUrl.$path : false,
        );        
    }    
}