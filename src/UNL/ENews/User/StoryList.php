<?php
class UNL_ENews_User_StoryList extends UNL_ENews_StoryList
{
    public $options = array('uid'=>NULL);

    function __construct($options = array())
    {
        $this->options = $options + $this->options;
        
        if (!isset($this->options['uid'])) {
            $this->options['uid'] = UNL_ENews_Controller::getUser(true);
        }

        $stories = array();
        $mysqli = UNL_ENews_Controller::getDB();
        $sql = 'SELECT id FROM stories WHERE uid_created = "'.$mysqli->escape_string($this->options['uid']).'" ORDER BY date_submitted DESC;';
        if ($result = $mysqli->query($sql)) {
            while($row = $result->fetch_array(MYSQLI_NUM)) {
                $stories[] = $row[0];
            }
        }
        $mysqli->close();
        parent::__construct($stories);
    }
}