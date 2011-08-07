<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'	     => 'Profile Photo',
    'pi_version'     => '1.0',
    'pi_author'	     => 'Peoples Elbow',
    'pi_author_url'  => 'http://www.peopleselbow.com/',
    'pi_description' => 'Custom member photo plugin',
	'pi_usage'       => Profile_photo::usage()
);

/*
	* File extension Class
	*
	* @package   ExpressionEngine
	* @category  Plugin
	* @author    Peoples Elbow
	* @copyright Copyright (c) 2011 Peoples Elbow
	* @link      http://www.peopleselbow.com
*/

class Profile_photo {
	
	public  $return_data = '';
	
	/*
		* Constructor
	*/
	function Profile_photo() {
		
		$this->EE =& get_instance();
		
		// The goto image if nothing else works:
		$goto = '/images/avatar/default.jpg';
		
		// is the member ID value set? If not, default to logged in member ID.
		$member_id     = ( ! $this->EE->TMPL->fetch_param('member_id') ) ? $this->EE->session->userdata['member_id'] : $this->EE->TMPL->fetch_param('member_id');
		
		if( $member_id == '' ) {
			// If there is no value for $member_id we should probably stop trying to do anything, return the goto file.
			//I suppose it's possible that this plugin could be used in a scenario where a default image is returned if a guest is viewing the page: but the example didn't seem to suggest that, so I didn't allow for it.  
			
			$this->return_data = $goto;
		} else {
			// We have a value, let's move on.
			
			// We may not need to perform queries to check for the avatar and / or photo if that data is already available.
			$avatar        = ( ! $this->EE->TMPL->fetch_param('avatar') ) ? '' : $this->EE->TMPL->fetch_param('avatar') ;
			$photo         = ( ! $this->EE->TMPL->fetch_param('photo') ) ? '' : $this->EE->TMPL->fetch_param('photo') ;
			
			// Accepts 'avatar' or 'photo': If it's empty call it photo, if not call it what it is.
			$primary_image = ( ! $this->EE->TMPL->fetch_param('primary_image') ) ? 'photo' : $this->EE->TMPL->fetch_param('primary_image');
		
			// You can set a default image.
			$default       = ( ! $this->EE->TMPL->fetch_param('default') ) ? '' : $this->EE->TMPL->fetch_param('default');
		
			// "Since we're storing the avatar data in a custom member field we would like to be able to pass the ID of that member field to the plugin." 
			//Here's that.
			$avatar_field  = ( ! $this->EE->TMPL->fetch_param('avatar_field') ) ? '' : $this->EE->TMPL->fetch_param('avatar_field');
			
			// Off we go.
			if ( $primary_image == 'avatar' ) {
				
				// The avatar has been defined as the preferred image.
				if ( $avatar != '' ) {
					
					// The member has an avatar: use it.
					$this->return_data = $avatar;
				} elseif ( $avatar == '' && $avatar_field != '' ) {
					
					// We don't have an avatar but we've got a custom field to look in:
					
					// The example shows a two digit value, so we'll add that to a typical custom member field:
					$avatar_field = 'm_field_id_' . $avatar_field;
					
					// THIS BREAKS IF THE AVATAR FIELD IS BAD!!!
					$query = $this->EE->db->query("SELECT $avatar_field FROM exp_member_data WHERE member_id = '$member_id'");
					if ( $query->row($avatar_field) != '' ) {
						
						// Results! Use them:
						$this->return_data = $query->row($avatar_field);
					} else {
						
						// No results: is there a default?
						if ( $default != '' ) {
							$this->return_data = $default;
						} else {
							
							// No default, use the goto file.
							$this->return_data = $goto;
						}
					}
				} elseif ( $avatar == '' && $avatar_field == '' && $default != '' ) {
					
					// There is no avatar and nowhere to look for one- but we've got a default: use it.
					$this->return_data = $default;
				} else {
					
					// Nothing came up. Use the goto file.
					$this->return_data = $goto;
				}
			} else {
				
				// The preferred image is photo. 
				if ( $photo != '' ) {
					
					// Use the photo!
					$this->return_data = $photo;
				} else {
					
					// We don't have a usable photo- check the DB for one:
					// THIS BREAKS IF THE MEMBER ID IS BAD!!!
					$query = $this->EE->db->query("SELECT photo_filename FROM exp_members WHERE member_id = '$member_id'");
					if ( $query->row('photo_filename') != '' ) {
						
						// Results! Use them:
						$this->return_data = $query->row('photo_filename');
					} else {
						
						// No results: is there a default?
						if ( $default != '' ) {
							$this->return_data = $default;
						} else {
							
							// No default, use the goto file.
							$this->return_data = $goto;
						}
					}
				}
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
	{
		ob_start(); 
		?>
        Looks to see if a user has a profile photo or a profile avatar associated with their account, outputs an image URL.
		
		Parameters (all are optional)
		member_id="" default is logged in member ID. WARNING! If the member_id is bad this will break.
		primary_image="" accepts avatar or photo, default value is photo.
		avatar="" If a member avatar can be supplied, this is where you'd put it.
		photo="" SAA for the member photo.
		default="" If you'd like to supply a default image if no member image can be found. 
		avatar_field="" Optionally you can supply a custom field to search for a member avatar. Looking for digits: ie "24" (will become m_field_id_24). WARNING! If the avatar_field is bad this will break.
		
		If all else fails, the plugin will return "/images/avatar/default.jpg". Unless of course the member_id or avatar_field contain bad data.
		
		Example:
		
		{exp:member:custom_profile_data}
		<img src="{exp:profile_photo member_id="{logged_in_member_id}" primary_image="photo" default="sample.png" avatar_field_id="1" photo="{photo_filename}"}">
		{/exp:member:custom_profile_data}
		
		{photo_filename} needs to be inside of {exp:member:custom_profile_data}, otherwise this plugin will work without it.
		
		<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------

}
// END CLASS

/* End of file pi.profile_photo.php */
/* Location: ./system/expressionengine/third_party/profile_photo/pi.profile_photo.php */