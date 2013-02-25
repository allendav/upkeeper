// upkeeper.js

////////////////////////////////////////////////////////////////////////////

var UpKeeperItem = Backbone.Model.extend( {
	defaults: {
		description: 'New Item',
		expires: '2013-12-31'
	}
} );

////////////////////////////////////////////////////////////////////////////

var UpKeeperItemView = Backbone.View.extend( {
	editing: false,

	initialize: function() {
		this.model.on( 'change', this.render, this );
		this.model.on( 'destroy', this.remove ,this );
	},

	events: {
		"click .upkeeper-edit" : "toggleEdit",
		"click .upkeeper-cancel" : "toggleEdit",
		"click .upkeeper-save" : "onSave",
		"click .upkeeper-delete" : "onDelete"
	},

	toggleEdit: function() {
		this.editing = ! this.editing;
		this.render();
	},

	onSave: function() {
		var newDescription = jQuery( this.el ).find( '.upkeeper-description' ).val();
		var newExpires = jQuery( this.el ).find( '.upkeeper-expires' ).val();
		// TODO: Validate

		this.model.set( 'description', newDescription );
		this.model.set( 'expires', newExpires );
		this.model.save(); // Initiates PUT (update existing model)
		this.editing = false;
		this.render();
	},

	onDelete: function() {
		var choice = confirm("Are you sure you want to remove this item?");
		if ( choice == true ) {
			this.model.destroy(); // Also initiates DELETE (remove model at server)
		}
	},

	render: function() {
		var html = '<p>';

		if ( this.editing ) {
			html += '<input type="text" class="upkeeper-description" value ="' + this.model.get( 'description' ) + '" />';
			html += ' - Expires: <input type="text" class="upkeeper-expires" value ="' + this.model.get( 'expires' ) + '" />';
			html += '<br/>';
			html += '<a href="#" class="upkeeper-save">Save</a> | <a href="#" class="upkeeper-cancel">Cancel</a>';
		} else {
			html += '<b>' + this.model.get( 'description' ) + '</b>';
			html += ' - Expires: ' + this.model.get( 'expires' );
			html += '<br/>';
			html += '<a href="#" class="upkeeper-edit">Edit</a> | ';
			html += '<a href="#" class="upkeeper-delete">Remove From List</a>';
		}
		html += '</p>';
		jQuery( this.el ).html( html );
		return this;
	},

	remove: function() {
		jQuery( this.el ).remove();
	}
} );

////////////////////////////////////////////////////////////////////////////

var UpKeeperList = Backbone.Collection.extend( {
	// url is defined on page ready (see below)
	model: UpKeeperItem
} );

////////////////////////////////////////////////////////////////////////////

var UpKeeperListView = Backbone.View.extend( {
	initialize: function() {
		this.collection.on( 'add', this.addOne, this );
		this.collection.bind( 'reset', this.render, this );
	},
	render: function() {
		this.collection.forEach( this.addOne, this );
	},
	addOne: function( upKeeperItem ) {
		var upKeeperItemView = new UpKeeperItemView( { model: upKeeperItem } );
		jQuery( this.el ).append( upKeeperItemView.render().el );
	}
} );

////////////////////////////////////////////////////////////////////////////

var upKeeperList = new UpKeeperList();

var upKeeperListView = new UpKeeperListView( {
	collection: upKeeperList	
} );

////////////////////////////////////////////////////////////////////////////

jQuery(document).ready(function() {

	if ( jQuery('#upkeeper-list-view').length > 0 ) {

		jQuery('#upkeeper-list-view').html( upKeeperListView.el );
		upKeeperList.url = jQuery('#upkeeper-endpoint').text();
		upKeeperList.fetch();

		// Listen for adds
		jQuery('.upkeeper-add').click( function() {
			var newItem = new UpKeeperItem( {
				description: 'New Item',
				expires: '2013-12-31'
			});
			upKeeperList.add( newItem );
			newItem.save(); // Initiates POST
		} );
	}
});