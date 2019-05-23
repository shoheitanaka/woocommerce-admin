/** @format */
/**
 * External dependencies
 */
import { parse, stringify } from 'qs';

/**
 * WooCommerce dependencies
 */
import { DateRangeFilterPicker } from '@woocommerce/components';

const DefaultDate = ( { value, onChange } ) => {
	const change = data => {
		onChange( {
			target: {
				name: 'woocommerce_default_date_range',
				value: stringify( data ),
			},
		} );
	};
	const query = parse( value );
	return <DateRangeFilterPicker query={ query } onRangeSelect={ change } />;
};

export default DefaultDate;
