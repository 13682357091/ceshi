
<?php


   header('Access-Control-Allow-Origin:*');//允许所有来源访问 
header('Access-Control-Allow-Method:POST,GET');//允许访问的方式 　

 $pdo = new PDO('mysql:host=localhost;dbname=puppyrobot_cms', 'root', 'Puppy_123');

$pdo->query("set names utf8");
      


$is_en = @$_GET['is_en'];
 $is_m = @$_GET['is_m'];
//$sql = 'select t.tid,nfd.nid,nfd.title from taxonomy_index as t LEFT JOIN node_field_data as nfd on nfd.nid = t.nid order by nfd.created ASC';


 if ($is_en) {
               

		$sql = 'select t.tid,t.name from taxonomy_term_field_data as t where t.vid = "service_instructions_english" order by weight ASC';


            } else {
               

				$sql = 'select t.tid,t.name from taxonomy_term_field_data as t where t.vid = "service_instructions" order by weight ASC';
            }




$smta=$pdo->query($sql);


 $rows=$smta->fetchAll(PDO::FETCH_ASSOC);

$data = array(
	'status'=> 200
);

$i = 0;

$info = array();
//print_r($rows);die;
//print_r(getNode(5));die;

if(!empty($_GET['nid'])){
	
	 $nid = $_GET['nid'];

	 //echo $nid;die;
	
	 
	 if ($nid == 87 && !$is_m) {
                $nid = 114;
      }
		

		if($is_en){
			$sql = 'select n.body_value from node__body as n left join node__field_is_english_version as nf on nf.entity_id = n.entity_id where n.entity_id = "'.$nid.'" and n.bundle = "service_instructions" and nf.field_is_english_version_value = 1 ';

		}else{
					
	
	$sql = 'select n.body_value from node__body as n left join node__field_is_english_version as nf on nf.entity_id = n.entity_id where n.entity_id = "'.$nid.'" and n.bundle = "service_instructions" ';
		}
	



		$bbaa=$pdo->query($sql);


		$data_info_message=$bbaa->fetchAll(PDO::FETCH_ASSOC);
		

		//print_R($data_info_message);die;

            if (strpos($data_info_message[0]['body_value'], 'img') !== false) {
                $info = replaceImgUri($data_info_message[0]['body_value']);
            }else{

				//echo 123;die;
				$info = $data_info_message[0]['body_value'];
			}

			//print_r($info);die;
	
}else{

	foreach($rows as $key=>$val){
		
		$node = getNode($val['tid']);
		$info[$key]['name'] = $val['name'];

              if (count($node) == 1) {
                    $info[$key]['has_child'] = 0;
                    $info[$key]['nid'] = $node[0]['nid'];
              } else {
                    $info[$key]['has_child'] = 1;
                    $info[$key]['list'] = $node;
              }


		
	}
	//print_r($info);die;
}
	//print_r
	$data['data'] = $info;

	echo json_encode($data);

	


function getNode($tid = 0){

	//if(!$tid) return false;
	 $pdo = new PDO('mysql:host=localhost;dbname=puppyrobot_cms', 'root', 'Puppy_123');
	 $pdo->query("set names utf8");
	$sql = 'select t.tid,nfd.nid,nfd.title from taxonomy_index as t left JOIN node_field_data as nfd on nfd.nid = t.nid  where t.tid= "'.$tid.'" and nfd.type = "service_instructions" order by nfd.created ASC ';
	$smta=$pdo->query($sql);
   $result_info=$smta->fetchAll(PDO::FETCH_ASSOC);
	//$data = array();
   
   return $result_info;
}

 function replaceImgUri($str = '') {
        preg_match_all('/<img[^>]+>/i', $str, $images);
        foreach ($images as $image) {
            $secureImg = str_replace('src="', (isset($_SERVER['HTTPS']) ? "src=\"https" : "src=\"http") . "://$_SERVER[HTTP_HOST]", $image);
            $str = str_replace($image, $secureImg, $str);
        }
        return $str;
    }


?>

