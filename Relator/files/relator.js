/*
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
 */
var display_request = 0;
var current_request = 0;
var loading_img;
var summary_input;


$(document).ready(function () {



    $('[name=add_relationship]').after('<li id="loader_area" style="list-style-type: none; float:left;" hidden=""><img width="40" height="40" id="loading_img" src="plugin_file.php?file=Relator/ajax-loading.gif"></li>');

    relator_info = document.getElementsByName('relator_info')[0].value;
    $('[name=add_relationship]').after('<div id="loader_info">' + relator_info + '</div>');

    loading_img = document.getElementById('loader_area');

    summary_input = document.getElementsByName('dest_bug_id')[0];

    var bug_relationship_add_token = document.getElementsByName('bug_relationship_add_token')[0];

    if (summary_input.value.length > 0) {
        search_request(summary_input.value, bug_relationship_add_token.value);
    }
    var current_timer = 0;
    $('[name=dest_bug_id]').bind("input", function () {
        if (this.value.length > 0 && this.value.trim() != 0) {

            loading_img.removeAttribute('hidden');
            clearTimeout(current_timer);
            var search_string = this.value;
            var token = $('[name=bug_relationship_add_token]').val();
            current_timer = setTimeout(function () {
                search_request(search_string, token);
            }, 700);
        }

        if (summary_input.value.length < 1) {
            $(".search_result").remove();
        }
    })
});

function search_request(search_string, token) {
    current_request++;
    $.ajax({
        type: 'post',
        url: 'plugin.php?page=Relator/search',
        data: {
            'referal': search_string,
            'bug_relationship_add_token': token,
            'request_id': current_request,
        },
        response: 'text',
        success: function (data, textStatus, jqXHR) {
            try {
                var response = JSON.parse(data);
                if (response['request_id'] > display_request) {
                    $(".search_result").remove();
                    $('[id=loader_area]').after(response['data']);
                    display_request = response['request_id'];
                }
            } catch (err) {
                console.log(err);
            }
            if (summary_input.value.length < 1) {
                $(".search_result").remove();
            }
            loading_img.setAttribute('hidden', '');
        }
    })
}


function related_issue_selected(issue_id) {
        document.getElementsByName('dest_bug_id')[0].value = issue_id;
        $('.search_result').remove(); 
        return false;
}



