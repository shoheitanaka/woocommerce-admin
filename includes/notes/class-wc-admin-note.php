<?php
/**
 * WooCommerce Admin (Dashboard) Notes.
 *
 * The WooCommerce admin notes class gets admin notes data from storage and checks validity.
 *
 * @package WooCommerce Admin/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Admin_Note class.
 */
class WC_Admin_Note extends WC_Data {

	// Note types.
	const E_WC_ADMIN_NOTE_ERROR         = 'error';   // used for presenting error conditions.
	const E_WC_ADMIN_NOTE_WARNING       = 'warning'; // used for presenting warning conditions.
	const E_WC_ADMIN_NOTE_UPDATE        = 'update';  // i.e. used when a new version is available.
	const E_WC_ADMIN_NOTE_INFORMATIONAL = 'info';    // used for presenting informational messages.

	// Note status codes.
	const E_WC_ADMIN_NOTE_UNACTIONED = 'unactioned'; // the note has not yet been actioned by a user.
	const E_WC_ADMIN_NOTE_ACTIONED   = 'actioned';   // the note has had its action completed by a user.
	const E_WC_ADMIN_NOTE_SNOOZED    = 'snoozed';    // the note has been snoozed by a user.

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'admin-note';

	/**
	 * Cache group.
	 *
	 * @var string
	 */
	protected $cache_group = 'admin-note';

