<?php

// Copyright 2004-present Facebook. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
/**
 * The base class for test cases.
 */
require_once('../../utils/vendor/facebook/webdriver/lib/__init__.php');

class WebDriverTest extends PHPUnit_Framework_TestCase {

	protected $host = 'http://www.mladenec-shop.ru/';

	/** @var RemoteWebDriver $driver */
	protected $driver;

	protected function setUp() {
		$this->driver = RemoteWebDriver::create(
						'http://213.247.143.81:4444/wd/hub', array(// 127.0.1.1
					WebDriverCapabilityType::BROWSER_NAME
					=> WebDriverBrowserType::FIREFOX,
						// => WebDriverBrowserType::HTMLUNIT,
						)
		);
	}

	protected function tearDown() {
		// $this->driver->quit();
	}

	/**
	 * Get the URL of the test html.
	 *
	 * @param $path
	 * @return string
	 */
	protected function getTestPath($path) {
		return 'file:///' . dirname(__FILE__) . '/html/' . $path;
	}

	/* public function testGoogle() {
	  $this->driver->get('http://google.ru/');
	  self::assertEquals(
	  'Google', $this->driver->findElement(WebDriverBy::tagName('title'))->getText()
	  );
	  } */

	public function testLogin() {

		$this->_login();
		self::assertTrue(true);
	}

	public function testLk() {

		$this->_login();

		$this->_("#userpad a.a")->click();
		$this->wait();

		$name = $this->_('#content [name=name]');

		$nv = $name->getAttribute('value');

		$newv = 'Тест ' . substr(md5($nv . rand(0, 100000)), 0, 5);

		$name->sendKeys(WebDriverKeys::CONTROL + "a");
		$name->sendKeys(WebDriverKeys::DELETE);
		$name->clear()->sendKeys($newv);

		$this->_('[value=Изменить]')->click();

		$this->driver->wait(20, 1000)->until(function () {
			if (mb_strpos($this->driver->getPageSource(), 'успешно')) {
				return true;
			}
			return null;
		});

		self::assertTrue(true);
	}

	public function testCatalog() {

		$this->driver->get($this->host);

		$topMenuTds = $this->driver->findElements(WebDriverBy::cssSelector('table#catalog td'));

		// TODO несколько итераций в этом методе
		$keys = [];

		while (true) {

			$key = rand(0, count($topMenuTds) - 1);

			if (!in_array($key, $keys)) {
				$keys[] = $key;
				break;
			}
		}

		$topMenuLink = $topMenuTds[$key]->findElement(WebDriverBy::cssSelector('div>a'));

		$menuLinkText = $topMenuLink->getText();
		$topMenuLink->click();
		$this->wait();

		$cur = str_replace(["\n", "\r"], '', trim(strip_tags(htmlspecialchars_decode($this->driver->findElement(WebDriverBy::cssSelector('#content h1'))->getText()))));

		self::assertEquals(str_replace(["\n", "\r"], '', mb_strtolower(trim(strip_tags(htmlspecialchars_decode($menuLinkText))))), mb_strtolower($cur));
	}

	public function testCatalogLevel2() {

		$this->driver->get($this->host);

		$topMenuTds = $this->driver->findElements(WebDriverBy::cssSelector('table#catalog td'));

		// TODO несколько итераций в этом методе
		$keys = [];

		while (true) {

			$key = rand(1, count($topMenuTds));

			if (!in_array($key, $keys)) {
				$keys[] = $key;
				break;
			}
		}

		$this->driver->action()->moveToElement($this->driver->findElements(WebDriverBy::cssSelector('table#catalog td:nth-child(' . $key . ')>div'))[0])->perform();

		$this->wait(1);

		$this->driver->executeScript("$(arguments[0]).show();", [$this->driver->findElement(WebDriverBy::cssSelector('table#catalog td:nth-child(' . $key . ')>div>div'))]);

		$topMenuLinks = $this->driver->findElements(WebDriverBy::cssSelector('table#catalog td:nth-child(' . $key . ')>div>div>ul>li'));

		$topMenuLink = $this->driver->findElement(WebDriverBy::cssSelector('table#catalog td:nth-child(' . $key . ')>div>div>ul>li:nth-child(' . rand(1, count($topMenuLinks)) . ')>a'));

		$menuLinkText = $topMenuLink->getText();

		$topMenuLink->click();

		// $this->driver->executeScript("arguments[0].click();", [$topMenuLink]);

		$this->wait();

		$cur = str_replace(["\n", "\r"], '', trim(strip_tags(htmlspecialchars_decode($this->driver->findElement(WebDriverBy::cssSelector('#content .yell h1'))->getText()))));

		self::assertEquals(str_replace(["\n", "\r"], '', mb_strtolower(trim(strip_tags(htmlspecialchars_decode($menuLinkText))))), mb_strtolower($cur));
	}

	public function testRegistration() {

		$this->driver->get($this->host);

		$this->_("[rel=user-registration]")->click();

		$this->driver->wait(0.6);

		$this->_(".user-registration [name=email]")->sendKeys($this->_rand() . '@' . $this->_rand() . '.ru');

		$pass = $this->_rand() . $this->_rand();

		$this->_(".user-registration [name=password]")->sendKeys($pass);

		$this->_(".user-registration [name=password2]")->sendKeys($pass);

		$this->_(".user-registration [name=name]")->sendKeys("Тест " . $this->_rand());

		$this->driver->action()->moveToElement($this->_(".user-registration [name=phone]"))->click()->perform();
		$this->driver->wait(0.3);

		$this->_(".user-registration [name=phone]")->sendKeys("9291111111");

		$this->_(".user-registration .registration-submit")->click();
		$this->driver->wait(5);

		$this->driver->wait(20, 1000)->until(function () {
			if (count($this->driver->findElements(WebDriverBy::cssSelector('#userpad a'))) == 2) {
				return true;
			}
			return null;
		});
		self::assertTrue(true);
		// $this->assertCount(2, $this->driver->findElements(WebDriverBy::cssSelector('#userpad a')));
	}

	protected function _rand() {

		return substr(md5(rand(1, 100000000)), rand(0, 5), 5);
	}

	protected function _login() {

		$this->driver->get($this->host);

		$this->driver->findElement(WebDriverBy::cssSelector('a[rel=user-login]'))->click();

		$this->driver->manage()->timeouts()->implicitlyWait(0.5);

		$login = $this->driver->findElement(WebDriverBy::cssSelector('[name=login]'));
		$login->sendKeys('puchkovk@gmail.com');

		$password = $this->driver->findElement(WebDriverBy::cssSelector('[name=password]'));
		$password->sendKeys('novgorod');

		$this->driver->findElement(WebDriverBy::cssSelector('.login-submit'))->click();
		$this->driver->wait(20, 1000)->until(function () {
			if (count($this->driver->findElements(WebDriverBy::cssSelector('#userpad a'))) == 2) {
				return true;
			}
			return null;
		});
	}

	/**
	 * @param type $selector
	 * @return RemoteWebElement
	 */
	protected function _($selector) {
		return $this->driver->findElement(WebDriverBy::cssSelector($selector));
	}

	public function __destruct() {
		$this->driver->close();
	}

	protected function wait($seconds = 20) {

		$this->driver->manage()->timeouts()->implicitlyWait($seconds);
	}

}
