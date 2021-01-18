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


function string_get_onclick($issue_id) {
	$tag = "related_issue_selected(" . $issue_id . "); return false;";
	return $tag;
}


function array_get_search_elems($input) {

	# search for [pro ject]search
	$pattern = "/\[(.*)\](.*)/";
	$result = array();
	if( preg_match($pattern, $input, $result) ) {
		$project_name = trim( $result[1] );
		$search_value = trim( $result[2] );
		return array( $project_name, $search_value);
	}


	# search for [project search
	$pattern = "/\[(.*)\s+(.*)/";
	$result = array();
	if( preg_match($pattern, $input, $result) ) {
		$project_name = trim( $result[1] );
		$search_value = trim( $result[2] );
		return array( $project_name, $search_value);
	}

	# match for incomplete search (bracket, but not complete..
	$pattern = "/\[.*/";
	$result = array();
	if( preg_match($pattern, $input, $result) ) {
		$project_name = $result[0];
		$search_value = $result[1];
		return array( "", "" );
	}

	$search_value = trim( $input );
	return array( '', $search_value);
}


function string_get_search_value($input) {
	return array_get_search_elems($input)[1];
}


function array_get_project_ids($input) {
	$project_search = strtolower( array_get_search_elems($input)[0] );

	# if no project selected, use current
	if($project_search=="") {
		$t_current_project = helper_get_current_project();
		return array($t_current_project);
	}

	# search in all projects:
	if($project_search=="*") {
		$p_project_ids = current_user_get_accessible_projects();
		return $p_project_ids;
	}

	# search for matching projects
	$p_project_ids = current_user_get_accessible_projects();
	$projects = array();
	foreach ($p_project_ids as $t_project_id ) {
		$t_project_name = strtolower(project_get_name($t_project_id));
		if( substr($t_project_name, 0, strlen($project_search)) === $project_search ) {
			array_push($projects, $t_project_id);
		}
	}

	return $projects;
}


form_security_validate( 'bug_relationship_add' );

$t_temp_filter = filter_get_default();

$t_temp_filter[FILTER_PROPERTY_HIDE_STATUS] = META_FILTER_NONE;

$t_filter = filter_ensure_valid_filter( $t_temp_filter );

$search_value = string_get_search_value( gpc_get_string( 'referal' ) );

if($search_value == "") {
    return ""; 
}

$t_src_bug_id = gpc_get_int( 'src_bug_id' );

$t_filter['search'] = $search_value;

$t_request_id = gpc_get_int( 'request_id' );

$t_project_ids = array_get_project_ids( gpc_get_string( 'referal' ) );


$t_per_page   = null;
$t_bug_count  = null;
$t_page_count = null;


$theprojectid = helper_get_current_project();
$t_project_names="";

$html = '';
$html.= '<ul class="search_result" style="margin-left: 50px;">';
$t_rows = array();


$cnt=0;
$maxnames=5;
if(count($t_project_ids)>0) {
	# query all selected projects
	foreach($t_project_ids as $t_project_id) {
		$cnt++;
		helper_set_current_project( $t_project_id );
		$t_project_name = project_get_name($t_project_id);
		if($cnt<=$maxnames) {
			if($t_project_names!="") { $t_project_names.= ", ";}
			$t_project_names.= $t_project_name;
			if($cnt==$maxnames && count($t_project_ids)>$maxnames) {
			    $t_project_names.= "...";
			}
		}

		$t_r = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter, null, null, true );
		$t_rows = array_merge($t_rows, $t_r);
	}

	helper_set_current_project($theprojectid);
} else {
	# inform about no projects selected
	$html.= '<li style="margin-left: 5px; margin-top: 5px; margin-bottom: 5px;">';
	$html.= 'keine passenden Projekte gefunden';
	$html.= '</ul>';
}


if(count($t_project_ids) == count(current_user_get_accessible_projects()) ) {
	$t_project_names = plugin_lang_get( 'any_project' );
}


$resultshtml="";
$resultcount=0;
if( count( $t_rows ) > 0 ) {
    foreach( $t_rows as $t_issue ) {
	if($t_src_bug_id != "" && $t_issue->id == $t_src_bug_id) {
		continue;	# do not include the current ticket id	
	}
	$resultcount++;
	$resultshtml.= '<li>';
	$resultshtml.= '<a class=search_result style="background-color:' . get_status_color( $t_issue->status );
	$resultshtml.= ';" href="#" onclick="' . string_get_onclick( $t_issue->id ) . '";>';
	$resultshtml.= $t_issue->id . ": [" . project_get_name($t_issue->project_id) . "] <b>" . $t_issue->summary . "</b>";
	$resultshtml.= '</a>';
	$resultshtml.= '</li>';
    }

}


if($resultcount>0) {
	# inform about amount of hits
	$html.= '<li style="margin-left: 5px; margin-top: 5px; margin-bottom: 5px;">';
	$html.= sprintf( plugin_lang_get( 'related_issue_list_found' ), $resultcount, $search_value, $t_project_names );
	$html.= '</li>';
} else { 
	$html.= '<li style="margin-left: 5px; margin-top: 5px; margin-bottom: 5px;">';
	$html.= sprintf( plugin_lang_get( 'related_issue_list_nores' ), $search_value, $t_project_names );
	$html.= '</li>';
}

$html.= $resultshtml;
$html.= '</ul>';


$t_response['request_id'] = $t_request_id;
$t_response['data'] = $html;
$t_response_json = json_encode( $t_response );

echo $t_response_json;

