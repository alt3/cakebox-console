<?php
namespace App\Test\TestCase\Routing;

use App\Lib\CakeboxInfo;
use Cake\TestSuite\TestCase;

/**
 * RouterTest class
 *
 */
class CakeboxInfoTest extends TestCase
{

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test metadata
     *
     * @return void
     */
    public function testMetaData()
    {
        $object = new CakeboxInfo();

        // test cakebox metadata
        $expected = [
            'host' => [
                'yaml' => '/home/vagrant/.cakebox/last-known-cakebox-yaml',
                'commit' => '/home/vagrant/.cakebox/last-known-cakebox-commit',
                'box_version' => '/home/vagrant/.cakebox/last-known-box-version',
            ],
            'cli_log' => '/var/log/cakephp/cakebox.cli.log'
        ];
        $res = $object->cakeboxMeta;
        $this->assertEquals($expected, $res);

        // test webserver metadata
        $expected = [
            'nginx' => [
                'sites-available' => '/etc/nginx/sites-available',
                'sites-enabled' => '/etc/nginx/sites-enabled'
            ]
        ];
        $res = $object->webserverMeta;
        $this->assertEquals($expected, $res);

        // test database metadata
        $expected = [
            'mysql' => [
                'system_databases' => [ 'mysql', 'information_schema', 'performance_schema', 'test' ]
            ],
            'test_prefix' => 'test_',
            'default_local_user' => 'cakebox',
            'default_local_password' => 'secret'
        ];
        $res = $object->databaseMeta;
        $this->assertEquals($expected, $res);

        // test framework metadata
        $expected = [
            'cakephp2' => [
                'installation_method' => 'git',
                'source' => 'https://github.com/cakephp/cakephp.git -b 2.x',
                'source_ssh' => 'git@github.com:cakephp/cakephp.git -b 2.x',
                'webroot' => 'app/webroot',
                'writable_dirs' => ['app/tmp'],
                'salt' => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
                'cipher' => '76859309657453542496749683645'
            ],
            'cakephp3' => [
                'installation_method' => 'composer',
                'source' => 'cakephp/app',
                'webroot' => 'webroot'
            ],
            'laravel' => [
                'installation_method' => 'composer',
                'source' => 'laravel/laravel',
                'webroot' => 'public',
                'writable_dirs' => ['storage'] // app/storage for Laravel 4
            ]
        ];
        $res = $object->frameworkMeta;
        $this->assertEquals($expected, $res);

        // test PHP modules metadata
        $expected = [
            'Core' => ['link' => false],
            'Zend OPcache' => ['link' => 'http://php.net/manual/en/book.opcache.php'],
            'apcu' => ['link' => 'http://pecl.php.net/package/APCu'],
            'bz2' => ['link' => 'http://php.net/manual/en/book.bzip2.php'],
            'cgi-fcgi' => ['link' => 'http://www.fastcgi.com/drupal/node/5?q=node/10'],
            'redis' => ['link' => 'https://github.com/nicolasff/phpredis'],
            'standard' => ['link' => false],
            'sysvmsg' => ['link' => 'http://php.net/manual/en/book.sem.php'],
            'sysvsem' => ['link' => 'http://php.net/manual/en/ref.sem.php'],
            'sysvshm' => ['link' => 'http://php.net/manual/en/ref.shmop.php'],
            'xdebug' => ['link' => 'http://xdebug.org']
        ];
        $res = $object->phpModuleMeta;
        $this->assertEquals($expected, $res);

        // test Nginx modules metadata
        $expected = [
            'auth-pam' => ['link' => 'http://web.iti.upv.es/~sto/nginx'],
            'dav-ext' => ['link' => 'https://github.com/arut/nginx-dav-ext-module'],
            'http_substitutions_filter' => ['link' => 'http://wiki.nginx.org/HttpSubsModule']
        ];
        $res = $object->nginxModuleMeta;
        $this->assertEquals($expected, $res);

        // test Ubuntu (apt) pacakges metadata
        $expected = [
            'composer' => ['link' => 'https://getcomposer.org'],
            'curl' => ['link' => 'http://curl.haxx.se'],
            'elasticsearch' => ['link' => 'https://www.elasticsearch.org'],
            'git' => ['link' => 'https://launchpad.net/~git-core'],
            'java' => ['link' => 'http://openjdk.java.net'],
            'heroku' => ['link' => 'https://toolbelt.heroku.com'],
            'hhvm' => ['link' => 'http://hhvm.com'],
            'kibana' => ['link' => 'https://www.elasticsearch.org/overview/kibana'],
            'logstash' => ['link' => 'http://logstash.net'],
            'mysql' => ['link' => 'http://www.percona.com/software/percona-server'],
            'memcached' => ['link' => 'http://memcached.org'],
            'mongodb' => [
                'link' => 'https://www.mongodb.org/',
                'alias' => 'mongod'
            ],
            'nginx' => ['link' => 'https://launchpad.net/nginx'],
            'openssl' => ['link' => 'https://www.openssl.org'],
            'php' => ['link' => 'https://launchpad.net/~ondrej/+archive/ubuntu/php5-5.6'],
            'phpunit' => ['link' => 'https://phpunit.de'],
            'phpcs' => ['link' => 'https://github.com/squizlabs/PHP_CodeSniffer'],
            'cakephp-codesniffer' => ['link' => 'https://github.com/cakephp/cakephp-codesniffer'],
            'postgresql' => [
                'link' => 'http://www.postgresql.org',
                'alias' => 'psql'
            ],
            'python' => ['link' => 'https://www.python.org'],
            'redis' => [
                'link' => 'https://launchpad.net/~chris-lea/+archive/ubuntu/redis-server',
                'alias' => 'redis-server'
            ],
            'ruby' => ['link' => 'https://www.ruby-lang.org/en'],
            'xtrabackup' => ['link' => 'http://www.percona.com/doc/percona-xtrabackup']
        ];
        $res = $object->packages;
        $this->assertEquals($expected, $res);
    }
}
