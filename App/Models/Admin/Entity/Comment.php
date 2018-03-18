<?php
namespace App\Models\Admin\Entity;

/**
 * Create a Comment entity
 */
class Comment
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
    private $nickName;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $content;
    /**
     * @var boolean
     */
    private $isValidated;
    /**
     * @var boolean
     */
    private $isPublished;
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
    public function __construct($array)
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
     * Get property: title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get property: content
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get property: isValidated
     * @return boolean
     */
    public function getIsValidated()
    {
        return $this->isValidated;
    }

    /**
     * Get property: isPublished
     * @return boolean
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Get property: postId
     * @return integer
     */
    public function getPostId()
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
        if (is_string($creationDate)) {
            $date = new \DateTime($creationDate);
            $this->creationDate = $date->format('d-m-Y H:i:s');
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
     * Set property: title
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        if (is_string($title)) {
            $this->title = $title;
        }
    }

    /**
     * Set property: content
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
        if (is_string($content)) {
            $this->content = $content;
        }
    }

    /**
     * Set property: isValidated
     * @param boolean $isValidated
     * @return void
     */
    public function setIsValidated($isValidated)
    {
        if (is_bool($isValidated)) {
            $this->isValidated = $isValidated;
        }
    }

    /**
     * Set property: isPublished
     * @param boolean $isPublished
     * @return void
     */
    public function setIsPublished($isPublished)
    {
        if (is_bool($isPublished)) {
            $this->isPublished = $isPublished;
        }
    }

    /**
     * Set property: postId
     * @param integer $postId
     * @return void
     */
    public function setPostId($postId)
    {
        $postId = (int) $postId;
        if ($postId > 0) {
            $this->postId = $postId;
        }
    }
}
