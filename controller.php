<?php
/**
 * Copyright © 2017-2020 Braveten Technology Co., Ltd.
 * Engineer: Makin
 * Date: 2020/11/22
 * Time: 4:29 下午
 */

class controller extends core
{
    protected mixed $db;
    protected mixed $config;
    public function __construct()
    {
        //header("Content-Type: application/json; charset=UTF-8");
        $this->data= new stdClass();
        $this->config = $GLOBALS['config'];
        //$this->db = inClass('postgresql');
        $this->db = inClass('postgresql');
    }
    /**
     * @param string $param 参数
     * $this->db->limitNum 页面上显示数据的条数，根据该值显示最大页面数
     * @param int $pageNum 页面上显示的最大页码数量 偶数
     * @param int $pageMax 最大页码数/尾页数
     * @return string
     * 2021-04-10 17:40:58 维护更新
     * Update: 2021-11-05 18:49:31
     */
    public function page(string $param = '' ,int $pageNum = 6, int $pageMax = 30): string
    {
        $echo = '';
        $url=($param?'?'.$param.'&':'?').'page=';
        $page = get('page')?:1;
        $maxDataPage = $GLOBALS['total']?ceil($GLOBALS['total']/$GLOBALS['limit']):0;
        $zPage = $pageMax&&$pageMax<=$maxDataPage?$pageMax:$maxDataPage;
        if($page>$zPage)return false;
        $end = floor($pageNum/2)?:1;

        $echo.= '<li><a ';
        if($page-1>0){
            $echo.='href="'.$url.'1"';
        }
        $echo.='>首页</a></li><li><a';
        if($page-1>0){$echo.=' href="'.$url.($page-1).'"';}
        $echo.='>上一页</a></li>';
        for($i=1; $i<=($pageNum<$zPage?$pageNum:$zPage) ;$i++){
            //if($page<=$zPage){
            if($page < $end){
                if($i == $page){
                    $echo.='<li class="active"><b>'.$page.'</b></li>';
                }
                else{
                    $echo.='<li><a href="'.$url.$i.'">'.$i.'</a></li>';
                }
            }
            //当page = 6 时，执行下方代码
            else{
                $endPage = $page+$i-$end;
                if($i < $end){
                    $echo.='<li><a class="p" href="'.$url.($page-($end-$i)).'">'.($page-($end-$i)).'</a></li>';
                }
                else if($i == $end){
                    $echo.='<li class="active"><b>'.$page.'</b><li>';
                }
                else if($endPage <= $zPage){
                    $echo.='<li><a class="z" href="'.$url.$endPage.'">'.$endPage.'</a></li>';
                }
            }

            //}
        }
        if(($page+1)<=$zPage){$echo.='<li><a href="'.$url.($page+1).'">下一页</a></li>';}
        return $echo;
    }
    /**
     * 字符串加密
     * [可以解密]
     * @param $string
     * @return string
     */
    protected function token($string): string{
        $letter='0123456789+/=aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ';
        $str=base64_encode($string);
        $arr=str_split($str);
        $letter=str_split($letter);
        $letter=array_flip($letter);
        $code='';
        foreach ($arr as $index){
            $code .= ($code?'|':'').$letter[$index];
        }
        return $code;
    }
    protected function deToken($code): string{
        if(!$code)exit;
        $letter='0123456789+/=aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ';
        $letter = str_split($letter);
        $arr = explode('|',$code);
        $str='';
        foreach ($arr as $index){
            $str.=$letter[$index];
        }
        return base64_decode($str);
    }
    /**
     * @param $str
     * @return array
     * 字符串加密
     */
    protected function strencode($str): array
    {
        $key1=strtoupper($this->RandKey(3));
        $key2=strtoupper($this->RandKey(4));
        $sign_code=$key1.base64_encode($str).$key2;
        $sign=base64_encode($sign_code);
        return array($sign,$key1,$key2);
    }
    /**
     * JSON 序列化
     * @param array $data
     * @param string $msg
     */
    protected function json($data=[],$msg=''){
        return json_encode([
            'result'=>'success',
            'msg'=>$msg,
            'data'=>$data
        ],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);//|JSON_UNESCAPED_UNICODE
    }

    /**
     * 输出JSON
     * @param array $data
     * @param string $msg
     */
    protected function echo_json($data=[],$msg=''){
        echo $this->json($data,$msg);
    }
    /**
     * @param $path
     * @param $chr
     * @return string
     * 2021-11-27 18:14:17
     */
    protected function pic_exists($path,$chr): string
    {
        $lower=strtolower($path);
        $lochr=strtolower($chr);
        #判断本地文件是否存在。注：CDN上的文件与本服务器上的文件相同
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/external/static/'.$lower)){
            return $this->config->https->cdn.$lower;
        }
        $e = match ($lochr) {
            'rm', 'mkv', 'mp4', 'avi', 'rmvb' => 'video',
            'vob' => 'iso',
            default => 'document',
        };
        return $this->config->https->cdn.str_replace($lochr,$e,$lower);
    }
}