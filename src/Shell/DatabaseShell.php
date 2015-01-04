<?php
namespace App\Shell;

use App\Lib\CakeboxInfo;
use App\Lib\CakeboxExecute;
use App\Lib\CakeboxUtility;
use Cake\Console\Shell;

/**
 * Shell class for managing databases.
 */
class DatabaseShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage databases directly from the command line.')]);

     # add
        $parser->addSubcommand('add', [
            'parser' => [
                'description' => [
                    __("Create a MySQL databases and accompanying 'test_' prefixed test database.")
                ],
                'arguments' => [
                    'name' => ['help' => __('Name to be used for the databases.'), 'required' => true]
                ],
                'options' => [
                    'username' => [
                        'short' => 'u',
                        'help' => __('Username with localhost database access.'),
                        'default' => 'cakebox'
                    ],
                    'password' => [
                        'short' => 'p',
                        'help' => __('Password for user with localhost access.'),
                        'default' => 'secret'
                    ],
                    'force' => [
                        'short' => 'f',
                        'help' => __('Drop existing database.'),
                        'boolean' => true
                    ]
                ]
            ]
        ]);

        # remove
        $parser->addSubcommand('remove', [
            'parser' => [
                'description' => [
                    __("Drops database and related 'test_' prefixed database.")
                ],
                'arguments' => [
                    'name' => [
                        'help' => __('Name of database to be dropped.'),
                        'required' => true
                    ]
                ]
            ]
        ]);

        # listall
        $parser->addSubcommand('listall', [
            'parser' => [
                'description' => [
                    __("Lists all databases.")
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Create two databases, one prefixed with 'test_'.
     *
     * @param string $database Name to be used for the databases.
     * @return void
     */
    public function add($database)
    {
        $database = CakeboxUtility::normalizeDatabaseName($database);
        $this->logStart("Creating databases $database and test_$database");
        $execute = new CakeboxExecute();

        # Will fail on existing databases without --force parameter
        if ($this->params['force'] == false) {
            if ($execute->addDatabase($database, $this->params['username'], $this->params['password']) == false) {
                $this->logInfo($execute->debug());
                $this->logError("Error creating databases");
                $this->exitBashError();
            }
        }

        # Option --force parameter passed
        if ($execute->addDatabase($database, $this->params['username'], $this->params['password'], true) == false) {
            $this->logInfo($execute->debug());
            $this->logError("Error creating databases");
            $this->exitBashError();
        }
        $this->logInfo("Databases created successfully");
        $this->exitBashSuccess();
    }

    /**
     * Remove/drop a database and related test-database.
     *
     * @param string $database Name of main database.
     * @return void
     */
    public function remove($database)
    {
        $database = CakeboxUtility::normalizeDatabaseName($database);
        $this->logStart("Dropping databases $database and test_$database");

        if (CakeboxUtility::dropDatabase($database) == false) {
            $this->logInfo($execute->debug());
            $this->logError("Error dropping databases");
            $this->exitBashError();
        }
        $this->logInfo("Databases dropped successfully");
        $this->exitBashSuccess();
    }

    /**
     * Return an array list containing all databases.
     *
     * @return void
     */
    public function listall()
    {
        $this->out('Test databases not highlighted:');
        $databases = (new CakeboxInfo)->getAppDatabases();
        foreach ($databases as $database) {
            if (substr($database['name'], 0, 5) == 'test_') {
                $this->out("  " . $database['name']);
            } else {
                $this->out("  <info>" . $database['name'] . "</info>");
            }
        }
    }
}
