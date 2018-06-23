<?php
namespace App\Models\Admin\Entity;

/**
 * Create a Contact entity
 */
class Contact
{
    /**
     * @var integer
     */
    private $id;
    /**
     * @var string
     */
    private $sendingDate;
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
    private $email;
    /**
     * @var string
     */
    private $message;
    /**
     * @var array: an array of temporary properties which are not in database but useful in methods
     */
    private $temporaryParams = [];

    /**
     * Constructor
     *
     * @param array $array: an array of properties
     *
     * @return void
     */
    public function __construct($array)
    {
        $this->hydrate($array);
    }

    /**
     * Hydrate entity
     *
     * @param array $datas
     *
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
     *
     * @param string $name: name of property
     * @param string $value: value of property to set
     *
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
     *
     * @param type $name: name of property to get
     *
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
     *
     * @return array: an array of temporary properties which are not hydrated
     */
    public function getTemporaryParams()
    {
        return $this->temporaryParams;
    }

    // Getters

    /**
     * Get property: id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get property: sendingDate
     *
     * @return string
     */
    public function getSendingDate()
    {
        return $this->sendingDate;
    }

    /**
     * Get property: familyName
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Get property: firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get property: email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get property: message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    // Setters

    /**
     * Set property: id
     *
     * @param integer $id
     *
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
     * Set property: sendingDate
     *
     * @param string $sendingDate
     *
     * @return void
     */
    public function setSendingDate($sendingDate)
    {
        if (is_string($sendingDate)) {
            $date = new \DateTime($sendingDate);
            $this->sendingDate = $date->format('d-m-Y H:i:s');
        }
    }

    /**
     * Set property: familyName
     *
     * @param string $familyName
     *
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
     *
     * @param string $firstName
     *
     * @return void
     */
    public function setFirstName($firstName)
    {
        if (is_string($firstName)) {
            $this->firstName = $firstName;
        }
    }

    /**
     * Set property: email
     *
     * @param string $email
     *
     * @return void
     */
    public function setEmail($email)
    {
        if (is_string($email)) {
            $this->email = $email;
        }
    }

    /**
     * Set property: message
     *
     * @param string $message
     *
     * @return void
     */
    public function setMessage($message)
    {
        if (is_string($message)) {
            $this->message = $message;
        }
    }
}
