<?php
namespace App\Models\Admin\Entity;

class User
{
	private $id;
	private $creationDate;
	private $name;
	private $firstname;
	private $pseudo; 
	private $email;
	private $password;
	private $activationCode;
	private $activationDate;
	private $isActivated;
	private $userTypeId;

	// Temporary params which are not in database but useful in methods
	private $temporaryParams = [];

	public function __construct(array $array)   
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

	public function getCreationDate()
	{
		return $this->creationDate;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getFirstname() 
	{
		return $this->firstname;
	}

	public function getPseudo()
	{
		return $this->pseudo;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getActivationCode()
	{
		return $this->activationCode;
	}

	public function getActivationDate()
	{
		return $this->activationDate;
	}

	public function getIsActivated()
	{
		return $this->isActivated;
	}

	public function getUserTypeId() 
	{
		return $this->userTypeId;
	}
	
	// Setters
	
	public function setId($id) 
	{
		$id = (int) $id;
		if ($id > 0) {
	    	$this->id = $id;
	    }
	}

	public function setCreationDate($creationDate)
	{
		if(is_string($creationDate)) {
			$date = new \DateTime($creationDate);
	      	$this->creationDate = date_format($date, 'd-m-Y H:i:s');
	    }
	}

	public function setName($name)
	{
		if(is_string($name)) {
	      	$this->name = $name;
	    }
	}

	public function setFirstname($firstname) 
	{
		if(is_string($firstname)) {
	      	$this->firstname = $firstname;
	    }
	}

	public function setPseudo($pseudo)
	{
		if(is_string($pseudo)) {
	      	$this->pseudo = $pseudo;
	    }
	}

	public function setEmail($email)
	{
		if(is_string($email)) {
	      	$this->email = $email;
	    }
	}

	public function setPassword($password)
	{
		if(is_string($password)) {
	      	$this->password = $password;
	    }
	}

	public function setActivationCode($activationCode)
	{
		if(is_string($activationCode)) {
	      	$this->activationCode = $activationCode;
	    }
	}

	public function setActivationDate($activationDate)
	{
		if(is_string($activationDate)) {
			$date = new \DateTime($activationDate);
	      	$this->activationDate = date_format($date, 'd-m-Y H:i:s');
	    }
	}

	public function setIsActivated($isActivated)
	{
		$isActivated = (bool) $isActivated;
		if(is_bool($isActivated)) {
	      	$this->isActivated = $isActivated;
	    }
	}

	public function setUserTypeId($userTypeId) 
	{
		$userTypeId = (int) $userTypeId;
		if ($userTypeId > 0) {
	    	$this->userTypeId = $userTypeId;
	    }
	}
}