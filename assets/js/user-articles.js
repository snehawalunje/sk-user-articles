jQuery(document).ready(function($) {
    var GetDataScript = {

        init: function() {
                var $ = jQuery.noConflict();
                var $this = this;
                $( document ).on( 'click', '.get_usr_list', this._GetUserData );
                $( document ).on( 'click', '.get_post_list', this._GetPostData );
        },

        // Get data
        _GetUserData: function() {
            event.preventDefault();
                var data_usr = $(this).attr("data-id"); 
                $(".get_usr_list").attr("disabled", true);
                var data = {
                    'url'   :getdatauserajaxsubmission.ajax_url,
                    'action': 'get_data_ajax_submission',
                    'data': data_usr,
                };
                $.post(getdatauserajaxsubmission.ajax_url, data, function( response ){
                    $(".get_usr_list").removeAttr("disabled");
                    if(response.html != null){
                       $('#success_message').fadeIn().html(response.html);
                        setTimeout(function() {
                            $('#success_message').fadeOut("slow");
                        }, 4000 );
                    }else{
                      $('#success_message').fadeIn().html(response.exist);
                        setTimeout(function() {
                            $('#success_message').fadeOut("slow");
                        }, 4000 );
                    }

                });    
        },
        _GetPostData: function() {
            event.preventDefault();
                var data_post = $(this).attr("data-id"); 
                $(".get_post_list").attr("disabled", true);
                var data = {
                    'url'   :getdatauserajaxsubmission.ajax_url,
                    'action': 'get_post_ajax_submission',
                    'data': data_post,
                };
                $.post(getdatauserajaxsubmission.ajax_url, data, function( response ){
                    $(".get_post_list").removeAttr("disabled");
                    if(response.html != null){
                       $('#success_message').fadeIn().html(response.html);
                        setTimeout(function() {
                            $('#success_message').fadeOut("slow");
                        }, 4000 );
                    }else{
                      $('#success_message').fadeIn().html(response.exist);
                        setTimeout(function() {
                            $('#success_message').fadeOut("slow");
                        }, 4000 );
                    }

                });    
        }
    };

    $( function() {
        GetDataScript.init();
    }); 
});
