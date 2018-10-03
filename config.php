<?php

define( 'BASE_PATH', 'http://localhost/youtube/');
//define( 'REDIRECT_URI', 'http://vfastsoft.com/youtube/admin/controller/c_channel.php?controller=channel&action=channel_add_view');
define( 'REDIRECT_URI', 'http://localhost/youtube/admin/controller/c_channel.php?controller=channel&action=channel_add_view');


//define('DB_USERNAME', 'duydn');
//define('DB_PASSWORD', '123456a@');
//define('DB_HOST', '108.61.89.2');

define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

define('DB_NAME', 'youtube_system');

	class ConnectionDB
	{		
		public $db;
		public function __construct()
		{
			// Create connection
			$this->db = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);	
			mysqli_query($this->db,"SET NAMES UTF8");
		}
        public function fetch($sql)//list du lieu
        {
            $result=mysqli_query($this->db,$sql);
            $arr_fetch=array();
            while($rows=mysqli_fetch_array($result))
            {
                $arr_fetch[]=$rows;
            }
            return $arr_fetch;
        }
		public function num_rows($sql)
		{
			$result = mysqli_query($this->db,$sql);
			return mysqli_num_rows($result);
		}
		public function query($sql)
		{
			mysqli_query($this->db,$sql);
		}
		public function query_get_id($sql)
		{
			mysqli_query($this->db,$sql);
			$currentId = mysqli_insert_id($this->db);
			return $currentId;
		}
		public function fetch_one($sql)
		{

			$result=mysqli_query($this->db,$sql);
			if(mysqli_num_rows($result) > 0)
			{
				$rows=mysqli_fetch_array($result);
			}
			else
                $rows=null;
			return $rows;
		}
	}
?>