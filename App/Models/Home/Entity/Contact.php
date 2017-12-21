<?php
namespace App\Models\Home\Entity;

class Contact
{
	private $id;
	private $sendingDate;
	private $name;
	private $firstname;
	private $message;

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
	
	
	// Setters
}