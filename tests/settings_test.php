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

/**
 * Tests for the OAuth redirect plugin settings.
 *
 * @package    local_oauthredirect
 * @copyright  (c) The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Tests for the issuer ID setting.
 *
 * @covers \local_oauthredirect\issuerid
 */
final class settings_test extends advanced_testcase {
    /**
     * Test that selecting a valid issuer updates the alternate login URL.
     */
    public function test_write_setting_updates_alternateloginurl_when_valid_issuer_selected(): void {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');

        $this->resetAfterTest();

        $originalwwwroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://example.edu';

        try {
            $setting = new \local_oauthredirect\issuerid(
                'local_oauthredirect/issuerid',
                'Default OAuth issuer',
                'Description',
                0,
                [0 => 'None', 1 => 'Test issuer']
            );

            $this->assertSame('', $setting->write_setting(1));
            $this->assertSame(
                'https://example.edu/local/oauthredirect/redirect.php',
                get_config('core', 'alternateloginurl')
            );
        } finally {
            $CFG->wwwroot = $originalwwwroot;
        }
    }

    /**
     * Test that selecting no issuer leaves the alternate login URL unchanged.
     */
    public function test_write_setting_does_not_change_alternateloginurl_when_none_selected(): void {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');

        $this->resetAfterTest();

        $originalwwwroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://example.edu';

        try {
            set_config('alternateloginurl', 'https://example.edu/login/index.php');

            $setting = new \local_oauthredirect\issuerid(
                'local_oauthredirect/issuerid',
                'Default OAuth issuer',
                'Description',
                0,
                [0 => 'None', 1 => 'Test issuer']
            );

            $this->assertSame('', $setting->write_setting(0));
            $this->assertSame(
                'https://example.edu/login/index.php',
                get_config('core', 'alternateloginurl')
            );
        } finally {
            $CFG->wwwroot = $originalwwwroot;
        }
    }
}