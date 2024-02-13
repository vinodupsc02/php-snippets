<?php
    include('header_includes.php');
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
   
    $dbc = new DB($pdo);
    
    if(isset($data['pin_code']) && !empty($data['pin_code']) && strlen($data['pin_code']) == 6){        
        $pin_code = strip_tags($data['pin_code']);
        $pin_code_field = strip_tags($data['field']);
        
        $pincodes = $dbc->getall_pincodes($pin_code);
        
        $post_office_str = '';
        if($pincodes){            
            $post_office_str .= '<div class="col-md-6 col-sm-12">
            <label for="'.$pin_code_field.'post_office" class="form-label">Post Office</label>
            <select class="form-select" name="'.$pin_code_field.'post_office" id="'.$pin_code_field.'post_office" required><option value="">--SELECT--</option>';
            foreach($pincodes as $post_name){
                $post_office_str .= '<option value="'.$post_name['office'].'">'.$post_name['office'].'</option>';
            }
            $post_office_str .= '</select></div>';
           

            

            $post_office_str .= '<div class="col-md-6 col-sm-12">
                <label for="'.$pin_code_field.'state_id" class="form-label">State</label>
                <input type="text" class="form-control readonly" readonly name="'.$pin_code_field.'state_id" id="'.$pin_code_field.'state_id" value="'.$pincodes[0]['state'].'" required>
            </div>';

            $post_office_str .= '<div class="col-md-6 col-sm-12">
                <label for="'.$pin_code_field.'district_id" class="form-label">District</label>
                <input type="text" class="form-control readonly" readonly name="'.$pin_code_field.'district_id" id="'.$pin_code_field.'district_id" value="'.$pincodes[0]['district'].'" required="">
            </div>';

        }else{

            $pinCodeUrl = 'https://api.postalpincode.in/pincode/'.$pin_code;             
       
            $ch_pin = curl_init();
            curl_setopt($ch_pin, CURLOPT_URL, trim($pinCodeUrl));
            curl_setopt($ch_pin, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch_pin, CURLOPT_NOSIGNAL, 1);
            curl_setopt($ch_pin, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch_pin, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch_pin, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch_pin, CURLOPT_TCP_NODELAY, 1);
            $output_pin = curl_exec($ch_pin);
            echo curl_error($ch_pin);
            curl_close($ch_pin);

            $pin_response1 = json_decode($output_pin, true);
            $pin_response = $pin_response1[0];

            if($pin_response['Status'] == 'Success'){                

                $total_post_office = count($pin_response['PostOffice']);
                $post_office_str = '';
                if($total_post_office>0){
                    $post_office_str .= '<div class="col-md-6 col-sm-12">
                    <label for="'.$pin_code_field.'post_office" class="form-label">Post Office</label>
                    <select class="form-select" name="'.$pin_code_field.'post_office" id="'.$pin_code_field.'post_office" required><option value="">--SELECT--</option>';
                    foreach($pin_response['PostOffice'] as $post_name){
                        $post_office_str .= '<option value="'.$post_name['Name'].'">'.$post_name['Name'].'</option>';
                        $insert = array('office' => $post_name['Name'], 'circle' => $post_name['Circle'], 'region' => $post_name['Region'], 'pincode' => $post_name['Pincode'], 'district' => $post_name['District'], 'state' => $post_name['State']);
                        $dbc->insert_pincode($insert); 
                    }
                    $post_office_str .= '</select></div>';
                }
    
                
    
                $post_office_str .= '<div class="col-md-6 col-sm-12">
                    <label for="'.$pin_code_field.'state_id" class="form-label">State</label>
                    <input type="text" class="form-control readonly" readonly name="'.$pin_code_field.'state_id" id="'.$pin_code_field.'state_id" value="'.$pin_response['PostOffice'][0]['State'].'" required>
                </div>';
    
                $post_office_str .= '<div class="col-md-6 col-sm-12">
                    <label for="'.$pin_code_field.'district_id" class="form-label">District</label>
                    <input type="text" class="form-control readonly" readonly name="'.$pin_code_field.'district_id" id="'.$pin_code_field.'district_id" value="'.$pin_response['PostOffice'][0]['District'].'" required="">
                </div>';
    
    
            }else{
                $p_id = $pin_code_field.'post_office';
                $d_id = $pin_code_field.'district_id';
                $s_id = $pin_code_field.'state_id';
                $old_p_id = (old($p_id)) ? old($p_id) : '';
                $c_state_id = (old($s_id)) ? old($s_id) : '';

                $post_office_str .= '<div class="col-md-6 col-sm-12">
                                    <label for="'.$p_id.'" class="form-label">Post Office</label>
                                    <input type="text" class="form-control" name="'.$p_id.'" id="'.$p_id.'" value="'.$old_p_id.'" required>
                                </div>';  

                $post_office_str .= '<div class="col-md-6 col-sm-12">
                                    <label for="'.$s_id.'" class="form-label">State</label>
                                    <select id="'.$s_id.'" name="'.$s_id.'" class="form-select check-other" onchange="getDistrict(this, '.$d_id.')" required>
                                        <option value="">Select State</option>
                                        '.select_state_name($c_state_id).'
                                    </select>
                                </div>';

                $post_office_str .= '<div class="col-md-6 col-sm-12"><label for="'.$d_id.'" class="form-label">District</label><select id="'.$d_id.'" name="'.$d_id.'" class="form-select check-other" required></select></div>';
            }
            
        }

        echo $post_office_str;
        exit();
    }


    function task_get_address(){
        global $dbc, $data;
        
        $type = strip_tags(trim($data['type']));
        $uid = strip_tags(trim($data['uid']));

        $data = '';
        if($type == 'corr'){
            $data = $dbc->select_by_uid($uid, 'correspondence_address');
        }elseif($type == 'perm'){
            $data = $dbc->select_by_uid($uid, 'permanent_address');
        }else{
            $dataf = $dbc->select_by_uid($uid, 'father_details');
            
            $data = array('f_address' => $dataf['f_address'], 'f_post_office' => $dataf['f_post_office'], 'f_city' => $dataf['f_city'], 'f_state_other' => $dataf['f_state_other'], 'f_district_other' => $dataf['f_district_other'], 'f_pincode' => $dataf['f_pincode'], 'f_state_id' => $dataf['f_state_id'], 'f_district_id' => $dataf['f_district_id']);           
        }

        echo json_encode($data);       
        exit();
    }


    // fire some task based on user event
    if (isset($data['task']) && !empty($data['task'])) {
        if (is_callable("task_" . $data['task'])) {
            flush();
            ob_start();
            echo call_user_func("task_" . $data['task']);
            exit;
        }
    }	