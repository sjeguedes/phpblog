<?php
namespace App\Models\Admin\Entity;

/**
 * Create a User entity
 */
class User
{
    /**
     * @var integer
     */
    private $id;
    /**
     * @var string
     */
    private $creationDate;
    /**
     * @var string
     */
    private $familyName;
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $nickName;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string|null
     */
    private $passwordUpdateToken;
    /**
     * @var string
     */
    private $passwordUpdateDate;
    /**
     * @var string\null
     */
    private $activationCode;
    /**
     * @var string
     */
    private $activationDate;
    /**
     * @var boolean
     */
    private $isActivated;
    /**
     * @var integer
     */
    private $userTypeId;
    /**
     * @var array: an array of temporary properties which are not in database but useful in methods
     */
    private $temporaryParams = [];

    /**
     * Constructor
     * @param array $array: an array of properties
     * @return void
     */
    public function __construct(array $array)
    {
        $this->hydrate($array);
    }

    /**
     * Hydrate entity
     * @param array $datas
     * @return void
     */
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
            } else {
                // Call magic __set
                $this->$key = $value;
            }
        }
    }

    /**
     * __set() magic method
     * @param string $name: name of property
     * @param string $value: value of property to set
     * @return void
     */
    public function __set($name, $value)
    {
        if (method_exists($this, $name)) {
            $this->$name($value);
        } else {
            // Setter is not defined so set as property of object
            $key = lcfirst(str_replace('set', '', $name));
            $this->temporaryParams[$key] = $value;
        }
    }

    /**
     * __get() magic method
     * @param type $name: name of property to get
     * @return callable|string|null
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        } elseif (property_exists($this, $name)) {
            // Getter is not defined so return property if it exists
            return $this->$name;
        } elseif (array_key_exists($name, $this->temporaryParams)) {
            return $this->temporaryParams[$name];
        } else {
            return null;
        }
    }

    /**
     * Get temporary entity properties which do not exist in database
     * @return array: an array of temporary properties which are not hydrated
     */
    public function getTemporaryParams()
    {
        return $this->temporaryParams;
    }

    // Getters

    /**
     * Get property: id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get property: creationDate
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get property: familyName
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Get property: firstName
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstName;
    }

    /**
     * Get property: nickName
     * @return string
     */
    public function getNickName()
    {
        return $this->nickName;
    }

    /**
     * Get property: email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get property: password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * Get property: passwordUpdateToken
     * @return string|null
     */
    public function getPasswordUpdateToken()
    {
        return $this->passwordUpdateToken;
    }

    /**
     * Get property: passwordUpdateDate
     * @return string
     */
    public function getPasswordUpdateDate()
    {
        return $this->passwordUpdateDate;
    }

    /**
     * Get property: activationCode
     * @return string\null
     */
    public function getActivationCode()
    {
        return $this->activationCode;
    }

    /**
     * Get property: activationDate
     * @return string
     */
    public function getActivationDate()
    {
        return $this->activationDate;
    }

    /**
     * Get property: isActivated
     * @return boolean
     */
    public function getIsActivated()
    {
        return $this->isActivated;
    }

    /**
     * Get property: userTypeId
     * @return integer
     */
    public function getUserTypeId()
    {
        return $this->userTypeId;
    }

    // Setters

    /**
     * Set property: id
     * @param integer $id
     * @return void
     */
    public function setId($id)
    {
        $id = (int) $id;
        if ($id > 0) {
            $this->id = $id;
        }
    }

    /**
     * Set property: creationDate
     * @param string $creationDate
     * @return void
     */
    public function setCreationDate($creationDate)
    {
        if (is_string($creationDate)) {
            $date = new \DateTime($creationDate);
            $this->creationDate = $date->format( 'd-m-Y H:i:s');
        }
    }

    /**
     * Set property: familyName
     * @param string $familyName
     * @return void
     */
    public function setFamilyName($familyName)
    {
        if (is_string($familyName)) {
            $this->familyName = $familyName;
        }
    }

    /**
     * Set property: firstName
     * @param string $firstName
     * @return void
     */
    public function setFirstname($firstName)
    {
        if (is_string($firstName)) {
            $this->firstName = $firstName;
        }
    }

    /**
     * Set property: nickName
     * @param string $nickName
     * @return void
     */
    public function setNickName($nickName)
    {
        if (is_string($nickName)) {
            $this->nickName = $nickName;
        }
    }

    /**
     * Set property: email
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        if (is_string($email)) {
            $this->email = $email;
        }
    }

    /**
     * Set property: password
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        if (is_string($password)) {
            $this->password = $password;
        }
    }

    /**
     * Set property: passwordUpdateToken
     * @param string|null $passwordUpdateToken
     * @return void
     */
    public function setPasswordUpdateToken($passwordUpdateToken)
    {
        if (is_string($passwordUpdateToken) || is_null($passwordUpdateToken)) {
            $this->passwordUpdateToken = $passwordUpdateToken;
        }
    }

   /**
     * Set property: passwordUpdateDate
     * @param string $passwordUpdateDate
     * @return void
     */
    public function setPasswordUpdateDate($passwordUpdateDate)
    {
        if (is_string($passwordUpdateDate)) {
            $this->passwordUpdateDate = $passwordUpdateDate;
        }
    }

    /**
     * Set property: activationCode
     * @param string\null $activationCode
     * @return void
     */
    public function setActivationCode($activationCode)
    {
        if (is_string($activationCode) || is_null($activationCode)) {
            $this->activationCode = $activationCode;
        }
    }

    /**
     * Set property: activationDate
     * @param string $activationDate
     * @return void
     */
    public function setActivationDate($activationDate)
    {
        if (is_string($activationDate)) {
            $date = new \DateTime($activationDate);
            $this->activationDate = $date->format( 'd-m-Y H:i:s');
        }
    }

    /**
     * Set property: isActivated
     * @param boolean $isActivated
     * @return void
     */
    public function setIsActivated($isActivated)
    {
        $isActivated = (bool) $isActivated;
        if (is_bool($isActivated)) {
            $this->isActivated = $isActivated;
        }
    }

    /**
     * Set property: userTypeId
     * @param integer $userTypeId
     * @return void
     */
    public function setUserTypeId($userTypeId)
    {
        $userTypeId = (int) $userTypeId;
        if ($userTypeId > 0) {
            $this->userTypeId = $userTypeId;
        }
    }
}