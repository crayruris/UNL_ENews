<?php
class UNL_ENews_Controller
{
    /**
     * Options array
     * Will include $_GET vars, this is the newsroom being used across views 
     */
    public $options = array('view' => 'submit', 'format' => 'html', 'newsroom' => '1');
    
    protected $view_map = array('newsletter'  => 'UNL_ENews_Newsletter_Public',
                                'latest'      => 'UNL_ENews_StoryList_Latest',
                                'mynews'      => 'UNL_ENews_User_StoryList',
                                'story'       => 'UNL_ENews_Story',
                                'submit'      => 'UNL_ENews_Submission',
                                'thanks'      => 'UNL_ENews_Confirmation',
                                'manager'     => 'UNL_ENews_Manager',
                                'file'        => 'UNL_ENews_File',
                                'preview'     => 'UNL_ENews_Newsletter_Preview',
                                'newsletters' => 'UNL_ENews_Newsroom_Newsletters',
                                'sendnews'    => 'UNL_ENews_EmailDistributor',
                                'help'        => 'UNL_ENews_Help',
                                'newsroom'    => 'UNL_ENews_Newsroom',
    ); 
    
    public static $pagetitle = array('latest'      => 'Latest News',
                                     'submit'      => 'Submit an Item',
                                     'manager'     => 'Manage News',
                                     'preview'     => 'Build Newsletter',
                                     'newsletters' => 'Newsletters',
    );
    
    protected static $auth;
    
    protected static $admins = array('admin'
        );
    
    /**
     * The currently logged in user.
     * 
     * @var UNL_ENews_User
     */
    protected static $user = false;
    
    public static $url = '';
    
    public static $db_user = 'enews';
    
    public static $db_pass = 'enews';
    
    public $actionable = array();
    
    function __construct($options)
    {
        $options += $this->options;
        $this->options = $options;
        $this->authenticate(true);
        
        try {
            if (!empty($_POST)) {
                $this->handlePost();
            }
            $this->run();
        } catch(Exception $e) {
            $this->actionable[] = $e;
        }
    }
    
    public static function setAdmins($admins = array())
    {
        self::$admins = $admins;
    }
    
    /**
     * Log in the current user
     * 
     * @return void
     */
    static function authenticate($logoutonly = false)
    {
        if (isset($_GET['logout'])) {
            self::$auth = UNL_Auth::factory('SimpleCAS');
            self::$auth->logout();
        }
        if ($logoutonly) {
            return true;
        }

        self::$auth = UNL_Auth::factory('SimpleCAS');
        self::$auth->login();
        
        if (!self::$auth->isLoggedIn()) {
            throw new Exception('You must log in to view this resource!');
            exit();
        }
        self::$user = UNL_ENews_User::getByUID(self::$auth->getUser());
        self::$user->last_login = date('Y-m-d H:i:s');
        self::$user->update();
    }
    
    /**
     * get the currently logged in user
     * 
     * @return UNL_ENews_User
     */
    public static function getUser($forceAuth = false)
    {
        if (self::$user) {
            return self::$user;
        }
        
        if ($forceAuth) {
            self::authenticate();
        }
        
        return self::$user;
    }
    
