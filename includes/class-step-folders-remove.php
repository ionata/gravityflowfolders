<?php

/**
 * Gravity Flow Add Entry Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_Folders_Remove extends Gravity_Flow_Step {
		public $_step_type = 'folders_remove';

		/**
		 * Returns the label.
		 *
		 * @since 1.0
		 *
		 * @return string
		 */
		public function get_label() {
			return esc_html__( 'Remove from Folder', 'gravityflowfolders' );
		}

		/**
		 * Returns the url/icon for the step.
		 *
		 * @since 1.0
		 *
		 * @return string
		 */
		public function get_icon_url() {
			return '<i class="fa fa fa-folder-o" style="color:darkgreen"></i>';
		}

		/**
		 * Returns the settings for this step.
		 *
		 * @return array
		 */
		public function get_settings() {
			$fields = array();

			$folders = gravity_flow_folders()->get_folders();

			$folder_choices = array();
			foreach ( $folders as $folder ) {
				$label = $folder->get_name();

				$folder_choices[] = array(
					'label' => $label,
					'name'  => $folder->get_meta_key(),
				);
			}

			if ( ! empty( $folder_choices ) ) {
				$fields[] = array(
					'name'     => 'folders',
					'required' => true,
					'label'    => esc_html__( 'Folders', 'gravityflowfolders' ),
					'type'     => 'checkbox',
					'choices'  => $folder_choices,
				);
			}

			if ( empty( $fields ) ) {
				$html     = esc_html__( "You don't have any folders set up.", 'gravityflow' );
				$fields[] = array(
					'name'  => 'no_folders',
					'label' => esc_html__( 'Folders', 'gravityflowfolders' ),
					'type'  => 'html',
					'html'  => $html,
				);
			}

			return array(
				'title'  => $this->get_label(),
				'fields' => $fields,
			);
		}

		/**
		 * Returns all the folders.
		 *
		 * @since 1.0
		 *
		 * @return Gravity_Flow_Folder[]
		 */
		public function get_folders() {
			return gravity_flow_folders()->get_folders();
		}

		/**
		 * Processes this step.
		 *
		 * @since 1.0
		 *
		 * @return bool
		 */
		function process() {
			// add to folders

			$folders = $this->get_folders();

			foreach ( $folders as $folder ) {
				$setting_key = $folder->get_meta_key();
				$setting_key_match = $this->{$setting_key};
				$entry_id = $this->get_entry_id();

				/**
				 * Allows determination of whether entry should be removed from a folder to be customized
				 *
				 * @since 1.3
				 *
				 * @param bool              $setting_key_match  Does the folder match current step settings
				 * @param array             $folder             The folder which created this entry.
				 * @param int               $entry_id           The entry ID.
				 * @param Gravity_Flow_Step $current_step       The current step for this entry.
				 * 
				 * @return bool
				 */
				$setting_key_match = apply_filters( 'gravityflowfolders_folder_match_remove_step', $setting_key_match, $folder, $entry_id, $this );

				if ( $setting_key_match ) {
					$entry_id = $this->get_entry_id();
					$folder->remove_entry( $entry_id );
					$label = $folder->get_name();
					$note  = sprintf( esc_html__( 'Removed from folder: %s', 'gravityflow' ), $label );
					$this->add_note( $note );
				}
			}

			return true;
		}
	}
}



