<?php
/**
 * 为了不影响效率，和字符串替换的过程
 * 这里只对输出操作和php分界符做替换，以便达到更好的书写效果和执行效果
 * @file template.class.php
 * @author luoage@msn.cn
 * @version $$version
 */
class template {
    /**
     * 变量/分隔符替换
     * @access public
     * @param string $string
     * @return string
     */
    public function r($string) {
        $left = $this->delimiter('TPL_LEFT_DELIMITER');
        $right = $this->delimiter('TPL_RIGHT_DELIMITER');
        if (!$left || !$right)
            return;

        $src = array(
            '/' . $left . '\s*(.*)\s*' . $right . '/sU', //{<>}替换成<?php echo \\1 \?\>
            '/<!--[^\[].*-->/sU', //注释掉<!-- -->
            '/\/\*.*\*\//sU'     //注释掉/* */
            
        );
        $dest = array(
            '<?php echo \\1;?>',
            '',
            ''
        );
        if (c('TPL_LEFT_PHP') && c('TPL_RIGHT_PHP')) {
            $src[] = $this->strtoDelimiter('TPL_LEFT_PHP');
            $src[] = $this->strtoDelimiter('TPL_RIGHT_PHP');

            $dest[] = '<?php';
            $dest[] = '?>';
        }

        $string = preg_replace($src, $dest, $string);
        //if(! c('ALL_STATIC_TEMPLATE_ONELINE')) return $string;
        // 减少复杂度,所有一律清除掉
        return preg_replace(
            array(
                '/(\s+|;\s*|{\s*|,\s*)\/\/.*\n/'   // 注释所有 //-----\n模式    
            )
            ,array(
                '\\1'
            )
            ,$string);
    }

    /**
     * 分隔符转义
     * @param string $del 需要转义的字符串
     * @access private
     * @return string
     */
    private function delimiter($del) {
        $d = c($del);
        $len = strlen($d);
        $delimiter = '';
        for ($i = 0; $i < $len; $i++) {
            $delimiter .='\\' . $d[$i];
        }
        return $delimiter;
    }

    /**
     * 增加正则表达式规则
     * @param string $del 需要转义的字符串
     * @access private
     * @return reg
     */
    private function strtoDelimiter($del) {
        return '/' . $this->delimiter($del) . '/sU';
    }

}

?>