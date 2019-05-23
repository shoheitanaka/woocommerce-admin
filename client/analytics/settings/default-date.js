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
	const change = query => {
		const queryString = stringify( query );
		// Update settings so navigating away from settings without a refresh reflects changes.
		wcSettings.wcAdminSettings.woocommerce_default_date_range = queryString;
		onChange( {
			target: {
				name: 'woocommerce_default_date_range',
				value: queryString,
			},
		} );
	};
	const query = parse( value.replace( /&amp;/g, '&' ) );
	return <DateRangeFilterPicker query={ query } onRangeSelect={ change } />;
};

export default DefaultDate;
