<?php 

    require_once('header_includes.php');

    $json = file_get_contents('php://input');
    $obj = json_decode($json, true);
    $name_matches ='';
    $ret='';

    function task_save_obc_parents_data(){
        global $db, $obj;

        $type = strip_tags(trim($obj['type']));
        $fatherPost = strip_tags(trim($obj['fatherPost']));
        $fatherPayScale = strip_tags(trim($obj['fatherPayScale']));
        $fatherAppointment = strip_tags(trim($obj['fatherAppointment']));
        $fatherAge = strip_tags(trim($obj['fatherAge']));
        $fatherRecuritt = strip_tags(trim($obj['fatherRecuritt']));
        $uid = strip_tags(trim($obj['uid']));

        $table = 'obc_annexure_mother_post';
        if($type == 'father'){
            $table = 'obc_annexure_father_post';
        }

        $cosql = "SELECT count(*) AS num_rows FROM $table WHERE uid='$uid'";
        $count_row = pg_fetch_object(pg_query($db, $cosql))->num_rows;

        if($count_row >= 10){
            $return = array('error' => 'You can add only max 10 Posts', 'code' => 2);
            echo json_encode($return);
            exit();
        }

        complete_log($uid);

        $sql = "INSERT INTO $table (uid, post, payscale, date_of_appointment, age_at_appointment, method_of_recruitment, created_at) VALUES ($uid, '$fatherPost', '$fatherPayScale', '$fatherAppointment', '$fatherAge', '$fatherRecuritt', NOW())";
        if(pg_query($db, $sql)){
            $ssql = "SELECT * FROM $table WHERE uid='$uid' ORDER BY created_at";
            $data = pg_fetch_all(pg_query($db, $ssql));            
            echo json_encode($data, true);
            exit();
        }
        $return = array('error' => 'Something wrong!', 'code' => 2);
        echo json_encode($return);
        exit();

    }

    //foreign degree detail save
    function task_save_foreign_parents_data(){
        global $db, $obj;

        $type = vir_trim($obj['type']);
        $ForeignEq = vir_trim($obj['ForeignEq']);
        $ForeignInsTitute = vir_trim($obj['ForeignInsTitute']);
        $EqType = vir_trim($obj['EqType']);
       
        $uid = vir_trim($obj['uid']);

        $table = 'foreign_details';
        

        $cosql = "SELECT count(*) AS num_rows FROM $table WHERE uid='$uid'";
        $count_row = pg_fetch_object(pg_query($db, $cosql))->num_rows;

        if($count_row >= 10){
            $return = array('error' => 'You can add only max 10 Foreign Degree', 'code' => 2);
            echo json_encode($return);
            exit();
        }

        complete_log($uid);

        $sql = "INSERT INTO $table (uid, foreign_eq, foreign_institute, eq_type, created_at) VALUES ($uid, '$ForeignEq', '$ForeignInsTitute', '$EqType', NOW())";
        if(pg_query($db, $sql)){
            $ssql = "SELECT * FROM $table WHERE uid='$uid' AND eq_type='$EqType' ORDER BY created_at";
            $data = pg_fetch_all(pg_query($db, $ssql));            
            echo json_encode($data, true);
            exit();
        }
        $return = array('error' => 'Something wrong!', 'code' => 2);
        echo json_encode($return);
        exit();

    }

    //language details save
    function task_save_language_data(){
        global $db, $obj;

        $type = vir_trim($obj['type']);
        $readOnly = vir_trim($obj['readOnly']);
        $speakOnly = vir_trim($obj['speakOnly']);
        $readSpeak = vir_trim($obj['readSpeak']);
        $readSpeakWrite = vir_trim($obj['readSpeakWrite']);

        $readOnlyOthers = ($obj['readOnly'] == '24') ? vir_trim($obj['readOnlyOthers']) : '';
        $speakOnlyOthers =($obj['speakOnly'] == '24') ? vir_trim($obj['speakOnlyOthers']) : '';
        $readSpeakOthers = ($obj['readSpeak'] == '24') ? vir_trim($obj['readSpeakOthers']) : '';
        $readSpeakWriteOthers = ($obj['readSpeakWrite'] == '24') ? vir_trim($obj['readSpeakWriteOthers']) : '';

        $examPassed = vir_trim($obj['examPassed']);
        $uid = vir_trim($obj['uid']);

        $table = 'lang_candidate';
        

        $cosql = "SELECT count(*) AS num_rows FROM $table WHERE uid='$uid'";
        $count_row = pg_fetch_object(pg_query($db, $cosql))->num_rows;

        if($count_row >= 15){            
            echo 3;
            exit();
        
        }

        complete_log($uid);

        $sql = "INSERT INTO $table (uid, read_only, speak_only, read_speak, read_write_speak, exam_passed_lang, others_read_only, others_speak_only, others_read_speak, others_read_write_speak, created_at) VALUES ($uid, '$readOnly', '$speakOnly', '$readSpeak', '$readSpeakWrite', '$examPassed', '$readOnlyOthers', '$speakOnlyOthers', '$readSpeakOthers', '$readSpeakWriteOthers', NOW())";
        if(pg_query($db, $sql)){
            $ssql = "SELECT * FROM $table WHERE uid='$uid' ORDER BY created_at";
            $qqery = pg_query($db, $ssql);
            $html = '';
            while($data = pg_fetch_object($qqery)):
                $otherread = ($data->read_only == '24') ? $data->others_read_only : '';
                $otherspeak = ($data->speak_only == '24') ? $data->others_speak_only : '';
                $otherreadspeak = ($data->read_speak == '24') ? $data->others_read_speak : '';
                $otherreadspeakwrite = ($data->read_write_speak == '24') ? $data->others_read_write_speak : '';

                $html .= '<tr>
                <td class="text-center">'.get_mothertongue_by_id($data->read_only).'<p>'.$otherread.'</p></td>
                <td class="text-center">'.get_mothertongue_by_id($data->speak_only).'<p>'.$otherspeak.'</p></td>
                <td class="text-center">'.get_mothertongue_by_id($data->read_speak).'<p>'.$otherreadspeak.'</p></td>
                <td class="text-center">'.get_mothertongue_by_id($data->read_write_speak).'<p>'.$otherreadspeakwrite.'</p></td>
                <td class="text-center">'.$data->exam_passed_lang.'</td>
                <td class="text-center"><a href="./prizes_info.php?action=delete&table=lang&id='.$data->id.'"><img src="./img/icons/bin.png" style="width: 24px; height: 24px;" alt="Delete this Record?" title="Delete this Record?"/></a></td>
            </tr>';
            endwhile;           
            echo $html;
            exit();
        }
        
        echo 2;
        exit();

    }

    function task_upload(){
        global $obj;
       
		$uid = strip_tags($_POST['uuid']);
		$type = strip_tags($_POST['type']);
		
		if($_FILES['file']['tmp_name'] != ''){

			if($_FILES['file']['size']>1048576 || $_FILES['file']['error']==1 ){				
				echo json_encode(array("success" => false, "message" => "File should not be more than 1 Mb."));
				exit();
			}

			if($_FILES['file']['type'] != 'application/pdf'){				
				echo json_encode(array("success" => false, "message" => "Please upload only pdf file."));
				exit();
			}

			$tmpname_file = $_FILES['file']['tmp_name'];
			$name_file = $uid."_".$type.".pdf";
			$final_file = Constant::$upload_path.$name_file;

			if(move_uploaded_file($tmpname_file, $final_file)){
				// $name_file_1 = $_FILES['file']['name'];				
				// $check_exists = "SELECT * FROM $table WHERE uid='$uid' AND type='$type'";
				// $query_check = pg_query($db, $check_exists);

				// if(pg_num_rows($query_check)){
				// 	$query = "UPDATE $table SET updated_at=NOW() WHERE uid='$uid' AND type='$type'";	
				// }else{
				// 	$query = "INSERT INTO $table (uid, type, name, created_at) VALUES ('$uid', '$type', '$name_file', NOW())";
				// }

				// if(pg_query($db, $query)){										
				// 	echo json_encode(array("success" => true, "type" => $type, "message" => "$type document uploaded successfully!"));
				// 	exit();
				// }else{										
				// 	echo json_encode(array("success" => false, "type" => $type, "message" => "Something wrong with query executions uploading."));
				// 	exit();
				// }
			}else{								
				echo json_encode(array("success" => false, "type" => $type, "message" => "Something wrong with file uploading."));
				exit();
			}

		}
	}


    // fire some task based on user event
    if (isset($obj['task']) && !empty($obj['task'])) {
        if (is_callable("task_" . $obj['task'])) {
            flush();
            ob_start();
            echo call_user_func("task_" . $obj['task']);
            exit;
        }
    }	