
$(function(){

	/*Add alpha-numeric validation*/
	jQuery.validator.addMethod("alphanumeric", function(value, element) {
		return this.optional(element) || /^[a-zA-Z0-9]+$/i.test(value);
	}, "Please use letters or numbers only.");

	/*Validate the Form*/
	$("#frm-MHI-Signup").validate({
		rules: {
			signup_first_name: {
				required: true,
				rangelength: [1, 30]
			},
			signup_last_name: {
				required: true,
				rangelength: [1, 30]
			},
			signup_email: {
				required: true,
				email: true,
				remote: {
			        url: "<?php echo url::base(); ?>mhi/checkemail/",
			        type: "post"
				}
			},
			signup_password: {
				required: true,
				rangelength: [5, 32]
			},
			signup_confirm_password: {
				required: true,
				equalTo: "#signup_password"
			},
			signup_subdomain: {
				required: true,
				alphanumeric: true,
				rangelength: [4, 32],
				remote: {
			        url: "<?php echo url::base(); ?>mhi/checksubdomain/",
			        type: "post"
				}
			},
			signup_instance_name: {
				required: true,
				rangelength: [4, 100]
			},
			signup_instance_tagline: {
				required: true,
				rangelength: [4, 100]
			},
			signup_tos: {
				required: true
			},
			signup_mailinglist: {
				required: false
			},
			verify_password: {
				required: true,
				rangelength: [5, 32]
			}
		},
		messages: {
			signup_first_name: {
				required: "Please enter your first name.",
				rangelength: "Your first name must be between 1 and 30 characters."
			},
			signup_last_name: {
				required: "Please enter your last name.",
				rangelength: "Your first last must be between 1 and 30 characters"
			},
			signup_email: {
				required: "Please enter your email address.",
				email: "Please enter a valid email address.",
				remote: "On CrowdmapID"
			},
			signup_password: {
				required: "Please enter a password.",
				rangelength: "Your password must be between 4 and 32 characters."
			},
			signup_confirm_password: {
				required: "Please confirm your password.",
				equalTo: "Passwords do not match."
			},
			signup_subdomain: {
				required: "Please enter your deployment address.",
				rangelength: "The name you use for your deployment address must be between 4 and 32 characters.",
				remote: "This subdomain has already been taken."
			},
			signup_instance_name: {
				required: "Please enter a name for your deployment.",
				rangelength: "Name must be between 4 and 100 characters."
			},
			signup_instance_tagline: {
				required: "Please enter a tagline for your deployment.",
				rangelength: "Tagline must be between 4 and 100 characters."
			},
			signup_tos: {
				required: "You must accept the Website Terms of Use."
			},
			signup_password: {
				required: "Please enter your password.",
				rangelength: "Your password is between 4 and 32 characters."
			}

		},
		errorPlacement: function(error, element) {
			if (error.text() == 'On CrowdmapID') {
				point_to_login();
			}else{
				error.appendTo(element.parent());
			}
	    }
	});
});

function reset_point_to_login()
{
	$('#frm-MHI-Signup input').not($('#signup_email')).fadeTo('fast',1);
	$('#frm-MHI-Signup input').not($('#signup_email'), '#frm-MHI-Login :input').attr('disabled', false);
	$("#login-form").hide();
	$('#username').val('');
	$('#already_registered_info').fadeOut('slow');
}

function point_to_login()
{
	$('#frm-MHI-Signup input').not($('#signup_email')).fadeTo('fast',0.25);
	$('#frm-MHI-Signup input').not($('#signup_email'), '#frm-MHI-Login :input').attr('disabled', true);
	// Show the login form box
	$("#login-form").show();
    $('#btn_sign-in').addClass("active");
    $('#username').val($('#signup_email').val());
    $('#already_registered_info').fadeIn('slow');
}