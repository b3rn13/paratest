<?php

declare(strict_types=1);

namespace ParaTest\Runners\PHPUnit;

class ExecutableTestTest extends \TestBase
{
    /**
     * @var ExecutableTestChild
     */
    protected $executableTestChild;

    public function setUp()
    {
        $this->executableTestChild = new ExecutableTestChild('pathToFile', 'ClassNameTest');
        parent::setUp();
    }

    public function testConstructor()
    {
        $this->assertEquals('pathToFile', $this->getObjectValue($this->executableTestChild, 'path'));
    }

    public function testGetCommandStringIncludesOptions()
    {
        $options = ['bootstrap' => 'test/bootstrap.php'];
        $binary = '/usr/bin/phpunit';

        $command = $this->call($this->executableTestChild, 'getCommandString', $binary, $options);
        $this->assertEquals("'/usr/bin/phpunit' '--bootstrap' 'test/bootstrap.php' 'ClassNameTest' 'pathToFile'", $command);
    }

    public function testCommandRedirectsCoverage()
    {
        $options = ['a' => 'b', 'coverage-php' => 'target_html', 'coverage-php' => 'target.php'];
        $binary = '/usr/bin/phpunit';

        $command = $this->executableTestChild->command($binary, $options);
        $coverageFileName = str_replace('/', '\/', $this->executableTestChild->getCoverageFileName());
        $this->assertRegExp("/^'\/usr\/bin\/phpunit' '--a' 'b' '--coverage-php' '$coverageFileName' '.*'/", $command);
    }

    public function testGetCommandStringDoesNotIncludeEnvironmentVariablesToKeepCompatibilityWithWindows()
    {
        $options = ['bootstrap' => 'test/bootstrap.php'];
        $binary = '/usr/bin/phpunit';
        $environmentVariables = ['APPLICATION_ENVIRONMENT_VAR' => 'abc'];
        $command = $this->call($this->executableTestChild, 'getCommandString', $binary, $options, $environmentVariables);

        $this->assertEquals("'/usr/bin/phpunit' '--bootstrap' 'test/bootstrap.php' 'ClassNameTest' 'pathToFile'", $command);
    }

    public function testGetCommandStringIncludesTheClassName()
    {
        $options = [];
        $binary = '/usr/bin/phpunit';

        $command = $this->call($this->executableTestChild, 'getCommandString', $binary, $options);
        $this->assertEquals("'/usr/bin/phpunit' 'ClassNameTest' 'pathToFile'", $command);
    }

    public function testHandleEnvironmentVariablesAssignsToken()
    {
        $environmentVariables = ['TEST_TOKEN' => 3, 'APPLICATION_ENVIRONMENT_VAR' => 'abc'];
        $this->call($this->executableTestChild, 'handleEnvironmentVariables', $environmentVariables);
        $this->assertEquals(3, $this->getObjectValue($this->executableTestChild, 'token'));
    }

    public function testGetTokenReturnsValidToken()
    {
        $this->setObjectValue($this->executableTestChild, 'token', 3);
        $this->assertEquals(3, $this->executableTestChild->getToken());
    }

    public function testGetTempFileShouldCreateTempFile()
    {
        $file = $this->executableTestChild->getTempFile();
        $this->assertFileExists($file);
        unlink($file);
    }

    public function testGetTempFileShouldReturnSameFileIfAlreadyCalled()
    {
        $file = $this->executableTestChild->getTempFile();
        $fileAgain = $this->executableTestChild->getTempFile();
        $this->assertEquals($file, $fileAgain);
        unlink($file);
    }
}

class ExecutableTestChild extends ExecutableTest
{
    /**
     * Get the expected count of tests to be executed.
     *
     * @return int
     */
    public function getTestCount(): int
    {
        return 1;
    }
}
