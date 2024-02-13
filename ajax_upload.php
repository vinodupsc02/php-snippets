<?php
	include('connectDatabase.php');
	include('./classes/constant.php');

	$table = 'upload_documents_list';

	if(isset($_POST['type']) && isset($_POST['uuid']) && isset($_POST['task']) && $_POST['task'] == 'upload'){
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
				$name_file_1 = $_FILES['file']['name'];				
				$check_exists = "SELECT * FROM $table WHERE uid='$uid' AND type='$type'";
				$query_check = pg_query($db, $check_exists);

				if(pg_num_rows($query_check)){
					$query = "UPDATE $table SET updated_at=NOW() WHERE uid='$uid' AND type='$type'";	
				}else{
					$query = "INSERT INTO $table (uid, type, name, created_at) VALUES ('$uid', '$type', '$name_file', NOW())";
				}

				if(pg_query($db, $query)){										
					echo json_encode(array("success" => true, "type" => $type, "message" => "$type document uploaded successfully!"));
					exit();
				}else{										
					echo json_encode(array("success" => false, "type" => $type, "message" => "Something wrong with query executions uploading."));
					exit();
				}
			}else{								
				echo json_encode(array("success" => false, "type" => $type, "message" => "Something wrong with file uploading."));
				exit();
			}

		}
	}

	if(isset($_POST['type']) && isset($_POST['uuid']) && isset($_POST['task']) && $_POST['task'] == 'delete'){
		$uid = strip_tags($_POST['uuid']);
		$type = strip_tags($_POST['type']);

		$name_file = $uid."_".$type.".pdf";
		$final_file = Constant::$upload_path.$name_file;

		$check_exists = "SELECT * FROM $table WHERE uid='$uid' AND type='$type'";
		$query_check = pg_query($db, $check_exists);

		if(pg_num_rows($query_check)){
			$query = "DELETE FROM $table WHERE uid='$uid' AND type='$type'";
			
			if(pg_query($db, $query)){
				unlink($final_file);
				echo json_encode(array("success" => true, "type" => $type, "message" => "Uploaded Document deleted successfully!"));
				exit();
			}
		}

		echo json_encode(array("success" => false, "type" => $type, "message" => "Something wrong with request!"));
		exit();
	}