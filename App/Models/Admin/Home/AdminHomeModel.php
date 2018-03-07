<?php
namespace App\Models\Admin\Home;
use App\Models\Admin\AdminModel;
use Core\Config\AppConfig;
use App\Models\Admin\Entity\Contact;
use App\Models\Blog\Entity\User;
use App\Models\Admin\Entity\Comment;

/**
 * Create an admin model for admin homepage
 */
class AdminHomeModel extends AdminModel
{
    /**
     * Constructor
     * @param AppConfig $config: an instance of AppConfig
     * @return void
     */
    public function __construct(AppConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Get a single contact with its id
     * @param string $contactId
     * @return object|boolean: a Contact entity instance or false
     */
    public function getContactById($contactId)
    {
        $query = $this->dbConnector->prepare('SELECT *
                                              FROM contacts
                                              WHERE contact_id = :contactId');
        $query->bindParam(':contactId', $contactId, \PDO::PARAM_INT);
        $query->execute();
        $datas = $query->fetch(\PDO::FETCH_ASSOC);
        // Is there a result?
        if ($datas != false) {
            $contact = new Contact($datas);
            return $contact;
        } else {
            return false;
        }
    }

    /**
     * Get a Comment entity
     * Use an external model: PostModel
     * @return array: an array of Comment entity datas
     */
    public function getCommentById($commentId) {
        return $this->externalModels['postModel']->getCommentById($commentId);
    }

    /**
     * Get Contact entities ordered by sending date
     * @return array: an array of Contact entities instances
     */
    public function getContactList()
    {
        $contacts = [];
        $query = $this->dbConnector->query('SELECT *
                                            FROM contacts
                                            ORDER BY contact_sendingDate
                                            DESC');
        while ($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $contacts[] = new Contact($datas);
        }
        return $contacts;
    }

    /**
     * Get all Comment entities ordered by creation date and by post id
     * @return array: an array which contains all Comment entities instances
     */
    public function getCommentList()
    {
        $comments = [];
        $query = $this->dbConnector->query('SELECT *
                                            FROM comments
                                            ORDER BY comment_creationDate
                                            DESC, comment_postId');
        $query->execute();

        while($datas = $query->fetch(\PDO::FETCH_ASSOC)) {
            $comments[] = new Comment($datas);
        }
        return $comments;
    }

    /**
     * Get Post entities ordered by creation date
     * Use an external model: PostModel
     * @return array: an array of Post entities instances
     */
    public function getPostList() {
        return $this->externalModels['postModel']->getList();
    }
}