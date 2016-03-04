<?php
/*==========================================================================*\
Filename : config.php - core configuration for Evermore
Project  : Evermore
Authors  : --
Revision : 20160220
License  : The MIT License (MIT)

Copyright (c) 2016 --

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
\*==========================================================================*/

session_start();

//----------------------------------------------------------------------------
// Note: some settings are in this file (not database) for security reasons

$config = array(
    // Database (correct driver must be installed)
    'database' => array(
        'type' => 'mysql',               // [ 'mysql', 'pgsql' ]
        'host' => 'localhost',           // [ 'localhost', <host> ]
        'port' => 'default',             // [ 'default', <port> ]
        'name' => 'evermore',            // database name
        'user' => 'root',                // should not be 'root'
        'pass' => 'evermore2016',        // must not be empty
    ),
);

//----------------------------------------------------------------------------
// Checks: do NOT modify anything below this line

// only run checks once per session (performance)
if(!isset($_SESSION['check_ok']) || !$_SESSION['check_ok'])
{
    //---------------------------------
    //             DATABASE
    //---------------------------------
    
    // type
    if(!in_array($config['database']['type'], array('mysql', 'pgsql')))
    {
        die('Database type is not valid.');
    }

    // host
    if(empty($config['database']['host']))
    {
        die('Database host cannot be empty.');
    }

    // port
    if(($config['database']['port'] != 'default')
        && ((!is_int($config['database']['port']))
        || ($config['database']['port'] > 65535)
        || ($config['database']['port'] < 0)))
    {
        die('Database port is not valid.');
    }

    // {name, user, pass}
    if(empty($config['database']['name'])
        || empty($config['database']['user'])
        || empty($config['database']['pass']))
    {
        die('Database {name, user, pass} cannot be empty.');
    }

    // test connect
    try{
        // PDO driver assumes default values
        $port = ($config['database']['port'] == 'default') ? ''
            : ';port='.$config['database']['port'];
        $db = new PDO(
            $config['database']['type'].':host='.
            $config['database']['host'].$port.';dbname='.
            $config['database']['name'].';charset=utf8',
            $config['database']['user'],
            $config['database']['pass']
        );
        
        // possible SQL injection fix
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // verify database integrity
        /*
        if((!isset($_GET['account']) || $_GET['account'] != 'install') && !checkTables())
        {
            header('Location: ./?account=install');
            exit();
        }
        */
    }
    catch(PDOException $e)
    {
        die('Database connection failed: '.$e->getMessage());
    }
    
    //---------------------------------
    //           TESTS PASSED
    //---------------------------------
    
    $_SESSION['check_ok'] = true;
}

//----------------------------------------------------------------------------
// Database: construct DB handle and load extra settings

// connect to database
if(!isset($db))
{
    // connect
    try{
        // PDO driver assumes default values
        $port = ($config['database']['port'] == 'default') ? ''
            : ';port='.$config['database']['port'];
        $db = new PDO(
            $config['database']['type'].':host='.
            $config['database']['host'].$port.';dbname='.
            $config['database']['name'].';charset=utf8',
            $config['database']['user'],
            $config['database']['pass']
        );
        
        // possible SQL injection fix
        //$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
    catch(PDOException $e)
    {
        die('Database connection failed: '.$e->getMessage());
    }
}