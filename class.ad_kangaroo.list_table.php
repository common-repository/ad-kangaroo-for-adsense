<?php
if ( ! class_exists( 'Ad_Kangaroo_Ads_List' ) ) {

	global $wp_version;

	if ( $wp_version <= 3.0 ) {
		return;
	}

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	class Ad_Kangaroo_Ads_List extends WP_List_Table {

		public $ad_kangaroo_table_data, $ad_kangaroo_table_adunits, $ad_kangaroo_adunit_positions, $ad_kangaroo_adunit_positions_pro;
		private $include_inactive_ads, $ad_kangaroo_options, $item_counter;

		function __construct( $options ) {
			$this->include_inactive_ads = ( ! isset( $options['include_inactive_ads'] ) ) ? 0 : $options['include_inactive_ads'];
			$this->item_counter = 0;
			parent::__construct( array(
				'singular'  => __( 'item', 'ad_kangaroo' ),
				'plural'    => __( 'items', 'ad_kangaroo' ),
				'ajax'      => false,
				)
			);
		}

		function get_columns() {
			$columns = array(
				'name'     => __( 'Name', 'ad_kangaroo' ),
				'code'     => __( 'Id', 'ad_kangaroo' ),
				'summary'  => __( 'Type / Size', 'ad_kangaroo' ),
				'status'   => __( 'Status', 'ad_kangaroo' ),
				'position' => __( 'Position', 'ad_kangaroo' )
			);
			if ( ! $this->ad_kangaroo_adunit_positions ) {
				unset( $columns['position'] );
			}
			return $columns;
		}

		function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';
			$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
			$result = strcasecmp( $a[$orderby], $b[$orderby] );
			return ( $order === 'asc' ) ? $result : -$result;
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'name'    => array( 'name',false ),
				'code'    => array( 'code',false ),
				'summary' => array( 'summary', false ),
				'status'  => array( 'status', false )
			);
			return $sortable_columns;
		}

		/**
		 * Add necessary css classes depending on item status
		 * @param     array     $item        The current item data.
		 * @return    void
		 */
		function single_row( $item ) {
			$row_class = isset( $item['status_value'] ) && 'INACTIVE' == $item['status_value'] ? 'ad_kangaroo_inactive' : '';
			if ( '1' != $this->include_inactive_ads ) {
				if ( isset( $item['status_value'] ) && 'INACTIVE' != $item['status_value'] ) {
					if ( $this->item_counter%2 == 0 ) {
						$row_class .= ( '' == $row_class ) ? 'ad_kangaroo_table_row_odd' : ' ad_kangaroo_table_row_odd';
					}
					$this->item_counter++;
				} elseif ( isset( $item['status_value'] ) && 'INACTIVE' == $item['status_value'] ) {
						$row_class .= ( '' == $row_class ) ? 'hidden' : ' hidden';
				}
			} else {
				if ( $this->item_counter%2 == 0 ) {
					$row_class .= ( '' == $row_class ) ? 'ad_kangaroo_table_row_odd' : ' ad_kangaroo_table_row_odd';
				}
				$this->item_counter++;
			}
			$row_class = ( '' != $row_class ) ? ' class="' . $row_class . '"' : '';
			echo "<tr{$row_class}>";
				$this->single_row_columns( $item );
			echo '</tr>';
		}

		function prepare_items() {
			global $ad_kangaroo_table_rows;
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$primary = 'name';
			$this->_column_headers = array( $columns, $hidden, $sortable, $primary );
			usort( $this->ad_kangaroo_table_data, array( &$this, 'usort_reorder' ) );
			$this->items = $this->ad_kangaroo_table_data;
		}

		function column_default( $item, $column_name ) {
			switch( $column_name ) {
				case 'cb':
				case 'name':
				case 'code':
				case 'summary':
				case 'status':
				case 'position':
					return $item[ $column_name ];
			default:
				return print_r( $item, true );
			}
		}

		function column_position( $item ) {
			$disabled = '';
			if ( count( $this->ad_kangaroo_table_adunits ) == 3 && !array_key_exists( $item['id'], $this->ad_kangaroo_table_adunits ) ) {
				$disabled = 'disabled="true"';
			}
			$ad_kangaroo_adunit_positions = is_array( $this->ad_kangaroo_adunit_positions ) ? $this->ad_kangaroo_adunit_positions : array();
			$ad_kangaroo_position = '';
			foreach ( $ad_kangaroo_adunit_positions as $value => $name ) {
				$ad_kangaroo_position .= sprintf( '<option value="%s" %s>%s</option>', $value, ( array_key_exists( $item['id'], $this->ad_kangaroo_table_adunits ) && $this->ad_kangaroo_table_adunits[ $item['id'] ]['position'] == $value ) ? 'selected="selected"' : '', $name );
			}
			return sprintf(
				'<select class="ad_kangaroo_adunit_position" data-id="%s" %s>%s</select>',
				$item['id'],
				$disabled,
				$ad_kangaroo_position
			);
		}
	}
}