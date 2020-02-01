<?php
/**
 * Gravity Flow Folders
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Extension
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

// Make sure Gravity Forms is active and already loaded.
if ( class_exists( 'GFForms' ) ) {

	class Gravity_Flow_Folders extends Gravity_Flow_Extension {

		private static $_instance = null;

		public $_version = GRAVITY_FLOW_FOLDERS_VERSION;

		public $edd_item_name = GRAVITY_FLOW_FOLDERS_EDD_ITEM_NAME;

		// The Framework will display an appropriate message on the plugins page if necessary
		protected $_min_gravityforms_version = '1.9.10';

		protected $_slug = 'gravityflowfolders';

		protected $_path = 'gravityflowfolders/folders.php';

		protected $_full_path = __FILE__;

		// Title of the plugin to be used on the settings page, form settings and plugins page.
		protected $_title = 'Folders Extension';

		// Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
		protected $_short_title = 'Folders';

		protected $_capabilities = array(
			'gravityflowfolders_folders',
			'gravityflowfolders_uninstall',
			'gravityflowfolders_settings',
			'gravityflowfolders_user_admin',
		);

		protected $_capabilities_app_settings = 'gravityflowfolders_settings';
		protected $_capabilities_uninstall = 'gravityflowfolders_uninstall';

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Gravity_Flow_Folders();
			}

			return self::$_instance;
		}

		private function __clone() {
		} /* do nothing */

		/**
		 * Early initialization.
		 *
		 * @since 1.0
		 */
		public function pre_init() {
			parent::pre_init();
			add_action( 'gravityflow_pre_restart_workflow', array( $this, 'action_gravityflow_pre_restart_workflow' ), 10, 2 );
		}

		/**
		 * Plugin initialization.
		 */
		public function init() {
			parent::init();
			add_filter( 'gravityflow_permission_granted_entry_detail', array( $this, 'filter_gravityflow_permission_granted_entry_detail' ), 10, 4 );
			if ( GFAPI::current_user_can_any( 'gravityflow_workflow_detail_admin_actions' ) ) {
				add_filter( 'gravityflow_status_args', array( $this, 'filter_gravityflow_status_args' ) );
				add_filter( 'gravityflow_bulk_action_status_table', array( $this, 'filter_gravityflow_bulk_action_status_table' ), 10, 4 );
				add_filter( 'gravityflow_admin_actions_workflow_detail', array( $this, 'filter_gravityflow_admin_actions_workflow_detail' ), 10, 5 );
				add_filter( 'gravityflow_admin_action_feedback', array( $this, 'filter_gravityflow_admin_action_feedback' ), 10, 4 );
			}
		}

		/**
		 * Front end initialization.
		 *
		 * @since 1.0
		 */
		public function init_frontend() {
			parent::init_frontend();
			add_filter( 'gravityflow_shortcode_folders', array( $this, 'shortcode' ), 10, 2 );
			add_filter( 'gravityflow_enqueue_frontend_scripts', array( $this, 'action_gravityflow_enqueue_frontend_scripts' ) );
		}

		/**
		 * Admin initialization.
		 *
		 * @since 1.0
		 */
		public function init_admin() {
			parent::init_admin();
			if ( $this->current_user_can_any( 'gravityflowfolders_user_admin' ) ) {
				add_filter( 'user_row_actions', array( $this, 'filter_user_row_actions' ), 10, 2 );
			}
		}

		/**
		 * Add the extension capabilities to the Gravity Flow group in Members.
		 *
		 * @since 1.2-dev
		 *
		 * @param array $caps The capabilities and their human readable labels.
		 *
		 * @return array
		 */
		public function get_members_capabilities( $caps ) {
			$prefix = $this->get_short_title() . ': ';

			$caps['gravityflowfolders_folders']    = $prefix . __( 'View Folders', 'gravityflowfolders' );
			$caps['gravityflowfolders_user_admin'] = $prefix . __( 'List Users', 'gravityflowfolders' );
			$caps['gravityflowfolders_settings']   = $prefix . __( 'Manage Settings', 'gravityflowfolders' );
			$caps['gravityflowfolders_uninstall']  = $prefix . __( 'Uninstall', 'gravityflowfolders' );

			return $caps;
		}

		/**
		 * Adds the scripts using the Gravity Forms Add-On Framework.
		 *
		 * @since 1.0
		 *
		 * @return array
		 */
		public function scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

			if ( $this->is_settings_page() ) {
				$forms = GFFormsModel::get_forms();

				$form_choices = array( array( 'value' => '', 'label' => __( 'Select a form', 'gravityflowfolders' ) ) );
				foreach ( $forms as $form ) {
					$form_choices[] = array(
						'value' => $form->id,
						'label' => $form->title,
					);
				}

				$user_choices = $this->get_users_as_choices();
				$scripts[]    = array(
					'handle'  => 'gravityflowfolders_settings_js',
					'src'     => $this->get_base_url() . "/js/folder-settings-build{$min}.js",
					'version' => $this->_version,
					'deps'    => array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-tabs' ),
					'enqueue' => array(
						array( 'query' => 'page=gravityflow_settings&view=gravityflowfolders' ),
					),
					'strings' => array(
						'vars'              => array(
							'forms'       => $form_choices,
							'userChoices' => $user_choices,
						),
						'folderName'        => __( 'Name', 'gravityflowfolders' ),
						'customLabel'       => __( 'Custom Label', 'gravityflowfolders' ),
						'forms'             => __( 'Forms', 'gravityflowfolders' ),
						'entryList'         => __( 'Entry List', 'gravityflowfolders' ),
						'checklist'         => __( 'Personal Checklist', 'gravityflowfolders' ),
						'sequential'        => __( 'Sequential', 'gravityflowfolders' ),
						'noItems'           => __( "You don't have any folders.", 'graviytflowfolders' ),
						'addOne'            => __( "Let's add one", 'graviytflowfolders' ),
						'areYouSure'        => __( 'This item will be deleted. Are you sure?', 'graviytflowfolders' ),
						'defaultFolderName' => __( 'New Folder', 'graviytflowfolders' ),
						'allUsers'          => __( 'All Users', 'gravityflowfolders' ),
						'selectUsers'       => __( 'Select Users', 'gravityflowfolders' ),
					),
				);
			}

			$scripts[] = array(
				'handle'  => 'gravityflow_status_list',
				'src'     => gravity_flow()->get_base_url() . "/js/status-list{$min}.js",
				'deps'    => array( 'jquery', 'gform_field_filter' ),
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'query' => 'page=gravityflow-folders&folder=_notempty_',
					),
				),
				'strings' => array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ),
			);

			return array_merge( parent::scripts(), $scripts );
		}

		/**
		 * Add the styles using the Gravity Forms Add-On Framework.
		 *
		 *
		 * @since 1.0
		 *
		 * @return array
		 */
		public function styles() {
			$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			$styles = array(
				array(
					'handle'  => 'gravityflowfolders_settings_css',
					'src'     => $this->get_base_url() . "/css/settings{$min}.css",
					'version' => $this->_version,
					'enqueue' => array(
						array( 'query' => 'page=gravityflow_settings&view=gravityflowfolders' ),
					),
				),
				array(
					'handle'  => 'gform_admin',
					'src'     => GFCommon::get_base_url() . "/css/admin{$min}.css",
					'version' => GFForms::$version,
					'enqueue' => array(
						array(
							'query' => 'page=gravityflow-folders',
						),
					),
				),
				array(
					'handle'  => 'gravityflowfolders_folders',
					'src'     => $this->get_base_url() . "/css/folders{$min}.css",
					'version' => GFForms::$version,
					'enqueue' => array(
						array(
							'query' => 'page=gravityflow-folders',
						),
					),
				),
				array(
					'handle'  => 'gravityflow_status',
					'src'     => gravity_flow()->get_base_url() . "/css/status{$min}.css",
					'version' => $this->_version,
					'enqueue' => array(
						array(
							'query' => 'page=gravityflow-folders&folder=_notempty_',
						),
					),
				),
			);

			return array_merge( parent::styles(), $styles );
		}


		/**
		 * Adds the fields for the app settings page.
		 *
		 * @since 1.0
		 * @since 1.3.1 Add save_callback ot sanitize the value.
		 *
		 * @return array
		 */
		public function app_settings_fields() {
			$settings   = parent::app_settings_fields();
			$settings[] = array(
				'title'  => esc_html__( 'Configuration', 'gravityflowfolders' ),
				'fields' => array(
					array(
						'name'          => 'folders',
						'label'         => esc_html__( 'Folders', 'gravityflowfolders' ),
						'type'          => 'folders',
						'save_callback' => array( $this, 'save_callback_folders' ),
					),
				),
			);

			return $settings;
		}


		/**
		 * Adds the Folders to the entry meta.
		 *
		 * @since 1.0
		 *
		 * @param array $entry_meta
		 * @param int   $form_id
		 *
		 * @return array
		 */
		public function get_entry_meta( $entry_meta, $form_id ) {
			$folders = $this->get_folders();
			foreach ( $folders as $folder ) {
				$meta_key                = $folder->get_meta_key();
				$entry_meta[ $meta_key ] = array(
					'label'             => $folder->get_name(),
					'is_numeric'        => true,
					'is_default_column' => false,
					'filter'            => array(
						'operators' => array( '>', '<' ),
					),
				);
			}

			return $entry_meta;
		}

		/**
		 * Returns the app settings.
		 *
		 *
		 * @since 1.0
		 *
		 * @return array
		 */
		public function get_folder_settings() {
			$settings        = $this->get_app_settings();
			$folder_settings = isset( $settings['folders'] ) ? $settings['folders'] : array();

			return $folder_settings;
		}

		/**
		 * Renders the folders setting.
		 *
		 * since 1.0
		 */
		public function settings_folders() {
			$hidden_field = array(
				'name'          => 'folders',
				'default_value' => '[]',
			);
			$this->settings_hidden( $hidden_field );
			?>
			<div id="gravityflowfolders-folders-settings-ui">
				<!-- placeholder for custom fields UI -->
			</div>
			<?php
		}

		/**
		 * Save callback function for the folders field.
		 *
		 * @since 1.3.1
         *
		 * @param array $field         The field object.
		 * @param array $field_setting The field setting.
		 *
		 * @return array
		 */
		public function save_callback_folders( $field, $field_setting ) {
			if ( ! is_array( $field_setting ) ) {
				return $field_setting;
			}

			foreach ( $field_setting as $key => $value ) {
				$field_setting[ $key ]['name'] = sanitize_text_field( $value['name'] );
			}

			return $field_setting;
		}

		/**
		 * Retrieves the setting for a specific field/input.
		 *
		 * @since 1.3.1
		 *
		 * @param string     $setting_name  The field or input name
		 * @param string     $default_value Optional. The default value
		 * @param bool|array $settings      Optional. THe settings array
		 *
		 * @return string|array
		 */
		public function get_setting( $setting_name, $default_value = '', $settings = false ) {
			$setting = parent::get_setting( $setting_name, $default_value, $settings );

			if ( $setting_name !== 'folders' ) {
				return $setting;
			}

			$setting = $this->save_callback_folders( array(), $setting );

			return $setting;
		}

		/**
		 * Adds the Folders menu item to the admin UI.
		 *
		 *
		 * @since 1.0
		 *
		 * @param $menu_items
		 *
		 * @return array
		 */
		public function menu_items( $menu_items ) {
			$folders_menu = array(
				'name'       => 'gravityflow-folders',
				'label'      => esc_html__( 'Folders', 'gravityflowfolders' ),
				'permission' => 'gravityflowfolders_folders',
				'callback'   => array( $this, 'folders' ),
			);

			$index = 3;

			$first_bit = array_slice( $menu_items, 0, $index, true );

			$last_bit = array_slice( $menu_items, $index, count( $menu_items ) - $index, true );

			$menu_items = array_merge( $first_bit, array( $folders_menu ), $last_bit );

			return $menu_items;
		}

		/**
		 * Adds folders to the Gravity Flow toolbar.
		 *
		 * @since 1.0
		 *
		 * @param $menu_items
		 *
		 * @return mixed
		 */
		public function toolbar_menu_items( $menu_items ) {

			$active_class     = 'gf_toolbar_active';
			$not_active_class = '';

			$menu_items['folders'] = array(
				'label'        => esc_html__( 'Folders', 'gravityflowfolders' ),
				'icon'         => '<i class="fa fa fa-folder-o fa-lg"></i>',
				'title'        => __( 'Folders', 'gravityflow' ),
				'url'          => '?page=gravityflow-folders',
				'menu_class'   => 'gf_form_toolbar_settings',
				'link_class'   => ( rgget( 'page' ) == 'gravityflow-folders' ) ? $active_class : $not_active_class,
				'capabilities' => 'gravityflowfolders_folders',
				'priority'     => 850,
			);

			return $menu_items;
		}

		/**
		 * Renders the folder page in the WordPress admin UI.
		 *
		 * @since 1.0
		 */
		public function folders() {
			$args = array(
				'display_header' => true,
			);
			$this->folders_page( $args );
		}

		/**
		 * Renders the folders page.
		 *
		 * @since 1.0
		 *
		 * @param $args
		 */
		public function folders_page( $args ) {
			$defaults = array(
				'display_header' => true,
				'breadcrumbs'    => true,
			);
			$args     = array_merge( $defaults, $args );
			?>
			<div class="wrap gf_entry_wrap gravityflow_workflow_wrap gravityflow_workflow_submit">
				<?php if ( $args['display_header'] ) : ?>
					<h2 class="gf_admin_page_title">
						<img width="45" height="22"
						     src="<?php echo gravity_flow()->get_base_url(); ?>/images/gravityflow-icon-blue-grad.svg"
						     style="margin-right:5px;"/>

						<span><?php esc_html_e( 'Folders', 'gravityflowfolders' ); ?></span>

					</h2>
					<?php
					$this->toolbar();
				endif;

				require_once( $this->get_base_path() . '/includes/class-folders-page.php' );
				Gravity_Flow_Folders_Page::render( $args );
				?>
			</div>
			<?php
		}

		/**
		 * Renders the Gravity Flow toolbar.
		 *
		 * @since 1.0
		 */
		public function toolbar() {
			gravity_flow()->toolbar();
		}

		/**
		 * Returns an array of folders for the given user.
		 *
		 * @since 1.0
		 *
		 * @param WP_User|null $user
		 *
		 * @return Gravity_Flow_Folder[]
		 */
		public function get_folders( WP_User $user = null ) {


			$folder_configs = $this->get_folder_settings();

			/**
			* Allow the list of folders to be customized.
			*
			* @since 1.3.1
			*
			* @param array              $folder_configs The set of folders and their respective configuration.
			*
			* @return array
			*/

			$folder_configs = apply_filters( 'gravityflowfolders_folders', $folder_configs );

			$folders = array();

			$folder = null;

			foreach ( $folder_configs as $folder_config ) {

				$folder = new Gravity_Flow_Folder_List( $folder_config, $user );

				if ( ! $user || $folder->user_has_permission( $user->ID ) ) {
					$folders[] = $folder;
				}
			}

			return $folders;
		}

		/**
		 * Get Folder by ID or Name.
		 *
		 * @since 1.0
		 *
		 * @param string $folder_id
		 * @param        WP_User @user
		 *
		 * @return bool|Gravity_Flow_Folder
		 */
		public function get_folder( $folder_id, WP_User $user = null ) {
			$folders = $this->get_folders( $user );

			foreach ( $folders as $folder ) {
				if ( $folder->get_id() == $folder_id || strtolower( $folder->get_name() ) == strtolower( $folder_id ) ) {
					return $folder;
				}
			}

			return false;
		}

		public static function get_entry_table_name() {
			return version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? GFFormsModel::get_lead_table_name() : GFFormsModel::get_entry_table_name();
		}

		public static function get_gravityforms_db_version() {

			if ( method_exists( 'GFFormsModel', 'get_database_version' ) ) {
				$db_version = GFFormsModel::get_database_version();
			} else {
				$db_version = GFForms::$version;
			}

			return $db_version;
		}

		/**
		 * Adds the Folders action item to the User actions.
		 *
		 * @since 1.0
		 *
		 * @param array $actions An array of action links to be displayed.
		 *                             Default 'Edit', 'Delete' for single site, and
		 *                             'Edit', 'Remove' for Multisite.
		 * @param WP_User $user_object WP_User object for the currently-listed user.
		 *
		 * @return array $actions
		 */
		public function filter_user_row_actions( $actions, $user_object ) {

			$url                             = admin_url( 'admin.php?page=gravityflow-folders&user_id=' . $user_object->ID );
			$url                             = esc_url_raw( $url );
			$new_actions['workflow_folders'] = "<a href='" . $url . "'>" . __( 'Folders', 'gravityflowfolders' ) . '</a>';

			return array_merge( $new_actions, $actions );
		}

		/**
		 * Renders the shortcode.
		 *
		 * @since 1.0
		 *
		 * @param $html
		 * @param $atts
		 *
		 * @return string
		 */
		public function shortcode( $html, $atts ) {

			$a = gravity_flow()->get_shortcode_atts( $atts );

			if ( $a['form_id'] > 0 ) {
				$a['form'] = $a['form_id'];
			}

			$a['folder'] = isset( $atts['folder'] ) ? $atts['folder'] : '';

			if ( rgget( 'view' ) ) {
				wp_enqueue_script( 'gravityflow_entry_detail' );
				$html .= $this->get_shortcode_folders_page_entry_detail( $a );
			} else {
				$html .= $this->get_shortcode_folders_page( $a );
			}

			return $html;
		}

		/**
		 * Returns the markup for the folders page.
		 *
		 * @since 1.0
		 *
		 * @param $a
		 *
		 * @return string
		 */
		public function get_shortcode_folders_page( $a ) {
			if ( ! class_exists( 'WP_Screen' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/screen.php' );
			}
			require_once( ABSPATH . 'wp-admin/includes/template.php' );

			$check_permissions = true;

			if ( $a['allow_anonymous'] || $a['display_all'] ) {
				$check_permissions = false;
			}

			$detail_base_url = add_query_arg( array( 'page' => 'gravityflow-inbox', 'view' => 'entry' ) );

			$args = array(
				'base_url'          => remove_query_arg( array(
					'entry-id',
					'form-id',
					'start-date',
					'end-date',
					'_wpnonce',
					'_wp_http_referer',
					'action',
					'action2',
					'o',
					'f',
					't',
					'v',
					'gravityflow-print-page-break',
					'gravityflow-print-timelines',
				) ),
				'detail_base_url'   => $detail_base_url,
				'display_header'    => false,
				'action_url'        => 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?",
				'field_ids'         => $a['fields'] ? explode( ',', $a['fields'] ) : array(),
				'display_all'       => true, // Display others' entries
				'id_column'         => $a['id_column'],
				'submitter_column'  => $a['submitter_column'],
				'step_column'       => $a['step_column'],
				'status_column'     => $a['status_column'],
				'last_updated'      => $a['last_updated'],
				'step_status'       => $a['step_status'],
				'workflow_info'     => $a['workflow_info'],
				'sidebar'           => $a['sidebar'],
				'check_permissions' => $check_permissions,
			);

			if ( ! empty( $a['form'] ) ) {
				$args['constraint_filters'] = array( 'form_id' => $a['form'] );
			}

			$folder = sanitize_text_field( rgget( 'folder' ) );

			if ( empty( $folder ) ) {
				$folder = rgar( $a, 'folder' );
			}

			$args['folder'] = $folder;

			if ( ! empty( $a['folder'] ) ) {
				$args['breadcrumbs'] = false;
			}

			wp_enqueue_script( 'gravityflow_status_list' );
			ob_start();
			$this->folders_page( $args );
			$html = ob_get_clean();

			return $html;
		}

		/**
		 * Returns the markup for the folders shortcode detail page.
		 *
		 * @since 1.2 Added support for the step_status, workflow_info, and sidebar shortcode attributes.
		 * @since 1.0
		 *
		 * @param $a
		 *
		 * @return string
		 */
		public function get_shortcode_folders_page_entry_detail( $a ) {

			ob_start();
			$check_permissions = true;

			if ( $a['allow_anonymous'] || $a['display_all'] ) {
				$check_permissions = false;
			}

			$args = array(
				'show_header'       => false,
				'detail_base_url'   => add_query_arg( array( 'page' => 'gravityflow-inbox', 'view' => 'entry' ) ),
				'check_permissions' => $check_permissions,
				'timeline'          => $a['timeline'],
				'step_status'       => $a['step_status'],
				'workflow_info'     => $a['workflow_info'],
				'sidebar'           => $a['sidebar'],
			);

			gravity_flow()->inbox_page( $args );
			$html = ob_get_clean();

			return $html;
		}

		/**
		 * Callback for the gravityflow_permission_granted_entry_detail filter.
		 *
		 * Grants access to the specified entry if the current user has permission to view the entry.
		 *
		 * @since 1.0
		 *
		 * @param $permission_granted
		 * @param $entry
		 * @param $form
		 * @param $current_step
		 *
		 * @return bool
		 */
		public function filter_gravityflow_permission_granted_entry_detail( $permission_granted, $entry, $form, $current_step ) {
			$this->log_debug( __METHOD__ . '(): starting. $permission_granted: ' . ( $permission_granted ? 'yes' : 'no' ) );
			if ( ! $permission_granted ) {
				if ( isset( $_GET['folder'] ) ) {
					$folder_id = sanitize_text_field( $_GET['folder'] );
					if ( ! empty( $entry[ 'workflow_folder_' . $folder_id ] ) ) {
						$folder = $this->get_folder( $folder_id );
						if ( $folder->user_has_permission() ) {
							$permission_granted = true;
							$this->log_debug( __METHOD__ . '(): User has permission to view entries in folder ID: ' . $folder->get_id() );
						}
					}
				} else {
					$folders = $this->get_folders();
					foreach ( $folders as $folder ) {
						if ( ! empty( $entry[ 'workflow_folder_' . $folder->get_id() ] ) ) {
							if ( $folder->user_has_permission() ) {
								$permission_granted = true;
								$this->log_debug( __METHOD__ . '(): User has permission to view entries in folder ID: ' . $folder->get_id() );
								break;
							}
						}
					}
				}
			}

			$this->log_debug( __METHOD__ . '(): ending. $permission_granted: ' . ( $permission_granted ? 'yes' : 'no' ) );

			return $permission_granted;
		}

		/**
		 * Returns user accounts as choices for settings.
		 *
		 * @since 1.0
		 *
		 * @return array
		 */
		public function get_users_as_choices() {
			$editable_roles = array_reverse( get_editable_roles() );

			$role_choices = array();
			foreach ( $editable_roles as $role => $details ) {
				$name           = translate_user_role( $details['name'] );
				$role_choices[] = array( 'value' => 'role|' . $role, 'label' => $name );
			}

			$args            = apply_filters( 'gravityflow_get_users_args', array( 'orderby' => 'display_name' ) );
			$accounts        = get_users( $args );
			$account_choices = array();
			foreach ( $accounts as $account ) {
				$account_choices[] = array( 'value' => 'user_id|' . $account->ID, 'label' => $account->display_name );
			}

			$choices = array(
				array(
					'label'   => __( 'Users', 'gravityflowfolders' ),
					'choices' => $account_choices,
				),
				array(
					'label'   => __( 'Roles', 'gravityflowfolders' ),
					'choices' => $role_choices,
				),
			);

			return $choices;
		}

		public function is_settings_page() {
			return is_admin() && rgget( 'page' ) == 'gravityflow_settings' && rgget( 'view' ) == 'gravityflowfolders';
		}

		/**
		 * Callback for the gravityflow_enqueue_frontend_scripts action.
		 *
		 * Adds the front end styles.
		 *
		 * @since 1.0
		 */
		public function action_gravityflow_enqueue_frontend_scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			wp_enqueue_style( 'gravityflowfolders_folders', $this->get_base_url() . "/css/folders{$min}.css", null, $this->_version );
		}

		/**
		 * Callback for the gravityflow_status_args filter.
		 *
		 * Adds the folder bulk actions to the status page.
		 *
		 * @since 1.0
		 *
		 * @param $args
		 *
		 * @return mixed
		 */
		public function filter_gravityflow_status_args( $args ) {
			$folders = $this->get_folders();
			if ( empty( $folders ) ) {
				return $args;
			}
			$folder_bulk_actions = array();
			foreach ( $folders as $folder ) {
				$folder_bulk_actions[ 'add_to_folder_' . $folder->get_id() ] = sprintf( esc_html__( 'Add to folder: %s', 'gravityflowfolders' ), $folder->get_name() );
			}
			$args['bulk_actions'] = $folder_bulk_actions;

			return $args;
		}

		/**
		 * Callback for the gravityflow_bulk_action_status_table filter.
		 *
		 * Fulfills the bulk action on the status page by adding entries to the folder.
		 *
		 * @since 1.0
		 *
		 * @param $feedback
		 * @param $bulk_action
		 * @param $entry_ids
		 * @param $args
		 *
		 * @return string
		 */
		public function filter_gravityflow_bulk_action_status_table( $feedback, $bulk_action, $entry_ids, $args ) {
			if ( strpos( $bulk_action, 'add_to_folder_' ) === false ) {
				return '';
			}

			$folder_id = str_replace( 'add_to_folder_', '', $bulk_action );

			$folder = $this->get_folder( $folder_id );

			if ( $folder ) {
				foreach ( $entry_ids as $entry_id ) {
					$folder->add_entry( $entry_id );
				}
			}

			$message = sprintf( esc_html__( 'Entries assigned to folder: %s.', 'gravityflowfolders' ), $folder->get_name() );

			return $message;
		}

		/**
		 * Callback for the gravityflow_admin_actions_workflow_detail filter.
		 *
		 * Adds the folder options for the admin actions on the workflow detail page.
		 *
		 * @since 1.0
		 *
		 * @param $admin_actions
		 * @param $current_step
		 * @param $steps
		 * @param $form
		 * @param $entry
		 *
		 * @return array
		 */
		public function filter_gravityflow_admin_actions_workflow_detail( $admin_actions, $current_step, $steps, $form, $entry ) {
			$folders = $this->get_folders();

			if ( empty( $folders ) ) {
				return $admin_actions;
			}
			$add_choices = $remove_choices = array();
			foreach ( $folders as $folder ) {
				$folder_id  = $folder->get_id();
				$folder_key = 'workflow_folder_' . $folder_id;
				if ( empty( $entry[ $folder_key ] ) ) {
					$add_choices[] = array(
						'label' => $folder->get_name(),
						'value' => 'folders_add|' . $folder_id,
					);
				}

				if ( isset( $entry[ $folder_key ] ) && $entry[ $folder_key ] > 0 ) {
					$remove_choices[] = array(
						'label' => $folder->get_name(),
						'value' => 'folders_remove|' . $folder_id,
					);
				}
			}

			$admin_actions[] = array(
				'label'   => esc_html__( 'Folders', 'gravityflowfolders' ),
				'choices' => array(
					array(
						'label'   => esc_html__( 'Add to folder', 'gravityflowfolders' ),
						'choices' => $add_choices,
					),
					array(
						'label'   => esc_html__( 'Remove from folder', 'gravityflowfolders' ),
						'choices' => $remove_choices,
					),
				),
			);

			return $admin_actions;

		}

		/**
		 * Process the entry detail admin actions for folders.
		 *
		 * @since 1.0
		 *
		 * @param $feedback
		 * @param $admin_action
		 * @param $form
		 * @param $entry
		 *
		 * @return bool|string|WP_Error
		 */
		public function filter_gravityflow_admin_action_feedback( $feedback, $admin_action, $form, $entry ) {
			if ( strpos( $admin_action, 'folders_' ) === 0 ) {
				list( $base_admin_action, $folder_id ) = rgexplode( '|', $admin_action, 2 );
				$folder = $this->get_folder( $folder_id );
				switch ( $base_admin_action ) {
					case 'folders_add' :
						$folder->add_entry( $entry['id'] );
						$feedback = sprintf( esc_html__( 'Entry added to folder: %s', 'gravityflowfolders' ), $folder->get_name() );
						break;
					case 'folders_remove' :
						$folder->remove_entry( $entry['id'] );
						$feedback = sprintf( esc_html__( 'Entry removed from folder: %s', 'gravityflowfolders' ), $folder->get_name() );
				}
				if ( $feedback ) {
					$user_id = get_current_user_id();
					gravity_flow()->add_timeline_note( $entry['id'], $feedback, $user_id );
				}
			}

			return $feedback;
		}

		/**
		 * Callback for the gravityflow_pre_restart_workflow action.
		 *
		 * Removes entries from all folders when the workflow is restarted.
		 *
		 * @param $entry
		 * @param $form
		 */
		public function action_gravityflow_pre_restart_workflow( $entry, $form ) {
			$folders = $this->get_folders();
			foreach ( $folders as $folder ) {
				$folder_meta_key = $folder->get_meta_key();
				if ( isset( $entry[ $folder_meta_key ] ) && $entry[ $folder_meta_key ] > 0 ) {
					$folder->remove_entry( $entry['id'] );
				}
			}
		}
	}
}
