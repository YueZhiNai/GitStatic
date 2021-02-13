<?
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
* 远去的究竟是什么呢
*
* @package GitStatic
* @author 乔千,纸奈
* @version 4.0.0
* @link https://blog.yuemoe.cn
*/

require   dirname(__FILE__)."/" . 'Helper.php';
//引入辅助资源
class GitStatic_Plugin implements Typecho_Plugin_Interface
{
  public static $action = 'GitStatic';
  public static function activate()
    {
     //判断是否可用
     if (false == Typecho_Http_Client::get()) {
      throw new Typecho_Plugin_Exception(_t('对不起, 您的主机不支持 php-curl 扩展而且没有打开 allow_url_fopen 功能, 无法正常使用此功能'));
     }  
     Helper::addAction(self::$action, 'GitStatic_Action');   
     if (!file_exists(dirname(__FILE__)."/"."cache/")){mkdir (dirname(__FILE__)."/"."cache/");}     
    //创建缓存目录 
    //上传
        Typecho_Plugin::factory('Widget_Upload')->uploadHandle = array('GitStatic_Plugin', 'uploadHandle');
        //修改
        Typecho_Plugin::factory('Widget_Upload')->modifyHandle = array('GitStatic_Plugin', 'modifyHandle');
        //删除
        Typecho_Plugin::factory('Widget_Upload')->deleteHandle = array('GitStatic_Plugin', 'deleteHandle');
        //路径参数处理
        Typecho_Plugin::factory('Widget_Upload')->attachmentHandle = array('GitStatic_Plugin', 'attachmentHandle');
        //文件内容数据
        //Typecho_Plugin::factory('Widget_Upload')->attachmentDataHandle = array('cosUploadV5_Plugin', 'attachmentDataHandle');
        return _t("起飞~");
    }
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    public static function deactivate()
      {
        return _t("暂停~");
      }
      public static function config(Typecho_Widget_Helper_Form $form)
        {
        echo '<a href="http://dev.yundreams.cn/auth.php?url=';
        Helper::options()->siteUrl();
        echo '" >点击获取Token  </a>';
        echo '<a href="/action/GitStatic?recache=1" >   点击获取刷新缓存</a>';       
         $t = new Typecho_Widget_Helper_Form_Element_Text('token',
          null, null,
          _t('Token'),
          _t('请登录Github获取'));
          $form->addInput($t->addRule('required', _t('token不能为空哦~')));
          
          $t = new Typecho_Widget_Helper_Form_Element_Text('username',
          null, null,
          _t('用户名'),
          _t(''));
          $form->addInput($t->addRule('required', _t('不能为空哦~')));
     
         $repos=array();
         //目录存在不一定代表缓存刷新
         if(@file_exists(dirname(__FILE__)."/cache/repos.json")){
         $tempfile=fopen(dirname(__FILE__)."/"."cache/repos.json","r");
         $repos_json=(array)json_decode(fread($tempfile,filesize(dirname(__FILE__)."/"."cache/repos.json")));
         fclose($tempfile);
        $new_repos=array();
         foreach ($repos_json as $key => $value) {
         $new_repos=array_merge($new_repos, array($value->name =>$value->name));
         }}
         //var_dump($repos_json);
              $t = new Typecho_Widget_Helper_Form_Element_Radio(
            'repo',
            $new_repos,
            'blog',
            _t('仓库名'),
            _t('')
        );
        $form->addInput($t);
        
        $t = new Typecho_Widget_Helper_Form_Element_Text('path',
          null, "/Gitstatic",
          _t('储存路径'),
          _t(''));
          $form->addInput($t->addRule('required', _t('不能为空哦~')));
     
         }

     public static function uploadHandle($file)
    {
        if (empty($file['name'])) return false;
        //获取扩展名
        $ext = self::getSafeName($file['name']);
        //判定是否是允许的文件类型
        if (!Widget_Upload::checkFileType($ext)) return false;

        //获取文件名
        $filePath = '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        $fileName = time() . '.' . $ext;
        //cos上传文件的路径+名称
        $newPath=$filePath.$fileName;
        //如果没有临时文件，则退出
        if (!isset($file['tmp_name'])) return false;
        //获取插件参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitStatic');
        $srcPath = $file['tmp_name'];
        $handle = fopen($srcPath, "r");
        $contents = fread($handle, $file['size']);//获取二进制数据流
        fclose($handle);
       // $cos_object->upload($options->bucket, $newPath, $contents);
     if(!Github_files_upload($options->username,$options->token,$options->repo,$options->path.$newPath,$contents))
      {Github_files_updata($options->username,$options->token,$options->repo,$options->path.$newPath,$contents,Github_get_sha($options->username,$options->repo,$options->path.$newPath,$options->token));
       }   return array(
            'name' => $file['name'],
            'path' => $newPath,
            'size' => $file['size'],
            'type' => $ext,
            'mime' => @Typecho_Common::mimeContentType($newPath),
        );
    }
public static function deleteHandle($content)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitStatic');
       $err= Github_files_del($options->username,$options->token,$options->repo,$options->path.$content['attachment']->path,Github_get_sha($options->username,$options->repo,$options->path.$content['attachment']->path,$options->token));
        return !$err;
    }
public static function attachmentHandle(array $content)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitStatic');     
        return Typecho_Common::url($content['attachment']->path, "https://cdn.jsdelivr.net/gh/".$options->username."/".$options->repo."/".$options->path);
    }
private static function getSafeName(&$name)
    {
        $name = str_replace(array('"', '<', '>'), '', $name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        $info = pathinfo($name);
        $name = substr($info['basename'], 1);
        return isset($info['extension']) ? strtolower($info['extension']) : '';
    }   
    public static function attachmentDataHandle($content)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('Gitstatic');
       
    }

}