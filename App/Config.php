<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 5.4
 */
class Config {

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'loginapp';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'dbuser';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = 'Password';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    /**
     * Secret key for hashing
     * @var string
     */
    const SECRET_KEY = '9gZaS4Q7Hzt817mXRsI2pNipMXoDL943';

    /**
     * Mailgun API key
     * @var string
     */
    const MAILGUN_API_KEY = 'key-bd3da47c3ffaf32c44bcfdbe413a362f';

    /**
     * Mailgun domain
     * @var string
     */
    const MAILGUN_DOMAIN = 'sandboxafa12b76c02048abbc53f6333e7443bc.mailgun.org';
}

?>
