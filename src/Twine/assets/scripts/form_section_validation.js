var TWINE_FORM_VALIDATION;
jQuery(document).ready(function($){

	/**
	 * @function EE Form Validation (TWINE_FORM_VALIDATION)
	 * @description uses the variables localized in EE_Form_Section_Proper's _enqueue_and_localize_form_js() to generate the validation js
	 *
	 * @namespace TWINE_FORM_VALIDATION
	 * @type {{
	 *     validation_rules_array: object,
	 *     validation_rules_per_html_form: object,
	 *     form_validators: object,
	 * }}
	 * @namespace twine_form_section_vars
	 * @type {{
	 *     form_data: object,
	 *     form_section_id: string,
	 *     email_validation_level: string,
	 *     validation_rules: object,
	 *     localized_error_messages: object,
	 *     errors: object
	 * }}
	 * @type {{ ee_form_section_validation_init : boolean }}
	 */
	TWINE_FORM_VALIDATION = {

        // validation rules from the eei18n localized JSON array
		validation_rules_array : twine_form_section_vars.form_data,
		// what level of email validation is required ?
		email_validation_level : twine_form_section_vars.email_validation_level,
		//foreach ee form section, compile an array of what validation rules apply to which html form
		validation_rules_per_html_form : {},
		// current form to be validated
		form_validators : {},

        /**
         * Set some settings common for EE form validation, that don't need
         * to be set every time we initialize (or re-initialize) a form
         */
        set_validation_defaults : function() {

            // jQuery validation object
            $.validator.setDefaults({
                errorPlacement: function (error, input) {
                    //remove error inputs added server-side,
                    //this new error overrides it
                    input.siblings('label.error').remove();
                    error.appendTo(input.parent());
                }
            });
            TWINE_FORM_VALIDATION.add_custom_validators();
        },


		/**
		 *	@function initialize
		 *  @param {object} form_data
		 */
		initialize : function( form_data ) {
            TWINE_FORM_VALIDATION.initialize_datepicker_inputs();
			TWINE_FORM_VALIDATION.initialize_select_reveal_inputs( form_data );
			TWINE_FORM_VALIDATION.validation_rules_array = form_data;
			TWINE_FORM_VALIDATION.setup_validation_rules( form_data );
			//add a trigger so anyone can know when forms are getting re-initialized
			$(document).trigger( 'TWINE_FORM_VALIDATION:initialize', form_data );
			//let's execute a trigger for each form in the localized data. This way
			//client code doesn't need to manually loop over it all
			$.each( form_data, function( html_id, form_data_for_specific_section ){
				$(document).trigger(
					'TWINE_FORM_VALIDATION:initialize_specific_form',
					{
						'html_id' : html_id,
						'form_data' : form_data_for_specific_section
					}
				);
			});
		},



        /**
         *	@function reset_validation_rules
		 */
        reset_validation_rules : function() {
			TWINE_FORM_VALIDATION.remove_previous_validation_rules();
			TWINE_FORM_VALIDATION.validation_rules_per_html_form = {};
            TWINE_FORM_VALIDATION.form_validators = {};
        },



		/**
		 * @function initialize_datepicker_inputs
		 */
		initialize_datepicker_inputs : function() {
			// if datepicker function exists
			if ( $.fn.datepicker ) {
				// activate datepicker fields
				$( '.twine-datepicker' ).datepicker({
					changeMonth: true,
					changeYear: true,
					yearRange: "-50:+50"
					// yearRange: "-150:+20"
				});
			}
		},

		/**
		 * Find each select_reveal input in the form_data, and reveal the section corresponding
		 * to its currently selected value, and setup a callback so that when the selection
		 * changes, the section revealed also changes
		 * @param {object} form_sections_to_validate property names are form section ids, values are objects:
		 *	which should have a property name "other_data", whose values is an object which:
		 *		has property names of each select_reveal input id, whose value is an object which:
		 *			has property names of the select's option values, and property values are related sections to show/hide
		 *			based on the select_reveal's value
		 * @returns void
		 */
		initialize_select_reveal_inputs : function( form_sections_to_validate ) {
			//for each form...
			$.each( form_sections_to_validate, function( index, form_data ){
				if (
					typeof form_data.other_data !== 'undefined'
					&& typeof form_data.other_data.select_reveal_inputs !== 'undefined'
				) {
					//for each select_reveal input...
					$.each( form_data.other_data.select_reveal_inputs , function( select_reveal_input_id, select_option_to_section_to_reveal_id ) {
						//define a callback for revealing/hiding the sections related to this select_reveal input
						var reveal_now = function( event ) {
							var current_selection = $('#' + event.currentTarget.id ).val();
							//show the selected section, hide others
							for( var value in select_option_to_section_to_reveal_id ) {
								var section_to_show_or_hide_selector = '#' +  select_option_to_section_to_reveal_id[ value ];
								if( value === current_selection ) {
									$( section_to_show_or_hide_selector ).show();
								} else {
									$( section_to_show_or_hide_selector ).hide();
								}
							}
						};
						//update what's shown or hidden when the select_reveal's value changes
						$('#' + select_reveal_input_id ).change(
							{ select_option_to_section_to_reveal_id : select_option_to_section_to_reveal_id },
							reveal_now
						);
						//and start off with it showing the right value
						reveal_now(
							{
								currentTarget: {
									id: select_reveal_input_id
								},
								data: {
									select_option_to_section_to_reveal_id : select_option_to_section_to_reveal_id
								}
							}
						);
					});
				}
			});
		},


		/**
		 *	@function setup_validation_rules
		 *	@param {object} form_sections_to_validate
		 */
		setup_validation_rules : function( form_sections_to_validate ) {
			//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.setup_validation_rules > form_sections_to_validate', form_sections_to_validate, true );
			// loop through all form sections
			$.each( form_sections_to_validate, function( index, form_data ){
				//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.setup_validation_rules > form_sections_to_validate > index', index, true );
				//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.setup_validation_rules > form_sections_to_validate > form_data', form_data, false );
				if ( typeof form_data.form_section_id !== 'undefined' && typeof form_data.validation_rules !== 'undefined' ) {

					// grab the actual html form from the DOM
					var html_form = $( form_data.form_section_id ).closest('form');
					// IF one exists, that is ...
					if ( html_form.length ) {
						//make sure the form tag has an id
						var form_id = html_form.attr('id');
						if ( typeof form_id === 'undefined' || form_id === '' ) {
							form_id = TWINE_FORM_VALIDATION.generate_random_string(15);
							html_form.attr( 'id', form_id );
						}
						// if the form already exists, then let's reset it
						if ( typeof TWINE_FORM_VALIDATION.form_validators[ form_id ] !== 'undefined' ) {
							TWINE_FORM_VALIDATION.resetForm(TWINE_FORM_VALIDATION.form_validators[ form_id ]);
						}

                        // remove the non-js-generated server-side validation errors
                        // because we will allow jquery validate to populate them
                        // need to call validate() before doing anything else, i know, seems counter intuitive...
                        // but let SPCO set it's own defaults
                        TWINE_FORM_VALIDATION.form_validators[form_id] = html_form.validate();
						// now add form section's validation rules
						TWINE_FORM_VALIDATION.add_rules( form_data.form_section_id, form_data.validation_rules );
						// and cache incoming form sections and rules so that they can be later removed if necessary
						TWINE_FORM_VALIDATION.validation_rules_per_html_form[ form_data.form_section_id ] = form_data.validation_rules;
					}
				}

			});

		},



		/**
		 *	@function apply_rules
		 *  @param {string} form_id
		 *  @param {object} form_data
		 */
		add_rules : function( form_id, form_data ) {
			//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.apply_rules', '', true );
			//console.log( form_data );
			//now apply those validation rules to each html form, and show the server-side errors properly
			$.each( form_data, function( input_id, validation_rules ){
				var form_input = $( input_id );
				//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.apply_rules > input_id', input_id, false );
				//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.apply_rules > validation_rules', validation_rules, false );
				//alert( 'form_input ID = ' + form_input.attr('id') );
				if ( typeof form_input !== 'undefined' && form_input.length ) {
					form_input.rules( 'add', validation_rules );
				}
			});
		},



		/**
		 *	@function remove_previous_validation_rules
		 */
		remove_previous_validation_rules : function() {
			// remove any previously applied validation rules for each html form
			$.each( TWINE_FORM_VALIDATION.validation_rules_per_html_form, function( form_section_id, form_data ){
				if (
					typeof form_section_id !== 'undefined'
					&& typeof $( form_section_id ).attr('id') !== 'undefined'
					&& typeof form_data !== 'undefined'
				) {
					TWINE_FORM_VALIDATION.remove_rules( form_data );
				}
			});
		},



		/**
		 *	@function apply_rules
		 *  @param {object} form_data
		 */
		remove_rules : function( form_data ) {
			//TWINE_FORM_VALIDATION.console_log( 'TWINE_FORM_VALIDATION.remove_rules', '', true );
			//console.log( form_data );
			//now apply those validation rules to each html form, and show the server-side errors properly
			$.each( form_data, function( input_id ){
				var form_input = $( input_id );
				if ( typeof form_input !== 'undefined' && form_input.length ) {
					//alert( 'remove_rules input_id = ' + input_id + '\n' + 'form_input ID = ' + form_input.attr('id') );
					form_input.rules( 'remove' );
				}
			});
		},



		/**
		 *	@function add_custom_validators
		 */
		add_custom_validators : function() {
            //adds a method used for validation URLs, which isn't native to jquery validate
			$.validator.addMethod( "validUrl",
				function( value, element ) {
					if ( this.optional( element )){
						return true;
					} else {
						var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
						return RegExp.test(value);
					}
				},
				twine_form_section_vars.localized_error_messages.validUrl
			);
			$.validator.addMethod(
				"regex",
				function(value, element, regexp) {
					//remove the delimiter PHP needed
//					var reMeta = /(^|[^\\])\/(\w+$){0,1}/;
//					regexp = new RegExp( regexp.replace(reMeta,'$1') );
					// after replace it looks like: "hello\/slash\/"
					var re = new RegExp(regexp);
					return this.optional(element) || re.test(value);
				},
				twine_form_section_vars.localized_error_messages.regex
			);

			if ( typeof TWINE_FORM_VALIDATION.email_validation_level !== 'undefined' && TWINE_FORM_VALIDATION.email_validation_level !== '' ) {
				var regex = /[^\s@]+@[^\s@]+\.[^\s@]+/;
				// use international email validation regex ?
				if ( TWINE_FORM_VALIDATION.email_validation_level === 'wp_default' ) {
					// override internal email validator
					$.validator.methods.email = function ( value, element ) {
						return this.optional( element ) || TWINE_FORM_VALIDATION.is_email( value );
					};
					return;
				} else if ( TWINE_FORM_VALIDATION.email_validation_level === 'i18n' || TWINE_FORM_VALIDATION.email_validation_level === 'i18n_dns' ) {
					// plz see http://stackoverflow.com/a/24817336 re: the following regex that supports unicode
					regex = /^(?!\.)((?!.*\.{2})[a-zA-Z0-9\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFFu20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF\.!#$%&'*+-/=?^_`{|}~\-\d]+)@(?!\.)([a-zA-Z0-9\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFF\u20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF\-\.\d]+)((\.([a-zA-Z\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFF\u20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF]){2,63})+)$/i;
				}
				// override internal email validator
				$.validator.methods.email = function ( value, element ) {
					return this.optional( element ) || regex.test( value );
				};
			}
		},
		/**
		 * We can't use jquery validate's native resetForm() because jquery-form
		 * also defines it and they conflict (ie, we want to call jquery validate's resetForm,
		 * but we instead get jquery-form's resetForm, which is a totally different method),
		 * so we're best off just avoiding using resetForm() entirely.
		 * But this method does the same thing as jquery-validate's resetForm.
		 * @param {object} form
		 * @returns void
		 */
		resetForm: function( form ) {
			form.invalid = {};
			form.submitted = {};
			form.prepareForm();
			form.hideErrors();
			var b = form.elements().removeData("previousValue").removeAttr("aria-invalid");
			form.resetElements(b)
		},



		/**
		 * is_email function from WordPress written in Javascript
		 * by Louy Alakkad <me@l0uy.com>
		 * https://gist.github.com/louy/5947841
		 * Verifies that an email is valid.
		 * Does not grok i18n domains. Not RFC compliant.
		 *
		 * @function is_email
		 * @param {string} $email Email address to verify.
		 * @return {boolean} Either false or the valid email address.
		 */
		is_email : function( $email ) {
			// Test for the minimum length the email can be
			if ( $email.length < 3 ) {
				return false;
			}

			// Test for a single @ character after the first position
			if ( $email.indexOf( '@' ) === -1 || $email.indexOf( '@' ) !== $email.lastIndexOf( '@' ) ) {
				return false;
			}

			// Split out the local and domain parts
			var parts = $email.split( '@', 2 );
			var $local = parts[ 0 ], $domain = parts[ 1 ];

			// LOCAL PART
			// Test for invalid characters
			if ( !/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/.test( $local ) ) {
				return false;
			}

			// DOMAIN PART
			// Test for sequences of periods
			if ( /\.{2,}/.test( $domain ) ) {
				return false;
			}

			// Test for leading and trailing periods and whitespace
			if ( TWINE_FORM_VALIDATION.string_trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
				return false;
			}

			// Split the domain into subs
			var subs = $domain.split( '.' );

			// Assume the domain will have at least two subs
			if ( 2 > subs.length ) {
				return false;
			}
			var i;
			// Loop through each sub
			for ( i in subs ) {
				if ( subs.hasOwnProperty( i ) ) {
					// Test for leading and trailing hyphens and whitespace
					if ( TWINE_FORM_VALIDATION.string_trim( subs[ i ], " \t\n\r\0\x0B-" ) !== subs[ i ] ) {
						return false;
					}
					// Test for invalid characters
					if ( !/^[a-z0-9-]+$/i.test( subs[ i ] ) ) {
						return false;
					}
				}
			}

			// Congratulations your email made it!
			return true;

		},



		/**
		 * trims leading and trailing hyphens and whitespace
		 * @function string_trim
		 * @param  {string} stringToTrim
		 * @param  {string} regex
		 */
		string_trim : function( stringToTrim, regex ) {
			if ( typeof stringToTrim !== 'string' || typeof regex !== 'string' ) {
				return '';
			}
			var chr = regex.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|\:\!\,\=]/g, "\\$&" );
			return stringToTrim.replace( new RegExp( '/^[' + chr + ']*/' ), '' ).replace(
				new RegExp( '/[' + chr + ']*$/' ),
				''
			);
		},



		/**
		 * for generating a random string to make an ID for an html form
		 * if it doesn't have one already
		 * @function generate_random_string
		 * @param {number} n
		 */
		generate_random_string : function( n ) {
			if( ! n ) {
				n = 5;
			}
			var text = '';
			var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			for( var i=0; i < n; i++ ) {
				text += possible.charAt(Math.floor(Math.random() * possible.length));
			}
			return text;
		},



		/**
		 *  @function console_log
		 *  print to the browser console
		 * @param  {string} item_name
		 * @param  {*} value
		 * @param  {boolean} spacer
		 */
		console_log: function ( item_name, value, spacer ) {
			if ( eei18n.wp_debug ) {
				if ( typeof spacer !== 'undefined' && spacer === true ) {
					console.log( ' ' );
				}
				if ( typeof value === 'object' ) {
					TWINE_FORM_VALIDATION.console_log_object( item_name, value, 0 );
				} else {
					if ( typeof item_name !== 'undefined' && typeof value !== 'undefined' && value !== '' ) {
						console.log( item_name + ' = ' + value );
					} else if ( typeof item_name !== 'undefined' ) {
						console.log( item_name );
					}
				}
			}
		},

		/**
		 * @function console_log_object
		 * print object to the browser console
		 * @param  {string} obj_name
		 * @param  {object} obj
		 * @param  {number} depth
		 */
		console_log_object: function ( obj_name, obj, depth ) {
			if ( eei18n.wp_debug ) {
				depth = typeof depth !== 'undefined' ? depth : 0;
				var spacer = '';
				for ( var i = 0; i < depth; i++ ) {
					spacer = spacer + '. ';
				}
				if ( typeof obj === 'object' ) {
					if ( typeof obj_name !== 'undefined' ) {
						//console.log( obj_name );
						TWINE_FORM_VALIDATION.console_log( spacer + obj_name, '', false );
					} else {
						//console.log( 'console_log_object : ' );
						TWINE_FORM_VALIDATION.console_log( spacer + 'console_log_object : ', '', false );
					}
					spacer = spacer + '. ';
					depth++;
					$.each( obj, function( index, value ){
						if ( typeof value === 'object' ) {
							if ( depth < 4 ) {
								TWINE_FORM_VALIDATION.console_log_object( index, value, depth );
							}
						} else {
							TWINE_FORM_VALIDATION.console_log( spacer + index, value, depth );
							depth++;
						}
					});
				} else {
					TWINE_FORM_VALIDATION.console_log( spacer + obj_name, obj, true );
				}
			}
		}


	};
	// end of TWINE_FORM_VALIDATION object
    //always setup default validation stuff
    TWINE_FORM_VALIDATION.set_validation_defaults();
    //conditionally initialize the form (other code may want to control this though)
	if(
		typeof( twine_form_section_validation_init ) !== 'undefined'
		&& twine_form_section_validation_init.init === '1'
		&& typeof( twine_form_section_vars ) !== 'undefined'
	) {
		TWINE_FORM_VALIDATION.initialize( twine_form_section_vars.form_data );
	}
});
