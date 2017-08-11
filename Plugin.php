<?php
/**
 * HTML压缩插件
 *
 * @package HTMLCompress
 * @author Kokororin
 * @version 1.3
 * @link https://kotori.love
 */

class HTMLCompress_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('HTMLCompress_Plugin', 'before');
	Typecho_Plugin::factory('Widget_Archive')->footer = array('HTMLCompress_Plugin', 'footer_script');
    }

    /**
     * 禁用插件
     */
    public static function deactivate() {}

    /**
     * 插件设置
     */
    public static function config(Typecho_Widget_Helper_Form $form) {}

    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    public static function before($archive)
    {
        ob_start(array('HTMLCompress_Plugin', 'parser'));
    }

    public static function parser($html)
    {
        require_once __DIR__ . '/vendor/autoload.php';
        $parser = \WyriHaximus\HtmlCompress\Factory::construct();
        $s = $parser->compress($html);

        $s = preg_replace('#\<!--[ \w\d,\.;]*--\>#', '', $s);
        if (isset($_GET['_pjax'])) {
		$title_text = '';
		if (preg_match('#(?<=\<title\>).*?(?=\<\/title\>)#', $s, $title)) {
			$title_text = $title[0];
		}
		$pos1 = strpos($s, '<div id="pjax">');
		$pos2 = strpos($s, '</div><!--*pjax-->');
        	$s = substr($s, $pos1, $pos2 - $pos1);
		if ($title_text) {
			$s .= '<span id="pjax-title" style="display: none">' . $title_text . '</span>';
		}
		$s .= '</div>';
	}

        return $s;
    }

	public static function footer_script() {
		$url = Typecho_Common::url('HTMLCompress/pjax.js', Helper::Options()->pluginUrl);
		echo '<script type="text/javascript" src="' . $url . '"></script>';
		echo '<script>pjax.connect({container:"pjax",smartLoad:false,success:function(e){var p=document.querySelector("span#pjax-title");if(p==null)return;document.title=p.innerText;p.parentNode.removeChild(p);}});</script>';
	}
}

