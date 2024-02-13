<?php
    
    include('connectDatabase.php');
    include('functions.php');
    include('util_functions.php');
	$json = file_get_contents('php://input');
    $obj = json_decode($json, true);


    function task_select_district_statename(){
        global $db, $obj;
        
        $state_code = strip_tags(trim($obj['state']));
        $district = strip_tags(trim($obj['district']));

        $sql = "SELECT districtid, districtname FROM district_mast d LEFT JOIN state_mast s ON (d.state_code=s.state_code) WHERE s.state='$state_code' ORDER BY districtname";

        $query = pg_query($db, $sql);

        $district_str = '<option value=""> Select District Name</option>';

        while($data_district = pg_fetch_object($query)){
            $selected = ($data_district->districtname == $district) ? ' selected="selected" ' : '';            
            $district_str .= "<option $selected value='{$data_district->districtname}'>{$data_district->districtname}</option>";
        }

        echo $district_str;
        exit();
    }


    function task_save_appear_data(){
        global $db, $obj;
       
        $appearExam = strip_tags(trim($obj['appearExam']));
        $appearCount = strip_tags(trim($obj['appearCount']));

        if(count($obj['appYears']) != $appearCount){
            $return = array('error' => 'Something wrong with YEARs!', 'code' => 2);
            echo json_encode($return);
            exit(); 
        }

        if(count($obj['appRollnos']) != $appearCount){
            $return = array('error' => 'Something wrong with ROLL NO.s!', 'code' => 2);
            echo json_encode($return);
            exit(); 
        }

        $appearyears = implode(',', $obj['appYears']);
        $appearrollnos =  implode(',', $obj['appRollnos']);
        $uid = strip_tags(trim($obj['uid']));

        $cosql = "SELECT count(*) AS num_rows FROM upscexam_appear_data WHERE uid='$uid'";
        $count_row = pg_fetch_object(pg_query($db, $cosql))->num_rows;

        if($count_row >= 20){
            $return = array('error' => 'You can add only max 20 Posts', 'code' => 2);
            echo json_encode($return);
            exit();
        }

        complete_log($uid);

        $sql = "INSERT INTO upscexam_appear_data (uid, exam_id, years, roll_nos, appear_count, created_at) VALUES ($uid, $appearExam, '$appearyears', '$appearrollnos', '$appearCount', NOW())";
        if(pg_query($db, $sql)){

            $ssql = "SELECT d.*, s.exam_name FROM upscexam_appear_data d INNER JOIN exam_mast s ON (d.exam_id=s.id) WHERE d.uid='$uid' ORDER BY created_at";
            $data = pg_fetch_all(pg_query($db, $ssql));
            
            echo json_encode($data, true);
            exit();
        }
        $return = array('error' => 'Something wrong!', 'code' => 2);
        echo json_encode($return);
        exit();

    }


// EWS Annex
	   //get family details
    function task_get_family_details(){
        global $db, $obj;
		
		$uid = strip_tags(trim($obj['uid']));        
        $relation = strip_tags(trim($obj['relation']));

        $sql = "SELECT name, dob, father_name, mother_name FROM candidate_mast WHERE uid='$uid'";
				
        if($relation == 'SELF' || $relation == 'FATHER' || $relation == 'MOTHER'){
            if($execute_query = pg_query($db, $sql)){
                $cand_data = pg_fetch_array($execute_query); 
                   	
                if($relation == 'SELF'){
                    $name = $cand_data['name'];                        
                    $dob =  $cand_data['dob'];
                    $return = array('name' => $name, 'dob' => $dob);
                }
                if($relation == 'FATHER'){
                    $f_name = $cand_data['father_name'];
                    $return = array('name' => $f_name);
                }
                if($relation == 'MOTHER'){
                    $m_name = $cand_data['mother_name'];
                    $return = array('name' => $m_name);
                }				
                echo json_encode($return);
                exit();
            }else{
                echo 0;
                exit();
            }
        }else{
            echo 0;
            exit();
        }
        
    }
    
    function task_delete_ews_file(){
        global $db, $obj;
        //return 1;
        $column_name = strip_tags(trim($obj['delete_field']));
        $uid = strip_tags(trim($obj['candUid']));

        
        $unlink_file = '';

        if($column_name == 'name_property_file'){
            $unlink_file = DOCUMENTLOCATIONEWS.$uid.'_property.pdf';
        }
        if($column_name == 'ews_other_doc_file'){
            $unlink_file = DOCUMENTLOCATIONEWS.$uid.'_ewsotherdoc.pdf';
        }
        

        $update_ews_file_query = "UPDATE ews_annexure SET $column_name='' WHERE uid='$uid'";        
        $update_ews_file_run_query = pg_query($db, $update_ews_file_query);

        if($update_ews_file_run_query && $unlink_file != ''){
            unlink($unlink_file);
            return 1;
        }

        return 0;

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