<?php

namespace Ty666\Login2hnnuJwc;
use Curl\Curl;
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
    public $curl = null;
    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Step.1 登陆进教务处
     * @param $studentNum 学号
     * @param $idCard 身份证号码
     * @return bool
     * @throws LoginJWCException 登录失败会抛出LoginJWCException异常
     */
    public function login2Jwc( $studentNum, $idCard){
        $this->curl->post($this->loginUri,[
            'xh' => $studentNum,
            'sfzh' => $idCard
        ]);

        if ($this->curl->error) {
            throw new HttpException(500, $this->curl->errorMessage);
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
                //登陆成功
                return true;
            }

            throw new HttpException('500', '发生内部错误');
        }
    }
    /**
     * Step.2 从教务处获取学生信息
     * @param Curl $curl
     * @return array
     */
    public function getStudentInfoFromJWC(){

        $this->curl->get($this->studentInfoUri);
        if ($this->curl->error) {
            throw new HttpException(500, $this->curl->errorMessage);
            //echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage;
        }
        $content = mb_convert_encoding($this->curl->response, 'UTF-8', 'gb2312');

        $m = [];

        if(1 === preg_match('/"center"><font color=red>(.+)<\/font>[\s\S]+?班级[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.+)<\/td>[\s\S]+?<td align="center" width="150" height="22" valign="middle">(.+)<\/td>[\s\S]+?政治面貌[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.+)<\/td>/i', $content, $m))
        {

            return [
                //姓名
                'student_name' => $m[1],
                //二级学院
                'department'   => $m[2],
                //专业+班级
                'student_class'=> $m[3],
                //生日
                'birthday' => $m[4]
            ];

        }else{
            throw new HttpException('500', '发生内部错误');
        }
    }
    public function __destruct()
    {
        $this->curl->close();
    }
}