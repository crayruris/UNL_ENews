<?php
// Call PeoplefinderTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'PeoplefinderTest::main');
}

require_once 'PHPUnit/Framework.php';
chdir(dirname(__FILE__).'/../');
require_once 'config.inc.php';
require_once 'UNL/Peoplefinder.php';

/**
 * Test class for Peoplefinder.
 * Generated by PHPUnit on 2007-11-14 at 08:59:54.
 */
class PeoplefinderTest extends PHPUnit_Framework_TestCase {
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('PeoplefinderTest');
        require_once dirname(__FILE__).'/BrowserTest.php';
        $suite->addTest(new BrowserTest());
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    }

    /**
     * Tests binding to the LDAP directory.
     */
    public function testBind() {
        $p = new UNL_Peoplefinder();
        $this->assertEquals(true, $p->bind(), 'Cannot connect to the LDAP directory.');
    }

    /**
     * @todo Implement testQuery().
     */
    public function testQuery() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * Tests unbinding from the LDAP directory.
     */
    public function testUnbind() {
        $p = new UNL_Peoplefinder();
        $this->assertEquals(true, $p->bind());
        $this->assertEquals(true, $p->unbind());
    }

    /**
     * Tests searching for exact matches to a query.
     */
    public function testGetExactMatches() {
        $p = new UNL_Peoplefinder();
        $r = $p->getExactMatches('Harvey Perlman');
        $this->assertTrue(is_array($r), 'An array of results was not returned.');
        $this->assertEquals(1, count($r), 'The size of the result set is not what was expected.');
        $this->assertEquals('Harvey', $r[0]->givenName);
    }
    
    /**
     * Makes sure that no results are returned for someone that does not exist.
     *
     */
    public function testGetExactMatches2() {
        $p = new UNL_Peoplefinder();
        $r = $p->getExactMatches('ThisPerson DoesNotExist');
        $this->assertTrue(is_array($r), 'An array of results was not returned.');
        $this->assertEquals(0, count($r), 'The size of the result set is not what was expected.');
    }

    /**
     * Test an advanced query.
     */
    public function testGetAdvancedSearchMatches() {
        $p = new UNL_Peoplefinder();
        $r = $p->getAdvancedSearchMatches('Perlman', 'Harvey', 'staff');
        $this->assertTrue(is_array($r), 'An array of results was not returned.');
        $this->assertEquals(1, count($r), 'The size of the result set is not what was expected.');
        
        $r = $p->getAdvancedSearchMatches('Perlman', 'Harvey', 'student');
        $this->assertTrue(is_array($r), 'An array of results was not returned.');
        $this->assertEquals(0, count($r), 'The size of the result set is not what was expected.');
    }

    /**
     * Get matches using a little fuzzy logic.
     */
    public function testGetLikeMatches() {
        $p = new UNL_Peoplefinder();
        $r = $p->getLikeMatches('bieber');
        $this->assertEquals(7, sizeof($r));
        $r = $p->getLikeMatches('bieber', array($r[0]), 'Testing exclusion of records did not work.');
        $this->assertEquals(5, sizeof($r));
        $r = $p->getLikeMatches('bieber', $r, 'Testing exclusion of records did not work.');
        $this->assertEquals(1, sizeof($r));
    }

    /**
     * Tests that matches are returned by phone number query.
     */
    public function testGetPhoneMatches() {
        $p = new UNL_Peoplefinder();
        $r = $p->getPhoneMatches('1598');
        $this->assertEquals($r[0]->telephoneNumber, '(402)472-1598');
        $r = $p->getPhoneMatches('472-1598');
        $this->assertEquals($r[0]->telephoneNumber, '(402)472-1598');
    }

    /**
     * Test grabbing an individual record.
     */
    public function testGetUID() {
        $p = new UNL_Peoplefinder();
        $r = $p->getUID('bbieber2');
        $this->assertEquals('UNL_Peoplefinder_Record', get_class($r));
        $this->assertEquals('Brett', $r->givenName);
    }
    
    /**
     * Tests that an exception is thrown when an invalid uid is queried for.
     * @expectedException Exception
     */
    public function testGetUID2() {
        $p = new UNL_Peoplefinder();
        $this->setExpectedException('Exception');
        $r = $p->getUID('thispersondoesnotexist');
    }

    /**
     * @todo Implement testArray_csort().
     */
    public function testArray_csort() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBuildStandardFilter().
     */
    public function testBuildStandardFilter() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBuildAdvancedFilter().
     */
    public function testBuildAdvancedFilter() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBuildFilter().
     */
    public function testBuildFilter() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
    
    public function testFormatAddress() {
        $r = unserialize('O:23:"UNL_Peoplefinder_Record":27:{s:2:"cn";s:12:"Brett Bieber";s:17:"eduPersonNickname";N;s:27:"eduPersonPrimaryAffiliation";s:5:"staff";s:9:"givenName";s:5:"Brett";s:11:"displayName";s:14:"Brett T Bieber";s:4:"mail";s:24:"bbieber@unlnotes.unl.edu";s:13:"postalAddress";s:26:"326D ADMN  UNL  68588-0424";s:2:"sn";s:6:"Bieber";s:15:"telephoneNumber";s:13:"(402)472-1598";s:5:"title";s:22:"Web Programmer/Analyst";s:3:"uid";s:8:"bbieber2";s:22:"unlHRPrimaryDepartment";s:25:"Office of University Comm";s:12:"unlHRAddress";s:26:"326D ADMN  UNL  68588-0424";s:16:"unlSISClassLevel";N;s:13:"unlSISCollege";N;s:16:"unlSISLocalAddr1";N;s:16:"unlSISLocalAddr2";N;s:15:"unlSISLocalCity";N;s:16:"unlSISLocalState";N;s:14:"unlSISLocalZip";N;s:15:"unlSISPermAddr1";N;s:15:"unlSISPermAddr2";N;s:14:"unlSISPermCity";N;s:15:"unlSISPermState";N;s:13:"unlSISPermZip";N;s:11:"unlSISMajor";N;s:13:"unlEmailAlias";s:8:"bbieber2";}');
        $address = $r->formatPostalAddress();
        $this->assertEquals('a:4:{s:6:"region";s:2:"NE";s:14:"street-address";s:9:"326D ADMN";s:11:"postal-code";s:10:"68588-0424";s:8:"locality";s:7:"Lincoln";}',
                            serialize($address));

        $r = unserialize('O:23:"UNL_Peoplefinder_Record":27:{s:2:"cn";s:10:"Ivy Miller";s:17:"eduPersonNickname";N;s:27:"eduPersonPrimaryAffiliation";s:5:"staff";s:9:"givenName";s:3:"Ivy";s:11:"displayName";s:12:"Ivy E Miller";s:4:"mail";s:24:"imiller@mail.unomaha.edu";s:13:"postalAddress";s:25:"103C PKI  UNO  68182-0681";s:2:"sn";s:6:"Miller";s:15:"telephoneNumber";s:13:"(402)554-3856";s:5:"title";s:25:"Administrative Technician";s:3:"uid";s:8:"imiller2";s:22:"unlHRPrimaryDepartment";s:25:"Architectural Engineering";s:12:"unlHRAddress";s:25:"103C PKI  UNO  68182-0681";s:16:"unlSISClassLevel";N;s:13:"unlSISCollege";N;s:16:"unlSISLocalAddr1";N;s:16:"unlSISLocalAddr2";N;s:15:"unlSISLocalCity";N;s:16:"unlSISLocalState";N;s:14:"unlSISLocalZip";N;s:15:"unlSISPermAddr1";N;s:15:"unlSISPermAddr2";N;s:14:"unlSISPermCity";N;s:15:"unlSISPermState";N;s:13:"unlSISPermZip";N;s:11:"unlSISMajor";N;s:13:"unlEmailAlias";s:8:"imiller2";}');
        $address = $r->formatPostalAddress();
        $this->assertEquals('a:4:{s:6:"region";s:2:"NE";s:14:"street-address";s:8:"103C PKI";s:11:"postal-code";s:10:"68182-0681";s:8:"locality";s:5:"Omaha";}',
                            serialize($address));
                            
    }
    
    /**
     * Tests rendering a student record with no local address.
     *
     */
    public function testStudentWithNoLocalAddress()
    {
        $r = unserialize('O:23:"UNL_Peoplefinder_Record":27:{s:2:"cn";s:21:"Chandra Brandenburger";s:17:"eduPersonNickname";N;s:27:"eduPersonPrimaryAffiliation";s:7:"student";s:9:"givenName";s:7:"Chandra";s:11:"displayName";s:29:"Chandra Rachael Brandenburger";s:4:"mail";N;s:13:"postalAddress";N;s:2:"sn";s:13:"Brandenburger";s:15:"telephoneNumber";N;s:5:"title";N;s:3:"uid";s:10:"s-cbrande6";s:22:"unlHRPrimaryDepartment";N;s:12:"unlHRAddress";N;s:16:"unlSISClassLevel";s:2:"FR";s:13:"unlSISCollege";s:3:"ARH";s:16:"unlSISLocalAddr1";N;s:16:"unlSISLocalAddr2";N;s:15:"unlSISLocalCity";N;s:16:"unlSISLocalState";N;s:14:"unlSISLocalZip";N;s:15:"unlSISPermAddr1";s:13:"1121 N 9th St";s:15:"unlSISPermAddr2";N;s:14:"unlSISPermCity";s:7:"Norfolk";s:15:"unlSISPermState";s:2:"NE";s:13:"unlSISPermZip";s:5:"68701";s:11:"unlSISMajor";s:4:"PARC";s:13:"unlEmailAlias";N;}');
        require_once 'UNL/Peoplefinder/Renderer/HTML.php';
        $renderer = new UNL_Peoplefinder_Renderer_HTML();
        ob_start();
        $renderer->renderRecord($r);
        $h = ob_get_clean();
        //file_put_contents(dirname(__FILE__).'/testStudentWithNoLocalAddress.html', $h);
        $this->assertEquals(file_get_contents(dirname(__FILE__).'/testStudentWithNoLocalAddress.html'), $h);
    }

}

// Call PeoplefinderTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'PeoplefinderTest::main') {
    PeoplefinderTest::main();
}
?>