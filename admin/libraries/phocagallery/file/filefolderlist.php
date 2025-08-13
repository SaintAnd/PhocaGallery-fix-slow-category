<?php
/**
 * @package   Phoca Gallery
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Object\CMSObject;
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
phocagalleryimport('phocagallery.image.image');
phocagalleryimport('phocagallery.path.path');
phocagalleryimport('phocagallery.file.filefolder');
setlocale(LC_ALL, 'C.UTF-8', 'C');

class PhocaGalleryFileFolderList
{
public static function getList($refreshUrl = '') {
    static $list;

    // Если список уже обработан, возвращаем его
    if (is_array($list)) {
        return $list;
    }

    // Получаем текущий путь из запроса
    $current = JFactory::getApplication()->input->get('folder', '', 'path');

    // Если путь не определен, устанавливаем его как пустой
    if ($current == 'undefined') {
        $current = '';
    }

    // Получаем путь к изображениям
    $path = PhocaGalleryPath::getPath();
    $origPath = JPath::clean($path->image_abs . $current);
    $origPathServer = str_replace('\\', '/', $path->image_abs);

    $images = array();
    $folders = array();

    // Получаем список файлов и папок из текущего каталога
    $fileList = JFolder::files($origPath);
    $folderList = JFolder::folders($origPath, '', false, false, array(0 => 'thumbs'));

    // Сортируем файлы
    if (is_array($fileList) && !empty($fileList)) {
        natcasesort($fileList);
    }

    // Обработка файлов
    if ($fileList !== false) {
        foreach ($fileList as $file) {
            $ext = strtolower(JFile::getExt($file));
            if (in_array($ext, ['jpg', 'png', 'gif', 'jpeg', 'webp'])) {
                if (JFile::exists($origPath . '/' . $file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
                    $fileNo = $current . "/" . $file;
                    $fileThumb = PhocaGalleryFileThumbnail::getOrCreateThumbnail($fileNo, $refreshUrl);

                    $tmp = new JObject();
                    $tmp->name = $fileThumb['name'];
                    $tmp->nameno = $fileThumb['name_no'];
                    $tmp->linkthumbnailpath = $fileThumb['thumb_name_m_no_rel'];
                    $tmp->linkthumbnailpathabs = $fileThumb['thumb_name_m_no_abs'];
                    $images[] = $tmp;
                }
            }
        }
    }

    // Обработка папок
    if ($folderList !== false) {
        foreach ($folderList as $folder) {
            $tmp = new JObject();
            $tmp->name = basename($folder);
            $tmp->path_with_name = str_replace('\\', '/', JPath::clean($origPath . '/' . $folder));
            $tmp->path_without_name_relative = $path->image_abs . str_replace($origPathServer, '', $tmp->path_with_name);
            $tmp->path_with_name_relative_no = str_replace($origPathServer, '', $tmp->path_with_name);

            $folders[] = $tmp;
        }
    }

    $list = array('folders' => $folders, 'Images' => $images);
    return $list;
	}
}
?>

