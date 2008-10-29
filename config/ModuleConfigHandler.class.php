<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003, 2004 Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.mojavi.org.                             |
// +---------------------------------------------------------------------------+

/**
 * ModuleConfigHandler reads module configuration files to determine the status
 * of a module.
 *
 * @package    mojavi
 * @subpackage config
 *
 * @author     Sean Kerr (skerr@mojavi.org)
 * @copyright  (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since      3.0.0
 * @version    $Id: ModuleConfigHandler.class.php 531 2004-12-03 12:53:33Z seank $
 *
 * $Id: ModuleConfigHandler.class.php 531 2004-12-03 12:53:33Z seank $
 */
class ModuleConfigHandler extends IniConfigHandler
{

    // +-----------------------------------------------------------------------+
    // | METHODS                                                               |
    // +-----------------------------------------------------------------------+

    /**
     * Execute this configuration handler.
     *
     * @param string An absolute filesystem path to a configuration file.
     *
     * @return string Data to be written to a cache file.
     *
     * @throws <b>ConfigurationException</b> If a requested configuration file
     *                                       does not exist or is not readable.
     * @throws <b>ParseException</b> If a requested configuration file is
     *                               improperly formatted.
     *
     * @author Sean Kerr (skerr@mojavi.org)
     * @since  3.0.0
     */
    public function & execute ($config)
    {

        // set our required categories list and initialize our handler
        $categories = array('required_categories' => array('module'));

        $this->initialize($categories);

        // parse the ini
        $ini = $this->parseIni($config);

        // verify the NAME key is set
        if (!isset($ini['module']['NAME']))
        {

            // missing NAME key
            $error = 'Configuration file "%s" specifies category ' .
                     '"module" with missing "NAME" key';
            $error = sprintf($error, $config);

            throw new ParseException($error);

        }

        // get our module name
        $moduleName = strtoupper($ini['module']['NAME']);

        // init our data array
        $data = array();

        // allowed keys
        $keys = array('AUTHOR', 'DESCRIPTION', 'ENABLED', 'HOMEPAGE', 'NAME',
                      'TITLE', 'UPDATE_URL', 'VERSION');

        // let's do the fancy work
        foreach ($ini['module'] as $key => &$value)
        {

            if (in_array($key, $keys))
            {

                // literalize our value
                $value = $this->literalize($value);

                $tmp    = "define('MOD_%s_%s', %s);";
                $data[] = sprintf($tmp, $moduleName, $key, $value);

            }

        }

        // compile data
        $retval = "<?php\n" .
                  "// auth-generated by ModuleConfigHandler\n" .
                  "// date: %s\n%s\n?>";

        $retval = sprintf($retval, date('m/d/Y H:i:s'), implode("\n", $data));

        return $retval;

    }

}

?>