<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Abstract class for a Gravity Flow Folder.
 *
 * @since 1.0
 */
abstract class Gravity_Flow_Folder {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $config = array();

	/**
	 * @var WP_User
	 */
	public $user;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * Gravity_Flow_Folder constructor.
	 *
	 * @param               $config
	 * @param \WP_User|null $user
	 */
	public function __construct( $config, WP_User $user = null ) {
		$this->id   = rgar( $config, 'id' );
		$this->name = rgar( $config, 'name' );

		if ( empty( $user ) ) {
			$user = wp_get_current_user();
		}
		$this->user   = $user;
		$this->type   = rgar( $config, 'type' );
		$this->config = $config;
	}

	/**
	 * Return the icon for the folder.
	 *
	 *
	 * @since 1.0
	 *
	 * @param int $size
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function icon( $size = 1, $echo = true ) {
		$icon = sprintf( '<i class="gravityflowfolders-folder fa fa-folder-o fa-%dx"></i>', $size );
		if ( $echo ) {
			echo $icon;
		}

		return $icon;
	}

	/**
	 * Returns the name of the folder.
	 *
	 * @since 1.0
	 */
	public function get_name() {
		return esc_html( $this->name );
	}

	/**
	 * Returns the ID of the folder.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns the folder type.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Adds and entry to the folder.
	 *
	 * @since 1.0
	 *
	 * @param $entry_id
	 */
	public function add_entry( $entry_id ) {
		$key = $this->get_meta_key();
		gform_update_meta( $entry_id, $key, time() );
	}

	/**
	 * Removes an entry from a folder.
	 *
	 * @since 1.0
	 *
	 * @param $entry_id
	 */
	public function remove_entry( $entry_id ) {
		$key = $this->get_meta_key();
		gform_delete_meta( $entry_id, $key );
	}

	/**
	 * Returns the entry meta key for the folder.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_meta_key() {
		return 'workflow_folder_' . $this->get_id();
	}

	/**
	 * Renders the folder.
	 *
	 * Must be overridden in a subclass.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function render( $args = array() ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be over-ridden in subclass." ), __METHOD__ ) );
	}

	/**
	 * Checks whether the given user has permission to open the folder and view all the entries.
	 *
	 * @since 1.0
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function user_has_permission( $user_id = 0 ) {

		$config = $this->config;

		$permissions = rgar( $config, 'permissions' );

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			return false;
		}

		if ( empty( $permissions ) || $permissions == 'all' ) {
			return true;
		}

		$assignees = rgar( $config, 'assignees' );

		if ( array_search( 'user_id|' . $user_id, $assignees ) !== false ) {
			return true;
		}

		foreach ( gravity_flow()->get_user_roles( $user_id ) as $role ) {
			if ( array_search( 'role|' . $role, $assignees ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
