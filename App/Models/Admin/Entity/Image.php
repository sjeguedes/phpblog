<?php
namespace App\Models\Admin\Entity;

/**
 * Create a Image entity
 */
class Image
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
     * @var string|null
     */
    private $updateDate;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $extension;
    /**
     * @var string
     */
    private $dimensions;
    /**
     * @var integer
     */
    private $size;
    /**
     * @var integer
     */
    private $creatorId; // authenticated user id
    /**
     * @var integer
     */
    private $postId;
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
     * Get property: updateDate
     * @return string|null
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Get property: name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get property: extension
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get property: dimensions
     * @return string
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Get property: size
     * Value corresponds to size in octets.
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get property: creatorId
     * @return integer
     */
    public function getCreatorId() // get authenticated user id who created post
    {
        return $this->creatorId;
    }

    /**
     * Get property: postId
     * @return integer
     */
    public function getPostId() // get attached post id
    {
        return $this->postId;
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
        if(is_string($creationDate)) {
            $date = new \DateTime($creationDate);
            $this->creationDate = $date->format( 'd-m-Y H:i:s');
        }
    }

    /**
     * Set property: updateDate
     * @param string $updateDate
     * @return void
     */
    public function setUpdateDate($updateDate)
    {
        if(is_string($updateDate)) {
            $date = new \DateTime($updateDate);
            $this->updateDate = $date->format( 'd-m-Y H:i:s');
        }
    }

    /**
     * Set property: name
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        if(is_string($name)) {
            $this->name = $name;
        }
    }

    /**
     * Set property: extension
     * @param string $extension
     * @return void
     */
    public function setExtension($extension)
    {
        if (is_string($extension)) {
            $this->extension = $extension;
        }
    }

    /**
     * Set property: dimensions
     * @param string $dimensions
     * @return void
     */
    public function setDimensions($dimensions)
    {
        if (is_string($dimensions)) {
            $this->dimensions = $dimensions;
        }
    }

    /**
     * Set property: size
     * Value corresponds to size in octets.
     * @param integer $size
     * @return void
     */
    public function setSize($size)
    {
        $size = (int) $size;
        if ($size > 0) {
            $this->size = $size;
        }
    }

    /**
     * Set property: creatorId
     * @param integer $creatorId
     * @return void
     */
    public function setCreatorId($creatorId)  // set user creator id
    {
        $creatorId = (int) $creatorId;
        if ($creatorId > 0) {
            $this->creatorId = $creatorId;
        }
    }

    /**
     * Set property: postId
     * @param integer $postId
     * @return void
     */
    public function setPostId($postId)  // set attached post id
    {
        $postId = (int) $postId;
        if ($postId > 0) {
            $this->postId = $postId;
        }
    }
}