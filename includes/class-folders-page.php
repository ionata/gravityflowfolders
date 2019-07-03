<?php

class Gravity_Flow_Folders_Page {

	/**
	 * Renders the folder page.
	 *
	 * @since 1.0
	 *
	 * @param $args
	 */
	public static function render( $args ) {

		$defaults = array(
			'user_id' => absint( rgget( 'user_id' ) ),
			'folder'  => sanitize_text_field( rgget( 'folder' ) ),
		);

		$args = array_merge( $defaults, $args );

		if ( ! empty( $args['user_id'] ) ) {
			$user_id = $args['user_id'];
			$user    = get_user_by( 'ID', $user_id );

		} else {
			$user = wp_get_current_user();
		}

		if ( ! empty( $args['folder'] ) ) {
			/**
			 * Purpose of the filter
			 *
			 * @since 1.4
			 *
			 * @param string Folder name / ID from shortcode arguments
			 * @param array  Shortcode arguments
			 *
			 * @return string
			 */
			$folder_id = apply_filters( 'gravityflowfolders_display_folder', $args['folder'], $args );

			$folder = gravity_flow_folders()->get_folder( $folder_id, $user );
			if ( $folder ) {
				require_once( gravity_flow_folders()->get_base_path() . '/includes/class-folders-detail.php' );
				Gravity_Flow_Folders_Detail::display( $folder, $args );
			} else {
				esc_html_e( 'Folder not found.', 'gravityflow' );
			}
		} else {
			require_once( gravity_flow_folders()->get_base_path() . '/includes/class-folders-list.php' );
			$folders = gravity_flow_folders()->get_folders( $user );
			if ( $folders ) {
				Gravity_Flow_Folders_List::display( $folders, $user );
			} else {
				esc_html_e( "You don't have any folders configured.", 'gravityflow' );
			}
		}
	}
}
