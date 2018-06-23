<?php
namespace Core\Form;

use Core\Routing\AppRouter;
use Core\Helper\AppStringModifier;

/**
 * Validate form user inputs
 */
class AppFormValidator
{
    /**
     * @var AppRouter: an AppRouter instance to use
     */
    private $router;
    /**
     * @var AppConfig: an AppConfig instance for configuration to use
     */
    private $config;
    /**
     * @var AppStringModifier: an AppStringModifier helper instance to use
     */
    private $helper;
    /**
     * @var array:/$_GET values before validation
     */
    private $datas = [];
    /**
     * @var string: form prefix name to distinguish values in/$_GET
     */
    private $formIdentifier;
    /**
     * @var string: index name based on for errors which are stored in
     */
    private $errorIndex;
    /**
     * @var array:/$_GET values filtered with PHP filters
     */
    private $filteredDatas = [];
    /**
     * @var array: datas stored after validation (values and errors)
     */
    private $result = [];

    /**
     * Constructor
     *
     * @param object $router: an AppRouter instance
     * @param array $datas to validate
     * @param string $formIdentifier
     *
     * @return void
     */
    public function __construct(AppRouter $router, $datas, $formIdentifier)
    {
        $this->router = $router;
        $this->datas = $datas;
        $this->formIdentifier = $formIdentifier;
        $this->errorIndex = $this->formIdentifier . 'errors';
        $this->config = $this->router->getConfig();
        $this->helper = AppStringModifier::getInstance($router);
    }

    /**
     * Get form identifier
     *
     * @return string: identifier name
     */
    public function getFormIdentifier()
    {
        return $this->formIdentifier;
    }

    /**
     * Get form helper
     *
     * @return object: AppStringModifier instance
     */
    public function getFormHelper()
    {
        return $this->helper;
    }

