<?php 

	use PDOException;

	class DB{
		public $pdo;
		public $uid;
		
		public function __construct($pdo) {
			$this->pdo = $pdo;
		}

		public function otrp_data($uid)
		{
			
			$stmt = $this->pdo->prepare("SELECT *
										FROM otrp_candidate_mast
										WHERE uid=:uid");
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetch(PDO::FETCH_ASSOC);

			return false;
		}

		public function insert($args, $table)
		{
			//remove 
			unset($args['form']);
			unset($args['submit']);
			unset($args['foreign_eq']);
			unset($args['foreign_institute']);
			unset($args['eq_type']);

			//fetch old records
			$uid = strip_tags($args['uid']);
			$log_data = $this->select_by_uid($uid, $table);
			
			$count_args = count($args);
			if(count($args)){
				$fields = implode(', ', array_keys($args));
				$fields = $fields.', created_at';
			}
			
			$sql = "INSERT INTO $table ($fields) VALUES (";
			if(count($args)){
				$coo = 1;
				foreach ($args as $key => $value) {				
					if($coo == $count_args){
						$sql .= " :".$key.", :created_at) ";
					}else{
						$sql .= " :".$key.", ";
					}
					
					$coo++;				
				}			
			}			
			
			$stmt = $this->pdo->prepare($sql);
		
			foreach ($args as $key => $value) {
				$stmt->bindValue(":$key", strtoupper($value));
			}
			
			$stmt->bindValue(':created_at', 'NOW()');
			try{
				$stmt->execute();
				//insert log
				$this->otrp_logs($uid, $log_data, $table);

				//last inserted ID
				return $this->pdo->lastInsertId($table.'_id_seq');
			} catch (PDOException $e) {				
				return 'ERROR: <br>'.$e->getMessage();
			}
			
		}

		public function insert_perm_address($args, $table, $uid)
		{
			//remove
			unset($args['form']);
			unset($args['submit']);
			unset($args['uid']);			 
			unset($args['p_same_as_c']);

			//fetch old records
			$log_data = $this->select_by_uid($uid, $table);
			
			$count_args = count($args);
			if(count($args)){
				$fields = implode(', ', substr_replace(array_keys($args),'p',0, 1));
				$fields = $fields.', created_at, uid';
			}
			
			$sql = "INSERT INTO $table ($fields) VALUES (";
			if(count($args)){
				$coo = 1;
				foreach ($args as $key => $value) {				
					if($coo == $count_args){
						$sql .= " :".substr_replace($key,'p',0, 1).", :created_at, :uid) ";
					}else{
						$sql .= " :".substr_replace($key,'p',0, 1).", ";
					}
					
					$coo++;				
				}			
			}			
			
			$stmt = $this->pdo->prepare($sql);
		
			foreach ($args as $key => $value) {
				$stmt->bindValue(":".substr_replace($key,'p',0, 1), strtoupper($value));
			}
			
			$stmt->bindValue(':created_at', 'NOW()');
			$stmt->bindValue(':uid', $uid);
			try{
				$stmt->execute();
				//insert log
				$this->otrp_logs($uid, $log_data, $table);
				//last inserted ID
				return $this->pdo->lastInsertId($table.'_id_seq');
			} catch (PDOException $e) {				
				return 'ERROR: <br>'.$e->getMessage();
			}
			
		}

		public function update($args, $table)
		{
			//remove 
			unset($args['form']);
			unset($args['submit']);
			unset($args['mode']);
			unset($args['show_tab']);
			unset($args['foreign_eq']);
			unset($args['foreign_institute']);
			unset($args['eq_type']);
			$uid = $args['uid'];
			unset($args['uid']);

			//fetch old records
			$log_data = $this->select_by_uid($uid, $table);
			
			$count_args = count($args);	
			try {
				$sql = "UPDATE $table SET ";
				if(count($args)){
					$coo = 1;
					foreach ($args as $key => $value) {				
						if($coo == $count_args){
							$sql .= " $key=:".$key.", updated_at=NOW() WHERE uid=:uid";
						}else{
							$sql .= " $key=:".$key.", ";
						}
						
						$coo++;				
					}			
				}
				
				$stmt = $this->pdo->prepare($sql);
				//var_dump($stmt);die;
				foreach ($args as $key => $value) {
					$stmt->bindValue(":$key", strtoupper($value));
				}
				
				$stmt->bindValue(':uid', $uid);
				
				// execute the insert statement
				
				$stmt->execute();

				//insert log
				$this->otrp_logs($uid, $log_data, $table);

				// return generated id
				return $stmt->rowCount();
			} catch (PDOException $e) {				
				return 'ERROR: <br>'.$e->getMessage();
			}
		}


		public function update_perm_address($args, $table, $uid)
		{
			//remove
			unset($args['form']);
			unset($args['submit']);
			unset($args['mode']);
			unset($args['show_tab']);
			$uid = $args['uid'];
			unset($args['uid']);
			unset($args['p_same_as_c']);

			//fetch old records
			$log_data = $this->select_by_uid($uid, $table);

			$count_args = count($args);	
			$sql = "UPDATE $table SET ";
			if(count($args)){
				$coo = 1;
				foreach ($args as $key => $value) {				
					if($coo == $count_args){
						$sql .= " ".substr_replace($key,'p',0, 1)."=:".substr_replace($key,'p',0, 1).", updated_at=NOW() WHERE uid=:uid";
					}else{
						$sql .= " ".substr_replace($key,'p',0, 1)."=:".substr_replace($key,'p',0, 1).", ";
					}
					
					$coo++;				
				}			
			}
			
			$stmt = $this->pdo->prepare($sql);
			//var_dump($stmt);die;
			foreach ($args as $key => $value) {
				$stmt->bindValue(":".substr_replace($key,'p',0, 1), strtoupper($value));
			}
			
			$stmt->bindValue(':uid', $uid);
			
			try{
				// execute the insert statement			
				$stmt->execute();
				
				//insert log
				$this->otrp_logs($uid, $log_data, $table);
				// return generated id
				return $stmt->rowCount();
			}catch (PDOException $e) {				
				return 'ERROR: <br>'.$e->getMessage();
			}
			
		}

		public function select_by_uid($uid, $table)
		{
			
			$stmt = $this->pdo->prepare("SELECT *
										FROM $table
										WHERE uid=:uid");
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetch(PDO::FETCH_ASSOC);

			return false;
		}
		
		
		public function select_by_candidate_id($candidate_id, $table_csp_cand_mast)
		{
			
			$stmt = $this->pdo->prepare("SELECT *
										FROM $table_csp_cand_mast
										WHERE candidate_id=:candidate_id");
			$stmt->bindValue(':candidate_id', $candidate_id);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetch(PDO::FETCH_ASSOC);

			return false;
		}

		public function selectall_by_uid($uid, $table)
		{
			
			$stmt = $this->pdo->prepare("SELECT *
										FROM $table
										WHERE uid=:uid");
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return false;
		}

		public function selectall_by_uid_other($uid, $table, $columns)
		{
			$sql_str = '';
			foreach($columns as $key => $value){
				$sql_str .=  " AND $key='$value' ";
			}
			$sql = "SELECT * FROM $table WHERE uid=:uid $sql_str ";
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return false;
		}

		public function selectall_active_by_uid($uid, $table)
		{
			
			$stmt = $this->pdo->prepare("SELECT *
										FROM $table
										WHERE uid=:uid AND active='1'");
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount()){
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			
			return false;
		}

		public function delete($id, $uid, $table)
		{	

			$sql = "DELETE FROM $table WHERE id=:del_id AND uid=:uid";

			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':del_id', $id);
			$stmt->bindValue(':uid', $uid);
			
			try{
				$stmt->execute();			
        		return $stmt->rowCount();
			}catch (PDOException $e) {				
				return 'ERROR: <br>'.$e->getMessage();
			}
		}

		public function softDelete($id, $uid, $table)
		{
			$sql = "UPDATE $table SET active='0',deleted_at=NOW() WHERE id=:del_id AND uid=:uid";

			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':del_id', $id);
			$stmt->bindValue(':uid', $uid);
			
			try{
				$stmt->execute();			
        		return $stmt->rowCount();
			}catch (PDOException $e) {				
				return 'ERROR: <br>'.$e->getMessage();
			}
		}


		public function selectall($table)
		{
			
			$stmt = $this->pdo->prepare("SELECT *
										FROM $table
										ORDER BY id");
			
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return false;
		}


		public function check_upload($uid, $type)
		{
			$stmt = $this->pdo->prepare("SELECT *
										FROM otrp_candidate_upload_documents_list
										WHERE uid=:uid AND type=:type");
			$stmt->bindValue(':uid', $uid);
			$stmt->bindValue(':type', $type);
			$stmt->execute();
			
			// return the result set as an object
			if($stmt->rowCount()){
				return true;
			}
			
			return false;
		}


		public function getall_docs_by_uid($uid)
		{
			$stmt = $this->pdo->prepare("SELECT * FROM otrp_candidate_upload_documents_list list LEFT JOIN otrp_document_upload_mast mast ON(list.type=mast.document_code) WHERE uid=:uid");

			$stmt->bindValue(':uid', $uid);
			$stmt->execute();			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return false;
		}


		public function getall_pincodes($pincode)
		{
			
			$stmt = $this->pdo->prepare("SELECT office, pincode, district, state FROM pincode_mast WHERE active='1' AND pincode=:pincode");

			$stmt->bindValue(':pincode', $pincode);
			$stmt->execute();			
			// return the result set as an object
			if($stmt->rowCount())
				return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return false;
		}

		public function insert_pincode($args, $table = 'pincode_mast')
		{
			//remove 
			
			$count_args = count($args);
			if(count($args)){
				$fields = implode(', ', array_keys($args));
			}
			
			$sql = "INSERT INTO $table ($fields) VALUES (";
			if(count($args)){
				$coo = 1;
				foreach ($args as $key => $value) {				
					if($coo == $count_args){
						$sql .= " :".$key.") ";
					}else{
						$sql .= " :".$key.", ";
					}
					
					$coo++;				
				}			
			}			
			
			$stmt = $this->pdo->prepare($sql);
		
			foreach ($args as $key => $value) {
				$stmt->bindValue(":$key", strtoupper($value));
			}
			
			$stmt->execute();
			//last inserted ID
			return $this->pdo->lastInsertId($table.'_id_seq');
		}


		public function otrp_logs($uid, $data, $table)
		{			
			if(count($data)){
				$ip = $_SERVER['REMOTE_ADDR'];
				$record = json_encode($data);

				$table = $table.'_logs';

				$sql = "INSERT INTO $table (uid, data, ip, inserted_at) VALUES (:uid, :data, :ip, :inserted_at)";

				$stmt = $this->pdo->prepare($sql);
				$stmt->bindValue(":uid", $uid);
				$stmt->bindValue(":data", $record);
				$stmt->bindValue(":ip", $ip);
				$stmt->bindValue(":inserted_at", 'NOW()');

				$stmt->execute();
			}
			
		}

		

	}