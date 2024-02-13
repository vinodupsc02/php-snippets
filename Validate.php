<?php 
 
	class Validate{
		private $_passed = false,
				$_errors = array(),
				$_db = null;
		public static $district = ['4','18','44','78','117','119','148','150','153','168','225','238','259','284','316','331','334','336','389','426','443','455','467','480','511','516','539','573','578','617','651','672','753','777', '165'];

		// public function __construct() {
		// 	$this->_db = DB::getInstance();
		// }

		public function check($source, $items = array()){
			
			foreach ($items as $item => $rules) {
				foreach ($rules as $rule => $rule_value) {
					//echo "{$item} {$rule} must be {$rule_value}<br>";die;
					$value = trim($source[$item]);					
					$rule = strtolower($rule);
					if($rule == 'name'){
						$name = $rule_value;
					}
					
					if($rule === 'required' && $value == ''){												
						$this->addError("{$name}  is required");
					}else if($rule === 'number' && !is_numeric($value)){
						$this->addError("{$name} must be number!");
					}else if(!empty($value)){
						switch ($rule) {
							case 'min':
								if(strlen($value) < $rule_value){
									$this->addError("{$name} must be a minimum of {$rule_value}");
								}
								break;
							case 'max':
								if(strlen($value) > $rule_value){
									$this->addError("{$name} must be a maximum of {$rule_value}");
								}
								break;
							case 'matches':
								if($value != $source[$rule_value]){
									$this->addError("{$name} must be match with {$item}");
								}
								break;
							
							// case 'unique':
							// 	$check = $this->_db->get($rule_value, array($item, '=', $value));
							// 	if($check->count()){
							// 		$this->addError("{$item} already exists!");
							// 	}
							// 	break;
							// default:
							// 	# code...
							// 	break;
						}

					}else if($rule === 'other_check'){
						$rrrule = explode('*', $rule_value);
						$field_tocheck = $rrrule[0];
						$value_tocheck = $rrrule[1];						
						if($value_tocheck != 'dis' && $source[$field_tocheck] == $value_tocheck && $value == ''){
							$this->addError("{$name}  is required");
						}

						if($value_tocheck == 'dis' && in_array($source[$field_tocheck], self::$district) && $value == ''){
							$this->addError("{$name}  is required");
						}
					}
				}
			}
			if(empty($this->_errors)){
				$this->_passed = true;
			}

			return $this;
		}

		public function addError($error){			
			$this->_errors[] = $error;
		}

		public function errors(){
			return $this->_errors;
		}

		public function passed(){
			return $this->_passed;
		}
	}