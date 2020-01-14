<?php



namespace XRA\Extend\Library;

class InstallHelpers
{
    const STATUS_OK = 'Ok';
    const STATUS_WARNING = 'Warning';
    const STATUS_ERROR = 'Error';

    public static function validate_php(&$results)
    {
        if (-1 == \version_compare(PHP_VERSION, '5.5')) {
            $results[] = new \XRA\Extend\Library\TestResult('Minimum PHP version required in order to run Faveo HELPDESK is PHP 5.5. Your PHP version: '.PHP_VERSION, self::STATUS_ERROR);

            return false;
        } else {
            $results[] = new \XRA\Extend\Library\TestResult('Your PHP version is '.PHP_VERSION, self::STATUS_OK);

            return true;
        } // if
    }

    // validate_php

    /**
     * Convert filesize value from php.ini to bytes.
     *
     * Convert PHP config value (2M, 8M, 200K...) to bytes. This function was taken  from PHP documentation. $val is string
     * value that need to be converted
     *
     * @param string $val
     *
     * @return int
     */
    public static function php_config_value_to_bytes($val)
    {
        $val = \trim($val);
        $last = \mb_strtolower($val[\mb_strlen($val) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        } // if

        return (int) $val;
    }

    // php_config_value_to_bytes

    /**
     * to check file permissions.
     */
    public static function checkFilePermission(&$results)
    {
        $path2 = base_path().\DIRECTORY_SEPARATOR.'storage';
        $f2 = \mb_substr(\sprintf('%o', \fileperms($path2)), -3);
        if (\file_exists(base_path().\DIRECTORY_SEPARATOR.'example.env')) {
            $path1 = base_path().\DIRECTORY_SEPARATOR.'example.env';
            $f1 = \mb_substr(\sprintf('%o', \fileperms($path1)), -3);
        } else {
            $f1 = '644';
        }
        if ($f1 >= '644' && $f2 >= '755') {
            $results[] = new \XRA\Extend\Library\TestResult('File permission looks fine', self::STATUS_OK);

            return true;
        } else {
            if (isset($path1)) {
                $results[] = new \XRA\Extend\Library\TestResult('File permissions needed.<ul><b>Change file permission for following files</b><li>'.$path1.'%nbsp: \'644\'</li><li>'.$path2.'%nbsp: \'755\'</li></ul></br>Change the permission manually on your server or <a href="change-file-permission">click here.</a>', self::STATUS_ERROR);
            } else {
                $results[] = new \XRA\Extend\Library\TestResult('File permissions needed.<ul><b>Change file permission to "755" for following files</b><li>'.$path2.'</li></ul></br>Change the permission manually on your server or <a href="change-file-permission">click here.</a>', self::STATUS_ERROR);
            }

            return false;
        }
    }

    /**
     * Validate memory limit.
     *
     * @param array $result
     */
    public static function validate_memory_limit(&$results)
    {
        $memory_limit = self::php_config_value_to_bytes(\ini_get('memory_limit'));

        $formatted_memory_limit = -1 === $memory_limit ? 'unlimited' : self::format_file_size($memory_limit);

        if (-1 === $memory_limit || $memory_limit >= 67108864) {
            $results[] = new \XRA\Extend\Library\TestResult('Your memory limit is: '.$formatted_memory_limit, self::STATUS_OK);

            return true;
        } else {
            $results[] = new \XRA\Extend\Library\TestResult('Your memory is too low to complete the installation. Minimal value is 64MB, and you have it set to '.$formatted_memory_limit, self::STATUS_ERROR);

            return false;
        } // if
    }

    // validate_memory_limit

    /**
     * Format filesize.
     *
     * @param string $value
     *
     * @return string
     */
    public static function format_file_size($value)
    {
        $data = [
            'TB' => 1099511627776,
            'GB' => 1073741824,
            'MB' => 1048576,
            'kb' => 1024,
        ];

        // commented because of integer overflow on 32bit sistems
        // http://php.net/manual/en/language.types.integer.php#language.types.integer.overflow
        // $value = (integer) $value;
        foreach ($data as $unit => $bytes) {
            $in_unit = $value / $bytes;
            if ($in_unit > 0.9) {
                return \trim(\trim(\number_format($in_unit, 2), '0'), '.').$unit;
            } // if
        } // foreach

        return $value.'b';
    }

    // format_file_size

    public static function validate_zend_compatibility_mode(&$results)
    {
        $ok = true;

        if (\version_compare(PHP_VERSION, '5.0') >= 0) {
            if (\ini_get('zend.ze1_compatibility_mode')) {
                $results[] = new \XRA\Extend\Library\TestResult('zend.ze1_compatibility_mode is set to On. This can cause some strange problems. It is strongly suggested to turn this value to Off (in your php.ini file)', self::STATUS_WARNING);
                $ok = false;
            } else {
                $results[] = new \XRA\Extend\Library\TestResult('zend.ze1_compatibility_mode is turned Off', self::STATUS_OK);
            } // if
        } // if

        return $ok;
    }

    // validate_zend_compatibility_mode

    public static function validate_extensions(&$results)
    {
        $ok = true;

        $required_extensions = ['mcrypt', 'openssl', 'pdo', 'fileinfo', 'curl', 'zip', 'mbstring'];

        foreach ($required_extensions as $required_extension) {
            if (\extension_loaded($required_extension)) {
                $results[] = new \XRA\Extend\Library\TestResult("Required extension '$required_extension' found", self::STATUS_OK);
            } else {
                $results[] = new \XRA\Extend\Library\TestResult("Extension '$required_extension' is required in order to run Faveo Helpdesk ", self::STATUS_ERROR);
                $ok = false;
            } // if
        } // foreach

        // Check for eAccelerator
        if (\extension_loaded('eAccelerator') && \ini_get('eaccelerator.enable')) {
            $results[] = new \XRA\Extend\Library\TestResult('eAccelerator opcode cache enabled. <span class="details">eAccelerator opcode cache causes Faveo Helpdesk to crash. <a href="https://eaccelerator.net/wiki/Settings">Disable it</a> for folder where Faveo Helpdesk is installed, or use APC instead: <a href="http://www.php.net/apc">http://www.php.net/apc</a>.</span>', self::STATUS_ERROR);
            $ok = false;
        } // if

        // Check for XCache
        if (\extension_loaded('XCache') && \ini_get('xcache.cacher')) {
            $results[] = new \XRA\Extend\Library\TestResult('XCache opcode cache enabled. <span class="details">XCache opcode cache causes Faveo Helpdesk to crash. <a href="http://xcache.lighttpd.net/wiki/XcacheIni">Disable it</a> for folder where Faveo Helpdesk is installed, or use APC instead: <a href="http://www.php.net/apc">http://www.php.net/apc</a>.</span>', self::STATUS_ERROR);
            $ok = false;
        } // if

        $recommended_extensions = [
            // 'gd' => 'GD is used for image manipulation. Without it, system is not able to create thumbnails for files or manage avatars, logos and project icons. Please refer to <a href="http://www.php.net/manual/en/image.installation.php">this</a> page for installation instructions',
            // 'mbstring' => 'MultiByte String is used for work with Unicode. Without it, system may not split words and string properly and you can have weird question mark characters in Recent Activities for example. Please refer to <a href="http://www.php.net/manual/en/mbstring.installation.php">this</a> page for installation instructions',
            // 'curl' => 'cURL is used to support various network tasks. Please refer to <a href="http://www.php.net/manual/en/curl.installation.php">this</a> page for installation instructions',
            // 'iconv' => 'Iconv is used for character set conversion. Without it, system is a bit slower when converting different character set. Please refer to <a href="http://www.php.net/manual/en/iconv.installation.php">this</a> page for installation instructions',
            // 'imap' => 'IMAP is used to connect to POP3 and IMAP servers. Without it, Incoming Mail module will not work. Please refer to <a href="http://www.php.net/manual/en/imap.installation.php">this</a> page for installation instructions',
            // 'zlib' => 'ZLIB is used to read and write gzip (.gz) compressed files',
            // SVN extension ommited, to avoid confusion
        ];

        foreach ($recommended_extensions as $recommended_extension => $recommended_extension_desc) {
            if (\extension_loaded($recommended_extension)) {
                $results[] = new \XRA\Extend\Library\TestResult("Recommended extension '$recommended_extension' found", self::STATUS_OK);
            } else {
                $results[] = new \XRA\Extend\Library\TestResult("Extension '$recommended_extension' was not found. <span class=\"details\">$recommended_extension_desc</span>", self::STATUS_WARNING);
            } // if
        } // foreach

        return $ok;
    }

    // validate_extensions

    /**
     * function to check if there are laravel required functions are disabled.
     */
    public static function checkDisabledFunctions(&$results)
    {
        $ok = true;
        $sets = \explode(',', \ini_get('disable_functions'));
        $required_functions = ['escapeshellarg'];
        // dd($required_functions,$sets);
        foreach ($sets as $key) {
            $key = \trim($key);
            foreach ($required_functions as $value) {
                if ($key == $value) {
                    if (false !== \mb_strpos(\ini_get('disable_functions'), $key)) {
                        $results[] = new \XRA\Extend\Library\TestResult("Function '$value' is required in order to run Faveo Helpdesk. Please check php.ini to enable this function or contact your server administrator", self::STATUS_ERROR);
                        $ok = false;
                    } else {
                        $results[] = new \XRA\Extend\Library\TestResult('All required functions found', self::STATUS_OK);
                    }
                }
            }
        }

        return $ok;
    }

    public static function checkMaxExecutiontime(&$results)
    {
        $ok = true;
        if ((int) \ini_get('max_execution_time') >= 120) {
            $results[] = new \XRA\Extend\Library\TestResult('Maximum execution time is as per requirement.', self::STATUS_OK);
        } else {
            $results[] = new \XRA\Extend\Library\TestResult('Maximum execution time is too low. Recommneded execution time is 120 seconds ', self::STATUS_WARNING);
        }

        return $ok;
    }

    public static function checkDatabaseConnection($default, $host, $username, $password, $databasename, $port)
    {
        $mysqli_ok = true;
        $results = [];
        // error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        \error_reporting(0);
        if ('mysql' == $default) {
            if ($connection = \mysqli_connect($host, $username, $password, $databasename)) {
                $results[] = new TestResult('Connected to database as '.$username.'@'.$host.$port, self::STATUS_OK);
                if (\mysqli_select_db($connection, $databasename)) {
                    $results[] = new TestResult('Database "'.$databasename.'" selected', self::STATUS_OK);
                    $mysqli_version = \mysqli_get_server_info($connection);
                    if (\version_compare($mysqli_version, '5') >= 0) {
                        $results[] = new TestResult('MySQL version is '.$mysqli_version, self::STATUS_OK);
                        // $have_inno = check_have_inno($connection);
                        $sql = 'SHOW TABLES FROM '.$databasename;
                        $res = \mysqli_query($connection, $sql);
                    //                        if (mysqli_fetch_array($res) === null) {
//                            $results[] = new TestResult('Database is empty', self::STATUS_OK);
//                            $mysqli_ok = true;
//                        } else {
//                            $results[] = new TestResult('The system installation requires an empty database, your database already has tables and data in it.', self::STATUS_ERROR);
//                            $mysqli_ok = false;
//                        }
                    } else {
                        $results[] = new TestResult('Your MySQL version is '.$mysqli_version.'. We recommend upgrading to at least MySQL5!', self::STATUS_ERROR);
                        $mysqli_ok = false;
                    } // if
                } else {
                    $results[] = new TestResult('Failed to select database. '.\mysqli_error(), self::STATUS_ERROR);
                    $mysqli_ok = false;
                } // if
            } else {
                $results[] = new TestResult('Failed to connect to database. '.\mysqli_error(), self::STATUS_ERROR);
                $mysqli_ok = false;
            } // if
        }

        return ['results' => $results, 'mysql_ok' => $mysqli_ok];
    }
}
