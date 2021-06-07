<?php
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Methods:DELETE,PUT');
    header('Content-Type:application/json');
    class props{
        protected $db;
        protected $sql;
        protected $response;
        protected $all_data=[];
        protected $column = ['s_name','s_roll','s_class'];
        protected $data;
    }
    class api extends  props{
        function __construct()
        {
            $this->db = new mysqli("localhost","root","","api");
            //examine http request
            if($_SERVER['REQUEST_METHOD']=='GET')
            {
                $this->get_Data();
            }
            if($_SERVER['REQUEST_METHOD']=='POST'){
                $this->insertData();
            }
            if($_SERVER['REQUEST_METHOD']=='PUT'){
                $this->updateData();
            }
            if($_SERVER['REQUEST_METHOD']=='DELETE'){
                $this->deleteData();
            }
        }
        function get_Data()
        {
            $this->sql = "SELECT * FROM student";
            $this->response = $this->db->query($this->sql);
            if($this->response->num_rows != 0 )
            {
                while($this->data = $this->response->fetch_assoc())
                {
                    array_push($this->all_data,$this->data);
                }
                http_response_code(200);
                echo json_encode($this->all_data);
            }
            else
            {
                http_response_code(404);
                echo json_encode(array("message"=>"Data Not found"));
            }
        }
        function insertData()
        {
            $count = 0;
            foreach($this->column as $result)
            {
                if(array_key_exists($result,$_POST))
                {
                    $count++;
                }
            }
            if($count==3)
            {
                $s_name = $_POST['s_name'];
                $s_class = $_POST['s_class'];
                $s_roll = $_POST['s_roll'];
                $this->data = "INSERT INTO student (s_name,s_roll,s_class) VALUES
                ('$s_name','$s_roll','$s_class')";
                $this->response = $this->db->query($this->data);
                if($this->response)
                {
                    http_response_code(200);
                    echo json_encode(array("message"=>"success"));
                }
                

            }
            else{
                http_response_code(501);
                echo json_encode(array("message"=>"Http error"));
            }
        }
        function updateData()
        {
            if(isset($_GET['id']))
            {
                $put = file_get_contents('php://input');
                $final = $this->prepare_data($put);
                $query = "";
                foreach($final as $key => $value)
                {
                    if(in_array($key,$this->column))
                    {
                        if($query=="")
                        {
                            $query .= "$key='$value'";
                        }
                        else
                        {
                            $query .= ", $key='$value'";
                        }
                    }
                }
                $query = "UPDATE student SET ".$query." where id=".$_GET['id'];
                $this->response =$this->db->query($query); 
                if($this->response)
                {
                    http_response_code(200);
                    echo json_encode(array("message"=>"update success"));
                }
                else
                {
                    http_response_code(500);
                    echo json_encode(array("message"=>"update failed"));
                }
            }
            else{
                http_response_code(404);
                echo json_encode(array("message"=>"Bad requests id get parameter"));
            }
            
        }
        function deleteData()
        {
            if(isset($_GET['id']))
            {
                $this->sql="DELETE FROM student WHERE id=".$_GET['id'];
                $this->response = $this->db->query($this->sql);
                if($this->response)
                {
                    http_response_code(200);
                    echo json_encode(array("message"=>"Delete success"));
                }
                else
                {
                    http_response_code(500);
                    echo json_encode(array("message"=>"Delete failed"));
                }
            }
            else
            {
                http_response_code(500);
                echo json_encode(array("message"=>"Bad requests id get parameter")); 
            }
        }
        function prepare_data($data)
        {
            $data = trim($data);
            $data=explode(";",$data);
            $i;
            $s;
            $final=array();
            for($i=1;$i<count($data);$i++)
            {
                $s=explode('----------------------------',$data[$i])[0];
                $key=explode('"',$s)[1];
                $value=trim(explode('"',$s)[2]);
                $final[$key]=$value;
                
            }
            return $final;
        }
    }
    new api();
?>