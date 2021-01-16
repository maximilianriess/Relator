<?php

# Author: M.Riess; Riess Business Group GmbH (info@riess-group.de)
# Relator for MantisBT is free software:
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation,
# either version 2 of the License, or (at your option) any later version.
#
# Relator plugin for MantisBT is distributed in the hope
# that it will be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Relator plugin for MantisBT.
# If not, see <http://www.gnu.org/licenses/>.

class RelatorPlugin extends MantisPlugin {

    public function register() {
        $this->name        = 'Relator';
        $this->description = plugin_lang_get( 'description' );

        $this->version  = '1.1.5';
        $this->requires = array(
                                  'MantisCore' => '2.14.0',
        );

        $this->author  = 'M. Riess; Riess Business Group GmbH';
        $this->contact = 'info@riess-group.de';
        $this->url     = 'http://github.com/mantisbt-plugins/Relator';
	//        $this->page    = 'config_page';
    }

    function hooks() {
        return array(
		'EVENT_LAYOUT_RESOURCES' => 'resources',
		'EVENT_VIEW_BUG_DETAILS' => 'viewpage',
        );
    }

    function resources() {

        $t_page = array_key_exists( 'REQUEST_URI', $_SERVER ) ? basename( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ) : basename( __FILE__ );
        if( $t_page == 'view.php' ) {
		$tag = '<link ';
		$tag.= 'rel="stylesheet" ';
		$tag.= 'type="text/css" ';
		$tag.= 'href="' . plugin_file('relator_150120211043.css') .'">';
		$tag.= '</link>';
		$tag.= '<script type="text/javascript" src="' . plugin_file( 'relator.js' ) . '"></script>';
		return $tag;
        }
    }

    function viewpage() {
		$tag = '';
		$tag.= '<form name="relator_form">';
		$tag.= '<input type="hidden" name="relator_info" value="' . plugin_lang_get('info_related_issue_list') . '">';
		$tag.= '</form>';
		echo($tag);
    }

}
