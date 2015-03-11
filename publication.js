M.mod_publication = {};

M.mod_publication.init_mod_form = function (Y, atts) {

	var mode0 = Y.one('#id_mode_0');

	if (mode0) {
		mode0.on('change', toggle_available_options);
	}
	
	var mode1 = Y.one('#id_mode_1');
	
	if (mode1) {
		mode1.on('change', toggle_available_options);
	}

	function toggle_available_options() {		
		var mode = 1;
		
		if(Y.one('#id_mode_1').get('checked')){
			mode = 0;
		}
		
		if(mode == 1){ // upload
			Y.one("#fitem_id_importfrom").hide();
			Y.one("#fitem_id_obtainstudentapproval").hide();
			
			Y.one("#fitem_id_maxfiles").show();
			Y.one("#fitem_id_maxbytes").show();
			Y.one("#fitem_id_allowedfiletypes").show();
			Y.one("#fitem_id_obtainteacherapproval").show();
		}else{ // import
			Y.one("#fitem_id_maxfiles").hide();
			Y.one("#fitem_id_maxbytes").hide();
			Y.one("#fitem_id_allowedfiletypes").hide();
			Y.one("#fitem_id_obtainteacherapproval").hide();
			
			Y.one("#fitem_id_importfrom").show();
			Y.one("#fitem_id_obtainstudentapproval").show();
		}
	}
	
	toggle_available_options();

}

M.mod_publication.init_files_form = function(Y){
	YUI().use('event', function (Y) {
        Y.one('#fastg').on('submit', function (e) {
			if(Y.one('#menuaction').get('value') == 'zipusers'){				
				setTimeout(
					function(){
						Y.all('.userselection').each( function() {
						  this.set('checked', false);
						});
					}, 100);
			}
        });
    });
}