    /**
     * Get result datas
     *
     * @return array: an array which contains filtered datas and error messages
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Filter each user input
     *
     * @param array $datas: datas to filter
     * @param int $inputType: chosen http request method
     *
     * @return void
     */
    public function filterDatas($datas, $inputType = INPUT_POST)
    {
        // Declare current validator to use it as argument
        $validator = $this;
        // Apply filter for each type of data
        for ($i = 0; $i < count($datas); $i++) {
            $name = $this->formIdentifier . $datas[$i]['name'];
            $filterType = $datas[$i]['filter'];
            $modifiers = $datas[$i]['modifiers'];
            switch ($filterType) {
                case 'alphanum':
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function ($data) use ($validator, $modifiers, $name) {
                            $data = $validator->modifyData($data, $modifiers);
                            $data = filter_var($data, FILTER_SANITIZE_STRING);
                            $this->result[$name] = $data;
                            return $data;
                        }
                    ]);
                    break;
                case 'email':
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function ($data) use ($validator, $modifiers, $name) {
                            $data = $validator->modifyData($data, $modifiers);
                            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
                            $this->result[$name] = $data;
                            return $data;
                        }
                    ]);
                    break;
                default:
                    $this->filteredDatas[$name] = filter_input($inputType, $name, FILTER_CALLBACK, [
                        'options' => function ($data) use ($validator, $modifiers, $name) {
                            $data = $validator->modifyData($data, $modifiers);
                            $data = filter_var($data, FILTER_UNSAFE_RAW);
                            $this->result[$name] = $data;
                            return $data;
                        }
                    ]);
                    break;
            }
        }
    }

    /**
     * Apply modifiers on data
     *
     * @param string $data: data to modify
     * @param array $helpers: format a data
     *
     * @return string: formatted data
     */
    private function modifyData($data, $helpers)
    {
        if (!is_null($helpers) && is_array($helpers)) {
            foreach ($helpers as $modifier) {
                $data = $this->helper->$modifier($data);
            }
            return $data;
        }
    }

    /**
     * Check if user input is set and user input is not an empty string
     *
     * @param string $name: field attribute name
     * @param string $label: field name to show
     * @param boolean $errorMessage: manage field error message
     *
     * @return boolean|void
     */
    public function validateRequired($name, $label, $errorMessage = true)
    {
        $name = $this->formIdentifier . $name;
        if (!$errorMessage) {
            return array_key_exists($name, $this->datas) && trim($this->datas[$name]) != '';
        } else {
            if (array_key_exists($name, $this->datas) && trim($this->datas[$name]) != '') {
                $this->result[$name] = $this->filteredDatas[$name];
            } else {
                $this->result[$this->errorIndex][$name] = 'Please fill in your ' . $label . '.';
                $this->result[$name] = '';
            }
        }
    }

    /**
     * Check if user input is a valid email
     *
     * @param string $name: field name
     * @param string $label: field name to show
     * @param string $value: field value
     *
     * @return void
     */
    public function validateEmail($name, $label, $value)
    {
        $required = $this->validateRequired($name, $label, false);
        $name = $this->formIdentifier . $name;
        if ($required) {
            if (!filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
                $this->result[$this->errorIndex][$name] = 'Sorry, <span class="text-muted">' . $value . '</span> is not a valid email address!<br>Please check its format.';
                $this->result[$name] = $value;
            } else {
                $this->result[$name] = $this->filteredDatas[$name];
            }
        } else {
            $this->result[$this->errorIndex][$name] = 'Please fill in your email address.';
        }
    }

    /**
     * Check if user input is a valid password
     *
     * @param string $name: field name
     * @param string $label: field name to show
     * @param string $value: field value
     *
     * @return void
     */
    public function validatePassword($name, $label, $value)
    {
        $required = $this->validateRequired($name, $label, false);
        $name = $this->formIdentifier . $name;
        if ($required) {
            // At least 1 number, 1 lowercase letter, 1 uppercase letter, 1 special character, a minimum of 8 characters
            $passwordFormat = '#^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$#';
            // A minimum of 8 characters
            if (strlen($value) < 8) {
                $this->result[$this->errorIndex][$name] = 'Sorry, your password must contain<br>at least 8 characters!';
                $this->result[$name] = $value;
            } elseif (!preg_match($passwordFormat, $value)) {
                $this->result[$this->errorIndex][$name] = 'Sorry, your password format is not valid!<br>Please check it or verify required characters<br>before login try.';
                $this->result[$name] = $value;
            } else {
                // No filter on user input: hash will only be checked with value in database.
                $this->result[$name] = $value;
            }
        } else {
            $this->result[$this->errorIndex][$name] = 'Please fill in your password.';
        }
    }

    /**
     * Check if password confirmation user input is identical to main password user input
     *
     * @param string $name: field name
     * @param string $password: main password
     * @param string $passwordConfirmation: password confirmation
     *
     * @return void
     */
    public function validatePasswordConfirmation($name, $password, $passwordConfirmation)
    {
        $name = $this->formIdentifier . $name;
        if ($passwordConfirmation !== $password) {
            $this->result[$this->errorIndex][$name] = 'Password confirmation does not match<br>your password!<br>Please check both to be identical.<br>Unwanted authorized space character(s) " "<br>may be an issue!';
        }
    }

    /**
     * Check if user password renewal authentication token in $_REQUEST value contains exactly 15 characters
     *
     * @param string $name: field name
     * @param string $tokenInput: user token input value
     *
     * @return void
     */
    public function validatePasswordUpdateTokenLength($name, $tokenInput)
    {
        $name = $this->formIdentifier . $name;
        if (strlen($tokenInput) != 15) {
            $this->result[$this->errorIndex][$name] = 'Sorry, your token must contain<br>exactly 15 characters!<br>Please check it.';
        }
    }

    /**
     * Validate anti CSRF token
     *
     * @param string $dynamicToken: dynamic token value in $_REQUEST with dynamic token index name
     * @param string $tokenPrefix: prefix used to override form identifier
     * if form validator manages multiple forms
     *
     * @return void
     */
    public function validateToken($dynamicToken, $tokenPrefix = null)
    {
        $prefix = !is_null($tokenPrefix) ? $tokenPrefix : $this->formIdentifier;
        if (isset($dynamicToken) && isset($_SESSION[$prefix . 'token'])) {
            // Get dynamic token index
            foreach ($_REQUEST as $key => $value) {
                if ($value === $dynamicToken) {
                    $tokenIndex =  $key;
                    break;
                }
            }
            // Check if form token value matches value stored in $_SESSION
            if ($this->checkTokenValue($dynamicToken, $prefix . 'token')) {
                // Check if form token index matches value stored in $_SESSION
                if (isset($tokenIndex) && isset($_SESSION[$prefix . 'check']) && $tokenIndex === $_SESSION[$prefix . 'check']) {
                    $this->result[$this->formIdentifier . 'check'] = true;
                }
            } else {
                // Wrong token value or wrong token dynamic index!
                $this->result[$this->formIdentifier . 'check'] = false;
            }
            // Anything else happened.
        } else {
            $this->result[$this->formIdentifier . 'check'] = false;
        }
        // Wrong token (expired or invalid (hacked) token)
        if (!$this->result[$this->formIdentifier . 'check']) {
            // Set 401 error page
            $this->setUnauthorizedFormSubmissionResponse();
        }
    }

    /**
     * Validate an uploaded image
     *
     * @param string $name: field name in $_FILES
     *
     * @return string|false: path to temporary file name or false
     */
    public function validateImageUpload($name)
    {
        try {
            $name = $this->formIdentifier . $name;
            // File is not found.
            if (!is_uploaded_file($_FILES[$name]['tmp_name'])) {
                $this->result[$this->errorIndex][$name] = 'No file is selected.';
            // File upload error
            } elseif ($_FILES[$name]['error'] > 0) {
                $this->result[$this->errorIndex][$name] = 'File can not be uploaded.';
            // Invalid file name
            } elseif (preg_match('#[\x00-\x1F\x7F-\x9F/\\\\]#', $_FILES[$name]['name'])) {
                $this->result[$this->errorIndex][$name] = 'File name is not valid.';
            } else {
                // Max size
                $maxSize = 350000; // octets
                // User file input extension
                $extension = strtolower(pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION));
                // Allowed extensions
                $validExtensions = ['jpg', 'jpeg', 'gif', 'png'];
                // Allowed dimensions
                $maxWidth = 480;
                $maxHeight = 360;
                // User input dimensions
                $imageSizes = getimagesize($_FILES[$name]['tmp_name']);
                // Size is to big.
                if ($_FILES[$name]['size'] > $maxSize) {
                    $this->result[$this->errorIndex][$name] = 'File size limit (0.350 Mo) is reached.';
                // Extension is not allowed.
                } elseif (!in_array($extension, $validExtensions)) {
                    $this->result[$this->errorIndex][$name] = 'File type is unauthorized!<br>"jpg, jpeg, gif, png" extensions are allowed.';
                // Dimensions which are too small are not allowed.
                } elseif ($imageSizes[0] < $maxWidth || $imageSizes[1] < $maxHeight) {
                    $this->result[$this->errorIndex][$name] = 'Image is too small!<br>Resizing format (width/height) is 480px/360px.';
                // Store selected file in session
                } else {
                    // Update current file data
                    if (isset($_SESSION['uploads'][$name]['tempFile'])) {
                        unset($_SESSION['uploads'][$name]['tempFile']);
                    }
                    $_SESSION['uploads'][$name]['tempFile'] = $_FILES[$name];
                    $this->result[$name] = $_SESSION['uploads'][$name]['tempFile'];
                }
            }
            // File is already uploaded, so cancel error message after new submission try with empty $_FILES.
            if (isset($_SESSION['uploads'][$name]['tempFile'])) {
                // Unset error if it exists (not to show it in case of previous selected image) and prevent image to be saved again!
                if (isset($this->result[$this->errorIndex][$name])) {
                    unset($this->result[$this->errorIndex][$name]);
                }
                // Return current file
                return $_SESSION['uploads'][$name]['tempFile'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save an uploaded image
     *
     * @param string $name: field name
     * @param boolean $temporary: generate a temporary file or not
     *
     * @return string|false: path to uploaded image or false
     */
    public function saveImageUpload($name, $temporary = false)
    {
        try {
            $name = $this->formIdentifier . $name;
            if ($temporary == false) {
                if (!isset($_SESSION['uploads'][$name]['tempFile']['saved'])) {
                    return false;
                }
                $fileName = $_SESSION['uploads'][$name]['tempFile']['saved'];
                $newFileName = bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM));
                // User file input extension
                $extension = strtolower(pathinfo($_SESSION['uploads'][$name]['tempFile']['saved'], PATHINFO_EXTENSION));
                $pathToUploadFolder = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images';
                // Create directory if it does not exist.
                if (!is_dir($pathToUploadFolder)) {
                    mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads/', 0755, true);
                    mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads/images', 0755, true);
                    // Not really a good practice to generate .htaccess for security reason
                    $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/uploads/images/.htaccess', 'a+');
                    $content = '# Define allowed files' . PHP_EOL .
                               'Order Allow,Deny' . PHP_EOL .
                               'Deny from all' . PHP_EOL .
                               '<FilesMatch "\.(jpe?g|gif|png)$">' . PHP_EOL .
                               'Order Deny,Allow' . PHP_EOL .
                               'Allow from all' . PHP_EOL .
                               '</FilesMatch>';
                    fwrite($file, $content);
                    fclose($file);
                    // Owner can read and write, others can only read.
                    chmod($file, 0644);
                }
                // Temporary file
            } else {
                if (!isset($_SESSION['uploads'][$name]['tempFile']['tmp_name'])) {
                    return false;
                }
                $fileName = $_SESSION['uploads'][$name]['tempFile']['tmp_name'];
                $newFileName = bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM));
                // User file input extension
                $extension = strtolower(pathinfo($_SESSION['uploads'][$name]['tempFile']['name'], PATHINFO_EXTENSION));
                $pathToUploadFolder = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/temp';
                // Delete previous temporary files if they exist.
                if (is_dir($pathToUploadFolder)) {
                    $files = glob($pathToUploadFolder . '/*'); // get all file names
                    foreach ($files as $file) { // iterate files
                        if (is_file($file)) {
                            @unlink($file); // delete file
                        }
                    }
                    // No directory, so create it!
                } else {
                    mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads/images/temp', 0755, true);
                }
            }
            // Get authenticated user id
            if ($temporary == false) {
                $authenticatedUser = $this->router->getSession()::isUserAuthenticated();
                $userFolder = 'ci-' . $authenticatedUser['userId'];
                // Define folder
                $folder = $pathToUploadFolder . '/' . $userFolder;
                // Create particular user directory if it does not exist.
                if (!is_dir($pathToUploadFolder. '/' . $userFolder)) {
                    mkdir($pathToUploadFolder . '/' . $userFolder, 0755, true);
                }
            } else {
                // Define folder
                $folder = $pathToUploadFolder;
            }
            // New image path
            $newImagePath = $folder . '/' . $newFileName . '.' . $extension;
            // File already exists.
            if (file_exists($folder . '/' . $newFileName . '.' . $extension)) {
                $this->result[$this->errorIndex][$name] = 'File already exists.';
                return false;
            } elseif ($temporary == true && !move_uploaded_file($fileName, $newImagePath)) {
                $this->result[$this->errorIndex][$name] = 'File can not be saved.';
                return false;
            } elseif ($temporary == false && !rename($fileName, $newImagePath)) {
                $this->result[$this->errorIndex][$name] = 'File can not be moved.';
                return false;
            // Copy renamed file in chosen folder.
            } else {
                // Store path in success result for both temporary and final file
                $this->result[$name] = $newImagePath;
                // Final file
                if ($temporary == false) {
                    // Unlink previous references to files if files are already uploaded.
                    $this->deleteUnattachedImage($name);
                    $_SESSION['uploads'][$name]['lastCreated'][] = $newImagePath;
                // Temporary file
                } else {
                    $_SESSION['uploads'][$name]['tempFile']['saved'] = $newImagePath;
                }
                return $newImagePath;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete unattached images when trying to upload
     *
     * @param string $name: field name
     *
     * @return boolean: true in case of success or false
     */
    public function deleteUnattachedImage($name)
    {
        try {
            $name = $this->formIdentifier . $name;
            // Unlink previous references to files if files are already uploaded and resized without validation
            if (isset($_SESSION['uploads'][$name]['lastCreated'])) {
                foreach ($_SESSION['uploads'][$name]['lastCreated'] as $file) {
                    @unlink($file);
                }
                unset($_SESSION['uploads'][$name]['lastCreated']);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Resize image with crop if it's necessary
     *
     * @param string $name: field name
     * @param string $imagePath: path to image to resize
     * @param integer $width: desired width
     * @param integer $height: desired height
     *
     * @return string|false: resized image complete path or false
     */
    public function resizeImageWithCrop($name, $imagePath, $width, $height)
    {
        try {
            $name = $this->formIdentifier . $name;
            // Check MIME Type.
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeTypes = [
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ];
            // Valid MIME type
            if (false !== $ext = array_search($finfo->file($imagePath), $mimeTypes, true)) {
                $mimeType = $ext;
                $imageFunction = 'image' . $mimeType; // can be imagejpeg, imagepng, imagegif
            // Stop script and avoid resizing
            } else {
                return false;
            }
            // Get image content
            $image = file_get_contents($imagePath);
            $image = imagecreatefromstring($image); // could be adpated directly if MIME type was known: imagecreatefromjpeg($image), imagecreatefrompng($image), imagecreatefromgif($image)
            // Get image size
            $currentWidth = @imagesx($image);
            $currentHeight = @imagesy($image);
            if (($currentWidth == $width) && ($currentHeight == $height)) {
                return $image; // no resizing needed
            }
            // Try max width first...
            $ratio = $width / $currentWidth;
            $newWidth = $width;
            $newHeight = $currentHeight * $ratio;
            // If that created an image smaller than what we wanted, try the other way.
            if ($newHeight < $height) {
                $ratio = $height / $currentHeight;
                $newHeight = $height;
                $newWidth = $currentWidth * $ratio;
            }
            // Create new empty image
            $image2 = imagecreatetruecolor($newWidth, $newHeight);
            // Resize old image into new
            imagecopyresampled($image2, $image, 0, 0, 0, 0, $newWidth, $newHeight, $currentWidth, $currentHeight);
            // Check to see if cropping needs to happen
            if (($newHeight != $height) || ($newWidth != $width)) {
                // Create new empty image
                $image3 = imagecreatetruecolor($width, $height);
                if ($newHeight > $height) { // crop vertically
                    $extra = $newHeight - $height;
                    $x = 0; // source x
                    $y = round($extra / 2); // source y
                    // Resize old image into new
                    imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
                } else {
                    $extra = $newWidth - $width;
                    $x = round($extra / 2); // source x
                    $y = 0; // source y
                    // Resize old image into new
                    imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
                }
                // Destroy image resource
                imagedestroy($image2);
                // Rename image with defined dimensions
                $imagePath3 = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME) . "-{$width}x{$height}." .pathinfo($imagePath, PATHINFO_EXTENSION);
                switch ($mimeType) {
                    case 'jpeg':
                        $imageFunction($image3, $imagePath3, 90); // quality 90%
                        break;
                    case 'png':
                        $imageFunction($image3, $imagePath3, null, null);
                        break;
                    case 'gif':
                        $imageFunction($image3, $imagePath3);
                        break;
                }
                $_SESSION['uploads'][$name]['lastCreated'][] = $imagePath3;
                return $imagePath3;
            } else {
                // Rename image with defined dimensions
                $imagePath2 = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME) . "-{$width}x{$height}." .pathinfo($imagePath, PATHINFO_EXTENSION);
                switch ($mimeType) {
                    case 'jpeg':
                        $imageFunction($image2, $imagePath2, 90); // quality 90%
                        break;
                    case 'png':
                        $imageFunction($image2, $imagePath2, null, null);
                        break;
                    case 'gif':
                        $imageFunction($image2, $imagePath2);
                        break;
                }
                $_SESSION['uploads'][$name]['lastCreated'][] = $imagePath2;
                return $imagePath2;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Render a 401 ("Unauthorized") form submission response
     *
     * @return string: page HTML content
     */
    public function setUnauthorizedFormSubmissionResponse()
    {
        // Call session expiration when user is authenticated and use back office
        // Redirect to login page for security reason thanks to this session var
        // Look at AdminUserController->showAdminAccess()
        if ($this->router->getSession()::isUserAuthenticated()) {
            // Run disconnection
            $this->router->getSession()::destroy();
            // Initialize state
            $_SESSION['expiredSession']['state'] = true;
            $_SESSION['expiredSession']['unauthorizedFromAdmin'] = true;
        // Call a 401 error response for forms which are not part of back office.
        } else {
            $_SESSION['unauthorizedFormSubmission'] = true;
            $this->router->getHTTPResponse()->set401ErrorResponse('<strong>Form submission is unauthorized.</strong><br>This message is mainly due to security reason.<br>This issue could also be due to inactivity (session expiration) on our website.<br>Please go back to <a href="/' . $this->router->getUrl() . '" class="normal-link" title="Previous visited page">previous page</a> and try again.', $this->router);
            exit();
        }
    }

    /**
     * Create a dynamic $_POST/$_GET check (token) index in addition to token value to prevent CSRF
     *
     * @param string $name: name of field which contains token
     *
     * @return string stored in $_SESSION
     */
    public function generateTokenIndex($name)
    {
        if (!isset($_SESSION[$name])) {
            $_SESSION[$name] = $name . mt_rand(0, mt_getrandmax());
        }
        return $_SESSION[$name];
    }

    /**
     * Create a token value to fight against CSRF
     *
     * @param string $varName: name which corresponds to token index
     *
     * @return string: value stored in $_SESSION
     */
    public function generateTokenValue($varName)
    {
        // Check if a token does not exist,
        if (!isset($_SESSION[$varName])) {
            $_SESSION[$varName] = hash('sha256', $varName . bin2hex(openssl_random_pseudo_bytes(8)) . session_id());
        }
        return $_SESSION[$varName];
    }

    /**
     * Check if created CSRF token matches token in $_POST/$_GET value
     *
     * @param string $token: $_POST/$_GET value
     * @param string $varName: name which corresponds to token index in $_SESSION
     *
     * @return boolean
     */
    public function checkTokenValue($token, $varName)
    {
        return $token === $this->generateTokenValue($varName);
    }
}