    function handlePost()
    {
        $this->filterPostValues();
        switch($_POST['_type']) {
            case 'story':
                if (!empty($_POST['storyid'])) {
                    if (!($story = UNL_ENews_Story::getByID($_POST['storyid']))) {
                        throw new Exception('The story could not be retrieved');
                    }
                    if (!$story->userCanEdit(UNL_ENews_Controller::getUser(true))) {
                        throw new Exception('You cannot edit that story.');
                    }
                } else {
                    $story = new UNL_ENews_Story;
                }
                self::setObjectFromArray($story, $_POST);

                if (!$story->save()) {
                    throw new Exception('Could not save the story');
                }

                foreach ($_POST['newsroom_id'] as $id) {
                    if (!$newsroom = UNL_ENews_Newsroom::getByID($id)) {
                        throw new Exception('Invalid newsroom selected');
                    }
                    $status = 'pending';
                    if (UNL_ENews_Controller::getUser(true)->hasPermission($newsroom->id)) {
                        $status = 'approved';
                    }
                    $newsroom->addStory($story, $status, UNL_ENews_Controller::getUser(true), 'create event form');
                }

                if (isset($_POST['ajaxupload'])) {
                    echo $story->id;
                    exit();
                }

                header('Location: ?view=thanks&_type='.$_POST['_type']);
                exit();
            case 'file':
                if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
                    throw new Exception("Error Uploading File!");
                }

                if (!$story = UNL_ENews_Story::getByID((int)$_POST['storyid'])) {
                    throw new Exception('Cannot get story to add file for!');
                }

                $file = new UNL_ENews_File;

                $file_data         = $_FILES['image'];
                $file_data['data'] = file_get_contents($_FILES['image']['tmp_name']);

                self::setObjectFromArray($file, $file_data);

                if (isset($this->options['ajaxupload'])) {
                    if (!UNL_ENews_File::validFileName($_FILES['image']['name'])) {
                        throw new Exception('Please Upload an Image in .jpg .png or .gif format.');
                    }
                    $file->use_for = 'originalimage'; 
                }

                if (!$file->save()) {
                    throw new Exception('Error saving the file');
                }

                $story->addFile($file);

                if (!isset($this->options['ajaxupload'])) {
                    header('Location: ?view=thanks&_type='.$_POST['_type']);
                    exit();
                }

                //We're doing the ajax upload in step 3 of the submission form, so delete the previous photo
                foreach ($story->getFiles() as $curfile) {
                    if (preg_match('/^image/', $curfile->type)) {
                        //Check to see that we Don't Delete the File we just uploaded
                        if ($curfile->id != $file->id) {
                            $story->removeFile($curfile);
                            $curfile->delete();
                        }
                    }
                }
                //Output the image that will be shown on step 3 of submission page
                header('Location: ?view=file&id='.$file->id);
                exit();
            case 'savethumb':
                if (!($story = UNL_ENews_Story::getByID((int)$_POST['storyid']))) {
                    throw new Exception('Could not find that story!');
                }

                //If there is an existing thumbnail we know we're in editing mode...
                //...and if no coords have been selected we keep existing thumbnail and exit
                if ($story->getFileByUse('thumbnail') && empty($_POST['x1'])) {
                    header('Location: ?view=thanks&_type=story');
                    exit();
                }

                //Delete existing thumbnail
                if ($thumb = $story->getFileByUse('thumbnail')) {
                    $story->removeFile($thumb);
                    $thumb->delete();
                }

                // Get the original, and make a new thumbnail
                $file = $story->getFileByUse('originalimage');
                $thumb = $file->saveThumbnail();
                $story->addFile($thumb);

                header('Location: ?view=thanks&_type=story');
                exit();
                break;
            case 'deletenewsletter':
                if (!($newsletter = UNL_ENews_Newsletter::getByID($_POST['newsletter_id']))) {
                    throw new Exception('Invalid newsletter selected for delete');
                }
                if (UNL_ENews_Controller::getUser(true)->hasPermission($newsletter->newsroom_id)) {
                    $newsletter->delete();
                }
                break;
        }
    }
    
    function filterPostValues()
    {
        unset($_POST['uid']);
        unset($_POST['id']);
    }
    
    public static function getURL()
    {
        return self::$url;
    }
    
    function run()
    {
         if (isset($this->view_map[$this->options['view']])) {
         //    $this->options['controller'] = $this;
             $this->actionable[] = new $this->view_map[$this->options['view']]($this->options);
         } else {
             throw new Exception('Un-registered view');
         }
    }
    
    public static function setObjectFromArray(&$object, $values)
    {
        if (!isset($object)) {
            throw new Exception('No object passed!');
        }
        foreach (get_object_vars($object) as $key=>$default_value) {
            if (isset($values[$key]) && !empty($values[$key])) {
                $object->$key = $values[$key]; 
            }
        }
    }
    
    /**
     * 
     * @return mysqli
     */
    public static function getDB()
    {
        $mysqli = new mysqli('localhost', self::$db_user, self::$db_pass, 'enews');
        if (mysqli_connect_error()) {
            throw new Exception('Database connection error (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());
        }
        $mysqli->set_charset('utf8');
        return $mysqli;
    }
    
    public static function isAdmin($uid)
    {
        if (in_array($uid, self::$admins)) {
            return true;
        }
        
        return false;
    }
}
