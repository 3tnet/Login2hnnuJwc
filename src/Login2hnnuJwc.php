<?php

namespace Ty666\Login2hnnuJwc;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Ty666\Login2hnnuJwc\Exception\LoginJWCException;
use RuntimeException;

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
    public $client = null;
    private $cookies = null;
    public function __construct(Client $client = null)
    {
        if($client == null)
            $client = new Client(new \GuzzleHttp\Client([RequestOptions::TIMEOUT => 7]));
        $this->client = $client;
    }

    /**
     * Step.1 登陆进教务处
     * @param $studentNum 学号
     * @param $idCard 身份证号码
     * @return bool
     * @throws LoginJWCException 登录失败会抛出LoginJWCException异常
     */
    public function login2Jwc( $studentNum, $idCard, $timeout = null){

        $res = null;
        $request = new Request('GET', $this->loginUri);
        try{
            $res = $this->client->send($request, [
                RequestOptions::QUERY => [
                    'xh' => $studentNum,
                    'sfzh' => $idCard
                ]
            ]);
        }catch (RequestException $exception){
            throw new LoginJWCException('教务处好像卡了...');
        }


        $this->cookies = new \GuzzleHttp\Cookie\CookieJar();
        $this->cookies->extractCookies($request, $res);


        $content = mb_convert_encoding($res->getBody(), 'UTF-8', 'gbk');

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

            throw new RuntimeException('500', '发生内部错误');
        }
    }

    /**
     * Step.2 从教务处获取学生信息
     * @return array
     */
    public function getStudentInfoFromJWC(){

        $res = null;

        try{
            $res = $this->client->get($this->studentInfoUri, [
                RequestOptions::COOKIES => $this->cookies
            ]);
        }catch (RequestException $exception){
            throw new LoginJWCException('教务处好像卡了...');
        }

        $content = mb_convert_encoding($res->getBody(), 'UTF-8', 'gbk');

        $m = [];
        if(1 === preg_match('/<IMG SRC="\.\.\/dbsdb\/tp\.asp\?xh=(\d{10})" width="120" height="160">[\s\S]+?"center"><font color=red>(.+)<\/font>[\s\S]+?班级[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.*)<\/td>[\s\S]+?<td align="center" width="150" height="22" valign="middle">(.*)<\/td>[\s\S]+?政治面貌[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.*)<\/td>/i', $content, $m))
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
            throw new RuntimeException('发生内部错误');
        }
    }

    /**
     * step.3　保存照片
     */
    public function savePhoto($path = ''){
        if($path=='')
            $path = tempnam(sys_get_temp_dir(), '3t_');
        $this->client->get($this->photoUri.$this->currentStudentNum, [
            RequestOptions::SINK=>$path,
            RequestOptions::HTTP_ERRORS => false
        ]);
        return $path;
    }

}
