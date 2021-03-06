<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\File\Transfer\Adapter;

use Zend\File\Transfer\Adapter\Http as ZendHttp;

/**
 * File transfer HTTP protocol
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Http extends ZendHttp
{
    /**
     * {@inheritDoc}
     */
    public function receive($files = null)
    {
        if (!$this->isValid($files)) {
            return false;
        }

        $check = $this->getFiles($files);
        foreach ($check as $file => $content) {
            if (!$content['received']) {
                $directory   = '';
                $destination = $this->getDestination($file);
                if ($destination !== null) {
                    $directory = $destination . DIRECTORY_SEPARATOR;
                }

                $filename = $directory . $content['name'];
                $rename   = $this->getFilter('Rename');
                if ($rename !== null) {
                    /**#@+
                     * Added by Taiwen Jiang
                     */
                    $rename->setSource($content);
                    /**#@-*/
                    $tmp = $rename->getNewName($content['tmp_name']);
                    if ($tmp != $content['tmp_name']) {
                        $filename = $tmp;
                    }

                    if (dirname($filename) == '.') {
                        $filename = $directory . $filename;
                    }

                    $key = array_search(
                        get_class($rename),
                        $this->files[$file]['filters']
                    );
                    unset($this->files[$file]['filters'][$key]);
                }

                // Should never return false
                // when it's tested by the upload validator
                if (!move_uploaded_file($content['tmp_name'], $filename)) {
                    if ($content['options']['ignoreNoFile']) {
                        $this->files[$file]['received'] = true;
                        $this->files[$file]['filtered'] = true;
                        continue;
                    }

                    $this->files[$file]['received'] = false;
                    return false;
                }

                if ($rename !== null) {
                    $this->files[$file]['destination'] = dirname($filename);
                    $this->files[$file]['name']        = basename($filename);
                }

                $this->files[$file]['tmp_name'] = $filename;
                $this->files[$file]['received'] = true;
            }

            if (!$content['filtered']) {
                if (!$this->filter($file)) {
                    $this->files[$file]['filtered'] = false;
                    return false;
                }

                $this->files[$file]['filtered'] = true;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @FIXME Array (or cURL upload) is handled differently in PHP 5.3/5.4
     */
    protected function getFiles($files, $names = false, $noexception = false)
    {
        $files = $files ?: null;
        $check = parent::getFiles($files, $names, $noexception);

        return $check;
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * Get uploaded file list
     *
     * @return array
     */
    public function getFileList()
    {
        return $this->files;
    }
    /**#@-*/
}
