<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5409 extends Enlight_Components_Test_Plugin_TestCase
{

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     */
    public function testLogin()
    {
	    //test the normal login
	    $this->assertEmpty(Shopware()->Session()->sUserId);
	    $this->Request()
			    ->setMethod('POST')
			    ->setPost('email', 'test@example.com')
			    ->setPost('password', 'shopware');

	    $this->dispatch('/account/login');
	    $this->assertNotEmpty(Shopware()->Session()->sUserId);
	    $this->assertEquals(1,Shopware()->Session()->sUserId);

	    $this->logoutUser();

	    //test with md5 password and without the ignoreAccountMode parameter
	    $this->assertEmpty(Shopware()->Session()->sUserId);
        $this->Request()
            ->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('passwordMD5', 'a256a310bc1e5db755fd392c524028a8');

        $this->dispatch('/account/login');
	    $this->assertEmpty(Shopware()->Session()->sUserId);

	    //test the internal call of the method with the $ignoreAccountMode parameter
	    $this->logoutUser();
	    $this->Request()
			    ->setMethod('POST')
			    ->setPost('email', 'test@example.com')
			    ->setPost('passwordMD5', 'a256a310bc1e5db755fd392c524028a8');
	    $this->dispatch('/');
	    $result = Shopware()->Modules()->Admin()->sLogin(true);
	    $this->assertNotEmpty(Shopware()->Session()->sUserId);
	    $this->assertEquals(1,Shopware()->Session()->sUserId);
	    $this->assertEmpty($result["sErrorFlag"]);
	    $this->assertEmpty($result["sErrorMessages"]);


		//test the internal call of the method without the $ignoreAccountMode parameter
	    $this->logoutUser();
	    $this->Request()
				->setMethod('POST')
				->setPost('email', 'test@example.com')
				->setPost('passwordMD5', 'a256a310bc1e5db755fd392c524028a8');
	    $this->dispatch('/');
	    $result = Shopware()->Modules()->Admin()->sLogin();
	    $this->assertEmpty(Shopware()->Session()->sUserId);
	    $this->assertNotEmpty($result["sErrorFlag"]);
	    $this->assertNotEmpty($result["sErrorMessages"]);


    }

	/**
	 * helper to logout the user
	 */
	private function logoutUser()
	{
		//reset the request
		$this->reset();
		$this->Request()->setMethod('POST');
		$this->dispatch('/account/logout');
		//reset the request
		$this->reset();
	}
}
