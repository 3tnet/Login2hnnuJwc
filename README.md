# Login2hnnuJwc
登陆到淮南师范学院教务处laravel扩展

## Installation
- Run `composer require ty666/login2hnnu-jwc`
- Add `Ty666\Login2hnnuJwc\Laravel\LaravelServiceProvider::class,` to  **providers** in *config/app.php*
- Add `'Login2hnnuJwc' => Ty666\Login2hnnuJwc\Facades\Login2hnnuJwc::class,` to **aliases** in *config/app.php*

## Usage
### 登陆进教务处
```
/**
 * Step.1 登陆进教务处
 * @param $studentNum 学号
 * @param $idCard 身份证号码
 * @return bool
 * @throws LoginJWCException 登录失败会抛出LoginJWCException异常
 */
 Login2hnnuJwc::login2Jwc($studentNum, $idCard);
```
### 从教务处获取学生信息(必须先登陆进教务处)
```
/**
  * Step.2 从教务处获取学生信息
  * @return array
  */

 Login2hnnuJwc::getStudentInfoFromJWC();
```
#### 返回数组
```
//学号
'student_num'
//姓名
'student_name'
//二级学院
'department'
//专业+班级
'student_class'
//生日
'birthday'
```
### 保存照片(必须先登陆进教务处)
`Login2hnnuJwc::savePhoto()`
#### 返回值
`Symfony\Component\HttpFoundation\File\UploadedFile`
