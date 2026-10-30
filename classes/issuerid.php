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

/**
 * OAuth issuer select that automatically updates alternateloginurl on save.
 *
 * @package    local_oauthredirect
 * @copyright  (c) The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issuerid extends \admin_setting_configselect {
    /**
     * Save the issuer setting and update Moodle's Alternate login URL when valid.
     *
     * @param mixed $data Submitted setting value.
     * @return string Empty string on success, error message otherwise.
     */
    public function write_setting($data) {
        global $CFG;

        $result = parent::write_setting($data);

        if ($result === '' && (int)$data > 0) {
            set_config('alternateloginurl', $CFG->wwwroot . '/local/oauthredirect/redirect.php');
        }

        return $result;
    }
}
