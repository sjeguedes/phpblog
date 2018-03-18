<?php
namespace App\Models\Blog\Entity;

/**
 * Create a Post entity
 */
class Post
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
    private $updateDate;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $intro; // chapô
    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $slug;
    /**
     * @var integer
     */
    private $userId; // Author id
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
     * @return string
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
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
     * Get property: intro
     * @return string
     */
    public function getIntro()
    {
        return $this->intro;
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
     * Get property: slug
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get property: userId
     * @return integer
     */
    public function getUserId() // get author id
    {
        return $this->userId;
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
     * Set property: title
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        if(is_string($title)) {
            $this->title = $title;
        }
    }

    /**
     * Set property: intro
     * @param string $intro
     * @return void
     */
    public function setIntro($intro)
    {
        if (is_string($intro)) {
            $this->intro = $intro;
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
     * Set property: slug
     * @param string $slug
     * @return void
     */
    public function setSlug($slug)
    {
        if (is_string($slug)) {
            $this->slug = $slug;
        }
    }

    /**
     * Set property: userId
     * @param integer $userId
     * @return void
     */
    public function setUserId($userId)  // set author id
    {
        $userId = (int) $userId;
        if ($userId > 0) {
            $this->userId = $userId;
        }
    }
}