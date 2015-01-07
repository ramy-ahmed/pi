<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading `video js` player
 *
 * Usage inside a phtml template
 *
 * $this->Videojs($source, $poster);
 * $this->Videojs($source, $poster, $width, $height);
 *
 * @see http://www.videojs.com/
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Videojs extends AbstractHtmlElement
{
    /**
     * Display social sharing buttons
     *
     * @todo The icon is not responsive yet
     *
     * @param string                $source     MP4 video or MP3 audio full url
     * @param string                $poster     Player image full url
     * @param string                $width      Player width
     * @param string                $height     Player height
     *
     * @return  string
     */
    public function __invoke($source, $poster, $width = 640, $height = 264)
    {
        // Set template
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        switch ($extension) {
        	case 'mp3':
                $template = <<<'EOT'
<audio id="%s" class="video-js vjs-default-skin" width="%s" height="%s" poster="%s" data-setup='{ "controls": true, "autoplay": false, "preload": "auto" }'>
    <source src="%s" type='audio/mp3' />
</audio>
EOT;
        		break;

        	case 'mp4':
                $template = <<<'EOT'
<video id="%s" class="video-js vjs-default-skin" width="%s" height="%s" poster="%s" data-setup='{ "controls": true, "autoplay": false, "preload": "auto" }'>
    <source src="%s" type='video/mp4' />
</video>
EOT;
        		break;

        	default:
                return '';
        		break;
        }

        // Load js file
        $js = 'vendor/player/video-js/video.js';
        $js = Pi::service('asset')->getStaticUrl($js);
    	$this->view->js($js);
        
        // Load css file
        $css = 'vendor/player/video-js/video-js.min.css';
        $css = Pi::service('asset')->getStaticUrl($css);
    	$this->view->css($css);

    	// Set random ID
    	$id = "video-js-" . rand(1000, 9999);
       
        // Set final content
        $content = sprintf($template, $id, $width, $height, $poster, $source);
        return $content;
    }
}