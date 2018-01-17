<?php
namespace App\Models\Admin\Entity;

class Contact
{
	private $id;
	private $sendingDate;
	private $familyName;
	private $firstname;
	private $message;

	// Temporary params which are not in database but useful in methods
	private $temporaryParams = [];

	public function __construct($array)   
	{
		$this->hydrate($array);
	}

	public function hydrate($datas)
	{
		$classShortName = (new \ReflectionClass($this))->getShortName();
		$classPrefix = strtolower($classShortName) . '_';
		foreach ($datas as $key => $value) {
			// Define setter: replace "classname_" tables fields prefix syntax by nothing
		    $method = 'set' . ucfirst(str_replace($classPrefix, '', $key)); 
		    
		    // Does setter exist?
		    if (method_exists($this, $method)) {
		      	// Call setter
		      	$this->$method($value);
		    }
		    else {
		    	// Call magic __set
		    	$this->$key = $value;
		    }
		}
	 
	}

	public function __set($name, $value)
	{	    
	    if(method_exists($this, $name)) {
	      	$this->$name($value);
	    }
	    else{
	      	// Setter is not defined so set as property of object
	      	$key = lcfirst(str_replace('set', '', $name)); 
	      	$this->temporaryParams[$key] = $value;
	    }
  	}

	public function __get($name)
	{
	    if(method_exists($this, $name)) {
	      return $this->$name();
	    }
	    elseif(property_exists($this, $name)){
	      // Getter is not defined so return property if it exists
	      return $this->$name;
	    }
	    elseif(array_key_exists($name, $this->temporaryParams)) {
	    	return $this->temporaryParams[$name];
	    }
	    else {
	    	return null;
	    }  
	}

	public function getTemporaryParams() 
	{
		return $this->temporaryParams;
	}

	// Getters
	
	public function getId() 
	{
		return $this->id;
	}

	public function getSendingDate()
	{
		return $this->sendingDate;
	}

	public function getFamilyName()
	{
		return $this->familyName;
	}

	public function getFirstName() 
	{
		return $this->firstName;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getMessage()
	{
		return $this->message;
	}
	
	// Setters
	
	public function setId($id) 
	{
		$id = (int) $id;
		if ($id > 0) {
	    	$this->id = $id;
	    }
	}

	public function setSendingDate($sendingDate)
	{
		if(is_string($sendingDate)) {
			$date = new \DateTime($sendingDate);
	      	$this->sendingDate = date_format($date, 'd-m-Y H:i:s');
	    }
	}

	public function setFamilyName($familyName) 
	{
		if(is_string($familyName)) {
	      	$this->familyName = $familyName;
	    }
	}

	public function setFirstName($firstName) 
	{
		if(is_string($firstName)) {
	      	$this->firstName = $firstName;
	    }
	}

	public function setEmail($email) 
	{
		if(is_string($email)) {
	      	$this->email = $email;
	    }
	}

	public function setMessage($message) 
	{
		if(is_string($message)) {
	      	$this->message = $message;
	    }
	}
}