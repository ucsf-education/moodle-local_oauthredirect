<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_oauthredirect;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/oauthredirect/classes/redirector.php');

/**
 * PHPUnit tests for the redirector helper.
 *
 * @covers \local_oauthredirect\redirector
 * @package    local_oauthredirect
 * @author     Carson Tam
 * @copyright  2026 The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class redirector_test extends \advanced_testcase {
    /**
     * Test that a zero issuer id throws an exception.
     *
     * @return void
     */
    public function test_build_login_url_throws_on_zero_issuer(): void {
        $this->expectException('moodle_exception');

        redirector::build_login_url(0, false, null);
    }

    /**
     * Create a standard OAuth issuer for tests.
     *
     * @return int The inserted issuer id.
     */
    protected function create_test_issuer(): int {
        self::setAdminUser();

        $issuer = \core\oauth2\api::create_standard_issuer('google');

        return (int) $issuer->get('id');
    }

    /**
     * Test that the basic URL includes id and wantsurl.
     *
     * @return void
     */
    public function test_build_login_url_basic_params(): void {
        $this->resetAfterTest();

        $id = $this->create_test_issuer();

        $url = redirector::build_login_url($id, false, '/path');
        $params = $url->params();

        $this->assertArrayHasKey('id', $params);
        $this->assertEquals($id, (int) $params['id']);
        $this->assertArrayHasKey('wantsurl', $params);
        $this->assertEquals('/path', $params['wantsurl']);
        $this->assertArrayNotHasKey('sesskey', $params);
    }

    /**
     * Test that sesskey is included when requested.
     *
     * @return void
     */
    public function test_build_login_url_includes_sesskey_when_requested(): void {
        $this->resetAfterTest();

        $id = $this->create_test_issuer();

        $this->setUser($this->getDataGenerator()->create_user());

        $url = redirector::build_login_url($id, true, '/hm');
        $params = $url->params();

        $this->assertArrayHasKey('sesskey', $params);
        $this->assertNotEmpty($params['sesskey']);
    }

    /**
     * Test that an explicit param takes precedence over the session value.
     *
     * @return void
     */
    public function test_resolve_wantsurl_prefers_explicit_param(): void {
        $result = redirector::resolve_wantsurl('/course/view.php?id=5', '/other/page.php');
        $this->assertEquals('/course/view.php?id=5', $result);
    }

    /**
     * Test that the session value is used when no param is supplied.
     *
     * @return void
     */
    public function test_resolve_wantsurl_falls_back_to_session(): void {
        $result = redirector::resolve_wantsurl('', '/course/view.php?id=5');
        $this->assertEquals('/course/view.php?id=5', $result);
    }

    /**
     * Test that an external session URL is rejected and '/' is returned.
     *
     * @return void
     */
    public function test_resolve_wantsurl_rejects_external_session_url(): void {
        $result = redirector::resolve_wantsurl('', 'https://evil.example.com/steal');
        $this->assertEquals('/', $result);
    }

    /**
     * Test that '/' is returned when both param and session are absent.
     *
     * @return void
     */
    public function test_resolve_wantsurl_defaults_to_root(): void {
        $result = redirector::resolve_wantsurl('', null);
        $this->assertEquals('/', $result);
    }

    /**
     * Test that '/' is returned when the session value is an empty string.
     *
     * @return void
     */
    public function test_resolve_wantsurl_defaults_to_root_on_empty_session(): void {
        $result = redirector::resolve_wantsurl('', '');
        $this->assertEquals('/', $result);
    }
}
