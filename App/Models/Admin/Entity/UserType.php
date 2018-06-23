<?php
namespace App\Models\Admin\Entity;

/**
 * Create a UserType entity
 */
class UserType
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
    private $label;
    /**
     * @var string
     */
    private $slugName;
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
    public function __construct(array $array)
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
     * Get property: creationDate
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get property: label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get property: slugName
     *
     * @return string
     */
    public function getSlugName()
    {
        return $this->slugName;
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
     * Set property: creationDate
     *
     * @param string $creationDate
     *
     * @return void
     */
    public function setCreationDate($creationDate)
    {
        if (is_string($creationDate)) {
            $date = new \DateTime($creationDate);
            $this->creationDate = $date->format('d-m-Y H:i:s');
        }
    }

    /**
     * Set property: label
     *
     * @param string $label
     *
     * @return void
     */
    public function setLabel($label)
    {
        if (is_string($label)) {
            $this->label = $label;
        }
    }

    /**
     * Set property: slugName
     *
     * @param string $slugName
     *
     * @return void
     */
    public function setSlugName($slugName)
    {
        if (is_string($slugName)) {
            $this->slugName = $slugName;
        }
    }
}
