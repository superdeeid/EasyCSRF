<?php

use EasyCSRF\EasyCSRF;
use EasyCSRF\NativeSessionProvider;
use PHPUnit\Framework\TestCase;

class EasyCSRFTest extends TestCase {

	protected $easyCSRF;

	protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $_SERVER['HTTP_USER_AGENT'] = 'useragent';

        $sessionProvider = new NativeSessionProvider();
        $this->easyCSRF = new EasyCSRF($sessionProvider);

    }

    public function testGenerate()
	{
		$token = $this->easyCSRF->generate('test');

		$this->assertNotNull($token);
	}

	public function testCheck()
	{
		$token = $this->easyCSRF->generate('test');
		$this->easyCSRF->check('test', $token);

		$this->assertNull($_SESSION['easycsrf_test']);
	}

	public function testCheckMultiple()
	{
		$token = $this->easyCSRF->generate('test');
		$this->easyCSRF->check('test', $token, null, true);

		$this->assertNotNull($_SESSION['easycsrf_test']);
	}

	public function testExceptionMissingFormToken()
	{
		$this->expectException(\EasyCSRF\Exceptions\MissingTokenException::class);

		$this->easyCSRF->check('test', '');
	}

	public function testExceptionMissingSessionToken()
	{
		$this->expectException(\EasyCSRF\Exceptions\TokenMismatchException::class);

		$this->easyCSRF->check('test', '12345');
	}

	public function testExceptionOrigin()
	{
		$this->expectException(\EasyCSRF\Exceptions\TokenMismatchException::class);

		$token = $this->easyCSRF->generate('test');
		$_SERVER['REMOTE_ADDR'] = '2.2.2.2';
		$this->easyCSRF->check('test', $token);
	}

	public function testExceptionInvalidToken()
	{
		$this->expectException(\EasyCSRF\Exceptions\Exception::class);

		$this->easyCSRF->generate('test');
		$this->easyCSRF->check('test', '12345');
	}

	public function testExceptionExpired()
	{
		$this->expectException(\EasyCSRF\Exceptions\ExpiredTokenException::class);

		$token = $this->easyCSRF->generate('test');
		sleep(2);
		$this->easyCSRF->check('test', $token, 1);
	}

}