	/**
	 * Note constructor. Loads note data.
	 *
	 * @param mixed $data Note data, object, or ID.
	 */
	public function __construct( $data = '' ) {
		// Set default data here to allow `content_data` to be an object.
		$this->data = array(
			'name'          => '-',
			'type'          => self::E_WC_ADMIN_NOTE_INFORMATIONAL,
			'locale'        => 'en_US',
			'title'         => '-',
			'content'       => '-',
			'icon'          => 'info',
			'content_data'  => new stdClass(),
			'status'        => self::E_WC_ADMIN_NOTE_UNACTIONED,
			'source'        => 'woocommerce',
			'date_created'  => '0000-00-00 00:00:00',
			'date_reminder' => '',
			'is_snoozable'  => false,
			'actions'       => array(),
		);

		parent::__construct( $data );

		if ( $data instanceof WC_Admin_Note ) {
			$this->set_id( absint( $data->get_id() ) );
		} elseif ( is_numeric( $data ) ) {
			$this->set_id( $data );
		} elseif ( is_object( $data ) && ! empty( $data->note_id ) ) {
			$this->set_id( $data->note_id );
			$this->set_props( (array) $data );
			$this->set_object_read( true );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'admin-note' );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	|
	| Methods for getting allowed types, statuses.
	|
	*/

	/**
	 * Get allowed types.
	 *
	 * @return array
	 */
	public static function get_allowed_types() {
		$allowed_types = array(
			self::E_WC_ADMIN_NOTE_ERROR,
			self::E_WC_ADMIN_NOTE_WARNING,
			self::E_WC_ADMIN_NOTE_UPDATE,
			self::E_WC_ADMIN_NOTE_INFORMATIONAL,
		);

		return apply_filters( 'woocommerce_admin_note_types', $allowed_types );
	}

	/**
	 * Get allowed statuses.
	 *
	 * @return array
	 */
	public static function get_allowed_statuses() {
		$allowed_statuses = array(
			self::E_WC_ADMIN_NOTE_ACTIONED,
			self::E_WC_ADMIN_NOTE_UNACTIONED,
			self::E_WC_ADMIN_NOTE_SNOOZED,
		);

		return apply_filters( 'woocommerce_admin_note_statuses', $allowed_statuses );
	}


	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the note object.
	|
	*/

	/**
	 * Get note name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_name( $context = 'view' ) {
		return $this->get_prop( 'name', $context );
	}

	/**
	 * Get note type.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get note locale.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_locale( $context = 'view' ) {
		return $this->get_prop( 'locale', $context );
	}

	/**
	 * Get note title.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_title( $context = 'view' ) {
		return $this->get_prop( 'title', $context );
	}

	/**
	 * Get note content.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_content( $context = 'view' ) {
		return $this->get_prop( 'content', $context );
	}

	/**
	 * Get note icon (Gridicon).
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_icon( $context = 'view' ) {
		return $this->get_prop( 'icon', $context );
	}

	/**
	 * Get note content data (i.e. values that would be needed for re-localization)
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return array
	 */
	public function get_content_data( $context = 'view' ) {
		return $this->get_prop( 'content_data', $context );
	}

	/**
	 * Get note status.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get note source.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_source( $context = 'view' ) {
		return $this->get_prop( 'source', $context );
	}

	/**
	 * Get date note was created.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/**
	 * Get date on which user should be reminded of the note (if any).
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_reminder( $context = 'view' ) {
		return $this->get_prop( 'date_reminder', $context );
	}

	/**
	 * Get note snoozability.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return bool   Whether or not the note can be snoozed.
	 */
	public function get_is_snoozable( $context = 'view' ) {
		return $this->get_prop( 'is_snoozable', $context );
	}

	/**
	 * Get actions on the note (if any).
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return array
	 */
	public function get_actions( $context = 'view' ) {
		return $this->get_prop( 'actions', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Methods for setting note data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	|
	*/

	/**
	 * Set note name.
	 *
	 * @param string $name Note name.
	 */
	public function set_name( $name ) {
		// Don't allow empty names.
		if ( empty( $name ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note name prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'name', $name );
	}

	/**
	 * Set note type.
	 *
	 * @param string $type Note type.
	 */
	public function set_type( $type ) {
		if ( empty( $type ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note type prop cannot be empty.', 'woocommerce-admin' ) );
		}

		if ( ! in_array( $type, self::get_allowed_types(), true ) ) {
			$this->error(
				'admin_note_invalid_data',
				sprintf(
					/* translators: %s: admin note type. */
					__( 'The admin note type prop (%s) is not one of the supported types.', 'woocommerce-admin' ),
					$type
				)
			);
		}

		$this->set_prop( 'type', $type );
	}

	/**
	 * Set note locale.
	 *
	 * @param string $locale Note locale.
	 */
	public function set_locale( $locale ) {
		if ( empty( $locale ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note locale prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'locale', $locale );
	}

	/**
	 * Set note title.
	 *
	 * @param string $title Note title.
	 */
	public function set_title( $title ) {
		if ( empty( $title ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note title prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'title', $title );
	}

	/**
	 * Set note content.
	 *
	 * @param string $content Note content.
	 */
	public function set_content( $content ) {
		$allowed_html = array(
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'a'      => array(
				'href'     => true,
				'rel'      => true,
				'name'     => true,
				'target'   => true,
				'download' => array(
					'valueless' => 'y',
				),
			),
			'p'      => array(),
		);

		$content = wp_kses( $content, $allowed_html );

		if ( empty( $content ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note content prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'content', $content );
	}

	/**
	 * Set note icon (Gridicon).
	 *
	 * @param string $icon Note icon.
	 */
	public function set_icon( $icon ) {
		if ( empty( $icon ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note icon prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'icon', $icon );
	}

	/**
	 * Set note data for potential re-localization.
	 *
	 * @todo Set a default empty array? https://github.com/woocommerce/woocommerce-admin/pull/1763#pullrequestreview-212442921.
	 * @param object $content_data Note data.
	 */
	public function set_content_data( $content_data ) {
		$allowed_type = false;

		// Make sure $content_data is stdClass Object or an array.
		if ( ! ( $content_data instanceof stdClass ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note content_data prop must be an instance of stdClass.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'content_data', $content_data );
	}

	/**
	 * Set note status.
	 *
	 * @param string $status Note status.
	 */
	public function set_status( $status ) {
		if ( empty( $status ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note status prop cannot be empty.', 'woocommerce-admin' ) );
		}

		if ( ! in_array( $status, self::get_allowed_statuses(), true ) ) {
			$this->error(
				'admin_note_invalid_data',
				sprintf(
					/* translators: %s: admin note status property. */
					__( 'The admin note status prop (%s) is not one of the supported statuses.', 'woocommerce-admin' ),
					$status
				)
			);
		}

		$this->set_prop( 'status', $status );
	}

	/**
	 * Set note source.
	 *
	 * @param string $source Note source.
	 */
	public function set_source( $source ) {
		if ( empty( $source ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note source prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_prop( 'source', $source );
	}

	/**
	 * Set date note was created. NULL is not allowed
	 *
	 * @param string|integer $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed.
	 */
	public function set_date_created( $date ) {
		if ( empty( $date ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note date prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$this->set_date_prop( 'date_created', $date );
	}

	/**
	 * Set date admin should be reminded of note. NULL IS allowed
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 */
	public function set_date_reminder( $date ) {
		$this->set_date_prop( 'date_reminder', $date );
	}

	/**
	 * Set note snoozability.
	 *
	 * @param bool $is_snoozable Whether or not the note can be snoozed.
	 */
	public function set_is_snoozable( $is_snoozable ) {
		return $this->set_prop( 'is_snoozable', $is_snoozable );
	}

	/**
	 * Clear actions from a note.
	 */
	public function clear_actions() {
		$this->set_prop( 'actions', array() );
	}

	/**
	 * Add an action to the note
	 *
	 * @param string  $name    Action name (not presented to user).
	 * @param string  $label   Action label (presented as button label).
	 * @param string  $url     Action URL, if navigation needed. Optional.
	 * @param string  $status  Status to transition parent Note to upon click. Defaults to 'actioned'.
	 * @param boolean $primary Whether or not this is the primary action. Defaults to false.
	 */
	public function add_action( $name, $label, $url = '', $status = self::E_WC_ADMIN_NOTE_ACTIONED, $primary = false ) {
		$name    = wc_clean( $name );
		$label   = wc_clean( $label );
		$query   = esc_url( $url );
		$status  = wc_clean( $status );
		$primary = (bool) $primary;

		if ( empty( $name ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note action name prop cannot be empty.', 'woocommerce-admin' ) );
		}

		if ( empty( $label ) ) {
			$this->error( 'admin_note_invalid_data', __( 'The admin note action label prop cannot be empty.', 'woocommerce-admin' ) );
		}

		$action = array(
			'name'    => $name,
			'label'   => $label,
			'query'   => $query,
			'status'  => $status,
			'primary' => $primary,
		);

		$note_actions   = $this->get_prop( 'actions', 'edit' );
		$note_actions[] = (object) $action;
		$this->set_prop( 'actions', $note_actions );
	}
}
