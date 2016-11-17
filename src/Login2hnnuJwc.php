<?php

namespace Ty666\Login2hnnuJwc;
use Curl\Curl;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ty666\Login2hnnuJwc\Exception\LoginJWCException;

/**
 * Created by PhpStorm.
 * User: ty
 * Date: 16-10-10
 * Time: 下午9:15
 */
class Login2hnnuJwc
{
    private $loginUri = 'http://211.70.176.123/wap/index.asp';
    private $studentInfoUri = 'http://211.70.176.123/wap/grxx.asp';
    private $photoUri = 'http://211.70.176.123/dbsdb/tp.asp?xh=';
    private $currentStudentNum = '';
    public $curl = null;
    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->setConnectTimeout(7);
    }
    public function setConnectTimeout($seconds){
        $this->curl->setConnectTimeout($seconds);
    }
    /**
     * Step.1 登陆进教务处
     * @param $studentNum 学号
     * @param $idCard 身份证号码
     * @return bool
     * @throws LoginJWCException 登录失败会抛出LoginJWCException异常
     */
    public function login2Jwc( $studentNum, $idCard){
        $this->curl->post($this->loginUri, [
            'xh' => $studentNum,
            'sfzh' => strtoupper($idCard)
        ]);

        if ($this->curl->error) {
            //throw new LoginJWCException($this->curl->errorMessage);
            throw new LoginJWCException('教务处好像卡了...');
            //echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage;
        }

        $content = mb_convert_encoding($this->curl->response, 'UTF-8', 'gb2312');
		
        foreach ($this->curl->responseCookies as $key => $val){
            if(starts_with($key, 'ASPSESSIONID')){
                $this->curl->setCookie($key, $this->curl->getCookie($key));
                break;
            }
        }

        $m = [];

        if(1 === preg_match('/<p align="center">[\s\S]+?<font color=blue>&nbsp; (.+)<\/font>[\s\S]+?<\/font>/i', $content, $m))
        {
            throw new LoginJWCException($m[1]);
        }else{

            if(1 === preg_match('/<SCRIPT language=JavaScript> window\.alert\(\'欢迎登陆教务系统！\'\);location\.href=\'main\.asp\'<\/SCRIPT>/', $content, $m)){
                $this->currentStudentNum = $studentNum;
                //登陆成功
                return $this;
            }

            throw new HttpException('500', '发生内部错误');
        }
    }
    /**
     * Step.2 从教务处获取学生信息
     * @return array
     */
    public function getStudentInfoFromJWC(){

        $this->curl->get($this->studentInfoUri);
        if ($this->curl->error) {
            //throw new HttpException(500, $this->curl->errorMessage);
            throw new LoginJWCException('教务处好像卡了...');
            //echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage;
        }
        $content = mb_convert_encoding($this->curl->response, 'UTF-8', 'gb2312');

        $m = [];

        if(1 === preg_match('/<IMG SRC="\.\.\/dbsdb\/tp\.asp\?xh=(\d{10})" width="120" height="160">[\s\S]+?"center"><font color=red>(.+)<\/font>[\s\S]+?班级[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.+)<\/td>[\s\S]+?<td align="center" width="150" height="22" valign="middle">(.+)<\/td>[\s\S]+?政治面貌[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.+)<\/td>/i', $content, $m))
        {

            $info = [
                //学号
                'student_num'   => $m[1],
                //姓名
                'student_name'  => $m[2],
                //二级学院
                'department'    => $m[3],
                //专业+班级
                'student_class' => $m[4],
                //生日
                'birthday'      => $m[5]
            ];
            $this->currentStudentNum = $info['student_num'];
            return $info;
        }else{
            throw new HttpException('500', '发生内部错误');
        }
    }

    /**
     * step.3　保存照片
     * @return bool
     */
    public function savePhoto(){
        $tempFile = tempnam(sys_get_temp_dir(), '3t_');
        //因为淮南师范学院手机版的头像　无论是否能获取都返回500状态码,因此无法判断
        /*if($this->curl->download($this->photoUri.$this->currentStudentNum, $temp_file)){
            return $temp_file;
        }else{

            throw new SavePhotoException($this->curl->errorMessage, $this->curl->errorCode);
        }*/
        $this->curl->download($this->photoUri.$this->currentStudentNum, $tempFile);
        return new UploadedFile($tempFile, basename($tempFile), mime_content_type($tempFile), filesize($tempFile), false, true);

    }

    public function __destruct()
    {
        $this->curl->close();
    }
}
